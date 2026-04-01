<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Controller;
use App\Http\Requests\Match\StoreMatchRequest;
use App\Http\Requests\Match\UpdateMatchRequest;
use App\Models\Arena;
use App\Models\Athlete;
use App\Models\Discipline;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\Contest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * MatchController
 *
 * php artisan make:controller Admin/MatchController --resource
 *
 * Akses: admin (manage) | coach/judge/athlete (view)
 */
class MatchController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    //     $this->middleware('role:admin')->only(['store', 'update', 'destroy', 'generate', 'assignArena', 'assignSchedule']);
    //     $this->middleware('permission:view schedule')->only(['index', 'show']);
    // }
    use AuthorizesRequests;

    // ──────────────────────────────────────────────────────────────
    // INDEX – List semua pertandingan
    // ──────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        try {
            $matches = Contest::query()
                ->with([
                    'event:id,name',
                    'discipline:id,name,type,match_type',
                    'ageCategory:id,name',
                    'arena:id,name,location',
                    'athlete1:id,name,club,gender',
                    'athlete2:id,name,club,gender',
                    'result',
                ])
                ->when($request->event_id,       fn ($q, $v) => $q->where('event_id', $v))
                ->when($request->discipline_id,  fn ($q, $v) => $q->where('discipline_id', $v))
                ->when($request->age_category_id,fn ($q, $v) => $q->where('age_category_id', $v))
                ->when($request->arena_id,       fn ($q, $v) => $q->where('arena_id', $v))
                ->when($request->round,          fn ($q, $v) => $q->where('round', $v))
                ->when($request->status,         fn ($q, $v) => $q->where('status', $v))
                ->when($request->date,           fn ($q, $v) => $q->whereDate('match_date', $v))
                ->when($request->athlete_id,     fn ($q, $v) => $q->where('athlete1_id', $v)->orWhere('athlete2_id', $v))
                ->orderBy('match_date')
                ->orderBy('match_time')
                ->paginate($request->integer('per_page', 20));

            return response()->json(['status' => 'success', 'data' => $matches]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────

    public function show(Contest $match): JsonResponse
    {
        try {
            $this->authorize('view', $match);

            $match->load([
                'event:id,name,location',
                'discipline:id,name,type,match_type',
                'ageCategory:id,name',
                'arena:id,name,location',
                'athlete1',
                'athlete2',
                'scores.judge:id,name',
                'result.winner:id,name',
            ]);

            return response()->json(['status' => 'success', 'data' => $match]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // STORE – Buat pertandingan manual
    // ──────────────────────────────────────────────────────────────

    public function store(StoreMatchRequest $request): JsonResponse
    {
        try {
            $this->authorize('create', Contest::class);

            $match = DB::transaction(fn () => Contest::create($request->validated()));

            return response()->json([
                'status'  => 'success',
                'data'    => $match->load('athlete1:id,name', 'athlete2:id,name', 'arena:id,name'),
                'message' => 'Match created successfully.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE – Update pertandingan (status, arena, jadwal)
    // ──────────────────────────────────────────────────────────────

    public function update(UpdateMatchRequest $request, Contest $match): JsonResponse
    {
        try {
            $this->authorize('update', $match);

            if ($match->status === 'completed') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Cannot update a completed match.',
                ], 422);
            }

            $match->update($request->validated());

            return response()->json([
                'status'  => 'success',
                'data'    => $match->fresh(['athlete1:id,name', 'athlete2:id,name', 'arena:id,name']),
                'message' => 'Match updated.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────

    public function destroy(Contest $match): JsonResponse
    {
        try {
            $this->authorize('delete', $match);

            if (in_array($match->status, ['ongoing', 'completed'])) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Cannot delete an ongoing or completed match.',
                ], 422);
            }

            $match->scores()->delete();
            $match->delete();

            return response()->json(['status' => 'success', 'message' => 'Match deleted.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // GENERATE – Auto-generate bracket dari peserta terverifikasi
    // ──────────────────────────────────────────────────────────────

    /**
     * Generate pertandingan ronde pertama untuk sebuah event + discipline.
     * Peserta dipasangkan secara acak (shuffle).
     * Untuk solo performance: satu peserta = satu match (athlete2 null).
     */
    public function generate(Request $request, Event $event): JsonResponse
    {
        try {
            // $this->middleware('permission:manage matches');

            $request->validate([
                'discipline_id'   => ['required', 'exists:disciplines,id'],
                'age_category_id' => ['required', 'exists:age_categories,id'],
            ]);

            $discipline  = Discipline::findOrFail($request->discipline_id);
            $ageCategory = \App\Models\AgeCategory::findOrFail($request->age_category_id);

            // Ambil peserta yang sudah terverifikasi
            $participants = EventParticipant::where([
                'event_id'        => $event->id,
                'discipline_id'   => $discipline->id,
                'age_category_id' => $ageCategory->id,
                'status'          => 'verified',
            ])->with('athlete:id,name')->get();

            if ($participants->count() < 1) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'No verified participants found for this discipline and age category.',
                ], 422);
            }

            // Cek apakah sudah ada match untuk kombinasi ini
            $existingMatches = Contest::where([
                'event_id'        => $event->id,
                'discipline_id'   => $discipline->id,
                'age_category_id' => $ageCategory->id,
            ])->exists();

            if ($existingMatches) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Matches already generated for this combination.',
                ], 422);
            }

            $athletes = $participants->pluck('athlete')->shuffle();
            $created  = [];

            DB::transaction(function () use ($event, $discipline, $ageCategory, $athletes, &$created) {
                if ($discipline->isPerformance()) {
                    // Solo performance: satu match per atlet, athlete2 = null
                    foreach ($athletes as $idx => $athlete) {
                        $created[] = Contest::create([
                            'event_id'        => $event->id,
                            'discipline_id'   => $discipline->id,
                            'age_category_id' => $ageCategory->id,
                            'athlete1_id'     => $athlete->id,
                            'athlete2_id'     => null,
                            'round'           => 'pool',
                            'match_number'    => $idx + 1,
                            'status'          => 'scheduled',
                        ]);
                    }
                } else {
                    // Duel (Sanda/Sparring): pasangkan berdua
                    $chunks = $athletes->chunk(2);
                    $matchNum = 1;
                    foreach ($chunks as $pair) {
                        $pairArr = $pair->values();
                        $created[] = Contest::create([
                            'event_id'        => $event->id,
                            'discipline_id'   => $discipline->id,
                            'age_category_id' => $ageCategory->id,
                            'athlete1_id'     => $pairArr[0]->id,
                            'athlete2_id'     => $pairArr[1]->id ?? null, // bye jika ganjil
                            'round'           => 'quarter_final',
                            'match_number'    => $matchNum++,
                            'status'          => 'scheduled',
                        ]);
                    }
                }
            });

            return response()->json([
                'status'  => 'success',
                'data'    => $created,
                'message' => count($created) . " matches generated for [{$discipline->name}] [{$ageCategory->name}].",
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // ASSIGN ARENA – Set arena untuk pertandingan
    // ──────────────────────────────────────────────────────────────

    public function assignArena(Request $request, Contest $match): JsonResponse
    {
        try {
            $request->validate(['arena_id' => ['required', 'exists:arenas,id']]);

            $arena = Arena::findOrFail($request->arena_id);

            if (! $arena->is_active) {
                return response()->json(['status' => 'error', 'message' => 'Arena is not active.'], 422);
            }

            // Cek konflik jadwal arena (arena sama, waktu overlap)
            if ($match->match_date && $match->match_time) {
                $conflict = Contest::where('arena_id', $arena->id)
                    ->whereDate('match_date', $match->match_date)
                    ->where('match_time', $match->match_time)
                    ->where('id', '!=', $match->id)
                    ->exists();

                if ($conflict) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => "Arena [{$arena->name}] is already occupied at that time.",
                    ], 422);
                }
            }

            $match->update(['arena_id' => $arena->id]);

            return response()->json([
                'status'  => 'success',
                'data'    => $match->fresh('arena:id,name'),
                'message' => "Arena [{$arena->name}] assigned to match #{$match->match_number}.",
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // ASSIGN SCHEDULE – Set tanggal & waktu pertandingan
    // ──────────────────────────────────────────────────────────────

    public function assignSchedule(Request $request, Contest $match): JsonResponse
    {
        try {
            $request->validate([
                'match_date' => ['required', 'date'],
                'match_time' => ['required', 'date_format:H:i'],
            ]);

            // Cek konflik arena di slot waktu yang sama
            if ($match->arena_id) {
                $conflict = Contest::where('arena_id', $match->arena_id)
                    ->whereDate('match_date', $request->match_date)
                    ->where('match_time', $request->match_time)
                    ->where('id', '!=', $match->id)
                    ->where('status', '!=', 'cancelled')
                    ->exists();

                if ($conflict) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Schedule conflict: another match is already assigned to this arena at the same time.',
                    ], 422);
                }
            }

            $match->update([
                'match_date' => $request->match_date,
                'match_time' => $request->match_time,
            ]);

            return response()->json([
                'status'  => 'success',
                'data'    => $match->fresh(),
                'message' => 'Schedule assigned successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
