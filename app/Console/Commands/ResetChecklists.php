<?php

namespace App\Console\Commands;

use App\Models\EobChecklist;
use App\Models\Party;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('etrm:reset-checklists')]
#[Description('Create or reset end-of-business checklists for all business units (runs daily at midnight)')]
class ResetChecklists extends Command
{
    public function handle(): void
    {
        $today = today();
        $businessUnits = Party::where('internal_external', 'Internal')
            ->where('party_type', 'BU')
            ->where('status', 'Authorized')
            ->get();

        foreach ($businessUnits as $bu) {
            $checklist = EobChecklist::firstOrCreate([
                'checklist_date'   => $today,
                'business_unit_id' => $bu->id,
            ]);
            $checklist->update([
                'signed_off'    => false,
                'signed_off_by' => null,
                'signed_off_at' => null,
            ]);
            $checklist->refreshItems();
        }

        $this->info('[' . now()->toDateTimeString() . '] EoB checklists reset for ' . $businessUnits->count() . ' BUs.');
    }
}
