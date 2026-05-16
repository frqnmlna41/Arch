<?php

namespace App\Services;

use App\Enums\RegistrationPaymentStatus;
use App\Enums\RegistrationStatus;
use App\Models\Registration;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrationService
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Registration::query()
            ->with([
                'athlete.perguruan',
                'eventCategory.event',
                'eventCategory.discipline',
                'user',
                'invoiceItem.invoice',
            ])
            ->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($filters['payment_status'] ?? null, fn($q, $v) => $q->where('payment_status', $v))
            ->when($filters['event_category_id'] ?? null, fn($q, $v) => $q->where('event_category_id', $v))
            ->when($filters['athlete_id'] ?? null, fn($q, $v) => $q->where('athlete_id', $v))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getById(int $id): Registration
    {
        return Registration::with([
            'athlete.perguruan',
            'athlete.discipline',
            'eventCategory.event',
            'eventCategory.discipline',
            'eventCategory.ageCategory',
            'user',
            'invoiceItem.invoice',
        ])->findOrFail($id);
    }

    /**
     * Create a new registration, enforcing the unique (athlete + event_category) constraint.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(array $data): Registration
    {
        return DB::transaction(function () use ($data) {
            $this->enforceUniqueRegistration(
                athleteId: $data['athlete_id'],
                eventCategoryId: $data['event_category_id'],
            );

            $data['user_id']        = $data['user_id'] ?? auth()->id();
            $data['status']         = RegistrationStatus::Pending->value;
            $data['payment_status'] = RegistrationPaymentStatus::Unpaid->value;
            $data['registered_at']  = now();

            return Registration::create($data);
        });
    }

    /**
     * Update registration (admin can change status/payment_status manually via this).
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(int $id, array $data): Registration
    {
        return DB::transaction(function () use ($id, $data) {
            $registration = Registration::findOrFail($id);
            $registration->update($data);
            return $registration->fresh(['athlete', 'eventCategory']);
        });
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete(int $id): void
    {
        DB::transaction(fn() => Registration::findOrFail($id)->delete());
    }

    /**
     * Approve a pending registration.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \LogicException
     */
    public function approve(int $id): Registration
    {
        return DB::transaction(function () use ($id) {
            $registration = Registration::findOrFail($id);

            if ($registration->status !== RegistrationStatus::Pending) {
                throw new \LogicException(
                    "Hanya registrasi berstatus 'pending' yang dapat disetujui. Status saat ini: {$registration->status->label()}."
                );
            }

            $registration->update([
                'status'      => RegistrationStatus::Approved,
                'approved_at' => now(),
            ]);

            return $registration->fresh(['athlete', 'eventCategory']);
        });
    }

    /**
     * Reject a pending registration with an optional reason.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \LogicException
     */
    public function reject(int $id, ?string $notes = null): Registration
    {
        return DB::transaction(function () use ($id, $notes) {
            $registration = Registration::findOrFail($id);

            if ($registration->status !== RegistrationStatus::Pending) {
                throw new \LogicException(
                    "Hanya registrasi berstatus 'pending' yang dapat ditolak. Status saat ini: {$registration->status->label()}."
                );
            }

            $registration->update([
                'status' => RegistrationStatus::Rejected,
                'notes'  => $notes ?? $registration->notes,
            ]);

            return $registration->fresh(['athlete', 'eventCategory']);
        });
    }

    /**
     * Enforce unique constraint: one athlete per event category.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function enforceUniqueRegistration(int $athleteId, int $eventCategoryId, ?int $excludeId = null): void
    {
        $exists = Registration::query()
            ->where('athlete_id', $athleteId)
            ->where('event_category_id', $eventCategoryId)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'athlete_id' => ['Atlet ini sudah terdaftar pada kategori event yang sama.'],
            ]);
        }
    }
}
