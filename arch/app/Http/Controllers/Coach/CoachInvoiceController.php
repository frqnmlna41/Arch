<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * CoachInvoiceController
 *
 * Mengelola tampilan invoice dari sisi Coach.
 * Coach hanya bisa:
 *   - Melihat daftar invoice miliknya sendiri
 *   - Melihat detail invoice
 *   - Tidak bisa membuat / mengedit / menghapus invoice
 *   - Tidak bisa mengubah status invoice (itu hak admin)
 *
 * Routes (tambahkan ke routes/web.php di group middleware 'coach'):
 * ────────────────────────────────────────────────────────────────
 *   Route::prefix('coach/invoices')->name('coach.invoices.')->middleware(['auth', 'role:coach'])->group(function () {
 *       Route::get('/',            [CoachInvoiceController::class, 'index'])->name('index');
 *       Route::get('/{invoice}',   [CoachInvoiceController::class, 'show'])->name('show');
 *   });
 */
class CoachInvoiceController extends Controller
{
    // ═══════════════════════════════════════════════════════════
    // HELPER — Pastikan invoice milik coach yang sedang login
    // ═══════════════════════════════════════════════════════════

    private function authorizeInvoice(Invoice $invoice): void
    {
        abort_if(
            $invoice->coach_id !== Auth::id(),
            403,
            'Anda tidak memiliki akses ke invoice ini.'
        );
    }

    // ═══════════════════════════════════════════════════════════
    // INDEX — Daftar invoice milik coach ini
    // ═══════════════════════════════════════════════════════════

    public function index(Request $request): View
    {
        $coach = Auth::user();
        $query = Invoice::where('user_id', $coach->id)
            ->withCount('items');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by invoice number
        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();

        // Summary stats hanya untuk coach ini
        $stats = [
            'total'   => Invoice::where('user_id', Auth::id())->count(),
            'draft'   => Invoice::where('user_id', Auth::id())->where('status', 'draft')->count(),
            'sent'    => Invoice::where('user_id', Auth::id())->where('status', 'sent')->count(),
            'paid'    => Invoice::where('user_id', Auth::id())->where('status', 'paid')->count(),
            'overdue' => Invoice::where('user_id', Auth::id())
                                ->where('status', 'sent')
                                ->where('due_date', '<', now())
                                ->count(),
            'total_billed' => Invoice::where('user_id', Auth::id())
                                     ->whereIn('status', ['sent', 'paid'])
                                     ->sum('total_amount'),

                                     ];

        return view('coach.invoices.index', compact('invoices', 'stats'));
    }

    // ═══════════════════════════════════════════════════════════
    // SHOW — Detail invoice (read-only)
    // ═══════════════════════════════════════════════════════════

    public function show(Invoice $invoice): View
    {
        $this->authorizeInvoice($invoice);

        $invoice->load([
            'coach',
            'items.athlete.perguruan',
            'items.discipline',
            'items.eventCategory.event',
            'items.eventCategory.ageCategory',
        ]);

        return view('coach.invoices.show', compact('invoice'));
    }
}
