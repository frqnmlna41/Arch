<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\EventCategory;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ContestController extends Controller
{
    /**
     * Daftar semua kategori + jumlah peserta + jadwal
     */
    public function index(): View
    {
        $categories = EventCategory::with([
                'discipline',
                'ageCategory',
                'contests.athlete',
                'contests.score',
            ])
            ->withCount('contests')
            ->orderBy('discipline_id')
            ->paginate(20);

        return view('admin.contests.index', compact('categories'));
    }

    /**
     * Detail satu kategori — daftar peserta + jadwal tanding
     */
    public function show(EventCategory $eventCategory): View
    {
        $contests = Contest::where('event_category_id', $eventCategory->id)
            ->with(['athlete.perguruan', 'score'])
            ->orderBy('order_number')
            ->get();

        return view('admin.contests.show', compact('eventCategory', 'contests'));
    }

    /**
     * Generate contest slots dari registrations yang approved
     */
    public function generate(EventCategory $eventCategory): RedirectResponse
    {
        $registrations = Registration::where('discipline_id', $eventCategory->discipline_id)
            ->where('age_category_id', $eventCategory->age_category_id)
            ->where('status', 'approved')
            ->get();

        $created = 0;
        foreach ($registrations as $reg) {
            Contest::firstOrCreate(
                [
                    'event_category_id' => $eventCategory->id,
                    'athlete_id'        => $reg->athlete_id,
                ],
                [
                    'registration_id' => $reg->id,
                    'status'          => Contest::STATUS_SCHEDULED,
                ]
            );
            $created++;
        }

        return back()->with('success', "{$created} slot pertandingan berhasil digenerate.");
    }

    /**
     * Form atur jadwal (gelanggang + waktu + nomor urut)
     */
    public function editSchedule(EventCategory $eventCategory): View
    {
        $contests = Contest::where('event_category_id', $eventCategory->id)
            ->with('athlete')
            ->orderBy('order_number')
            ->get();

        return view('admin.contests.schedule', compact('eventCategory', 'contests'));
    }

    /**
     * Simpan jadwal
     */
    public function updateSchedule(Request $request, EventCategory $eventCategory): RedirectResponse
    {
        $request->validate([
            'contests'                => ['required', 'array'],
            'contests.*.id'           => ['required', 'exists:contests,id'],
            'contests.*.gelanggang'   => ['nullable', 'string', 'max:50'],
            'contests.*.start_time'   => ['nullable', 'date'],
            'contests.*.order_number' => ['nullable', 'integer', 'min:1'],
        ]);

        foreach ($request->contests as $data) {
            Contest::where('id', $data['id'])->update([
                'gelanggang'   => $data['gelanggang']   ?? null,
                'start_time'   => $data['start_time']   ?? null,
                'order_number' => $data['order_number'] ?? null,
            ]);
        }

        return back()->with('success', 'Jadwal berhasil disimpan.');
    }
}