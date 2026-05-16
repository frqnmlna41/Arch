<?php

namespace App\Http\Controllers\Coach;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\Athlete\StoreAthleteRequest;
use App\Http\Requests\Athlete\UpdateAthleteRequest;
use App\Models\Athlete;
use App\Models\EventParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * AthleteController
 *
 * php artisan make:controller AthleteController --resource
 *
 * Multi-role controller:
 *   admin  → full CRUD semua atlet
 *   coach  → CRUD atlet miliknya saja
 *   athlete → view profil diri sendiri
 *   judge  → view daftar atlet
 *
 * Authorization dilakukan via AthletePolicy (di-register di AuthServiceProvider).
 */
class AthleteController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    //     $this->middleware('permission:view athletes')->only(['index', 'show']);
    //     $this->middleware('permission:create athletes')->only(['store']);
    //     $this->middleware('permission:update athletes')->only(['update']);
    //     $this->middleware('permission:manage athletes')->only(['destroy']);
    // }
use AuthorizesRequests;

    public function index(Request $request)
{
    $this->authorize('viewAny', Athlete::class);

    // /** @var \App\Models\User $user */
    $user = $request->user();

    $query = Athlete::query()
        ->with('coach:id,name')
        ->withCount('eventParticipants');

    // Coach hanya melihat atlet miliknya
    if ($user->hasRole('coach') && !$user->isAdmin()) {
    $query->where('coach_id', $user->id);
}

// Atau lebih eksplisit:
    if ($user->hasRole(['coach', 'perguruan']) && !$user->isAdmin()) {
        $query->where(function ($q) use ($user) {
            $q->where('coach_id', $user->id)
            ->orWhere('perguruan_id', $user->perguruan_id);
        });
}

    // Filter
    $query
        ->when($request->gender, fn ($q, $v) => $q->where('gender', $v))
        ->when($request->club, fn ($q, $v) => $q->where('club', 'like', "%{$v}%"))
        ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
        ->when($request->boolean('active'), fn ($q) => $q->where('is_active', true));

    // $athletes = $query->latest()->paginate($request->integer('per_page', 15));
    $athletes = Athlete::select('id','name','gender','weight','birth_date','is_active')
    ->latest()
    ->paginate(10);
    $participants = EventParticipant::with('athlete')
    ->latest()
    ->paginate(5);
    // ⬇️ INI YANG DIUBAH
    return view('coach.athletes.index', compact('athletes'));
}

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────

    public function show(Athlete $athlete)
    {
        $this->authorize('view', $athlete);

        $athlete->load([
            'coach:id,name,email',
            'eventParticipants.event:id,name,start_date',
            'eventParticipants.discipline:id,name',
            'winners.event:id,name',
            'winners.discipline:id,name',
            'winners.certificate:id,certificate_number,issued_at',
        ]);
        return view('coach.athletes.show', [
                'athlete' => $athlete
            ]);
        // return view('coach.athletes.show', compact('athletes'));
    }
    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────
    public function store(StoreAthleteRequest $request)
    {
    $this->authorize('create', Athlete::class);

    $data = $request->validated();

    return DB::transaction(function () use ($request, $data) {

        // Upload foto (temporary variable)
        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')
                ->store('athletes/photos', 'public');

            $data['photo'] = $photoPath;
        }

        // Assign coach
        if ($request->user()->hasRole('coach')) {
            $data['coach_id'] = $request->user()->id;
        }

        // CREATE ATHLETE
        $athlete = Athlete::create($data);

        // HANDLE DISCIPLINES
        if (!empty($request->disciplines)) {

            $syncData = [];

            foreach ($request->disciplines as $item) {

                $discipline = \App\Models\Discipline::findOrFail($item['discipline_id']);
                $age = \App\Models\AgeCategory::findOrFail($item['age_category_id']);
                $sport = \App\Models\Sport::findOrFail($discipline->sport_id);

                // 🔥 VALIDASI RELASI
                if ($discipline->sport_id !== $age->sport_id) {

                    // rollback file upload
                    if ($photoPath) {
                        Storage::disk('public')->delete($photoPath);
                    }

                    abort(422, 'Discipline dan Age Category tidak sesuai');
                }

                $syncData[$item['discipline_id']] = [
                    'age_category_id' => $item['age_category_id']
                ];
            }

            // 🔥 NO DUPLICATE
            $athlete->disciplines()->sync($syncData);
        }

        // 🔥 RESPONSE (WEB FRIENDLY)
        return redirect()
            ->route('coach.athletes.index')
            ->with('success', "Athlete {$athlete->name} berhasil dibuat");
    });
}
    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────

    public function update(UpdateAthleteRequest $request, Athlete $athlete): JsonResponse
    {
        try {
            // AthletePolicy::update() — coach hanya bisa edit atlet miliknya
            $this->authorize('update', $athlete);

            $data = $request->validated();

            // Handle upload foto baru
            if ($request->hasFile('photo')) {
                // Hapus foto lama
                if ($athlete->photo) {
                    Storage::disk('public')->delete($athlete->photo);
                }
                $data['photo'] = $request->file('photo')->store('athletes/photos', 'public');
            }

            $athlete->update($data);

            return response()->json([
                'status'  => 'success',
                'data'    => $athlete->fresh('coach:id,name'),
                'message' => "Athlete [{$athlete->name}] updated.",
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY (admin only via policy)
    // ──────────────────────────────────────────────────────────────

    public function destroy(Athlete $athlete): JsonResponse
    {
        try {
            // AthletePolicy::delete() — hanya admin (bypass via before())
            $this->authorize('delete', $athlete);

            // Cek apakah atlet masih terdaftar di pertandingan aktif
            $hasActiveMatch = $athlete->matchesAsAthlete1()
                ->orWhere('athlete2_id', $athlete->id)
                ->whereIn('status', ['scheduled', 'ongoing'])
                ->exists();

            if ($hasActiveMatch) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Cannot delete [{$athlete->name}]: athlete has active matches.",
                ], 422);
            }

            $name = $athlete->name;

            // Hapus foto dari storage
            if ($athlete->photo) {
                Storage::disk('public')->delete($athlete->photo);
            }

            $athlete->delete(); // soft delete

            return response()->json(['status' => 'success', 'message' => "Athlete [{$name}] deleted."]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
