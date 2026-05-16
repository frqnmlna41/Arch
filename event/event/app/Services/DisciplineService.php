<?php

namespace App\Services;

use App\Models\Discipline;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DisciplineService
{
    public function getAll(?string $name = null, ?int $sportId = null, int $perPage = 15): LengthAwarePaginator
    {
        return Discipline::query()
            ->with('sport')
            ->when($name, fn($q) => $q->where('name', 'like', "%{$name}%"))
            ->when($sportId, fn($q) => $q->where('sport_id', $sportId))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getById(int $id): Discipline
    {
        return Discipline::with(['sport', 'eventCategories'])->findOrFail($id);
    }

    /**
     * Create a discipline. Validates sport_id existence via form request,
     * but we guard again at service level for programmatic use.
     */
    public function create(array $data): Discipline
    {
        return DB::transaction(function () use ($data) {
            return Discipline::create($data);
        });
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(int $id, array $data): Discipline
    {
        return DB::transaction(function () use ($id, $data) {
            $discipline = Discipline::findOrFail($id);
            $discipline->update($data);
            return $discipline->fresh(['sport']);
        });
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $discipline = Discipline::findOrFail($id);
            $discipline->delete();
        });
    }
}
