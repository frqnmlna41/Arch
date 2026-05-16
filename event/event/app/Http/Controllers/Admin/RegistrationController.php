<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiResponder;
use App\Http\Requests\Registration\StoreRegistrationRequest;
use App\Http\Requests\Registration\UpdateRegistrationRequest;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RegistrationController extends Controller
{
    use ApiResponder;

    public function __construct(private readonly RegistrationService $service) {}

    /**
     * GET /admin/registrations
     * Filter: ?status=&payment_status=&event_category_id=&athlete_id=
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Registration::class);

        try {
            $data = $this->service->getAll(
                filters: $request->only(['status', 'payment_status', 'event_category_id', 'athlete_id']),
                perPage: (int) $request->query('per_page', 15),
            );
            return $this->success($data, 'Daftar registrasi berhasil diambil.');
        } catch (\Throwable $e) {
            Log::error('RegistrationController@index', ['error' => $e->getMessage()]);
            return $this->error('Gagal mengambil data registrasi.');
        }
    }

    /**
     * POST /admin/registrations
     */
    public function store(StoreRegistrationRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Registration::class);

        try {
            $registration = $this->service->create($request->validated());
            return $this->created($registration, 'Registrasi berhasil dibuat.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('RegistrationController@store', ['error' => $e->getMessage()]);
            return $this->error('Gagal membuat registrasi.');
        }
    }

    /**
     * GET /admin/registrations/{id}
     */
    public function show(int $id): JsonResponse
    {
        $this->authorize('view', \App\Models\Registration::class);

        try {
            $registration = $this->service->getById($id);
            return $this->success($registration, 'Detail registrasi berhasil diambil.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Registrasi tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('RegistrationController@show', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal mengambil data registrasi.');
        }
    }

    /**
     * PUT /admin/registrations/{id}
     */
    public function update(UpdateRegistrationRequest $request, int $id): JsonResponse
    {
        $this->authorize('update', \App\Models\Registration::class);

        try {
            $registration = $this->service->update($id, $request->validated());
            return $this->success($registration, 'Registrasi berhasil diperbarui.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Registrasi tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('RegistrationController@update', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal memperbarui registrasi.');
        }
    }

    /**
     * DELETE /admin/registrations/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete', \App\Models\Registration::class);

        try {
            $this->service->delete($id);
            return $this->success(null, 'Registrasi berhasil dihapus.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Registrasi tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('RegistrationController@destroy', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal menghapus registrasi.');
        }
    }

    /**
     * PATCH /admin/registrations/{id}/approve
     */
    public function approve(int $id): JsonResponse
    {
        $this->authorize('update', \App\Models\Registration::class);

        try {
            $registration = $this->service->approve($id);
            return $this->success($registration, 'Registrasi berhasil disetujui.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Registrasi tidak ditemukan.');
        } catch (\LogicException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            Log::error('RegistrationController@approve', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal menyetujui registrasi.');
        }
    }

    /**
     * PATCH /admin/registrations/{id}/reject
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $this->authorize('update', \App\Models\Registration::class);

        $request->validate(['notes' => ['nullable', 'string', 'max:500']]);

        try {
            $registration = $this->service->reject($id, $request->input('notes'));
            return $this->success($registration, 'Registrasi berhasil ditolak.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Registrasi tidak ditemukan.');
        } catch (\LogicException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            Log::error('RegistrationController@reject', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal menolak registrasi.');
        }
    }
}
