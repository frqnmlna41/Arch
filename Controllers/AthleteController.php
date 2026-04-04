<?php

namespace App\Http\Controllers;

use App\Http\Requests\Athlete\StoreAthleteRequest;
use App\Http\Requests\Athlete\UpdateAthleteRequest;
use App\Models\Athlete;
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
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view athletes')->only(['index', 'show']);
        $this->middleware('permission:create athletes')->only(['store']);
        $this->middleware('permission:update athletes')->only(['update']);
        $this->middleware('permission:manage athletes')->only(['destroy']);
    }

    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        try {
            $this->authorize('viewAny', Athlete::class);

            /** @var \App\Models\User $user */
            $user = $request->user();

            $query = Athlete::query()
                ->with('coach:id,name')
                ->withCount('eventParticipants');

            // Coach hanya melihat atlet miliknya
            if ($user->hasRole('coach')) {
                $query->where('coach_id', $user->id);
            }

            // Filter tambahan
            $query
                ->when($request->gender,  fn ($q, $v) => $q->where('gender', $v))
                ->when($request->club,    fn ($q, $v) => $q->where('club', 'like', "%{$v}%"))
                ->when($request->search,  fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
                ->when($request->boolean('active'), fn ($q) => $q->where('is_active', true));

            $athletes = $query->latest()->paginate($request->integer('per_page', 15));

            return response()->json(['status' => 'success', 'data' => $athletes]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────

    public function show(Athlete $athlete): JsonResponse
    {
        try {
            // AthletePolicy::view() — coach hanya atlet miliknya,
            // athlete hanya dirinya sendiri, judge bebas
            $this->authorize('view', $athlete);

            $athlete->load([
                'coach:id,name,email',
                'eventParticipants.event:id,name,start_date',
                'eventParticipants.discipline:id,name',
                'winners.event:id,name',
                'winners.discipline:id,name',
                'winners.certificate:id,certificate_number,issued_at',
            ]);

            // Tambahkan computed attribute
            $data = $athlete->toArray();
            $data['age']            = $athlete->age;
            $data['age_range_label'] = $athlete->ageRangeLabel ?? null;

            return response()->json(['status' => 'success', 'data' => $data]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────

    public function store(StoreAthleteRequest $request): JsonResponse
    {
        try {
            $this->authorize('create', Athlete::class);

            $data = $request->validated();

            // Upload foto jika ada
            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('athletes/photos', 'public');
            }

            // Coach otomatis menjadi coach_id
            if ($request->user()->hasRole('coach')) {
                $data['coach_id'] = $request->user()->id;
            }

            $athlete = DB::transaction(fn () => Athlete::create($data));

            return response()->json([
                'status'  => 'success',
                'data'    => $athlete->load('coach:id,name'),
                'message' => "Athlete [{$athlete->name}] created successfully.",
            ], 201);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
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
