<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Http\Requests\Athlete\StoreAthleteRequest;
use App\Services\RegistrationService;
use App\Models\Athlete;
use App\Models\AgeCategory;
use App\Models\Discipline;
use App\Models\Registration;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AthleteController extends Controller
{
     use AuthorizesRequests;
    /**
     * Daftar semua athlete milik coach yang sedang login.
     */
     public function __construct(
        private RegistrationService $registrationService  // ← inject service
    ) {}
    public function index(): View
    {
        $athletes = Athlete::where('user_id', Auth::id())
            ->with(['disciplines', 'ageCategories'])
            ->orderBy('name')
            ->paginate(15);

        return view('coach.athletes.index', compact('athletes'));
    }

    /**
     * Form tambah athlete baru.
     */
public function create(): View
{
    $disciplines   = Discipline::with('ageCategories')->orderBy('name')->get();
    $ageCategories = AgeCategory::orderBy('name')->get();
    $perguruan     = Auth::user()->perguruan; // nullable jika coach belum punya perguruan
    // Siapkan map di controller, bukan di blade
    $ageCategoriesMap = $disciplines->mapWithKeys(fn($d) => [
        $d->id => $d->ageCategories->map(fn($ac) => [
            'id'   => $ac->id,
            'name' => $ac->name,
        ]),
    ]);

    return view('coach.athletes.create', compact(
        'disciplines', 'ageCategories', 'ageCategoriesMap', 'perguruan'
    ));
}

    /**
     * Simpan athlete baru beserta disiplin-disiplinnya.
     * Satu athlete bisa punya banyak discipline + age_category.
     */
    // public function store(StoreAthleteRequest $request): RedirectResponse
    // {
    //     // 1. Handle upload foto
    //     $photoPath = null;
    //     if ($request->hasFile('photo')) {
    //         $photoPath = $request->file('photo')->store('athletes/photos', 'public');
    //     }

    //     // 2. Buat athlete
    //     $athlete = Athlete::create([
    //         'coach_id'   => Auth::id(),
    //         'name'       => $request->name,
    //         'birth_date' => $request->birth_date,
    //         'gender'     => $request->gender,
    //         'club'       => $request->club,
    //         'photo'      => $photoPath,
    //         'perguruan_id' => Auth::user()->perguruan_id,
    //     ]);

    //     // 3Regiistration

    //     // 3. Attach discipline + age_category (pivot)
    //     // disciplines[] = [['discipline_id' => x, 'age_category_id' => y], ...]
    //     // $pivotData = [];
    //     // foreach ($request->disciplines as $item) {
    //     //     $pivotData[$item['discipline_id']] = [
    //     //         'age_category_id' => $item['age_category_id'],
    //     //     ];
    //     // }
    //     $pivotData = [];

    //     foreach ($request->disciplines as $item) {
    //     $disciplineId   = $item['discipline_id'];
    //     $ageCategoryId  = $item['age_category_id'];

    //     // Pivot untuk tabel athlete_discipline
    //     $pivotData[$disciplineId] = [
    //         'age_category_id' => $ageCategoryId,
    //     ];

    //     // Buat Registration per kombinasi discipline + age_category
    //     Registration::create([
    //         'user_id'          => Auth::id(),
    //         'athlete_id'       => $athlete->id,
    //         'discipline_id'    => $disciplineId,
    //         'age_category_id'  => $ageCategoryId,
    //         'status'           => 'pending',
    //         'registered_at'    => now(),
    //      ]);
    //     }



    //     $athlete->disciplines()->attach($pivotData);
    //     return redirect()
    //         ->route('coach.athletes.index')
    //         ->with('success', 'Athlete berhasil ditambahkan.');
    // }
    public function store(StoreAthleteRequest $request): RedirectResponse
{
    DB::transaction(function () use ($request) {

        // 1. Handle upload foto
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('athletes/photos', 'public');
        }

        // 2. Buat athlete
        $athlete = Athlete::create([
            'user_id'     => Auth::id(),
            'name'         => $request->name,
            'birth_date'   => $request->birth_date,
            'gender'       => $request->gender,
            'club'         => $request->club,
            'photo'        => $photoPath,
            'perguruan_id' => Auth::user()->perguruan_id,
        ]);

        // 3. Registrasi discipline + generate invoice (pivot, registration, invoice, invoice item)
        $this->registrationService->registerDisciplines(
            $athlete,
            $request->disciplines
        );
    });

    return redirect()
        ->route('coach.athletes.index')
        ->with('success', 'Athlete berhasil ditambahkan dan invoice telah dibuat.');
}
    /**
     * Detail athlete.
     */
    public function show(Athlete $athlete): View
    {
        $this->authorize('view', $athlete);
        $athlete->load([    'disciplines.ageCategories',
                            'ageCategories',
                            'registrations',
                            'eventParticipants',
                            // 'eventCategories.event',
                            'winners',
                            'perguruan']);
        return view('coach.athletes.show', compact('athlete'));

        //Ambil Age category dari registration
        $registration = Registration::where('athlete_id', $athlete->id)->first();
        $age_category = AgeCategory::where('id', $registration->age_category_id)->first();
        // Ambil perguruan milik coach yang login
        $perguruan = $coach->perguruan;
        return view('coach.athletes.show', compact(
            'disciplines', 'ageCategories','athlete', 'ageCategoriesMap', 'perguruan'
        )); // nullable jika coach belum punya perguruan
     
    }


    /**
     * Hapus athlete — hanya bisa jika tidak ada registrasi aktif.
     */
    public function destroy(Athlete $athlete): RedirectResponse
    {
        $this->authorize('delete', $athlete);

        if ($athlete->registrations()->whereIn('status', ['pending', 'approved'])->exists()) {
            return back()->with('error', 'Tidak bisa menghapus athlete yang memiliki registrasi aktif.');
        }

        if ($athlete->photo) {
            Storage::disk('public')->delete($athlete->photo);
        }

        $athlete->delete();

        return redirect()
            ->route('coach.athletes.index')
            ->with('success', 'Athlete berhasil dihapus.');
    }
}
