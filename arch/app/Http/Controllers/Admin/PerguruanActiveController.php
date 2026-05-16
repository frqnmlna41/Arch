<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerguruanActiveController extends Controller
{
    /**
     * Display list of ACTIVE perguruan registrations
     */
    public function index(Request $request): View
    {
        $query = User::with(['perguruan', 'roles'])
            ->role('perguruan')
            ->where('status', 'active');  // ← ACTIVE ones!

        $perguruans = $query->when($request->search, function ($q, $search) {
            return $q->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            });
        })->latest('updated_at')->paginate(15);

        return view('admin.perguruans.active', compact('perguruans'));
    }
}

