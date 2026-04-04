<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * EventController
 *
 * php artisan make:controller Admin/EventController --resource
 *
 * Akses: admin only
 * Middleware: role:admin, permission:manage events
 */
class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
        $this->middleware('permission:manage events');
    }

    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        try {
            $events = Event::query()
                ->with('creator:id,name,email')
                ->withCount(['participants', 'matches'])
                ->when($request->status, fn ($q, $v) => $q->where('status', $v))
                ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
                ->when($request->year,   fn ($q, $v) => $q->whereYear('start_date', $v))
                ->latest('start_date')
                ->paginate($request->integer('per_page', 15));

            return response()->json(['status' => 'success', 'data' => $events]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────

    public function store(StoreEventRequest $request): JsonResponse
    {
        try {
            $this->authorize('create', Event::class);

            $event = DB::transaction(function () use ($request) {
                return Event::create([
                    ...$request->validated(),
                    'created_by' => $request->user()->id,
                ]);
            });

            return response()->json([
                'status'  => 'success',
                'data'    => $event->load('creator:id,name'),
                'message' => "Event [{$event->name}] created successfully.",
            ], 201);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────

    public function show(Event $event): JsonResponse
    {
        try {
            $this->authorize('view', $event);

            $event->load([
                'creator:id,name',
                'participants' => fn ($q) => $q->with('athlete:id,name', 'discipline:id,name'),
                'matches'      => fn ($q) => $q->with('athlete1:id,name', 'athlete2:id,name', 'arena:id,name'),
            ]);

            return response()->json(['status' => 'success', 'data' => $event]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────

    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        try {
            $this->authorize('update', $event);

            // Tidak bisa ubah event yang sudah completed
            if ($event->status === Event::STATUS_COMPLETED) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Cannot update a completed event.',
                ], 422);
            }

            $event->update($request->validated());

            return response()->json([
                'status'  => 'success',
                'data'    => $event->fresh('creator:id,name'),
                'message' => "Event [{$event->name}] updated.",
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────

    public function destroy(Event $event): JsonResponse
    {
        try {
            $this->authorize('delete', $event);

            if ($event->status === Event::STATUS_ONGOING) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Cannot delete an ongoing event.',
                ], 422);
            }

            $name = $event->name;
            $event->delete(); // soft delete

            return response()->json(['status' => 'success', 'message' => "Event [{$name}] deleted."]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // CHANGE STATUS
    // ──────────────────────────────────────────────────────────────

    public function changeStatus(Request $request, Event $event): JsonResponse
    {
        try {
            $this->authorize('update', $event);

            $request->validate([
                'status' => ['required', 'in:draft,published,ongoing,completed,cancelled'],
            ]);

            $event->update(['status' => $request->status]);

            return response()->json([
                'status'  => 'success',
                'message' => "Event status changed to [{$request->status}].",
                'data'    => $event->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
