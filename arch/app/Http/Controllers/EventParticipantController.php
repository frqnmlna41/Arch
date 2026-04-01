<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventParticipant\RegisterParticipantRequest;
use App\Models\AgeCategory;
use App\Models\Athlete;
use App\Models\Discipline;
use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * EventParticipantController
 *
 * php artisan make:controller EventParticipantController
 *
 * Role yang bisa akses:
 *   admin  → manage semua participant (verify, reject, delete)
 *   coach  → daftarkan atlet miliknya ke event
 *   semua  → view list participant event
 */
class EventParticipantController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    // ──────────────────────────────────────────────────────────────
    // INDEX – List peserta per event
    // ──────────────────────────────────────────────────────────────

    public function index(Request $request, Event $event): JsonResponse
    {
        try {
            $this->authorize('view', $event);

            /** @var \App\Models\User $user */
            $user = $request->user();

            $query = $event->participants()
                ->with([
                    'athlete:id,name,gender,birth_date,club,photo',
                    'discipline:id,name,type,match_type',
                    'ageCategory:id,name,min_age,max_age',
                    'registeredBy:id,name',
                ]);

            // Coach hanya lihat atlet miliknya
            if ($user->hasRole('coach')) {
                $coachAthleteIds = $user->athletes()->pluck('id');
                $query->whereIn('athlete_id', $coachAthleteIds);
            }

            $participants = $query
                ->when($request->status,        fn ($q, $v) => $q->where('status', $v))
                ->when($request->discipline_id, fn ($q, $v) => $q->where('discipline_id', $v))
                ->latest()
                ->paginate($request->integer('per_page', 20));

            return response()->json(['status' => 'success', 'data' => $participants]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // STORE – Register atlet ke event
    // ──────────────────────────────────────────────────────────────

    public function store(RegisterParticipantRequest $request, Event $event): JsonResponse
    {
        try {
            // Coach: hanya bisa daftarkan atlet miliknya
            // Admin: bisa daftarkan siapapun
            $this->authorize('registerParticipant', $event);

            $athlete     = Athlete::findOrFail($request->athlete_id);
            $discipline  = Discipline::findOrFail($request->discipline_id);
            $ageCategory = AgeCategory::findOrFail($request->age_category_id);

            /** @var \App\Models\User $user */
            $user = $request->user();

            // Coach: validasi atlet miliknya
            if ($user->hasRole('coach') && $athlete->coach_id !== $user->id) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'You can only register athletes under your coaching.',
                ], 403);
            }

            // Validasi usia atlet sesuai age category
            if (! $ageCategory->coversAge($athlete->age)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Athlete age [{$athlete->age}] does not match category [{$ageCategory->name}] ({$ageCategory->ageRangeLabel}).",
                ], 422);
            }

            // Validasi age category tersedia untuk discipline ini
            $isAllowed = $discipline->ageCategories()->where('age_categories.id', $ageCategory->id)->exists();
            if (! $isAllowed) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Age category [{$ageCategory->name}] is not available for discipline [{$discipline->name}].",
                ], 422);
            }

            // Cek duplikat
            $exists = EventParticipant::where([
                'event_id'        => $event->id,
                'athlete_id'      => $athlete->id,
                'discipline_id'   => $discipline->id,
                'age_category_id' => $ageCategory->id,
            ])->exists();

            if ($exists) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Athlete [{$athlete->name}] is already registered for this discipline and age category.",
                ], 422);
            }

            $participant = DB::transaction(function () use ($event, $athlete, $discipline, $ageCategory, $request, $user) {
                return EventParticipant::create([
                    'event_id'               => $event->id,
                    'athlete_id'             => $athlete->id,
                    'discipline_id'          => $discipline->id,
                    'age_category_id'        => $ageCategory->id,
                    'registered_by'          => $user->id,
                    'registration_number'    => $this->generateRegNumber($event, $discipline),
                    'weight_at_registration' => $request->weight_at_registration,
                    'status'                 => 'pending',
                ]);
            });

            return response()->json([
                'status'  => 'success',
                'data'    => $participant->load('athlete:id,name', 'discipline:id,name', 'ageCategory:id,name'),
                'message' => "Athlete [{$athlete->name}] registered successfully. Awaiting verification.",
            ], 201);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // VERIFY – Admin verifikasi pendaftaran
    // ──────────────────────────────────────────────────────────────

    public function verify(Request $request, Event $event, EventParticipant $participant): JsonResponse
    {
        try {
            $this->middleware('role:admin');

            abort_if($participant->event_id !== $event->id, 404);

            if ($participant->status !== 'pending') {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Cannot verify participant with status [{$participant->status}].",
                ], 422);
            }

            $participant->verify($request->user());

            return response()->json([
                'status'  => 'success',
                'data'    => $participant->fresh(['athlete:id,name', 'verifiedBy:id,name']),
                'message' => "Participant [{$participant->registration_number}] verified.",
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // REJECT – Admin tolak pendaftaran
    // ──────────────────────────────────────────────────────────────

    public function reject(Request $request, Event $event, EventParticipant $participant): JsonResponse
    {
        try {
            $request->validate(['reason' => 'required|string|max:500']);

            abort_if($participant->event_id !== $event->id, 404);

            $participant->reject($request->reason);

            return response()->json([
                'status'  => 'success',
                'message' => "Participant [{$participant->registration_number}] rejected.",
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY – Hapus / tarik pendaftaran
    // ──────────────────────────────────────────────────────────────

    public function destroy(Event $event, EventParticipant $participant): JsonResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = request()->user();

            abort_if($participant->event_id !== $event->id, 404);

            // Coach hanya bisa tarik pendaftaran atlet miliknya
            if ($user->hasRole('coach')) {
                $coachAthleteIds = $user->athletes()->pluck('id');
                if (! $coachAthleteIds->contains($participant->athlete_id)) {
                    return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
                }
            }

            // Tidak bisa hapus jika sudah verified dan event sudah ongoing
            if ($participant->status === 'verified' && $event->status === 'ongoing') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Cannot remove participant from an ongoing event.',
                ], 422);
            }

            $number = $participant->registration_number;
            $participant->update(['status' => 'withdrawn']);

            return response()->json([
                'status'  => 'success',
                'message' => "Participant [{$number}] withdrawn.",
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // HELPER: Generate registration number
    // ──────────────────────────────────────────────────────────────

    private function generateRegNumber(Event $event, Discipline $discipline): string
    {
        $prefix = strtoupper(substr($discipline->sport->name ?? 'EV', 0, 2));
        $year   = now()->year;
        $count  = EventParticipant::where('event_id', $event->id)->count() + 1;

        return sprintf('%s-%d-%04d', $prefix, $year, $count);
    }
}
