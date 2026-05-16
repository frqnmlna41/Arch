<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiResponder;
use App\Http\Requests\Perguruan\StorePerguruanRequest;
use App\Http\Requests\Perguruan\UpdatePerguruanRequest;
use App\Services\PerguruanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PerguruanController extends Controller
{
    use ApiResponder;

    public function __construct(private readonly PerguruanService $service) {}

    /**
     * GET /admin/perguruans
     * Filter: ?name=
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Perguruan::class);

        try {
            $data = $this->service->getAll(
                name: $request->query('name'),
                perPage: (int) $request->query('per_page', 15),
            );
            return $this->success($data, 'Daftar perguruan berhasil diambil.');
        } catch (\Throwable $e) {
            Log::error('PerguruanController@index', ['error' => $e->getMessage()]);
            return $this->error('Gagal mengambil data perguruan.');
        }
    }

    /**
     * POST /admin/perguruans
     */
    public function store(StorePerguruanRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Perguruan::class);

        try {
            $perguruan = $this->service->create($request->validated());
            return $this->created($perguruan, 'Perguruan berhasil dibuat.');
        } catch (\Throwable $e) {
            Log::error('PerguruanController@store', ['error' => $e->getMessage()]);
            return $this->error('Gagal membuat perguruan.');
        }
    }

    /**
     * GET /admin/perguruans/{id}
     */
    public function show(int $id): JsonResponse
    {
        $this->authorize('view', \App\Models\Perguruan::class);

        try {
            $perguruan = $this->service->getById($id);
            return $this->success($perguruan, 'Detail perguruan berhasil diambil.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Perguruan tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('PerguruanController@show', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal mengambil data perguruan.');
        }
    }

    /**
     * PUT /admin/perguruans/{id}
     */
    public function update(UpdatePerguruanRequest $request, int $id): JsonResponse
    {
        $this->authorize('update', \App\Models\Perguruan::class);

        try {
            $perguruan = $this->service->update($id, $request->validated());
            return $this->success($perguruan, 'Perguruan berhasil diperbarui.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Perguruan tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('PerguruanController@update', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal memperbarui perguruan.');
        }
    }

    /**
     * DELETE /admin/perguruans/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete', \App\Models\Perguruan::class);

        try {
            $this->service->delete($id);
            return $this->success(null, 'Perguruan berhasil dihapus.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Perguruan tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('PerguruanController@destroy', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal menghapus perguruan.');
        }
    }
}
