<?php

namespace App\Services;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EventService
{
    public function getAll(?string $name = null, ?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        return Event::query()
            ->with(['eventCategories.discipline', 'eventCategories.ageCategory', 'creator'])
            ->when($name, fn($q) => $q->where('name', 'like', "%{$name}%"))
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getById(int $id): Event
    {
        return Event::with([
            'eventCategories.discipline.sport',
            'eventCategories.ageCategory',
            'creator',
        ])->findOrFail($id);
    }

    /**
     * Create event and auto-assign created_by from auth user.
     */
    public function create(array $data): Event
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = auth()->id();
            $data['status']     = $data['status'] ?? EventStatus::Draft->value;

            return Event::create($data);
        });
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(int $id, array $data): Event
    {
        return DB::transaction(function () use ($id, $data) {
            $event = Event::findOrFail($id);
            $event->update($data);
            return $event->fresh(['eventCategories', 'creator']);
        });
    }

    /**
     * Soft-delete an event.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            Event::findOrFail($id)->delete();
        });
    }
}
