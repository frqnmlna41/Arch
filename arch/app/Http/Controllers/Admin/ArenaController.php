<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Arena\StoreArenaRequest;
use App\Http\Requests\Arena\UpdateArenaRequest;
use App\Models\Arena;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArenaController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware(['auth', 'role:admin']);
    // }

    public function index(Request $request): View
    {
        $arenas = Arena::query()
            ->withCount('matches')
            ->when($request->boolean('active'), fn($q) => $q->where('is_active', true))
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return view('admin.arenas.index', compact('arenas'));
    }

    public function create(): View
    {
        return view('admin.arenas.create');
    }

    public function store(StoreArenaRequest $request): RedirectResponse
    {
        $arena = Arena::create($request->validated());

        return redirect()
            ->route('admin.arenas.index')
            ->with('success', "Arena [{$arena->name}] berhasil ditambahkan.");
    }

    public function show(Arena $arena): View
    {
        $arena->loadCount('matches');

        $recentMatches = $arena->matches()
            ->with(['eventCategory.discipline', 'eventCategory.ageCategory'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.arenas.show', compact('arena', 'recentMatches'));
    }

    public function edit(Arena $arena): View
    {
        return view('admin.arenas.edit', compact('arena'));
    }

    public function update(UpdateArenaRequest $request, Arena $arena): RedirectResponse
    {
        $arena->update($request->validated());

        return redirect()
            ->route('admin.arenas.index')
            ->with('success', "Arena [{$arena->name}] berhasil diupdate.");
    }

    public function destroy(Arena $arena): RedirectResponse
    {
        $hasScheduled = $arena->matches()
            ->whereIn('status', ['scheduled', 'ongoing'])
            ->exists();

        if ($hasScheduled) {
            return back()->with('error',
                "Arena [{$arena->name}] tidak bisa dihapus karena masih ada pertandingan aktif.");
        }

        $name = $arena->name;
        $arena->delete();

        return redirect()
            ->route('admin.arenas.index')
            ->with('success', "Arena [{$name}] berhasil dihapus.");
    }
}