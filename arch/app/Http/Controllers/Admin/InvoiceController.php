<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\Athlete;
use App\Models\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * InvoiceController
 *
 * Mengelola invoice biaya pendaftaran atlet ke event.
 *
 * Alur bisnis:
 *   1. Admin membuat invoice per coach
 *   2. Tiap baris (InvoiceItem) = 1 atlet × 1 event_category
 *   3. Coach bisa dikirim invoice → status: draft → sent → paid
 *   4. Admin konfirmasi pembayaran → status: paid
 *
 * Routes yang dibutuhkan (tambahkan ke routes/web.php):
 * ─────────────────────────────────────────────────────
 *   Route::prefix('admin/invoices')->name('admin.invoices.')->group(function () {
 *       Route::get('/',                    [InvoiceController::class, 'index'])->name('index');
 *       Route::get('/create',              [InvoiceController::class, 'create'])->name('create');
 *       Route::post('/',                   [InvoiceController::class, 'store'])->name('store');
 *       Route::get('/{invoice}',           [InvoiceController::class, 'show'])->name('show');
 *       Route::get('/{invoice}/edit',      [InvoiceController::class, 'edit'])->name('edit');
 *       Route::put('/{invoice}',           [InvoiceController::class, 'update'])->name('update');
 *       Route::delete('/{invoice}',        [InvoiceController::class, 'destroy'])->name('destroy');
 *       Route::post('/{invoice}/send',     [InvoiceController::class, 'send'])->name('send');
 *       Route::post('/{invoice}/pay',      [InvoiceController::class, 'markPaid'])->name('pay');
 *       Route::post('/{invoice}/cancel',   [InvoiceController::class, 'cancel'])->name('cancel');
 *       Route::post('/{invoice}/items',    [InvoiceController::class, 'addItem'])->name('items.add');
 *       Route::delete('/{invoice}/items/{item}', [InvoiceController::class, 'removeItem'])->name('items.remove');
 *   });
 */
class InvoiceController extends Controller
{
    // ═══════════════════════════════════════════════════════════
    // INDEX — Daftar semua invoice
    // ═══════════════════════════════════════════════════════════

    public function index(Request $request): View
    {
        $query = Invoice::with(['user', 'items'])
            ->withCount('items');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by coach
        if ($request->filled('coach_id')) {
            $query->where('coach_id', $request->coach_id);
        }

        // Search by invoice number
        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();

        // Data untuk filter dropdown
        $coaches = User::role('coach')->active()->orderBy('name')->get();

        // Summary stats untuk header cards
        $stats = [
            'total'   => Invoice::count(),
            'draft'   => Invoice::where('status', 'draft')->count(),
            'sent'    => Invoice::where('status', 'sent')->count(),
            'paid'    => Invoice::where('status', 'paid')->count(),
            'overdue' => Invoice::where('status', 'sent')
                                ->where('due_date', '<', now())
                                ->count(),
            'revenue' => Invoice::where('status', 'paid')->sum('total_amount'),
        ];

        return view('admin.invoices.index', compact('invoices', 'coaches', 'stats'));
    }

    // ═══════════════════════════════════════════════════════════
    // CREATE — Form invoice baru
    // ═══════════════════════════════════════════════════════════

    public function create(Request $request): View
    {
        // Jika coach_id di-pass via query string, pre-fill form
        $selectedCoach = null;
        $athletes      = collect();
        $eventCategories = collect();

        if ($request->filled('coach_id')) {
            $selectedCoach = User::role('coach')->findOrFail($request->coach_id);

            // Ambil atlet milik coach ini yang sudah verified di event manapun
            $athletes = Athlete::where('coach_id', $selectedCoach->id)
                ->active()
                ->with(['eventParticipants.eventCategory.event', 'perguruan'])
                ->get();

            // Ambil semua event_categories yang relevan (untuk item selection)
            $eventCategories = EventCategory::with(['event', 'discipline', 'ageCategory'])
                ->whereHas('eventParticipants', fn($q) =>
                    $q->where('status', 'verified')
                      ->whereIn('athlete_id', $athletes->pluck('id'))
                )
                ->get();
        }

        $coaches = User::role('coach')->active()->orderBy('name')->get();

        return view('admin.invoices.create', compact(
            'coaches',
            'selectedCoach',
            'athletes',
            'eventCategories'
        ));
    }

    // ═══════════════════════════════════════════════════════════
    // STORE — Simpan invoice baru
    // ═══════════════════════════════════════════════════════════

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'coach_id'  => ['required', 'exists:users,id'],
            'due_date'  => ['required', 'date', 'after:today'],
            'notes'     => ['nullable', 'string', 'max:1000'],

