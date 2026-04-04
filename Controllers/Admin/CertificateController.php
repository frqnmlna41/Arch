<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Winner;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * CertificateController
 *
 * php artisan make:controller Admin/CertificateController
 *
 * Install DomPDF:
 *   composer require barryvdh/laravel-dompdf
 *   php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
 *
 * Akses:
 *   admin  → generate certificate, view all
 *   coach/athlete/judge → download sertifikat (view miliknya)
 */
class CertificateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:generate certificates')->only(['generate', 'generateAll']);
        $this->middleware('permission:view certificates')->only(['index', 'show', 'download']);
    }

    // ──────────────────────────────────────────────────────────────
    // INDEX – Daftar semua sertifikat
    // ──────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        try {
            $certificates = Certificate::query()
                ->with([
                    'winner.athlete:id,name,club',
                    'winner.event:id,name',
                    'winner.discipline:id,name',
                    'winner.ageCategory:id,name',
                    'issuedBy:id,name',
                ])
                ->when($request->event_id, fn ($q, $v) => $q->whereHas('winner', fn ($wq) => $wq->where('event_id', $v)))
                ->when($request->athlete_id, fn ($q, $v) => $q->whereHas('winner', fn ($wq) => $wq->where('athlete_id', $v)))
                ->when($request->boolean('issued'), fn ($q) => $q->whereNotNull('issued_at'))
                ->when($request->boolean('printed'), fn ($q) => $q->where('is_printed', true))
                ->latest('issued_at')
                ->paginate($request->integer('per_page', 15));

            return response()->json(['status' => 'success', 'data' => $certificates]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // GENERATE – Generate sertifikat untuk satu winner
    // ──────────────────────────────────────────────────────────────

    public function generate(Request $request, Winner $winner): JsonResponse
    {
        try {
            $this->authorize('generateCertificate', $winner->event);

            // Cek apakah sudah ada sertifikat
            if ($winner->hasCertificate()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Certificate already exists for this winner.',
                    'data'    => $winner->certificate,
                ], 422);
            }

            $winner->load([
                'athlete:id,name,club,gender,birth_date,photo',
                'event:id,name,location,start_date,end_date',
                'discipline:id,name,type',
                'ageCategory:id,name',
            ]);

            $certificate = DB::transaction(function () use ($winner, $request) {
                // Buat record certificate dahulu
                $cert = Certificate::create([
                    'winner_id'          => $winner->id,
                    'issued_by'          => $request->user()->id,
                    'certificate_number' => Certificate::generateNumber(),
                    'issued_at'          => now(),
                    'template_version'   => '1.0',
                ]);

                // Generate PDF
                $pdf      = $this->buildPdf($winner, $cert);
                $filename = "certificates/{$cert->certificate_number}.pdf";
                $path     = 'app/public/' . $filename;

                // Simpan ke storage
                Storage::disk('public')->put($filename, $pdf->output());

                // Update path di database
                $cert->update(['file_path' => $filename]);

                return $cert;
            });

            return response()->json([
                'status'  => 'success',
                'data'    => $certificate->load('winner.athlete:id,name', 'issuedBy:id,name'),
                'message' => "Certificate [{$certificate->certificate_number}] generated.",
            ], 201);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // GENERATE ALL – Generate semua sertifikat untuk satu event
    // ──────────────────────────────────────────────────────────────

    public function generateAll(Request $request, \App\Models\Event $event): JsonResponse
    {
        try {
            $this->authorize('generateCertificate', $event);

            if ($event->status !== 'completed') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Certificates can only be generated for completed events.',
                ], 422);
            }

            $winners = Winner::where('event_id', $event->id)
                ->whereDoesntHave('certificate')
                ->with([
                    'athlete:id,name,club,gender,birth_date',
                    'event:id,name,location,start_date,end_date',
                    'discipline:id,name',
                    'ageCategory:id,name',
                ])
                ->get();

            if ($winners->isEmpty()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'All certificates are already generated.',
                    'data'    => ['generated' => 0],
                ]);
            }

            $generated = 0;

            DB::transaction(function () use ($winners, $request, &$generated) {
                foreach ($winners as $winner) {
                    $cert = Certificate::create([
                        'winner_id'          => $winner->id,
                        'issued_by'          => $request->user()->id,
                        'certificate_number' => Certificate::generateNumber(),
                        'issued_at'          => now(),
                        'template_version'   => '1.0',
                    ]);

                    $pdf      = $this->buildPdf($winner, $cert);
                    $filename = "certificates/{$cert->certificate_number}.pdf";

                    Storage::disk('public')->put($filename, $pdf->output());
                    $cert->update(['file_path' => $filename]);

                    $generated++;
                }
            });

            return response()->json([
                'status'  => 'success',
                'message' => "{$generated} certificate(s) generated for event [{$event->name}].",
                'data'    => ['generated' => $generated],
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // DOWNLOAD – Download PDF sertifikat
    // ──────────────────────────────────────────────────────────────

    public function download(Certificate $certificate): Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = request()->user();

            // Athlete hanya bisa download sertifikat miliknya
            if ($user->hasRole('athlete')) {
                $athleteProfile = $user->athletes()->first();
                $certAthleteId  = $certificate->winner?->athlete_id;

                if (! $athleteProfile || $certAthleteId !== $athleteProfile->id) {
                    abort(403, 'Unauthorized.');
                }
            }

            if (! $certificate->file_path || ! Storage::disk('public')->exists($certificate->file_path)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Certificate file not found. Please regenerate.',
                ], 404);
            }

            // Update status printed
            $certificate->update([
                'is_printed' => true,
                'printed_at' => now(),
            ]);

            $fullPath = Storage::disk('public')->path($certificate->file_path);

            return response()->download(
                $fullPath,
                "Certificate-{$certificate->certificate_number}.pdf",
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // PRIVATE: Build DomPDF
    // ──────────────────────────────────────────────────────────────

    private function buildPdf(Winner $winner, Certificate $certificate): \Barryvdh\DomPDF\PDF
    {
        $data = [
            'certificate'  => $certificate,
            'winner'       => $winner,
            'athlete'      => $winner->athlete,
            'event'        => $winner->event,
            'discipline'   => $winner->discipline,
            'age_category' => $winner->ageCategory,
            'rank_label'   => $winner->medalLabel,
            'issued_date'  => now()->translatedFormat('d F Y'),
            'qr_code'      => $this->generateQrCode($certificate->certificate_number),
        ];

        return Pdf::loadView('certificates.template', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'dpi'                  => 150,
            ]);
    }

    /**
     * Generate QR code URL untuk verifikasi sertifikat.
     * (Bisa diganti dengan library QR code jika diperlukan)
     */
    private function generateQrCode(string $certNumber): string
    {
        $verifyUrl = url("/verify-certificate/{$certNumber}");
        return "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($verifyUrl);
    }
}
