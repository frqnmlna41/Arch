<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgeCategory\StoreAgeCategoryRequest;
use App\Http\Requests\AgeCategory\UpdateAgeCategoryRequest;
use App\Models\AgeCategory;
use App\Models\Sport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AgeCategoryController extends Controller
{
    use AuthorizesRequests;

    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $categories = AgeCategory::query()
            ->with('sport')
            ->withCount('disciplines')
            ->when($request->sport_id, fn ($q, $v) => $q->where('sport_id', $v))
            ->when($request->boolean('active'), fn ($q) => $q->where('is_active', true))
            ->orderBy('sport_id')
            ->orderBy('min_age')
            ->paginate($request->integer('per_page', 15));

        $sports = Sport::orderBy('name')->get();

        return view('admin.age-categories.index', compact('categories', 'sports'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────────

    public function create(): View
    {
        $sports = Sport::orderBy('name')->get();

        return view('admin.age-categories.create', compact('sports'));
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────

    public function store(StoreAgeCategoryRequest $request): RedirectResponse
    {
        $category = AgeCategory::create($request->validated());

        return redirect()
            ->route('admin.age-categories.index')
            ->with('success', "Age category [{$category->name}] created successfully.");
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────

    public function show(AgeCategory $ageCategory): View
    {
        $ageCategory->load('sport', 'disciplines');

        return view('admin.age-categories.show', compact('ageCategory'));
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────────

    public function edit(AgeCategory $ageCategory): View
    {
        $sports = Sport::orderBy('name')->get();

        return view('admin.age-categories.edit', compact('ageCategory', 'sports'));
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────

    public function update(UpdateAgeCategoryRequest $request, AgeCategory $ageCategory): RedirectResponse
    {
        $minAge = $request->input('min_age', $ageCategory->min_age);
        $maxAge = $request->input('max_age', $ageCategory->max_age);

        if ($minAge >= $maxAge && $maxAge < 999) {
            return back()
                ->withInput()
                ->withErrors(['min_age' => 'Min age must be less than max age.']);
        }

        $ageCategory->update($request->validated());

        return redirect()
            ->route('admin.age-categories.index')
            ->with('success', "Age category [{$ageCategory->name}] updated.");
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────

    public function destroy(AgeCategory $ageCategory): RedirectResponse
    {
        if ($ageCategory->eventParticipants()->exists()) {
            return back()->with(
                'error',
                "Cannot delete: age category [{$ageCategory->name}] is used in event participants."
            );
        }

        $name = $ageCategory->name;
        $ageCategory->disciplines()->detach();
        $ageCategory->delete();

        return redirect()
            ->route('admin.age-categories.index')
            ->with('success', "Age category [{$name}] deleted.");
    }
}
