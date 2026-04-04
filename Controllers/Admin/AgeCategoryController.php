<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgeCategory\StoreAgeCategoryRequest;
use App\Http\Requests\AgeCategory\UpdateAgeCategoryRequest;
use App\Models\AgeCategory;
use App\Models\Sport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * AgeCategoryController
 *
 * php artisan make:controller Admin/AgeCategoryController --resource
 *
 * Akses: admin only
 */
class AgeCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
        $this->middleware('permission:manage age categories');
    }

    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        try {
            $categories = AgeCategory::query()
                ->with('sport')
                ->when($request->sport_id, fn ($q, $v) => $q->where('sport_id', $v))
                ->when($request->boolean('active'), fn ($q) => $q->where('is_active', true))
                ->orderBy('sport_id')
                ->orderBy('min_age')
                ->paginate($request->integer('per_page', 20));

            return response()->json(['status' => 'success', 'data' => $categories]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────

    public function store(StoreAgeCategoryRequest $request): JsonResponse
    {
        try {
            $category = AgeCategory::create($request->validated());

            return response()->json([
                'status'  => 'success',
                'data'    => $category->load('sport'),
                'message' => "Age category [{$category->name}] created successfully.",
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────

    public function show(AgeCategory $ageCategory): JsonResponse
    {
        try {
            $ageCategory->load('sport', 'disciplines');

            return response()->json(['status' => 'success', 'data' => $ageCategory]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────

    public function update(UpdateAgeCategoryRequest $request, AgeCategory $ageCategory): JsonResponse
    {
        try {
            // Validasi: min_age harus lebih kecil dari max_age
            $minAge = $request->input('min_age', $ageCategory->min_age);
            $maxAge = $request->input('max_age', $ageCategory->max_age);

            if ($minAge >= $maxAge && $maxAge < 999) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'min_age must be less than max_age.',
                ], 422);
            }

            $ageCategory->update($request->validated());

            return response()->json([
                'status'  => 'success',
                'data'    => $ageCategory->fresh('sport'),
                'message' => "Age category [{$ageCategory->name}] updated.",
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────

    public function destroy(AgeCategory $ageCategory): JsonResponse
    {
        try {
            if ($ageCategory->eventParticipants()->exists()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Cannot delete: age category [{$ageCategory->name}] is used in event participants.",
                ], 422);
            }

            $name = $ageCategory->name;
            $ageCategory->disciplines()->detach();
            $ageCategory->delete();

            return response()->json(['status' => 'success', 'message' => "Age category [{$name}] deleted."]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
