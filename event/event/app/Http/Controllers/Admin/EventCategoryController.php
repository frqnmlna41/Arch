<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiResponder;
use App\Http\Requests\EventCategory\StoreEventCategoryRequest;
use App\Http\Requests\EventCategory\UpdateEventCategoryRequest;
use App\Services\EventCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EventCategoryController extends Controller
{
    use ApiResponder;

    public function __construct(private readonly EventCategoryService $service) {}

    /**
     * GET /admin/event-categories
     * Filter: ?name=&event_id=&discipline_id=&age_category_id=
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\EventCategory::class);

        try {
            $data = $this->service->getAll(
                filters: $request->only(['event_id', 'discipline_id', 'age_category_id', 'gender']),
                perPage: (int) $request->query('per_page', 15),
            );
            return $this->success($data, 'Daftar kategori event berhasil diambil.');
        } catch (\Throwable $e) {
            Log::error('EventCategoryController@index', ['error' => $e->getMessage()]);
            return $this->error('Gagal mengambil data kategori event.');
        }
    }

    /**
     * POST /admin/event-categories
     */
    public function store(StoreEventCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\EventCategory::class);

        try {
            $category = $this->service->create($request->validated());
            return $this->created($category, 'Kategori event berhasil dibuat.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('EventCategoryController@store', ['error' => $e->getMessage()]);
            return $this->error('Gagal membuat kategori event.');
        }
    }

    /**
     * GET /admin/event-categories/{id}
     */
    public function show(int $id): JsonResponse
    {
        $this->authorize('view', \App\Models\EventCategory::class);

        try {
            $category = $this->service->getById($id);
            return $this->success($category, 'Detail kategori event berhasil diambil.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Kategori event tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('EventCategoryController@show', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal mengambil data kategori event.');
        }
    }

    /**
     * PUT /admin/event-categories/{id}
     */
    public function update(UpdateEventCategoryRequest $request, int $id): JsonResponse
    {
        $this->authorize('update', \App\Models\EventCategory::class);

        try {
            $category = $this->service->update($id, $request->validated());
            return $this->success($category, 'Kategori event berhasil diperbarui.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Kategori event tidak ditemukan.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('EventCategoryController@update', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal memperbarui kategori event.');
        }
    }

    /**
     * DELETE /admin/event-categories/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete', \App\Models\EventCategory::class);

        try {
            $this->service->delete($id);
            return $this->success(null, 'Kategori event berhasil dihapus.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Kategori event tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('EventCategoryController@destroy', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal menghapus kategori event.');
        }
    }
}
