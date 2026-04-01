<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Discipline\StoreDisciplineRequest;
use App\Http\Requests\Discipline\UpdateDisciplineRequest;
use App\Models\AgeCategory;
use App\Models\Discipline;
use App\Models\Sport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * DisciplineController
 *
 * php artisan make:controller Admin/DisciplineController --resource
 *
 * Akses: admin only
 * Middleware: role:admin, permission:manage disciplines
 */
class DisciplineController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware(['auth', 'role:admin']);
    //     $this->middleware('permission:manage disciplines');
    // }

    // ──────────────────────────────────────────────────────────────
    // INDEX – Daftar discipline (bisa difilter per sport)
    // ──────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        try {
            $disciplines = Discipline::query()
                ->with('sport')
                ->withCount('ageCategories')
                ->when($request->sport_id,   fn ($q, $v) => $q->where('sport_id', $v))
                ->when($request->type,        fn ($q, $v) => $q->where('type', $v))
                ->when($request->match_type,  fn ($q, $v) => $q->where('match_type', $v))
                ->when($request->search,      fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
                ->when($request->boolean('active'), fn ($q) => $q->where('is_active', true))
                ->latest()
                ->paginate($request->integer('per_page', 15));

            return response()->json(['status' => 'success', 'data' => $disciplines]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────

    public function store(StoreDisciplineRequest $request): JsonResponse
    {
        try {
            $discipline = Discipline::create($request->validated());

            // Attach age categories jika dikirimkan
            if ($request->has('age_category_ids')) {
                $discipline->ageCategories()->sync($request->age_category_ids);
            }

            return response()->json([
                'status'  => 'success',
                'data'    => $discipline->load('sport', 'ageCategories'),
                'message' => "Discipline [{$discipline->name}] created successfully.",
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────

    public function show(Discipline $discipline): JsonResponse
    {
        try {
            $discipline->load('sport', 'ageCategories');

            return response()->json(['status' => 'success', 'data' => $discipline]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────

    public function update(UpdateDisciplineRequest $request, Discipline $discipline): JsonResponse
    {
        try {
            $discipline->update($request->validated());

            // Sync age categories jika dikirim
            if ($request->has('age_category_ids')) {
                $discipline->ageCategories()->sync($request->age_category_ids);
            }

            return response()->json([
                'status'  => 'success',
                'data'    => $discipline->fresh(['sport', 'ageCategories']),
                'message' => "Discipline [{$discipline->name}] updated successfully.",
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────

    public function destroy(Discipline $discipline): JsonResponse
    {
        try {
            if ($discipline->matches()->exists()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Cannot delete: discipline [{$discipline->name}] has existing matches.",
                ], 422);
            }

            $name = $discipline->name;
            $discipline->ageCategories()->detach(); // bersihkan pivot
            $discipline->delete();

            return response()->json([
                'status'  => 'success',
                'message' => "Discipline [{$name}] deleted successfully.",
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
