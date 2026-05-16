<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Http\Requests\Registration\StoreRegistrationRequest;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    /**
     * Daftar semua registrasi milik coach yang sedang login.
     */
    public function index(): View
    {
        $registrations = Registration::where('coach_id', Auth::id())
            ->with(['athlete', 'discipline', 'ageCategory'])
            ->latest()
            ->paginate(15);

        return view('coach.registrations.index', compact('registrations'));
    }

    /**
     * Simpan satu registrasi baru.
     */
    public function store(StoreRegistrationRequest $request): RedirectResponse
    {
        Registration::create([
            'coach_id'        => Auth::id(),
            'athlete_id'      => $request->athlete_id,
            'discipline_id'   => $request->discipline_id,
            'age_category_id' => $request->age_category_id,
            'status'          => 'pending',
            'registered_at'   => now(),
        ]);

        return redirect()
            ->route('coach.registrations.index')
            ->with('success', 'Pendaftaran berhasil dikirim, menunggu persetujuan admin.');
    }

    /**
     * Detail satu registrasi.
     */
    public function show(Registration $registration): View
    {
        $this->authorize('view', $registration);
        $registration->load(['athlete', 'discipline', 'ageCategory', 'invoiceItem.invoice']);

        return view('coach.registrations.show', compact('registration'));
    }

    /**
     * Batalkan registrasi — hanya bisa jika masih pending.
     */
    public function destroy(Registration $registration): RedirectResponse
    {
        $this->authorize('delete', $registration);

        if ($registration->status !== 'pending') {
            return back()->with('error', 'Hanya registrasi berstatus pending yang bisa dibatalkan.');
        }

        $registration->delete();

        return redirect()
            ->route('coach.registrations.index')
            ->with('success', 'Registrasi berhasil dibatalkan.');
    }
}
