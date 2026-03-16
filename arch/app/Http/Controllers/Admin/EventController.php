<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Athlete;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\Winner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * EventController (Admin)
 *
 * Controller admin untuk full CRUD event dan generate sertifikat.
 *
 * Semua route sudah dilindungi middleware 'role:admin' di routes/web.php.
 * Controller ini menambahkan lapisan authorize() untuk kejelasan eksplisit.
 *
 * Artisan command:
 *   php artisan make:controller Admin/EventController --resource
 */
class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
        $this->middleware('permission:manage events');
    }

    // ══════════════════════════════════════════════════════════════
    // INDEX – Daftar semua event
    // ══════════════════════════════════════════════════════════════

    public function index(): View
    {
        $this->authorize('viewAny', Event::class);

        $events = Event::withCount(['participants', 'matches'])
            ->with('creator')
            ->latest()
            ->paginate(15);

        return view('admin.events.index', compact('events'));
    }

    // ══════════════════════════════════════════════════════════════
    // CREATE – Form buat event baru
    // ══════════════════════════════════════════════════════════════

    public function create(): View
    {
        $this->authorize('create', Event::class);

        return view('admin.events.create');
    }

    // ══════════════════════════════════════════════════════════════
    // STORE – Simpan event baru
    // ══════════════════════════════════════════════════════════════

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $this->authorize('create', Event::class);

        $event = Event::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.events.show', $event)
            ->with('success', "Event [{$event->name}] berhasil dibuat.");
    }

    // ══════════════════════════════════════════════════════════════
    // SHOW – Detail event
    // ══════════════════════════════════════════════════════════════

    public function show(Event $event): View
    {
        $this->authorize('view', $event);

        $event->load([
            'participants.athlete',
            'participants.discipline',
            'matches.athlete1',
            'matches.athlete2',
            'matches.arena',
        ]);

        return view('admin.events.show', compact('event'));
    }

    // ══════════════════════════════════════════════════════════════
    // EDIT – Form edit event
    // ══════════════════════════════════════════════════════════════

    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        return view('admin.events.edit', compact('event'));
    }

    // ══════════════════════════════════════════════════════════════
    // UPDATE – Simpan perubahan event
    // ══════════════════════════════════════════════════════════════

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        $event->update($request->validated());

        return redirect()
            ->route('admin.events.show', $event)
            ->with('success', "Event [{$event->name}] berhasil diperbarui.");
    }

    // ══════════════════════════════════════════════════════════════
    // DESTROY – Hapus event (soft delete)
    // ══════════════════════════════════════════════════════════════

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        // Pastikan event tidak sedang berlangsung sebelum dihapus
        abort_if(
            $event->status === 'ongoing',
            403,
            'Event yang sedang berlangsung tidak dapat dihapus.'
        );

        $event->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('success', "Event [{$event->name}] berhasil dihapus.");
    }

    // ══════════════════════════════════════════════════════════════
    // GENERATE CERTIFICATES – Generate sertifikat untuk semua pemenang
    // ══════════════════════════════════════════════════════════════

    /**
     * Generate sertifikat untuk semua pemenang dalam event ini.
     *
     * Custom ability: 'generateCertificate' (bukan CRUD standar)
     * Cek: event harus sudah 'completed'.
     */
    public function generateCertificates(Request $request, Event $event): RedirectResponse
    {
        // Policy check dengan custom ability
        $this->authorize('generateCertificate', $event);

        // Event harus sudah selesai
        abort_if(
            $event->status !== 'completed',
            422,
            'Sertifikat hanya bisa digenerate setelah event selesai.'
        );

        $winners = Winner::where('event_id', $event->id)
            ->whereDoesntHave('certificate') // hanya yang belum punya sertifikat
            ->with('athlete')
            ->get();

        $generated = 0;
        foreach ($winners as $winner) {
            Certificate::create([
                'winner_id'          => $winner->id,
                'issued_by'          => $request->user()->id,
                'certificate_number' => Certificate::generateNumber(),
                'issued_at'          => now(),
                'template_version'   => '1.0',
            ]);
            $generated++;
        }

        return redirect()
            ->route('admin.events.show', $event)
            ->with('success', "{$generated} sertifikat berhasil digenerate.");
    }
}
