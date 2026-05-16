<?php

namespace App\Services;

use App\Models\Athlete;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AthleteService
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Athlete::query()
            ->with(['perguruan', 'discipline', 'ageCategory', 'user'])
            ->when($filters['name'] ?? null, fn($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->when($filters['gender'] ?? null, fn($q, $v) => $q->where('gender', $v))
            ->when($filters['perguruan_id'] ?? null, fn($q, $v) => $q->where('perguruan_id', $v))
            ->when(
                isset($filters['is_active']),
                fn($q) => $q->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN))
            )
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getById(int $id): Athlete
    {
        return Athlete::with([
            'user',
            'coach',
            'perguruan',
            'discipline.sport',
            'ageCategory',
            'registrations.eventCategory.event',
        ])->findOrFail($id);
    }

    /**
     * Create athlete. user_id comes from admin input.
     * perguruan_id is nullable.
     */
    public function create(array $data): Athlete
    {
        return DB::transaction(fn() => Athlete::create($data));
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(int $id, array $data): Athlete
    {
        return DB::transaction(function () use ($id, $data) {
            $athlete = Athlete::findOrFail($id);
            $athlete->update($data);
            return $athlete->fresh(['perguruan', 'discipline', 'ageCategory']);
        });
    }

    /**
     * Soft-delete an athlete.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete(int $id): void
    {
        DB::transaction(fn() => Athlete::findOrFail($id)->delete());
    }
}
