@extends('layouts.admin')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
    <div class="inv-page">

        {{-- ══════════════════════════════════════
            PAGE HEADER
        ══════════════════════════════════════ --}}
        <div class="inv-header">
            <div class="inv-header__left">
                <div class="inv-header__breadcrumb">
                    <a href="{{ route('admin.invoices.index') }}" class="inv-header__breadcrumb-link">Invoices</a>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                    <span>{{ $invoice->invoice_number }}</span>
                </div>
                <div class="inv-header__badge">INVOICE DETAIL</div>
                <div class="inv-header__title-row">
                    <h1 class="inv-header__title">{{ $invoice->invoice_number }}<span
                            class="inv-header__dot">.</span></h1>
                    @switch($invoice->status)
                        @case('draft')
                            <span class="inv-status inv-status--draft inv-status--lg">Draft</span>
                        @break

                        @case('sent')
                            <span class="inv-status inv-status--sent inv-status--lg">Sent</span>
                        @break

                        @case('paid')
                            <span class="inv-status inv-status--paid inv-status--lg">Paid</span>
                        @break

                        @case('cancelled')
                            <span class="inv-status inv-status--cancelled inv-status--lg">Cancelled</span>
                        @break
                    @endswitch
                </div>
                <p class="inv-header__sub">Created {{ $invoice->created_at->format('d F Y, H:i') }}</p>
            </div>
            <div class="inv-header__right">
                {{-- Status Actions --}}
                @if ($invoice->status === 'draft')
                    <a href="{{ route('admin.invoices.edit', $invoice) }}" class="inv-btn inv-btn--ghost">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                        Edit
                    </a>
                    <form method="POST" action="{{ route('admin.invoices.send', $invoice) }}" style="display:inline">
                        @csrf
                        <button type="submit" class="inv-btn inv-btn--primary">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <line x1="22" y1="2" x2="11" y2="13" />
                                <polygon points="22 2 15 22 11 13 2 9 22 2" />
                            </svg>
                            Send to Coach
                        </button>
                    </form>
                @endif

                @if ($invoice->status === 'sent')
                    <button onclick="openPayModal()" class="inv-btn inv-btn--success">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
                        Confirm Payment
                    </button>
                @endif

                @if (!in_array($invoice->status, ['paid', 'cancelled']))
                    <button onclick="openCancelModal()" class="inv-btn inv-btn--ghost inv-btn--danger-ghost">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="15" y1="9" x2="9" y2="15" />
                            <line x1="9" y1="9" x2="15" y2="15" />
                        </svg>
                        Cancel
                    </button>
                @endif
            </div>
        </div>

        {{-- ══════════════════════════════════════
            FLASH MESSAGES
        ══════════════════════════════════════ --}}
        @if (session('success'))
            <div class="inv-alert inv-alert--success">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="inv-detail-grid">
            {{-- ══════════════════════════════════════
                LEFT COLUMN — Invoice Info + Items
            ══════════════════════════════════════ --}}
            <div class="inv-detail-main">

                {{-- Coach & Invoice Meta --}}
                <div class="inv-detail-card">
                    <div class="inv-detail-card__header">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        Invoice To
                    </div>
                    <div class="inv-detail-card__body">
                        <div class="inv-coach-info">
                            <div class="inv-coach-avatar">
                                {{ strtoupper(substr($invoice->user->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="inv-coach-name">{{ $invoice->user->name }}</div>
                                <div class="inv-coach-email">{{ $invoice->user->email }}</div>
                            </div>
                        </div>
                        <div class="inv-meta-grid">
                            <div class="inv-meta-item">
                                <span class="inv-meta-label">Invoice Number</span>
                                <span class="inv-meta-value inv-meta-value--mono">{{ $invoice->invoice_number }}</span>
                            </div>
                            <div class="inv-meta-item">
                                <span class="inv-meta-label">Issue Date</span>
                                <span class="inv-meta-value">{{ $invoice->created_at->format('d M Y') }}</span>
                            </div>
                            <div class="inv-meta-item">
                                <span class="inv-meta-label">Due Date</span>
                                @php
                                    $isOverdue = $invoice->status === 'sent' && $invoice->due_date->isPast();
                                @endphp
                                <span
                                
                                    class="inv-meta-value {{ $isOverdue ? 'inv-meta-value--danger' : '' }}">{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}
                                    @if ($isOverdue)
                                        <span class="inv-overdue-tag">OVERDUE</span>
                                    @endif
                                </span>
                            </div>
                            @if ($invoice->paid_at)
                                <div class="inv-meta-item">
                                    <span class="inv-meta-label">Paid At</span>
                                    <span
                                        class="inv-meta-value inv-meta-value--success">{{ \Carbon\Carbon::parse($invoice->paid_at)->format('d M Y') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Items Table --}}
                <div class="inv-detail-card">
                    <div class="inv-detail-card__header">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <line x1="8" y1="6" x2="21" y2="6" />
                            <line x1="8" y1="12" x2="21" y2="12" />
                            <line x1="8" y1="18" x2="21" y2="18" />
                            <line x1="3" y1="6" x2="3.01" y2="6" />
                            <line x1="3" y1="12" x2="3.01" y2="12" />
                            <line x1="3" y1="18" x2="3.01" y2="18" />
                        </svg>
                        Invoice Items
                        <span class="inv-detail-card__count">{{ $invoice->items->count() }} item(s)</span>
                    </div>
                    <div class="inv-items-table-wrap">
                        <table class="inv-items-table">
                            <thead>
                                <tr>
                                    <th>Athlete</th>
                                    <th>Event / Category</th>
                                    <th>Discipline</th>
                                    <th class="text-right">Price</th>
                                    @if ($invoice->status === 'draft')
                                        <th class="text-center">Remove</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoice->items as $item)
                                    <tr>
                                        <td>
                                            <div class="inv-athlete-cell">
                                                <div class="inv-athlete-avatar">
                                                    {{ strtoupper(substr($item->athlete->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="inv-athlete-name">{{ $item->athlete->name }}</div>
                                                    @if ($item->athlete->perguruan)
                                                        <div class="inv-athlete-club">
                                                            {{ $item->athlete->perguruan->name }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="inv-event-cell">
                                                <span class="inv-event-name">
                                                    {{ $item->eventCategory?->event?->name ?? '—' }}
                                                </span>

                                                @if ($item->eventCategory?->ageCategory)
                                                    <span class="inv-age-badge">
                                                        {{ $item->eventCategory->ageCategory->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="inv-discipline-chip">{{ $item->discipline->name ?? '—' }}</span>
                                        </td>
                                        <td class="text-right inv-item-price">
                                            Rp {{ number_format($item->price, 0, ',', '.') }}
                                        </td>
                                        @if ($invoice->status === 'draft')
                                            <td class="text-center">
                                                <form method="POST"
                                                    action="{{ route('admin.invoices.items.remove', [$invoice, $item]) }}"
                                                    onsubmit="return confirm('Remove this item?')"
                                                    style="display:inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inv-action-btn inv-action-btn--delete">
                                                        <svg width="13" height="13" viewBox="0 0 24 24"
                                                            fill="none" stroke="currentColor" stroke-width="2">
                                                            <polyline points="3 6 5 6 21 6" />
                                                            <path
                                                                d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                                            <path d="M10 11v6" />
                                                            <path d="M14 11v6" />
                                                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="inv-items-total">
                                    <td colspan="{{ $invoice->status === 'draft' ? 3 : 3 }}">
                                        <strong>Total</strong>
                                    </td>
                                    <td class="text-right">
                                        <strong class="inv-total-amount">Rp
                                            {{ number_format($invoice->total_amount, 0, ',', '.') }}</strong>
                                    </td>
                                    @if ($invoice->status === 'draft')
                                        <td></td>
                                    @endif
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Add Item Form (draft only) --}}
                    @if ($invoice->status === 'draft')
                        <div class="inv-add-item">
                            <div class="inv-add-item__title">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5">
                                    <line x1="12" y1="5" x2="12" y2="19" />
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                </svg>
                                Add Item
                            </div>
                            <form method="POST" action="{{ route('admin.invoices.items.add', $invoice) }}"
                                class="inv-add-item__form">
                                @csrf
                                <div class="inv-add-item__fields">
                                    <div class="inv-form-group">
                                        <label class="inv-form-label">Athlete</label>
                                        <select name="athlete_id" class="inv-input inv-input--select" required>
                                            <option value="">Select athlete…</option>
                                            {{-- Populated via JS or pass $athletes from controller --}}
                                        </select>
                                    </div>
                                    <div class="inv-form-group">
                                        <label class="inv-form-label">Event Category</label>
                                        <select name="event_category_id" class="inv-input inv-input--select"
                                            required>
                                            <option value="">Select category…</option>
                                        </select>
                                    </div>
                                    <div class="inv-form-group inv-form-group--sm">
                                        <label class="inv-form-label">Price (Rp)</label>
                                        <input type="number" name="price" class="inv-input" min="0"
                                            step="1000" placeholder="0" required>
                                    </div>
                                    <div class="inv-form-group inv-form-group--action">
                                        <button type="submit" class="inv-btn inv-btn--primary">Add</button>
                                    </div>
                                </div>
                                @error('athlete_id')
                                    <p class="inv-form-error">{{ $message }}</p>
                                @enderror
                            </form>
                        </div>
                    @endif
                </div>

                {{-- Notes --}}
                @if ($invoice->notes)
                    <div class="inv-detail-card">
                        <div class="inv-detail-card__header">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                            </svg>
                            Notes
                        </div>
                        <div class="inv-detail-card__body">
                            <p class="inv-notes-text">{{ $invoice->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- ══════════════════════════════════════
                RIGHT COLUMN — Summary
            ══════════════════════════════════════ --}}
            <div class="inv-detail-aside">
                <div class="inv-summary-card">
                    <div class="inv-summary-card__header">Summary</div>
                    <div class="inv-summary-rows">
                        <div class="inv-summary-row">
                            <span>Total Items</span>
                            <strong>{{ $invoice->items->count() }}</strong>
                        </div>
                        <div class="inv-summary-row">
                            <span>Athletes</span>
                            <strong>{{ $invoice->items->pluck('athlete_id')->unique()->count() }}</strong>
                        </div>
                        <div class="inv-summary-row">
                            <span>Subtotal</span>
                            <strong>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    <div class="inv-summary-total">
                        <span>Total Due</span>
                        <span>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                    </div>
                    @if ($invoice->status === 'paid')
                        <div class="inv-summary-paid">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.5">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                            Invoice Paid
                        </div>
                    @endif
                </div>

                {{-- Timeline --}}
                <div class="inv-timeline-card">
                    <div class="inv-detail-card__header">Activity</div>
                    <div class="inv-timeline">
                        <div class="inv-timeline-item inv-timeline-item--done">
                            <div class="inv-timeline-dot"></div>
                            <div class="inv-timeline-content">
                                <span class="inv-timeline-label">Created</span>
                                <span
                                    class="inv-timeline-date">{{ $invoice->created_at->format('d M Y, H:i') }}</span>
                            </div>
                        </div>
                        <div
                            class="inv-timeline-item {{ in_array($invoice->status, ['sent', 'paid']) ? 'inv-timeline-item--done' : '' }}">
                            <div class="inv-timeline-dot"></div>
                            <div class="inv-timeline-content">
                                <span class="inv-timeline-label">Sent to Coach</span>
                                @if (in_array($invoice->status, ['sent', 'paid']))
                                    <span class="inv-timeline-date">{{ $invoice->updated_at->format('d M Y') }}</span>
                                @else
                                    <span class="inv-timeline-pending">Pending</span>
                                @endif
                            </div>
                        </div>
                        <div
                            class="inv-timeline-item {{ $invoice->status === 'paid' ? 'inv-timeline-item--done inv-timeline-item--success' : '' }}">
                            <div class="inv-timeline-dot"></div>
                            <div class="inv-timeline-content">
                                <span class="inv-timeline-label">Payment Confirmed</span>
                                @if ($invoice->status === 'paid' && $invoice->paid_at)
                                    <span
                                        class="inv-timeline-date">{{ \Carbon\Carbon::parse($invoice->paid_at)->format('d M Y') }}</span>
                                @else
                                    <span class="inv-timeline-pending">Pending</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════
            MARK PAID MODAL
        ══════════════════════════════════════ --}}
        @if ($invoice->status === 'sent')
            <div id="payModal" class="inv-modal-overlay" style="display:none">
                <div class="inv-modal">
                    <div class="inv-modal__header">
                        <h3>Confirm Payment</h3>
                        <button onclick="closePayModal()" class="inv-modal__close">&times;</button>
                    </div>
                    <form method="POST" action="{{ route('admin.invoices.pay', $invoice) }}">
                        @csrf
                        <div class="inv-modal__body">
                            <p class="inv-modal__desc">Confirm that payment for <strong>{{ $invoice->invoice_number }}</strong>
                                has been received.</p>
                            <div class="inv-form-group">
                                <label class="inv-form-label">Payment Date</label>
                                <input type="date" name="paid_at" class="inv-input"
                                    value="{{ now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}">
                            </div>
                            <div class="inv-form-group">
                                <label class="inv-form-label">Notes (optional)</label>
                                <textarea name="notes" class="inv-input inv-input--textarea" rows="3"
                                    placeholder="e.g. Payment via BCA transfer…"></textarea>
                            </div>
                        </div>
                        <div class="inv-modal__footer">
                            <button type="button" onclick="closePayModal()"
                                class="inv-btn inv-btn--ghost">Cancel</button>
                            <button type="submit" class="inv-btn inv-btn--success">Confirm Paid</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- ══════════════════════════════════════
            CANCEL MODAL
        ══════════════════════════════════════ --}}
        @if (!in_array($invoice->status, ['paid', 'cancelled']))
            <div id="cancelModal" class="inv-modal-overlay" style="display:none">
                <div class="inv-modal">
                    <div class="inv-modal__header">
                        <h3>Cancel Invoice</h3>
                        <button onclick="closeCancelModal()" class="inv-modal__close">&times;</button>
                    </div>
                    <form method="POST" action="{{ route('admin.invoices.cancel', $invoice) }}">
                        @csrf
                        <div class="inv-modal__body">
                            <p class="inv-modal__desc">This will cancel
                                <strong>{{ $invoice->invoice_number }}</strong>. This action cannot be undone.</p>
                            <div class="inv-form-group">
                                <label class="inv-form-label">Cancellation Reason <span
                                        class="inv-form-required">*</span></label>
                                <textarea name="cancel_reason" class="inv-input inv-input--textarea" rows="3"
                                    placeholder="Reason for cancellation…" required></textarea>
                            </div>
                        </div>
                        <div class="inv-modal__footer">
                            <button type="button" onclick="closeCancelModal()"
                                class="inv-btn inv-btn--ghost">Back</button>
                            <button type="submit" class="inv-btn inv-btn--danger">Cancel Invoice</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    </div>
@endsection

@push('styles')
    <style>
        .inv-page {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* ── HEADER ───────────────────────────────────────────── */
        .inv-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 1.75rem;
            gap: 1rem;
        }

        .inv-header__breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            color: #94a3b8;
            margin-bottom: 0.5rem;
        }

        .inv-header__breadcrumb-link {
            color: #6366f1;
            text-decoration: none;
        }

        .inv-header__breadcrumb-link:hover {
            text-decoration: underline;
        }

        .inv-header__badge {
            display: inline-block;
            font-size: 0.625rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #6366f1;
            background: #eef2ff;
            border-radius: 4px;
            padding: 3px 8px;
            margin-bottom: 0.4rem;
        }

        .inv-header__title-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.25rem;
        }

        .inv-header__title {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
            margin: 0;
            line-height: 1;
            font-family: 'Courier New', monospace;
        }

        .inv-header__dot {
            color: #6366f1;
        }

        .inv-header__sub {
            color: #64748b;
            font-size: 0.85rem;
            margin: 0;
        }

        .inv-header__right {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            flex-shrink: 0;
        }

        /* ── STATUS ───────────────────────────────────────────── */
        .inv-status {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .inv-status--lg {
            padding: 5px 14px;
            font-size: 0.75rem;
        }

        .inv-status--draft {
            background: #f1f5f9;
            color: #475569;
        }

        .inv-status--sent {
            background: #ede9fe;
            color: #7c3aed;
        }

        .inv-status--paid {
            background: #dcfce7;
            color: #16a34a;
        }

        .inv-status--cancelled {
            background: #fff1f2;
            color: #dc2626;
        }

        /* ── ALERT ────────────────────────────────────────────── */
        .inv-alert {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            margin-bottom: 1.25rem;
        }

        .inv-alert--success {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        /* ── BUTTONS ──────────────────────────────────────────── */
        .inv-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.125rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.15s;
            white-space: nowrap;
        }

        .inv-btn--primary {
            background: #6366f1;
            color: #fff;
        }

        .inv-btn--primary:hover {
            background: #4f46e5;
        }

        .inv-btn--success {
            background: #16a34a;
            color: #fff;
        }

        .inv-btn--success:hover {
            background: #15803d;
        }

        .inv-btn--danger {
            background: #ef4444;
            color: #fff;
        }

        .inv-btn--danger:hover {
            background: #dc2626;
        }

        .inv-btn--ghost {
            background: transparent;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }

        .inv-btn--ghost:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .inv-btn--danger-ghost {
            color: #dc2626;
            border-color: #fecdd3;
        }

        .inv-btn--danger-ghost:hover {
            background: #fff1f2;
        }

        .inv-btn--sm {
            padding: 0.375rem 0.875rem;
            font-size: 0.8rem;
        }

        /* ── DETAIL GRID ──────────────────────────────────────── */
        .inv-detail-grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 1.25rem;
            align-items: start;
        }

        @media (max-width: 900px) {
            .inv-detail-grid {
                grid-template-columns: 1fr;
            }
        }

        .inv-detail-main {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .inv-detail-aside {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        /* ── CARDS ────────────────────────────────────────────── */
        .inv-detail-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }

        .inv-detail-card__header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.25rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
        }

        .inv-detail-card__count {
            margin-left: auto;
            background: #e2e8f0;
            color: #475569;
            border-radius: 20px;
            padding: 2px 8px;
            font-size: 0.7rem;
        }

        .inv-detail-card__body {
            padding: 1.25rem;
        }

        /* ── COACH INFO ───────────────────────────────────────── */
        .inv-coach-info {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            margin-bottom: 1.25rem;
        }

        .inv-coach-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #ede9fe;
            color: #7c3aed;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .inv-coach-name {
            font-weight: 700;
            font-size: 1rem;
            color: #0f172a;
        }

        .inv-coach-email {
            font-size: 0.8rem;
            color: #64748b;
        }

        .inv-meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.875rem;
        }

        .inv-meta-item {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .inv-meta-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #94a3b8;
        }

        .inv-meta-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #0f172a;
        }

        .inv-meta-value--mono {
            font-family: 'Courier New', monospace;
            color: #4f46e5;
        }

        .inv-meta-value--danger {
            color: #dc2626;
        }

        .inv-meta-value--success {
            color: #16a34a;
        }

        .inv-overdue-tag {
            display: inline-block;
            background: #ef4444;
            color: #fff;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            padding: 2px 5px;
            border-radius: 3px;
            vertical-align: middle;
            margin-left: 4px;
        }

        /* ── ITEMS TABLE ──────────────────────────────────────── */
        .inv-items-table-wrap {
            overflow-x: auto;
        }

        .inv-items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .inv-items-table th {
            padding: 0.625rem 1rem;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
            text-align: left;
        }

        .inv-items-table th.text-right {
            text-align: right;
        }

        .inv-items-table th.text-center {
            text-align: center;
        }

        .inv-items-table td {
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .inv-items-table td.text-right {
            text-align: right;
        }

        .inv-items-table td.text-center {
            text-align: center;
        }

        .inv-items-table tr:last-child td {
            border-bottom: none;
        }

        .inv-items-total td {
            background: #f8fafc;
            font-size: 0.9rem;
            border-top: 2px solid #e2e8f0 !important;
        }

        .inv-total-amount {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
        }

        .inv-item-price {
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            color: #0f172a;
        }

        .inv-athlete-cell {
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }

        .inv-athlete-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #eff6ff;
            color: #1d4ed8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.7rem;
            flex-shrink: 0;
        }

        .inv-athlete-name {
            font-weight: 600;
            font-size: 0.875rem;
            color: #0f172a;
        }

        .inv-athlete-club {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .inv-event-cell {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .inv-event-name {
            font-size: 0.8rem;
            font-weight: 600;
            color: #334155;
        }

        .inv-age-badge {
            display: inline-block;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .inv-discipline-chip {
            display: inline-block;
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
        }

        /* ── ADD ITEM FORM ────────────────────────────────────── */
        .inv-add-item {
            padding: 1rem 1.25rem;
            border-top: 1px dashed #e2e8f0;
            background: #fafbfc;
        }

        .inv-add-item__title {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            margin-bottom: 0.75rem;
        }

        .inv-add-item__fields {
            display: flex;
            gap: 0.75rem;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        /* ── NOTES ────────────────────────────────────────────── */
        .inv-notes-text {
            font-size: 0.875rem;
            color: #475569;
            line-height: 1.6;
            margin: 0;
            white-space: pre-wrap;
        }

        /* ── FORM ELEMENTS ────────────────────────────────────── */
        .inv-form-group {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            flex: 1;
            min-width: 160px;
        }

        .inv-form-group--sm {
            max-width: 160px;
        }

        .inv-form-group--action {
            flex: 0;
            min-width: auto;
        }

        .inv-form-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
        }

        .inv-form-required {
            color: #ef4444;
        }

        .inv-form-error {
            font-size: 0.75rem;
            color: #dc2626;
            margin: 0.375rem 0 0;
        }

        .inv-input {
            height: 38px;
            padding: 0 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.875rem;
            color: #0f172a;
            background: #f8fafc;
            transition: border-color 0.15s;
            width: 100%;
        }

        .inv-input:focus {
            outline: none;
            border-color: #818cf8;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            background: #fff;
        }

        .inv-input--textarea {
            height: auto;
            padding: 0.625rem 0.75rem;
            resize: vertical;
        }

        .inv-input--select {
            cursor: pointer;
        }

        /* ── ACTION BUTTONS ───────────────────────────────────── */
        .inv-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.15s;
            background: transparent;
        }

        .inv-action-btn--delete {
            color: #64748b;
            border-color: #e2e8f0;
        }

        .inv-action-btn--delete:hover {
            background: #fff1f2;
            color: #dc2626;
            border-color: #fecdd3;
        }

        /* ── SUMMARY CARD ─────────────────────────────────────── */
        .inv-summary-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }

        .inv-summary-card__header {
            padding: 0.875rem 1.25rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
        }

        .inv-summary-rows {
            padding: 0.875rem 1.25rem;
        }

        .inv-summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            font-size: 0.875rem;
            color: #64748b;
            border-bottom: 1px solid #f1f5f9;
        }

        .inv-summary-row:last-child {
            border-bottom: none;
        }

        .inv-summary-row strong {
            color: #0f172a;
            font-weight: 600;
        }

        .inv-summary-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.25rem;
            background: #0f172a;
            color: #fff;
            font-weight: 800;
            font-size: 1rem;
        }

        .inv-summary-paid {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.625rem;
            background: #f0fdf4;
            color: #16a34a;
            font-size: 0.8rem;
            font-weight: 700;
            border-top: 1px solid #bbf7d0;
        }

        /* ── TIMELINE ─────────────────────────────────────────── */
        .inv-timeline-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }

        .inv-timeline {
            padding: 1rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .inv-timeline-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding-bottom: 1rem;
            position: relative;
        }

        .inv-timeline-item:last-child {
            padding-bottom: 0;
        }

        .inv-timeline-item::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 18px;
            bottom: 0;
            width: 1px;
            background: #e2e8f0;
        }

        .inv-timeline-item:last-child::before {
            display: none;
        }

        .inv-timeline-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid #e2e8f0;
            background: #fff;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .inv-timeline-item--done .inv-timeline-dot {
            background: #6366f1;
            border-color: #6366f1;
        }

        .inv-timeline-item--success .inv-timeline-dot {
            background: #16a34a;
            border-color: #16a34a;
        }

        .inv-timeline-content {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .inv-timeline-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #334155;
        }

        .inv-timeline-date {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .inv-timeline-pending {
            font-size: 0.72rem;
            color: #cbd5e1;
            font-style: italic;
        }

        /* ── MODALS ───────────────────────────────────────────── */
        .inv-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .inv-modal {
            background: #fff;
            border-radius: 14px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .inv-modal__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.125rem 1.25rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .inv-modal__header h3 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .inv-modal__close {
            width: 28px;
            height: 28px;
            border: none;
            background: #f1f5f9;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1rem;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .inv-modal__close:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .inv-modal__body {
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .inv-modal__desc {
            margin: 0;
            font-size: 0.875rem;
            color: #64748b;
        }

        .inv-modal__footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.625rem;
            padding: 1rem 1.25rem;
            border-top: 1px solid #e2e8f0;
        }
    </style>
@endpush

@push('scripts')
    <script>
        function openPayModal() {
            document.getElementById('payModal').style.display = 'flex';
        }

        function closePayModal() {
            document.getElementById('payModal').style.display = 'none';
        }

        function openCancelModal() {
            document.getElementById('cancelModal').style.display = 'flex';
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
        }

        // Close modal when clicking overlay
        document.querySelectorAll('.inv-modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) this.style.display = 'none';
            });
        });
    </script>
@endpush
