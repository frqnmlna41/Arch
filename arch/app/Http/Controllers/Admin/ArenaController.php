<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Arena\StoreArenaRequest;
use App\Http\Requests\Arena\UpdateArenaRequest;
use App\Models\Arena;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ArenaController
 *
 * php artisan make:controller Admin/ArenaController --resource
 *
 * Akses: admin only
 */
class ArenaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
        $this->middleware('permission:manage arenas');
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $arenas = Arena::query()
                ->withCount('matches')
                ->when($request->boolean('active'), fn ($q) => $q->where('is_active', true))
                ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
                ->latest()
                ->paginate($request->integer('per_page', 15));

            return response()->json(['status' => 'success', 'data' => $arenas]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function store(StoreArenaRequest $request): JsonResponse
    {
        try {
            $arena = Arena::create($request->validated());

            return response()->json([
                'status'  => 'success',
                'data'    => $arena,
                'message' => "Arena [{$arena->name}] created successfully.",
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function show(Arena $arena): JsonResponse
    {
        try {
            $arena->loadCount('matches');
            return response()->json(['status' => 'success', 'data' => $arena]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateArenaRequest $request, Arena $arena): JsonResponse
    {
        try {
            $arena->update($request->validated());
            return response()->json([
                'status'  => 'success',
                'data'    => $arena->fresh(),
                'message' => "Arena [{$arena->name}] updated.",
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Arena $arena): JsonResponse
    {
        try {
            // Jangan hapus jika masih ada match yang scheduled
            $hasScheduled = $arena->matches()->whereIn('status', ['scheduled', 'ongoing'])->exists();
            if ($hasScheduled) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Cannot delete arena [{$arena->name}]: it has scheduled or ongoing matches.",
                ], 422);
            }

            $name = $arena->name;
            $arena->delete();

            return response()->json(['status' => 'success', 'message' => "Arena [{$name}] deleted."]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
