<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiResponder;
use App\Http\Requests\Athlete\StoreAthleteRequest;
use App\Http\Requests\Athlete\UpdateAthleteRequest;
use App\Services\AthleteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AthleteController extends Controller
{
    use ApiResponder;

    public function __construct(private readonly AthleteService $service) {}
    /**
     * GET /admin/athletes
     * Filter: ?name=&gender=&perguruan_id=&is_active=
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Athlete::class);

        try {
            $data = $this->service->getAll(
                filters: $request->only(['name', 'gender', 'perguruan_id', 'is_active']),
                perPage: (int) $request->query('per_page', 15),
            );
            return $this->success($data, 'Daftar atlet berhasil diambil.');
        } catch (\Throwable $e) {
            Log::error('AthleteController@index', ['error' => $e->getMessage()]);
            return $this->error('Gagal mengambil data atlet.');
        }
    }

    /**
     * POST /admin/athletes
     */
    public function store(StoreAthleteRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Athlete::class);

        try {
            $athlete = $this->service->create($request->validated());
            return $this->created($athlete, 'Atlet berhasil didaftarkan.');
        } catch (\Throwable $e) {
            Log::error('AthleteController@store', ['error' => $e->getMessage()]);
            return $this->error('Gagal mendaftarkan atlet.');
        }
    }

    /**
     * GET /admin/athletes/{id}
     */
    public function show(int $id): JsonResponse
    {
        $this->authorize('view', \App\Models\Athlete::class);

        try {
            $athlete = $this->service->getById($id);
            return $this->success($athlete, 'Detail atlet berhasil diambil.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Atlet tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('AthleteController@show', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal mengambil data atlet.');
        }
    }

    /**
     * PUT /admin/athletes/{id}
     */
    public function update(UpdateAthleteRequest $request, int $id): JsonResponse
    {
        $this->authorize('update', \App\Models\Athlete::class);

        try {
            $athlete = $this->service->update($id, $request->validated());
            return $this->success($athlete, 'Data atlet berhasil diperbarui.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Atlet tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('AthleteController@update', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal memperbarui data atlet.');
        }
    }

    /**
     * DELETE /admin/athletes/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete', \App\Models\Athlete::class);

        try {
            $this->service->delete($id);
            return $this->success(null, 'Atlet berhasil dihapus.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Atlet tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('AthleteController@destroy', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal menghapus atlet.');
        }
    }
}
