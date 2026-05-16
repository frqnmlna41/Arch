<?php

namespace App\Services;

use App\Models\EventCategory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EventCategoryService
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return EventCategory::query()
            ->with(['event', 'sport', 'discipline', 'ageCategory'])
            ->when($filters['event_id'] ?? null, fn($q, $v) => $q->where('event_id', $v))
            ->when($filters['discipline_id'] ?? null, fn($q, $v) => $q->where('discipline_id', $v))
            ->when($filters['age_category_id'] ?? null, fn($q, $v) => $q->where('age_category_id', $v))
            ->when($filters['gender'] ?? null, fn($q, $v) => $q->where('gender', $v))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getById(int $id): EventCategory
    {
        return EventCategory::with([
            'event',
            'sport',
            'discipline.sport',
            'ageCategory',
            'registrations.athlete',
        ])->findOrFail($id);
    }

    /**
     * Create a new event category, enforcing the unique combination:
     * (event_id + discipline_id + age_category_id + gender + weight_class)
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(array $data): EventCategory
    {
        return DB::transaction(function () use ($data) {
            $this->enforceUniqueCombination($data);

            return EventCategory::create($data);
        });
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(int $id, array $data): EventCategory
    {
        return DB::transaction(function () use ($id, $data) {
            $category = EventCategory::findOrFail($id);

            $this->enforceUniqueCombination($data, excludeId: $id);

            $category->update($data);
            return $category->fresh(['event', 'discipline', 'ageCategory']);
        });
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete(int $id): void
    {
        DB::transaction(fn() => EventCategory::findOrFail($id)->delete());
    }

    /**
     * Enforce that the combination of event + discipline + age_category + gender + weight_class is unique.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function enforceUniqueCombination(array $data, ?int $excludeId = null): void
    {
        $query = EventCategory::query()
            ->where('event_id', $data['event_id'])
            ->where('discipline_id', $data['discipline_id'])
            ->where('age_category_id', $data['age_category_id'])
            ->where('gender', $data['gender'])
            ->where('weight_class', $data['weight_class'] ?? null);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'event_id' => [
                    'Kombinasi event, disiplin, kategori usia, gender, dan kelas berat sudah terdaftar.',
                ],
            ]);
        }
    }
}
