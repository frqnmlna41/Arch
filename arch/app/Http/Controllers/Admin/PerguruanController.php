<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Perguruan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;

class PerguruanController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display list of pending perguruan registrations
     */
    public function index(Request $request): View
    {
        $query = User::with(['perguruan', 'roles'])
            ->role('perguruan')
            ->where('status', 'pending');

        $perguruans = $query->when($request->search, function ($q, $search) {
            return $q->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%" )
                      ->orWhere('email', 'like', "%{$search}%" );
            });
        })->latest('created_at')->paginate(15);

        return view('admin.perguruans.index', compact('perguruans'));
    }

    /**
     * Display perguruan detail (user + athletes)
     */
    public function show(User $user): View
    {
        $this->authorize('view', $user);

        if (!$user->hasRole('perguruan')) {
            abort(404);
        }

        $user->load(['perguruan', 'roles', 'athletes:id,name,perguruan_id,is_active,weight,gender,birth_date']);

        return view('admin.perguruans.show', compact('user'));
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

        // Update existing draft perguruan (created during registration)
        $perguruan = $user->perguruan;
        if (!$perguruan) {
            return response()->json([
                'status' => 'error',
                'message' => 'No draft perguruan found.',
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        DB::transaction(function () use ($user, $validated, $request, $perguruan) {
            if ($request->hasFile('logo')) {
                if ($perguruan->logo) {
                    Storage::disk('public')->delete($perguruan->logo);
                }
                $validated['logo'] = $request->file('logo')->store('perguruans', 'public');
            }

            $perguruan->update($validated);

            $user->update([
                'status' => 'active',
            ]);

            $user->assignRole('perguruan');
        });

        $user->fresh()->load('perguruan');

        return response()->json([
            'status' => 'success',
            'message' => "Perguruan '{$perguruan->name}' activated successfully!",
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

        // Log rejection reason (add reason column if needed, or use events)
        // User::where('id', $user->id)->update(['rejection_reason' => $request->reason]);

        return response()->json([
            'status' => 'success',
            'message' => "Registration rejected: {$request->reason}"
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
