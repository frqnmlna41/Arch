<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiResponder;
use App\Http\Requests\AgeCategory\StoreAgeCategoryRequest;
use App\Http\Requests\AgeCategory\UpdateAgeCategoryRequest;
use App\Services\AgeCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AgeCategoryController extends Controller
{
    use ApiResponder;

    public function __construct(private readonly AgeCategoryService $service) {}

    /**
     * GET /admin/age-categories
     * Filter: ?name=&sport_id=
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\AgeCategory::class);

        try {
            $data = $this->service->getAll(
                name: $request->query('name'),
                sportId: $request->query('sport_id') ? (int) $request->query('sport_id') : null,
                perPage: (int) $request->query('per_page', 15),
            );
            return $this->success($data, 'Daftar kategori usia berhasil diambil.');
        } catch (\Throwable $e) {
            Log::error('AgeCategoryController@index', ['error' => $e->getMessage()]);
            return $this->error('Gagal mengambil data kategori usia.');
        }
    }

    /**
     * POST /admin/age-categories
     */
    public function store(StoreAgeCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\AgeCategory::class);

        try {
            $ageCategory = $this->service->create($request->validated());
            return $this->created($ageCategory, 'Kategori usia berhasil dibuat.');
        } catch (\Throwable $e) {
            Log::error('AgeCategoryController@store', ['error' => $e->getMessage()]);
            return $this->error('Gagal membuat kategori usia.');
        }
    }

    /**
     * GET /admin/age-categories/{id}
     */
    public function show(int $id): JsonResponse
    {
        $this->authorize('view', \App\Models\AgeCategory::class);

        try {
            $ageCategory = $this->service->getById($id);
            return $this->success($ageCategory, 'Detail kategori usia berhasil diambil.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Kategori usia tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('AgeCategoryController@show', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal mengambil data kategori usia.');
        }
    }

    /**
     * PUT /admin/age-categories/{id}
     */
    public function update(UpdateAgeCategoryRequest $request, int $id): JsonResponse
    {
        $this->authorize('update', \App\Models\AgeCategory::class);

        try {
            $ageCategory = $this->service->update($id, $request->validated());
            return $this->success($ageCategory, 'Kategori usia berhasil diperbarui.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Kategori usia tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('AgeCategoryController@update', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal memperbarui kategori usia.');
        }
    }

    /**
     * DELETE /admin/age-categories/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete', \App\Models\AgeCategory::class);

        try {
            $this->service->delete($id);
            return $this->success(null, 'Kategori usia berhasil dihapus.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Kategori usia tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('AgeCategoryController@destroy', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal menghapus kategori usia.');
        }
    }
}
