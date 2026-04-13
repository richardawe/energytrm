<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('etrm:reset-checklists')]
#[Description('Create or reset end-of-business checklists for all business units (runs daily at midnight)')]
class ResetChecklists extends Command
{
    public function handle(): void
    {
        // Phase 3 will implement this fully when the eob_checklists table is built.
        // Placeholder so the scheduler registration doesn't error.
        $this->info('[' . now()->toDateTimeString() . '] EoB checklist reset — placeholder (Phase 3).');
    }
}
