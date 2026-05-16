<?php

namespace App\Services;

use App\Models\Sport;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SportService
{
    /**
     * Retrieve paginated list of sports with optional name filter.
     */
    public function getAll(?string $name = null, int $perPage = 15): LengthAwarePaginator
    {
        return Sport::query()
            ->with('disciplines')
            ->when($name, fn($q) => $q->where('name', 'like', "%{$name}%"))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find a single sport by ID, with disciplines eager-loaded.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getById(int $id): Sport
    {
        return Sport::with(['disciplines', 'ageCategories'])->findOrFail($id);
    }

    /**
     * Create a new sport inside a transaction.
     */
    public function create(array $data): Sport
    {
        return DB::transaction(function () use ($data) {
            return Sport::create($data);
        });
    }

    /**
     * Update an existing sport.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(int $id, array $data): Sport
    {
        return DB::transaction(function () use ($id, $data) {
            $sport = Sport::findOrFail($id);
            $sport->update($data);
            return $sport->fresh(['disciplines']);
        });
    }

    /**
     * Delete a sport (will cascade-validate if disciplines exist).
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $sport = Sport::findOrFail($id);
            $sport->delete();
        });
    }
}
