<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Perguruan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PerguruanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin');
        $this->middleware('permission:manage perguruans');
    }

    /**
     * Display list of pending perguruan registrations
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['perguruan', 'roles'])
            ->role('perguruan')
            ->where('status', 'pending')
            ->select('id', 'name', 'email', 'phone', 'status', 'created_at');

        $perguruans = $query->when($request->search, function ($q, $search) {
            return $q->where('name', 'like', "%{$search}%")
                     ->orWhere('email', 'like', "%{$search}%");
        })->latest('created_at')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $perguruans->through(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '-',
                    'perguruan_name' => $user->perguruan?->name ?? 'Not set',
                    'registered_at' => $user->created_at->format('d M Y H:i'),
                    'roles' => $user->roles->pluck('name'),
                ];
            }),
            'meta' => [
                'current_page' => $perguruans->currentPage(),
                'last_page' => $perguruans->lastPage(),
                'per_page' => $perguruans->perPage(),
                'total' => $perguruans->total(),
            ]
        ]);
    }

    /**
     * Display perguruan detail (user + athletes)
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        if (!$user->hasRole('perguruan')) {
            return response()->json(['status' => 'error', 'message' => 'Not a perguruan user'], 404);
        }

        $user->load(['perguruan', 'roles', 'athletes:id,name,perguruan_id,is_active,weight,gender,birth_date']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user,
                'athletes' => $user->athletes->map(function ($athlete) {
                    return [
                        'id' => $athlete->id,
                        'name' => $athlete->name,
                        'gender' => $athlete->gender,
                        'age' => $athlete->age,
                        'weight' => $athlete->weight,
                        'is_active' => $athlete->is_active,
                    ];
                })
            ]
        ]);
    }

    /**
     * Verify and activate perguruan registration
     */
    public function verify(Request $request, User $user): JsonResponse
    {
        $this->authorize('verify', $user);

        if ($user->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'User registration not pending.',
            ], 422);
        }

        $validated = $request->validate([
            'perguruan_name' => 'required|string|max:255|unique:perguruans,name',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
        ]);

        DB::transaction(function () use ($user, $validated) {
            $perguruan = Perguruan::create([
                'name' => $validated['perguruan_name'],
                'slug' => Str::slug($validated['perguruan_name']),
                'address' => $validated['address'],
                'phone' => $validated['phone'] ?? $user->phone,
                'email' => $user->email,
                'logo' => $request->file('logo') ? $request->file('logo')->store('perguruans', 'public') : null,
            ]);

            $user->update([
                'status' => 'active',
                'perguruan_id' => $perguruan->id,
            ]);
        });

        $user->fresh()->load('perguruan');

        return response()->json([
            'status' => 'success',
            'message' => "Perguruan '{$validated['perguruan_name']}' activated successfully!",
            'data' => $user
        ]);
    }

    /**
     * Reject perguruan registration
     */
    public function reject(Request $request, User $user): JsonResponse
    {
        $this->authorize('verify', $user);

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        if ($user->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'User registration not pending.',
            ], 422);
        }

        $user->update([
            'status' => 'rejected',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Registration rejected: {$request->reason}",
        ]);
    }

    /**
     * Update existing perguruan
     */
    public function update(Request $request, Perguruan $perguruan): JsonResponse
    {
        $this->authorize('update', $perguruan);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'is_active' => 'boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($perguruan->logo) {
                Storage::disk('public')->delete($perguruan->logo);
            }
            $validated['logo'] = $request->file('logo')->store('perguruans', 'public');
        }

        $perguruan->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Perguruan updated successfully.',
            'data' => $perguruan->fresh()
        ]);
    }

    /**
     * List all active perguruans (admin dashboard)
     */
    public function listActive(): JsonResponse
    {
        $perguruans = Perguruan::withCount('users', 'athletes')
            ->active()
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $perguruans
        ]);
    }
}

