<?php

namespace App\Services;

use App\Models\Athlete;
use App\Models\EventCategory;
use App\Models\EventParticipant;
use App\Models\Invoice;
use App\Models\Discipline;
use App\Models\Event;
use App\Models\AgeCategory;
use App\Models\CompetitionSession;
use App\Models\InvoiceItem;
use App\Models\Contest; 
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    const PRICE_PER_DISCIPLINE = 200000;

    /**
     * Generate nomor registrasi yang aman dari race condition
     * Gunakan pessimistic lock agar tidak ada duplikat di concurrent request
     */
    private function generateRegistrationNumber(): string
    {
        $date   = now()->format('Ymd');
        $prefix = 'REG-' . $date . '-';

        // FOR UPDATE mencegah race condition — baris terkunci selama transaksi
        $last = EventParticipant::where('registration_number', 'like', $prefix . '%')
                    ->orderByDesc('registration_number')
                    ->lockForUpdate()
                    ->value('registration_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function registerDisciplines(Athlete $athlete, array $disciplines): Invoice
    {
        return DB::transaction(function () use ($athlete, $disciplines) {

            $invoice   = $this->getOrCreateInvoice();
            $pivotData = [];
            
    foreach ($disciplines as $item) {

    $disciplineId  = $item['discipline_id'];
    $ageCategoryId = $item['age_category_id'];
    /**
 * Ambil event aktif
 */
    $event = Event::where('status', 'published')->first();

    if (! $event) {
    throw new \Exception('Tidak ada event aktif.');
    }
    // Resolve model
    $discipline = Discipline::findOrFail($disciplineId);
    $ageCategory = AgeCategory::findOrFail($ageCategoryId);

    // Nama category otomatis
    $categoryName = implode(' ', [
        $discipline->name,
        $ageCategory->name,
        ucfirst($athlete->gender),
    ]);

    $pivotData[$disciplineId] = [
        'age_category_id' => $ageCategoryId,
    ];

    /**
     * 1. Event Category
     */
    $eventCategory = EventCategory::firstOrCreate(
        [
            'event_id'        => $event->id,
            'sport_id'        => $discipline->sport_id,
            'gender'          => $athlete->gender,
            'discipline_id'   => $disciplineId,
            'age_category_id' => $ageCategoryId,
        ]
    );
    /**
     * 2. Registration
     */
    $registration = Registration::firstOrCreate(
        [
            'athlete_id'      => $athlete->id,
            'discipline_id'   => $disciplineId,
            'age_category_id' => $ageCategoryId,
            'event_category_id' => $eventCategory->id,
        ],
        [
            'user_id'       => Auth::id(),
            'status'        => 'pending',
            'registered_at' => now(),
        ]
    );


    /**
     * 3. Competition Session
     */
    $session = CompetitionSession::firstOrCreate(
        [
            'event_category_id' => $eventCategory->id,
            'gender'            => $athlete->gender,
        ],
        [
            'start_time'           => now()->startOfDay()->addHours(8),
            'duration_per_athlete' => 4,
            'status'               => 'draft',
        ]
    );

    /**
     * 4. Event Participant
     */
    $eventParticipant = EventParticipant::firstOrCreate(
        [
            'registration_id' => $registration->id,
            'athlete_id'      => $athlete->id,
        ],
        [
            'event_category_id'   => $eventCategory->id,
            'registration_number' => $this->generateRegistrationNumber(),
            'registered_by'       => Auth::id(),
            'status'              => EventParticipant::STATUS_PENDING,
        ]
    );

    /**
     * 5. Contest
     */
    $nextOrder = Contest::where(
        'competition_session_id',
        $session->id
    )->max('appearance_order');

    $nextOrder = $nextOrder ? $nextOrder + 1 : 1;

    Contest::firstOrCreate(
        [
            'registration_id' => $registration->id,
        ],
        [
            'competition_session_id' => $session->id,
            'athlete_id'             => $athlete->id,
            'discipline_id'          => $disciplineId,
            'age_category_id'        => $ageCategoryId,
            'appearance_order'       => $nextOrder,
            'status'                 => 'scheduled',
        ]
    );

    /**
     * 6. Invoice Item
     */
    $alreadyExists = InvoiceItem::where('invoice_id', $invoice->id)
        ->where('athlete_id', $athlete->id)
        ->where('discipline_id', $disciplineId)
        ->exists();

    if (! $alreadyExists) {

        InvoiceItem::create([
            'invoice_id'           => $invoice->id,
            'athlete_id'           => $athlete->id,
            'discipline_id'        => $disciplineId,
            'event_participant_id' => $eventParticipant->id,
            'price'                => self::PRICE_PER_DISCIPLINE,
        ]);
    }
}

            // 5. Sync pivot athlete_discipline
            $athlete->disciplines()->syncWithoutDetaching($pivotData);

            // 6. Hitung ulang total invoice
            $invoice->recalculateTotal();

            return $invoice;
        });
    }

    private function getOrCreateInvoice(): Invoice
    {
        return Invoice::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'status'  => Invoice::STATUS_DRAFT,
            ],
            [
                'invoice_number' => Invoice::generateNumber(),
                'total_amount'   => 0,
            ]
        );
    }

    public function approve(Registration $registration): void
    {
        $registration->update(['status' => 'approved']);
    }

    public function reject(Registration $registration): void
    {
        $registration->update(['status' => 'rejected']);
    }
}