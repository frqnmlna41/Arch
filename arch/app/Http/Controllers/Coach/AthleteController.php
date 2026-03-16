<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAthleteRequest;
use App\Http\Requests\UpdateAthleteRequest;
use App\Models\Athlete;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AthleteController (Coach)
 *
 * Controller untuk coach mengelola atlet-atlet miliknya.
 *
 * Semua method menggunakan $this->authorize() untuk memastikan
 * hanya coach yang berhak yang bisa mengakses data.
 *
 * Artisan command:
 *   php artisan make:controller Coach/AthleteController --resource
 */
class AthleteController extends Controller
{
    /**
     * Pastikan seluruh controller hanya bisa diakses user yang sudah login
     * dan memiliki permission yang sesuai.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:coach');

        // Permission middleware per method
        $this->middleware('permission:view athletes')->only(['index', 'show']);
        $this->middleware('permission:create athletes')->only(['create', 'store']);
        $this->middleware('permission:update athletes')->only(['edit', 'update']);
        // Tidak ada destroy → coach tidak bisa hapus atlet
    }

    // ══════════════════════════════════════════════════════════════
    // INDEX – Daftar atlet milik coach ini
    // ══════════════════════════════════════════════════════════════

    /**
     * Menampilkan daftar atlet yang dikelola coach yang sedang login.
     *
     * authorize('viewAny', Athlete::class) → cek AthletePolicy::viewAny()
     */
    public function index(Request $request): View
    {
        // Policy check: apakah coach boleh melihat daftar atlet?
        $this->authorize('viewAny', Athlete::class);

        /** @var \App\Models\User $coach */
        $coach = $request->user();

        // Coach hanya melihat atlet miliknya (di-filter by coach_id)
        $athletes = Athlete::query()
            ->where('coach_id', $coach->id)
            ->with('eventParticipants.event')
            ->latest()
            ->paginate(15);

        return view('coach.athletes.index', compact('athletes'));
    }

    // ══════════════════════════════════════════════════════════════
    // SHOW – Detail satu atlet
    // ══════════════════════════════════════════════════════════════

    /**
     * authorize('view', $athlete) → cek AthletePolicy::view()
     * Coach hanya bisa lihat atlet yang coach_id === $coach->id
     */
    public function show(Athlete $athlete): View
    {
        // Policy check: apakah coach boleh melihat atlet ini?
        // AthletePolicy::view() akan verifikasi coach_id === auth()->id()
        $this->authorize('view', $athlete);

        $athlete->load([
            'eventParticipants.event',
            'eventParticipants.discipline',
            'scores',
            'winners.certificate',
        ]);

        return view('coach.athletes.show', compact('athlete'));
    }

    // ══════════════════════════════════════════════════════════════
    // CREATE – Form tambah atlet baru
    // ══════════════════════════════════════════════════════════════

    /**
     * authorize('create', Athlete::class) → cek AthletePolicy::create()
     */
    public function create(): View
    {
        $this->authorize('create', Athlete::class);

        return view('coach.athletes.create');
    }

    // ══════════════════════════════════════════════════════════════
    // STORE – Simpan atlet baru
    // ══════════════════════════════════════════════════════════════

    /**
     * Coach yang store otomatis menjadi coach_id dari atlet baru.
     */
    public function store(StoreAthleteRequest $request): RedirectResponse
    {
        $this->authorize('create', Athlete::class);

        $athlete = Athlete::create([
            ...$request->validated(),
            'coach_id' => $request->user()->id, // otomatis set coach_id
        ]);

        return redirect()
            ->route('coach.athletes.show', $athlete)
            ->with('success', "Atlet [{$athlete->name}] berhasil ditambahkan.");
    }

    // ══════════════════════════════════════════════════════════════
    // EDIT – Form edit atlet
    // ══════════════════════════════════════════════════════════════

    /**
     * authorize('update', $athlete) → AthletePolicy::update()
     * Hanya bisa edit atlet miliknya sendiri.
     */
    public function edit(Athlete $athlete): View
    {
        $this->authorize('update', $athlete);

        return view('coach.athletes.edit', compact('athlete'));
    }

    // ══════════════════════════════════════════════════════════════
    // UPDATE – Simpan perubahan atlet
    // ══════════════════════════════════════════════════════════════

    public function update(UpdateAthleteRequest $request, Athlete $athlete): RedirectResponse
    {
        // Double check: coach hanya bisa update atlet miliknya
        $this->authorize('update', $athlete);

        $athlete->update($request->validated());

        return redirect()
            ->route('coach.athletes.show', $athlete)
            ->with('success', "Data atlet [{$athlete->name}] berhasil diperbarui.");
    }

    // NOTE: Tidak ada method destroy() untuk coach.
    // Coach tidak boleh menghapus atlet.
    // Jika ada request ke route destroy, middleware 'role:coach' tidak akan
    // mengizinkan karena route destroy tidak didefinisikan untuk coach.
}
