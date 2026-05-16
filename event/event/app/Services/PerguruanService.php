<?php

namespace App\Services;

use App\Models\Perguruan;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PerguruanService
{
    public function getAll(?string $name = null, int $perPage = 15): LengthAwarePaginator
    {
        return Perguruan::query()
            ->when($name, fn($q) => $q->where('name', 'like', "%{$name}%"))
            ->withCount('athletes')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getById(int $id): Perguruan
    {
        return Perguruan::withCount('athletes')->findOrFail($id);
    }

    /**
     * Auto-generate slug from name if not provided.
     */
    public function create(array $data): Perguruan
    {
        return DB::transaction(function () use ($data) {
            $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

            return Perguruan::create($data);
        });
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(int $id, array $data): Perguruan
    {
        return DB::transaction(function () use ($id, $data) {
            $perguruan = Perguruan::findOrFail($id);

            if (isset($data['name']) && !isset($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $perguruan->update($data);
            return $perguruan->fresh();
        });
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete(int $id): void
    {
        DB::transaction(fn() => Perguruan::findOrFail($id)->delete());
    }
}
