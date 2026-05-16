@extends('layouts.admin')

@section('title', 'Input Nilai - ' . ($contest->participant->name ?? 'Peserta'))

@section('content')
<div class="min-h-screen bg-base-200 p-6">

    {{-- Breadcrumb --}}
    <div class="breadcrumbs text-sm mb-6">
        <ul>
            <li><a href="{{ route('operator.scores.index') }}" class="text-primary">Input Nilai</a></li>
            <li class="text-base-content/60">{{ $contest->participant->name ?? 'Peserta' }}</li>
        </ul>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Contestant Info Card --}}
        <div class="lg:col-span-1">
            <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6 sticky top-6">
                <div class="flex flex-col items-center text-center mb-6">
                    <div class="avatar placeholder mb-4">
                        <div class="bg-success/10 text-success rounded-full w-20">
                            <span class="text-3xl font-bold">{{ substr($contest->participant->name ?? 'P', 0, 1) }}</span>
                        </div>
                    </div>
                    <h2 class="text-lg font-bold text-base-content">{{ $contest->participant->name ?? '-' }}</h2>
                    <p class="text-sm text-base-content/60">{{ $contest->participant->registration_number ?? '-' }}</p>
                    <div class="badge badge-outline badge-sm mt-2">{{ $contest->eventCategory->name ?? '-' }}</div>
                </div>

                <div class="divider my-4"></div>

                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-base-content/50">Kontingen</span>
                        <span class="text-sm font-medium text-right">{{ $contest->participant->contingent->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-base-content/50">No. Urut</span>
                        <div class="badge badge-neutral font-bold">{{ $contest->order ?? '-' }}</div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-base-content/50">Waktu Tampil</span>
                        <span class="text-sm font-medium">
                            @if($contest->scheduled_at)
                                {{ \Carbon\Carbon::parse($contest->scheduled_at)->format('H:i') }} WIB
                            @else
                                -
                            @endif
                        </span>
                    </div>
                </div>

                @if($contest->final_score)
                <div class="divider my-4"></div>
                <div class="text-center">
                    <div class="text-xs text-base-content/50 mb-1">Nilai Saat Ini</div>
                    <div class="text-4xl font-black text-success">{{ number_format($contest->final_score, 3) }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Score Form --}}
        <div class="lg:col-span-2">
            @if($errors->any())
            <div class="alert alert-error mb-6 rounded-2xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('operator.scores.update', $contest) }}" method="POST" id="scoreForm">
                @csrf
                @method('PUT')

                {{-- Nilai Juri --}}
                <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6 mb-6">
                    <h3 class="font-semibold text-base-content mb-5 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Penilaian per Juri
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @for($j = 1; $j <= 3; $j++)
                        <div class="bg-base-200 rounded-xl p-4">
                            <div class="text-center mb-3">
                                <div class="avatar placeholder mb-2">
                                    <div class="bg-primary text-primary-content rounded-full w-10">
                                        <span class="font-bold">J{{ $j }}</span>
                                    </div>
                                </div>
                                <div class="text-sm font-semibold text-base-content">Juri {{ $j }}</div>
                            </div>

                            <div class="space-y-3">
                                <div class="form-control">
                                    <label class="label py-1">
                                        <span class="label-text text-xs">Nilai Gerak</span>
                                    </label>
                                    <input type="number" step="0.001" min="0" max="10"
                                           name="scores[{{ $j }}][movement]"
                                           value="{{ old("scores.{$j}.movement", $contest->scores[$j-1]['movement'] ?? '') }}"
                                           placeholder="0.000"
                                           class="input input-bordered input-sm rounded-lg text-center font-mono @error("scores.{$j}.movement") input-error @enderror"
                                           oninput="calculateTotal()" />
                                </div>
                                <div class="form-control">
                                    <label class="label py-1">
                                        <span class="label-text text-xs">Nilai Penampilan</span>
                                    </label>
                                    <input type="number" step="0.001" min="0" max="10"
                                           name="scores[{{ $j }}][performance]"
                                           value="{{ old("scores.{$j}.performance", $contest->scores[$j-1]['performance'] ?? '') }}"
                                           placeholder="0.000"
                                           class="input input-bordered input-sm rounded-lg text-center font-mono @error("scores.{$j}.performance") input-error @enderror"
                                           oninput="calculateTotal()" />
                                </div>
                            </div>

                            <div class="bg-base-100 rounded-lg p-2 mt-3 text-center">
                                <div class="text-xs text-base-content/50">Subtotal</div>
                                <div class="font-bold text-primary font-mono" id="subtotal_{{ $j }}">0.000</div>
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>

                {{-- Deductions --}}
                <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6 mb-6">
                    <h3 class="font-semibold text-base-content mb-5 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                        </svg>
                        Pengurangan Nilai
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Pengurangan Waktu</span>
                                <span class="label-text-alt text-base-content/40">maks. 2.0</span>
                            </label>
                            <input type="number" step="0.1" min="0" max="2"
                                   name="deduction_time"
                                   value="{{ old('deduction_time', $contest->deduction_time ?? 0) }}"
                                   placeholder="0.0"
                                   class="input input-bordered rounded-xl font-mono @error('deduction_time') input-error @enderror"
                                   oninput="calculateTotal()" />
                            @error('deduction_time')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                            @enderror
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Pengurangan Lainnya</span>
                                <span class="label-text-alt text-base-content/40">0.1 per kejadian</span>
                            </label>
                            <input type="number" step="0.1" min="0"
                                   name="deduction_other"
                                   value="{{ old('deduction_other', $contest->deduction_other ?? 0) }}"
                                   placeholder="0.0"
                                   class="input input-bordered rounded-xl font-mono @error('deduction_other') input-error @enderror"
                                   oninput="calculateTotal()" />
                            @error('deduction_other')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Final Score Preview --}}
                <div class="bg-gradient-to-r from-success/10 to-primary/10 rounded-2xl border border-success/20 p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-base-content/70 mb-1">Nilai Akhir</div>
                            <div class="text-5xl font-black text-success font-mono" id="finalScore">0.000</div>
                        </div>
                        <div class="text-right text-sm text-base-content/50 space-y-1">
                            <div>Rata-rata juri: <span id="avgScore" class="font-mono font-semibold text-base-content">0.000</span></div>
                            <div>Total pengurangan: <span id="totalDeduction" class="font-mono font-semibold text-error">0.000</span></div>
                        </div>
                    </div>
                </div>

                {{-- Catatan --}}
                <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6 mb-6">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Catatan (opsional)</span>
                        </label>
                        <textarea name="notes" rows="3"
                                  class="textarea textarea-bordered rounded-xl"
                                  placeholder="Catatan tambahan dari operator...">{{ old('notes', $contest->notes ?? '') }}</textarea>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-between">
                    <a href="{{ route('operator.scores.index') }}" class="btn btn-ghost rounded-xl gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali
                    </a>
                    <button type="submit" class="btn btn-success rounded-xl gap-2 px-8 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Nilai
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function calculateTotal() {
    let totalJuri = 0;
    let juriCount = 3;

    for (let j = 1; j <= juriCount; j++) {
        const movement = parseFloat(document.querySelector(`[name="scores[${j}][movement]"]`)?.value) || 0;
        const performance = parseFloat(document.querySelector(`[name="scores[${j}][performance]"]`)?.value) || 0;
        const subtotal = movement + performance;

        const subtotalEl = document.getElementById(`subtotal_${j}`);
        if (subtotalEl) subtotalEl.textContent = subtotal.toFixed(3);

        totalJuri += subtotal;
    }

    const avgScore = totalJuri / juriCount;
    const deductionTime = parseFloat(document.querySelector('[name="deduction_time"]')?.value) || 0;
    const deductionOther = parseFloat(document.querySelector('[name="deduction_other"]')?.value) || 0;
    const totalDeduction = deductionTime + deductionOther;
    const finalScore = Math.max(0, avgScore - totalDeduction);

    const avgEl = document.getElementById('avgScore');
    const deductEl = document.getElementById('totalDeduction');
    const finalEl = document.getElementById('finalScore');

    if (avgEl) avgEl.textContent = avgScore.toFixed(3);
    if (deductEl) deductEl.textContent = totalDeduction.toFixed(3);
    if (finalEl) finalEl.textContent = finalScore.toFixed(3);
}

// Init on load
document.addEventListener('DOMContentLoaded', calculateTotal);
</script>
@endsection