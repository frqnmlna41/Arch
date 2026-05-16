<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiResponder;
use App\Http\Requests\Discipline\StoreDisciplineRequest;
use App\Http\Requests\Discipline\UpdateDisciplineRequest;
use App\Services\DisciplineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DisciplineController extends Controller
{
    use ApiResponder;

    public function __construct(private readonly DisciplineService $service) {}

    /**
     * GET /admin/disciplines
     * Filter: ?name=&sport_id=
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Discipline::class);

        try {
            $data = $this->service->getAll(
                name: $request->query('name'),
                sportId: $request->query('sport_id') ? (int) $request->query('sport_id') : null,
                perPage: (int) $request->query('per_page', 15),
            );

            return $this->success($data, 'Daftar disiplin berhasil diambil.');
        } catch (\Throwable $e) {
            Log::error('DisciplineController@index', ['error' => $e->getMessage()]);
            return $this->error('Gagal mengambil data disiplin.');
        }
    }

    /**
     * POST /admin/disciplines
     */
    public function store(StoreDisciplineRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Discipline::class);

        try {
            $discipline = $this->service->create($request->validated());
            return $this->created($discipline, 'Disiplin berhasil dibuat.');
        } catch (\Throwable $e) {
            Log::error('DisciplineController@store', ['error' => $e->getMessage()]);
            return $this->error('Gagal membuat disiplin.');
        }
    }

    /**
     * GET /admin/disciplines/{id}
     */
    public function show(int $id): JsonResponse
    {
        $this->authorize('view', \App\Models\Discipline::class);

        try {
            $discipline = $this->service->getById($id);
            return $this->success($discipline, 'Detail disiplin berhasil diambil.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Disiplin tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('DisciplineController@show', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal mengambil data disiplin.');
        }
    }

    /**
     * PUT /admin/disciplines/{id}
     */
    public function update(UpdateDisciplineRequest $request, int $id): JsonResponse
    {
        $this->authorize('update', \App\Models\Discipline::class);

        try {
            $discipline = $this->service->update($id, $request->validated());
            return $this->success($discipline, 'Disiplin berhasil diperbarui.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Disiplin tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('DisciplineController@update', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal memperbarui disiplin.');
        }
    }

    /**
     * DELETE /admin/disciplines/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete', \App\Models\Discipline::class);

        try {
            $this->service->delete($id);
            return $this->success(null, 'Disiplin berhasil dihapus.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Disiplin tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('DisciplineController@destroy', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal menghapus disiplin.');
        }
    }
}
