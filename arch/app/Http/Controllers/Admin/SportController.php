<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sport\StoreSportRequest;
use App\Http\Requests\Sport\UpdateSportRequest;
use App\Models\Sport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SportController
 *
 * php artisan make:controller Admin/SportController --resource
 *
 * Akses: admin only
 * Middleware: role:admin (di route)
 */
class SportController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware(['auth', 'role:admin']);
    //     $this->middleware('permission:manage sports');
    // }

    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        try {
            $sports = Sport::query()
                ->withCount('disciplines')
                ->when($request->boolean('active'), fn ($q) => $q->where('is_active', true))
                ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
                ->latest()
                ->paginate($request->integer('per_page', 15));

            return response()->json([
                'status'  => 'success',
                'data'    => $sports,
                'message' => 'Sports retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve sports.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────

    public function store(StoreSportRequest $request): JsonResponse
    {
        try {
            $sport = Sport::create($request->validated());

            return response()->json([
                'status'  => 'success',
                'data'    => $sport,
                'message' => "Sport [{$sport->name}] created successfully.",
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to create sport.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────

    public function show(Sport $sport): JsonResponse
    {
        try {
            $sport->load('disciplines');

            return response()->json([
                'status' => 'success',
                'data'   => $sport,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────

    public function update(UpdateSportRequest $request, Sport $sport): JsonResponse
    {
        try {
            $sport->update($request->validated());

            return response()->json([
                'status'  => 'success',
                'data'    => $sport->fresh('disciplines'),
                'message' => "Sport [{$sport->name}] updated successfully.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to update sport.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────

    public function destroy(Sport $sport): JsonResponse
    {
        try {
            // Cek apakah masih ada discipline aktif
            if ($sport->disciplines()->exists()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Cannot delete sport [{$sport->name}]: it still has associated disciplines.",
                ], 422);
            }

            $name = $sport->name;
            $sport->delete();

            return response()->json([
                'status'  => 'success',
                'message' => "Sport [{$name}] deleted successfully.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to delete sport.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
