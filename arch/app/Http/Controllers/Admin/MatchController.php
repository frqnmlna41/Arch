<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Controller;
use App\Models\CompetitionSession;
use App\Models\Contest;
use App\Models\EventCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MatchController extends Controller
{
    use AuthorizesRequests;

    // ──────────────────────────────────────────────────────────────
    // INDEX – List semua pertandingan berdasarkan Sesi
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request): View
    {
        $query = Contest::query()
            ->with([
                'session.eventCategory.discipline:id,name', // Select specific columns
                'session.eventCategory.ageCategory:id,name',
                'session.arena:id,name',
                'athlete:id,name,perguruan_id', // Select specific columns
                'athlete.perguruan:id,name',
            ]);

        // Filter berdasarkan Event Category
        if ($request->filled('event_category_id')) {
            $query->whereHas('session', function($q) use ($request) {
                $q->where('event_category_id', $request->event_category_id);
            });
        }

        // Filter berdasarkan Session
        if ($request->filled('competition_session_id')) {
            $query->where('competition_session_id', $request->competition_session_id);
        }

        // Filter berdasarkan Status Contest
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Menggunakan join agar bisa sorting berdasarkan start_time dari session
        // Kita cache paginasi ini khusus jika tidak ada filter untuk mengurangi load
        $matches = $query->join('competition_sessions', 'contests.competition_session_id', '=', 'competition_sessions.id')
            ->select('contests.*')
            ->orderBy('competition_sessions.start_time', 'asc')
            ->orderBy('contests.appearance_order', 'asc')
            ->simplePaginate(50); // MENGGUNAKAN SIMPLE PAGINATE UNTUK PERFORMA

        // Cache Data untuk dropdown filter & form (Cache 1 Hari, di-flush saat ada event baru)
        $eventCategories = \Illuminate\Support\Facades\Cache::remember('admin.event_categories.dropdown', 86400, function() {
            return EventCategory::with(['discipline:id,name', 'ageCategory:id,name'])->get(['id', 'discipline_id', 'age_category_id']);
        });
        
        $sessions = \Illuminate\Support\Facades\Cache::remember('admin.competition_sessions.dropdown', 86400, function() {
            return CompetitionSession::with(['eventCategory.discipline:id,name', 'eventCategory.ageCategory:id,name', 'arena:id,name'])
                ->orderBy('start_time')
                ->get(['id', 'event_category_id', 'arena_id', 'start_time', 'gender']);
        });

        return view('admin.matches.index', compact('matches', 'eventCategories', 'sessions'));
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────
    public function show(Contest $match): JsonResponse
    {
        try {
            $match->load([
                'session.eventCategory.discipline',
                'session.eventCategory.ageCategory',
                'session.arena',
                'athlete.perguruan',
                'score.judge'
            ]);

            return response()->json(['status' => 'success', 'data' => $match]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // GENERATE / UPDATE SCHEDULE
    // ──────────────────────────────────────────────────────────────
    // Karena session sudah meng-generate contest saat dibuat (di SessionController), 
    // fungsi ini bisa digunakan untuk mereset/mengupdate estimasi urutan (appearance_order) jika diperlukan
    public function generate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'competition_session_id' => ['required', 'exists:competition_sessions,id'],
            ]);

            $session = CompetitionSession::findOrFail($request->competition_session_id);
            $contests = Contest::where('competition_session_id', $session->id)
                ->orderBy('appearance_order')
                ->get();

            if ($contests->isEmpty()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tidak ada pertandingan di sesi ini.',
                ], 422);
            }

            // Memastikan status di-update ke scheduled
            foreach ($contests as $contest) {
                if ($contest->status === 'draft') {
                    $contest->update(['status' => 'scheduled']);
                }
            }

            return response()->json([
                'status'  => 'success',
                'message' => "Jadwal untuk sesi ini berhasil divalidasi dan dijadwalkan.",
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE STATUS PERTANDINGAN
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, Contest $match): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|in:scheduled,ongoing,completed,cancelled'
            ]);

            if ($match->status === 'completed') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Cannot update a completed match.',
                ], 422);
            }

            $match->update(['status' => $request->status]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Match status updated.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Contest $match): JsonResponse
    {
        try {
            if (in_array($match->status, ['ongoing', 'completed'])) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Cannot delete an ongoing or completed match.',
                ], 422);
            }

            $match->score()->delete();
            $match->delete();

            return response()->json(['status' => 'success', 'message' => 'Match deleted.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
