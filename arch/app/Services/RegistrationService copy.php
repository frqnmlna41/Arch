<?php

namespace App\Services;

use App\Models\Athlete;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Registration;
use App\Models\EventCategory;
use App\Models\EventParticipant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;




class RegistrationService
{
    const PRICE_PER_DISCIPLINE = 200000;

private function generateRegistrationNumber(): string
{
    $date = now()->format('Ymd');

    $countToday = EventParticipant::whereDate('created_at', now())->count() + 1;

    return 'REG-' . $date . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);
}

public function registerDisciplines(Athlete $athlete, array $disciplines): Invoice
{
    return DB::transaction(function () use ($athlete, $disciplines) {

        $invoice   = $this->getOrCreateInvoice();
        $pivotData = [];

        foreach ($disciplines as $item) {

            $disciplineId  = $item['discipline_id'];
            $ageCategoryId = $item['age_category_id'];

            $pivotData[$disciplineId] = [
                'age_category_id' => $ageCategoryId,
            ];

            // ✅ REGISTRATION
            $registration = Registration::firstOrCreate(
                [
                    'athlete_id'      => $athlete->id,
                    'discipline_id'   => $disciplineId,
                    'age_category_id' => $ageCategoryId,
                ],
                [
                    'user_id'       => Auth::id(),
                    'status'        => 'pending',
                    'registered_at' => now(),
                ]
            );

            // ✅ EVENT PARTICIPANT (pakai updateOrCreate biar aman)
            $eventParticipant = EventParticipant::updateOrCreate(
                [
                    'registration_id' => $registration->id,
                    'athlete_id'      => $athlete->id,
                ],
                [
                    'registration_number' => $this->generateRegistrationNumber(),
                    'registered_by'       => Auth::id(),
                    'status'              => 'pending',
                ]
            );

            // ✅ INVOICE ITEM
            $alreadyExists = InvoiceItem::where([
                'invoice_id'           => $invoice->id,
                'event_participant_id' => $eventParticipant->id,
            ])->exists();

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

        $athlete->disciplines()->syncWithoutDetaching($pivotData);
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