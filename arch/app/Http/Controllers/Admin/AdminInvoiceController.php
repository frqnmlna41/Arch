<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

    /**
     * Semua invoice — bisa filter by status / coach.
     */
    public function index(): View
    {
        $invoices = Invoice::with(['coach.user', 'items'])
            ->when(request('status'), fn($q, $s) => $q->where('status', $s))
            ->when(request('coach_id'), fn($q, $id) => $q->where('coach_id', $id))
            ->latest()
            ->paginate(20);

        $coaches  = Coach::with('user')->orderBy('id')->get();
        $statuses = ['unpaid', 'paid', 'cancelled'];

        return view('admin.invoices.index', compact('invoices', 'coaches', 'statuses'));
    }

    /**
     * Detail invoice lengkap.
     */
    public function show(Invoice $invoice): View
    {
        $invoice->load(['coach.user', 'items.athlete', 'items.discipline', 'items.ageCategory']);

        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * Tandai invoice sebagai lunas.
     */
    public function markAsPaid(Invoice $invoice): RedirectResponse
    {
        if ($invoice->isPaid()) {
            return back()->with('error', 'Invoice ini sudah lunas.');
        }

        if ($invoice->status === 'cancelled') {
            return back()->with('error', 'Invoice yang dibatalkan tidak bisa diubah.');
        }

        $invoice->markAsPaid();

        return back()->with('success', "Invoice {$invoice->invoice_number} berhasil ditandai lunas.");
    }

    /**
     * Batalkan invoice — hanya jika belum dibayar.
     */
    public function cancel(Invoice $invoice): RedirectResponse
    {
        if ($invoice->isPaid()) {
            return back()->with('error', 'Invoice yang sudah lunas tidak bisa dibatalkan.');
        }

        $invoice->update(['status' => 'cancelled']);

        return back()->with('success', "Invoice {$invoice->invoice_number} berhasil dibatalkan.");
    }

    /**
     * Generate invoice manual untuk satu coach
     * (untuk kasus approved tapi belum dibuatkan invoice).
     */
    public function generate(Coach $coach): RedirectResponse
    {
        try {
            $invoice = $this->invoiceService->generateForCoach($coach);

            return redirect()
                ->route('admin.invoices.show', $invoice)
                ->with('success', "Invoice {$invoice->invoice_number} berhasil dibuat.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
