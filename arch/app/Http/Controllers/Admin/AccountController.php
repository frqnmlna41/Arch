<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{
    use AuthorizesRequests;

/**
 * Display list of coach accounts (pending for verification)
 */
public function index(Request $request)
{
    $query = User::role('coach');

    if ($request->search) {
        $query->where('name', 'like', '%' . $request->search . '%');
    }

    $coaches = $query->paginate(15);
    $Coach = User::role('coach');
    return view('admin.coaches.index', [
        'coaches' => $coaches,
        'activeCount' => $Coach->where('is_active', true)->count(),
        'inactiveCount' => $Coach->where('is_active', false)->count(),
    ]);
}

    /**
     * Display coach detail (account + athletes)
     */
    public function show(User $user): View
    {
        $this->authorize('view', $user);

        if (!$user->hasRole('coach')) {
            abort(404);
        }

        $user->load([
            'roles',
            'athletes:id,name,coach_id,perguruan_id,is_active,weight,gender,birth_date'
        ]);

        return view('admin.coaches.show', compact('user'));
    }

    /**
     * Verify and activate coach account
     */
    public function verify(Request $request, User $user): JsonResponse
    {
        Log::info('Verify coach attempt', ['user_id' => $user->id, 'status' => $user->status, 'actor_id' => auth()->id()]);

        try {
            $this->authorize('verify', $user);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Coach verify policy failed', ['user_id' => $user->id, 'actor' => auth()->id(), 'error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to verify this account.',
            ], 403);
        }

        if ($user->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Coach account not pending verification.',
            ], 422);
        }

        $user->update([
            'status' => 'active',
        ]);

        // $user->assignRole('coach');
        $user = $user->fresh()->load('roles');

        Log::info('Coach verified successfully', ['user_id' => $user->id, 'name' => $user->name]);

        return response()->json([
            'status' => 'success',
            'message' => "Coach '{$user->name}' activated successfully!",
            'data' => $user
        ]);
    }

    /**
     * Reject coach registration
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
                'message' => 'Coach account not pending verification.',
            ], 422);
        }

        $user->update([
            'status' => 'rejected',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Coach registration rejected: {$request->reason}",
        ]);
    }
}
