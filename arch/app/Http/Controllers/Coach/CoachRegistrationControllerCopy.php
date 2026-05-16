<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Http\Requests\Registration\StoreRegistrationRequest;
use App\Models\AgeCategory;
use App\Models\Discipline;
use App\Models\Registration;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CoachRegistrationController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

    /**
     * Daftar semua registrasi milik coach yang sedang login.
     */
    public function index(): View
    {
        $registrations = Registration::where('user_id', Auth::user('coach')->id)
            ->with(['athlete', 'discipline', 'ageCategory'])
            ->latest()
            ->paginate(15);

        return view('coach.regis.index', compact('registrations'));
    }

    /**
     * Form pendaftaran athlete ke nomor tanding.
     */
    public function create(): View
    {
        $coach       = Auth::user('coach');
        $athletes    = $coach->athletes()->orderBy('name')->get();
        $disciplines = Discipline::orderBy('name')->get();
        $ageCategories  = AgeCategory::orderBy('name')->get();

        return view('coach.athletes.create', compact(
            'athletes', 'disciplines', 'ageCategories'
        ));
    }

    /**
     * Simpan satu registrasi baru.
     * Coach bisa daftar satu athlete ke banyak nomor — submit satu per satu.
     */
    public function store(StoreRegistrationRequest $request): RedirectResponse
    {
        $coach = Auth::user('user')->id;

        Registration::create([
            'coach_id'        => $coach->id,
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
