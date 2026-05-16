<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiResponder;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    use ApiResponder;

    public function __construct(private readonly InvoiceService $service) {}

    /**
     * GET /admin/invoices
     * Filter: ?status=&user_id=
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\Invoice::class);

        try {
            $data = $this->service->getAll(
                filters: $request->only(['status', 'user_id']),
                perPage: (int) $request->query('per_page', 15),
            );
            return $this->success($data, 'Daftar invoice berhasil diambil.');
        } catch (\Throwable $e) {
            Log::error('InvoiceController@index', ['error' => $e->getMessage()]);
            return $this->error('Gagal mengambil data invoice.');
        }
    }

    /**
     * POST /admin/invoices
     * Standard manual creation of an invoice.
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Invoice::class);

        try {
            $invoice = $this->service->create($request->validated());
            return $this->created($invoice, 'Invoice berhasil dibuat.');
        } catch (\Throwable $e) {
            Log::error('InvoiceController@store', ['error' => $e->getMessage()]);
            return $this->error('Gagal membuat invoice.');
        }
    }

    /**
     * GET /admin/invoices/{id}
     */
    public function show(int $id): JsonResponse
    {
        $this->authorize('view', \App\Models\Invoice::class);

        try {
            $invoice = $this->service->getById($id);
            return $this->success($invoice, 'Detail invoice berhasil diambil.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Invoice tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('InvoiceController@show', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal mengambil data invoice.');
        }
    }

    /**
     * PUT /admin/invoices/{id}
     */
    public function update(UpdateInvoiceRequest $request, int $id): JsonResponse
    {
        $this->authorize('update', \App\Models\Invoice::class);

        try {
            $invoice = $this->service->update($id, $request->validated());
            return $this->success($invoice, 'Invoice berhasil diperbarui.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Invoice tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('InvoiceController@update', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal memperbarui invoice.');
        }
    }

    /**
     * DELETE /admin/invoices/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete', \App\Models\Invoice::class);

        try {
            $this->service->delete($id);
            return $this->success(null, 'Invoice berhasil dihapus.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound('Invoice tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('InvoiceController@destroy', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->error('Gagal menghapus invoice.');
        }
    }

    /**
     * POST /admin/invoices/generate
     * Generate an invoice from one or more approved registrations.
     *
     * Body: { "registration_ids": [1, 2, 3], "due_date": "2026-05-01", "notes": "..." }
     */
    public function generate(Request $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Invoice::class);

        $validated = $request->validate([
            'registration_ids'   => ['required', 'array', 'min:1'],
            'registration_ids.*' => ['integer', 'exists:registrations,id'],
            'due_date'           => ['nullable', 'date', 'after:today'],
            'notes'              => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $invoice = $this->service->generateFromRegistrations(
                registrationIds: $validated['registration_ids'],
                dueDate: $validated['due_date'] ?? null,
                notes: $validated['notes'] ?? null,
            );
            return $this->created($invoice, 'Invoice berhasil di-generate dari registrasi.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('InvoiceController@generate', ['error' => $e->getMessage()]);
            return $this->error('Gagal generate invoice.');
        }
    }
}
