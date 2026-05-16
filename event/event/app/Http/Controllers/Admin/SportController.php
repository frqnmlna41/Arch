<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiResponder;
use App\Http\Requests\Sport\StoreSportRequest;
use App\Http\Requests\Sport\UpdateSportRequest;
use App\Services\SportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SportController extends Controller
{
    use ApiResponder;

    public function __construct(private readonly SportService $service) {}

    /**
     * GET /admin/sports
     * Filter: ?name=
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Sport::class);

        try {
            $data = $this->service->getAll(
                name: $request->query('name'),
                perPage: (int) $request->query('per_page', 15),
            );

            return $this->success($data, 'Daftar sport berhasil diambil.');
        } catch (\Throwable $e) {
            Log::error('SportController@index', ['error' => $e->getMessage()]);
            return $this->error('Gagal mengambil data sport.');
        }
    }

    /**
     * POST /admin/sports
     */
    public function store(StoreSportRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Sport::class);

        try {
            $sport = $this->service->create($request->validated());
            return $this->created($sport, 'Sport berhasil dibuat.');
        } catch (\Throwable $e) {
            Log::error('SportController@store', ['error' => $e->getMessage()]);
            return $this->error('Gagal membuat sport.');
        }
    }

    /**
     * GET /admin/sports/{id}
     */
    public function show(int $id): JsonResponse
    {
        $this->authorize('view', \App\Models\Sport::class);

        try {
            $sport = $this->service->getById($id);
            return $this->success($sport, 'Detail sport berhasil diambil.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Sport tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('SportController@show', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal mengambil data sport.');
        }
    }

    /**
     * PUT /admin/sports/{id}
     */
    public function update(UpdateSportRequest $request, int $id): JsonResponse
    {
        $this->authorize('update', \App\Models\Sport::class);

        try {
            $sport = $this->service->update($id, $request->validated());
            return $this->success($sport, 'Sport berhasil diperbarui.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Sport tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('SportController@update', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal memperbarui sport.');
        }
    }

    /**
     * DELETE /admin/sports/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete', \App\Models\Sport::class);

        try {
            $this->service->delete($id);
            return $this->success(null, 'Sport berhasil dihapus.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Sport tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('SportController@destroy', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal menghapus sport.');
        }
    }
}
