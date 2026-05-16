<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompetitionSession;
use App\Models\Contest;
use App\Models\EventCategory;
use App\Models\Registration;
use App\Models\Arena;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SessionController extends Controller
{
    /**
     * Daftar semua sesi (dikelompokkan per event_category + gender)
     */
    public function index(): View
    {
        $sessions = CompetitionSession::with([
                'eventCategory.discipline',
                'eventCategory.ageCategory',
                'contests',
            ])
            ->withCount('contests')
            ->orderBy('start_time')
            ->paginate(20);

        // Kategori yang belum punya sesi
        $unsessioned = EventCategory::with(['discipline', 'ageCategory'])
            ->whereDoesntHave('sessions')
            ->get();

        return view('admin.sessions.index', compact('sessions', 'unsessioned'));
    }

    /**
     * Form buat sesi baru
     */
    public function create(): View
    {
        $eventCategories = EventCategory::with(['discipline', 'ageCategory', 'arena'])->get();
        $arenas = Arena::orderBy('name')->get();

        return view('admin.sessions.create', compact('eventCategories', 'arenas'));
    }

    /**
     * Simpan sesi baru
     */
 public function store(Request $request): RedirectResponse
{
    $request->validate([
        'event_category_id'    => ['required', 'exists:event_categories,id'],
        'gender'               => ['required', 'in:male,female,mixed'],
        'arena_id'             => ['required', 'exists:arenas,id'],  // ← ganti lapangan → arena_id
        'start_time'           => ['required', 'date'],
        'duration_per_athlete' => ['required', 'integer', 'min:1', 'max:60'],
        'notes'                => ['nullable', 'string'],
    ]);

    $session = CompetitionSession::create([
        'event_category_id'    => $request->event_category_id,
        'gender'               => $request->gender,
        'arena_id'             => $request->arena_id,   // ← simpan arena_id
        'start_time'           => $request->start_time,
        'duration_per_athlete' => $request->duration_per_athlete,
        'notes'                => $request->notes,
        'status'               => 'draft',
    ]);

    $registrations = Registration::where('discipline_id',
            $session->eventCategory->discipline_id)
        ->where('age_category_id', $session->eventCategory->age_category_id)
        ->where('status', 'approved')
        ->whereHas('athlete', fn($q) => $q->where('gender', $session->gender))
        ->get();

    foreach ($registrations as $i => $reg) {
        Contest::firstOrCreate(
            [
                'event_category_id' => $session->event_category_id,
                'athlete_id'        => $reg->athlete_id,
            ],
            [
                'registration_id'        => $reg->id,
                'competition_session_id' => $session->id,
                'order_number'           => $i + 1,   // ← pakai order_number konsisten
                'status'                 => 'scheduled',
            ]
        );
    }

    return redirect()
        ->route('admin.sessions.show', $session)
        ->with('success', "Sesi berhasil dibuat dengan {$registrations->count()} atlet.");
}
    /**
     * Detail sesi + atur urutan atlet
     */
    public function show(CompetitionSession $session): View
    {
        $session->load([
            'eventCategory.discipline',
            'eventCategory.ageCategory',
            'contests.athlete.perguruan',
            'contests.score',
        ]);

        return view('admin.sessions.show', compact('session'));
    }

    /**
     * Simpan urutan atlet dalam sesi
     */
    public function updateOrder(Request $request, CompetitionSession $session): RedirectResponse
    {
    $request->validate([
        'contests'                  => ['required', 'array'],
        'contests.*.id'             => ['required', 'exists:contests,id'],
        'contests.*.order_number'   => ['required', 'integer', 'min:1'], // ← konsisten
    ]);

    foreach ($request->contests as $data) {
        Contest::where('id', $data['id'])
            ->where('competition_session_id', $session->id)
            ->update(['order_number' => $data['order_number']]);
    }

    return back()->with('success', 'Urutan atlet berhasil disimpan.');
    }
    public function edit(CompetitionSession $session): View
    {
        $eventCategories = EventCategory::with(['discipline', 'ageCategory'])->get();
        return view('admin.sessions.edit', compact('session', 'eventCategories'));
    }

    public function update(Request $request, CompetitionSession $session): RedirectResponse
    {
        $request->validate([
            'lapangan'             => ['required', 'string', 'max:50'],
            'start_time'           => ['required', 'date'],
            'duration_per_athlete' => ['required', 'integer', 'min:1', 'max:60'],
            'notes'                => ['nullable', 'string'],
        ]);

        $session->update($request->only([
            'lapangan', 'start_time', 'duration_per_athlete', 'notes',
        ]));

        return back()->with('success', 'Sesi berhasil diupdate.');
    }
    // Tambah di SessionController
public function generateAll(): RedirectResponse
{
    // Ambil semua registrasi approved, group by discipline+age_category+gender
    $groups = Registration::where('status', 'approved')
        ->with('athlete', 'discipline', 'ageCategory')
        ->get()
        ->groupBy(fn($r) =>
            $r->discipline_id . '-' .
            $r->age_category_id . '-' .
            $r->athlete->gender
        );

    $sessionCount = 0;
    $contestCount = 0;

    foreach ($groups as $key => $registrations) {
        $first         = $registrations->first();
        $disciplineId  = $first->discipline_id;
        $ageCategoryId = $first->age_category_id;
        $gender        = $first->athlete->gender;

        // Cari atau buat EventCategory
        $eventCategory = EventCategory::firstOrCreate([
            'discipline_id'   => $disciplineId,
            'age_category_id' => $ageCategoryId,
        ]);

        // Cari atau buat Session
        $session = CompetitionSession::firstOrCreate(
            [
                'event_category_id' => $eventCategory->id,
                'gender'            => $gender,
            ],
            [
                'lapangan'             => 'Belum diatur',
                'start_time'           => now()->startOfDay()->addHours(8),
                'duration_per_athlete' => 4,
                'status'               => 'draft',
            ]
        );

        $sessionCount++;

        // Generate contest slot per atlet
  foreach ($registrations as $i => $reg) {
    Contest::firstOrCreate(
        [
            'event_category_id' => $eventCategory->id,
            'athlete_id'        => $reg->athlete_id,
        ],
        [
            'registration_id'        => $reg->id,
            'competition_session_id' => $session->id,
            'order_number'           => $i + 1,   // ← ganti appearance_order → order_number
            'status'                 => 'scheduled',
        ]
    );
}
    }

    return redirect()
        ->route('admin.sessions.index')
        ->with('success', "{$sessionCount} sesi berhasil digenerate dengan total {$contestCount} atlet.");
}
}