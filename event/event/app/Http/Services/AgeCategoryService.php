<?php

namespace App\Services;

use App\Models\AgeCategory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AgeCategoryService
{
    public function getAll(?string $name = null, ?int $sportId = null, int $perPage = 15): LengthAwarePaginator
    {
        return AgeCategory::query()
            ->with('sport')
            ->when($name, fn($q) => $q->where('name', 'like', "%{$name}%"))
            ->when($sportId, fn($q) => $q->where('sport_id', $sportId))
            ->orderBy('min_age')
            ->paginate($perPage);
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getById(int $id): AgeCategory
    {
        return AgeCategory::with(['sport', 'eventCategories.event'])->findOrFail($id);
    }

    public function create(array $data): AgeCategory
    {
        return DB::transaction(fn() => AgeCategory::create($data));
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(int $id, array $data): AgeCategory
    {
        return DB::transaction(function () use ($id, $data) {
            $ageCategory = AgeCategory::findOrFail($id);
            $ageCategory->update($data);
            return $ageCategory->fresh(['sport']);
        });
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            AgeCategory::findOrFail($id)->delete();
        });
    }
}
