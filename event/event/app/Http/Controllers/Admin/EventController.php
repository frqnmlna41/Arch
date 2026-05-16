<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiResponder;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    use ApiResponder;

    public function __construct(private readonly EventService $service) {}

    /**
     * GET /admin/events
     * Filter: ?name=&status=
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Event::class);

        try {
            $data = $this->service->getAll(
                name: $request->query('name'),
                status: $request->query('status'),
                perPage: (int) $request->query('per_page', 15),
            );
            return $this->success($data, 'Daftar event berhasil diambil.');
        } catch (\Throwable $e) {
            Log::error('EventController@index', ['error' => $e->getMessage()]);
            return $this->error('Gagal mengambil data event.');
        }
    }

    /**
     * POST /admin/events
     */
    public function store(StoreEventRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Event::class);

        try {
            $event = $this->service->create($request->validated());
            return $this->created($event, 'Event berhasil dibuat.');
        } catch (\Throwable $e) {
            Log::error('EventController@store', ['error' => $e->getMessage()]);
            return $this->error('Gagal membuat event.');
        }
    }

    /**
     * GET /admin/events/{id}
     */
    public function show(int $id): JsonResponse
    {
        $this->authorize('view', \App\Models\Event::class);

        try {
            $event = $this->service->getById($id);
            return $this->success($event, 'Detail event berhasil diambil.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Event tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('EventController@show', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal mengambil data event.');
        }
    }

    /**
     * PUT /admin/events/{id}
     */
    public function update(UpdateEventRequest $request, int $id): JsonResponse
    {
        $this->authorize('update', \App\Models\Event::class);

        try {
            $event = $this->service->update($id, $request->validated());
            return $this->success($event, 'Event berhasil diperbarui.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Event tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('EventController@update', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal memperbarui event.');
        }
    }

    /**
     * DELETE /admin/events/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete', \App\Models\Event::class);

        try {
            $this->service->delete($id);
            return $this->success(null, 'Event berhasil dihapus.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Event tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('EventController@destroy', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal menghapus event.');
        }
    }
}