            // Items: array of [athlete_id, event_category_id, price]
            'items'                          => ['required', 'array', 'min:1'],
            'items.*.athlete_id'             => ['required', 'exists:athletes,id'],
            'items.*.event_category_id'      => ['required', 'exists:event_categories,id'],
            'items.*.price'                  => ['required', 'numeric', 'min:0'],
        ]);

        // Pastikan coach ada dan punya role coach
        $coach = User::role('coach')->findOrFail($validated['coach_id']);

        // Validasi: semua atlet harus milik coach ini
        $athleteIds = collect($validated['items'])->pluck('athlete_id')->unique();
        $invalidAthletes = Athlete::whereIn('id', $athleteIds)
            ->where('coach_id', '!=', $coach->id)
            ->exists();

        if ($invalidAthletes) {
            return back()
                ->withInput()
                ->withErrors(['items' => 'Beberapa atlet tidak terdaftar di bawah coach ini.']);
        }

        // Buat invoice + items dalam satu transaksi
        $invoice = DB::transaction(function () use ($validated, $coach) {
            $invoice = Invoice::create([
                'user_id'       => $coach->id,
                'invoice_number' => Invoice::generateNumber(),
                'total_amount'   => 0, // dihitung setelah items dibuat
                'status'         => 'draft',
                'due_date'       => $validated['due_date'],
                'notes'          => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id'        => $invoice->id,
                    'athlete_id'        => $item['athlete_id'],
                    'event_category_id' => $item['event_category_id'],
                    'discipline_id'     => EventCategory::find($item['event_category_id'])->discipline_id,
                    'price'             => $item['price'],
                ]);
            }

            // Hitung total dari items yang baru dibuat
            $invoice->recalculateTotal();

            return $invoice;
        });

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} berhasil dibuat.");
    }

    // ═══════════════════════════════════════════════════════════
    // SHOW — Detail invoice
    // ═══════════════════════════════════════════════════════════

    public function show(Invoice $invoice): View
    {
        $invoice->load([
            'user',
            'items.athlete.perguruan',
            'items.discipline.sport',
            'items.eventCategory.event',
            'items.eventCategory.ageCategory',
        ]);
        return view('admin.invoices.show', compact('invoice'));
    }

    // ═══════════════════════════════════════════════════════════
    // EDIT — Form edit invoice (hanya draft)
    // ═══════════════════════════════════════════════════════════

    public function edit(Invoice $invoice): View
    {
        // Invoice yang sudah sent/paid tidak bisa diedit
        abort_if(
            ! in_array($invoice->status, ['draft']),
            403,
            'Invoice yang sudah dikirim atau dibayar tidak dapat diedit.'
        );

        $invoice->load([
            'user',
            'items.athlete',
            'items.eventCategory.event',
            'items.discipline',
        ]);

        // Atlet milik coach ini untuk tambah item baru
        $athletes = Athlete::where('coach_id', $invoice->coach_id)
            ->active()
            ->with(['eventParticipants.eventCategory.event'])
            ->get();

        return view('admin.invoices.edit', compact('invoice', 'athletes'));
    }

    // ═══════════════════════════════════════════════════════════
    // UPDATE — Update invoice (hanya draft)
    // ═══════════════════════════════════════════════════════════

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_if(
            $invoice->status !== 'draft',
            403,
            'Hanya invoice berstatus draft yang bisa diubah.'
        );

        $validated = $request->validate([
            'due_date' => ['required', 'date'],
            'notes'    => ['nullable', 'string', 'max:1000'],
        ]);

        $invoice->update($validated);

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice berhasil diperbarui.');
    }

    // ═══════════════════════════════════════════════════════════
    // DESTROY — Hapus invoice (hanya draft)
    // ═══════════════════════════════════════════════════════════

    public function destroy(Invoice $invoice): RedirectResponse
    {
        abort_if(
            $invoice->status !== 'draft',
            403,
            'Hanya invoice berstatus draft yang bisa dihapus.'
        );

        DB::transaction(function () use ($invoice) {
            $invoice->items()->delete();
            $invoice->delete();
        });

        return redirect()
            ->route('admin.invoices.index')
            ->with('success', "Invoice {$invoice->invoice_number} berhasil dihapus.");
    }

    // ═══════════════════════════════════════════════════════════
    // STATUS TRANSITIONS
    // ═══════════════════════════════════════════════════════════

    /**
     * Kirim invoice ke coach (draft → sent)
     */
    public function send(Invoice $invoice): RedirectResponse
    {
        abort_if(
            $invoice->status !== 'draft',
            403,
            'Hanya invoice berstatus draft yang bisa dikirim.'
        );

        abort_if(
            $invoice->items()->count() === 0,
            422,
            'Invoice tidak bisa dikirim karena tidak ada item.'
        );

        $invoice->update(['status' => 'sent']);

        // TODO: kirim notifikasi / email ke coach
        // Notification::send($invoice->coach, new InvoiceSentNotification($invoice));

        return back()->with('success', "Invoice {$invoice->invoice_number} berhasil dikirim ke {$invoice->coach->name}.");
    }

    /**
     * Konfirmasi pembayaran (sent → paid)
     */
    public function markPaid(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_if(
            $invoice->status !== 'sent',
            403,
            'Hanya invoice berstatus sent yang bisa dikonfirmasi pembayarannya.'
        );

        $request->validate([
            'paid_at' => ['nullable', 'date', 'before_or_equal:today'],
            'notes'   => ['nullable', 'string', 'max:500'],
        ]);

        $invoice->update([
            'status'  => 'paid',
            'paid_at' => $request->paid_at ?? now(),
            'notes'   => $request->filled('notes')
                ? $invoice->notes . "\n[Pembayaran] " . $request->notes
                : $invoice->notes,
        ]);

        return back()->with('success', "Pembayaran invoice {$invoice->invoice_number} berhasil dikonfirmasi.");
    }

    /**
     * Batalkan invoice (draft / sent → cancelled)
     */
    public function cancel(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_if(
            in_array($invoice->status, ['paid', 'cancelled']),
            403,
            'Invoice yang sudah dibayar atau dibatalkan tidak bisa dibatalkan lagi.'
        );

        $request->validate([
            'cancel_reason' => ['required', 'string', 'max:255'],
        ]);

        $invoice->update([
            'status' => 'cancelled',
            'notes'  => $invoice->notes . "\n[Dibatalkan] " . $request->cancel_reason,
        ]);

        return back()->with('success', "Invoice {$invoice->invoice_number} berhasil dibatalkan.");
    }

    // ═══════════════════════════════════════════════════════════
    // ITEM MANAGEMENT
    // ═══════════════════════════════════════════════════════════

    /**
     * Tambah item ke invoice (hanya draft)
     */
    public function addItem(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_if(
            $invoice->status !== 'draft',
            403,
            'Item hanya bisa ditambahkan ke invoice berstatus draft.'
        );

        $validated = $request->validate([
            'athlete_id'        => ['required', 'exists:athletes,id'],
            'event_category_id' => ['required', 'exists:event_categories,id'],
            'price'             => ['required', 'numeric', 'min:0'],
        ]);

        // Cek atlet milik coach invoice ini
        $athlete = Athlete::where('id', $validated['athlete_id'])
            ->where('coach_id', $invoice->coach_id)
            ->firstOrFail();

        // Cek duplikat — 1 atlet + 1 event_category hanya boleh 1 baris per invoice
        $duplicate = $invoice->items()
            ->where('athlete_id', $athlete->id)
            ->where('event_category_id', $validated['event_category_id'])
            ->exists();

        if ($duplicate) {
            return back()->withErrors([
                'athlete_id' => 'Atlet ini sudah ada dalam invoice untuk kategori tersebut.',
            ]);
        }

        $eventCategory = EventCategory::findOrFail($validated['event_category_id']);

        DB::transaction(function () use ($invoice, $athlete, $eventCategory, $validated) {
            InvoiceItem::create([
                'invoice_id'        => $invoice->id,
                'athlete_id'        => $athlete->id,
                'event_category_id' => $eventCategory->id,
                'discipline_id'     => $eventCategory->discipline_id,
                'price'             => $validated['price'],
            ]);

            $invoice->recalculateTotal();
        });

        return back()->with('success', "Item untuk {$athlete->name} berhasil ditambahkan.");
    }

    /**
     * Hapus item dari invoice (hanya draft)
     */
    public function removeItem(Invoice $invoice, InvoiceItem $item): RedirectResponse
    {
        abort_if(
            $invoice->status !== 'draft',
            403,
            'Item hanya bisa dihapus dari invoice berstatus draft.'
        );

        // Pastikan item benar-benar milik invoice ini
        abort_if($item->invoice_id !== $invoice->id, 404);

        DB::transaction(function () use ($invoice, $item) {
            $item->delete();
            $invoice->recalculateTotal();
        });

        return back()->with('success', 'Item berhasil dihapus dari invoice.');
    }
}
