<?php

namespace App\Console\Commands;

use App\Models\FinancialTrade;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

#[Signature('etrm:purge-trials {--hours=24 : Purge trials that expired at least this many hours ago}')]
#[Description('Delete expired trial accounts and the trades, operations records and prices they created')]
class PurgeTrialUsers extends Command
{
    public function handle(): int
    {
        $cutoff = now()->subHours((int) $this->option('hours'));

        $ids = User::whereNotNull('trial_expires_at')
            ->where('trial_expires_at', '<=', $cutoff)
            ->pluck('id');

        $purged = 0;
        foreach ($ids as $id) {
            try {
                DB::transaction(fn () => $this->purge($id));
                $purged++;
            } catch (Throwable $e) {
                // Leave this account for the next run rather than blocking the others
                report($e);
                $this->warn("Could not purge trial user {$id}: {$e->getMessage()}");
            }
        }

        $this->info("Purged {$purged} expired trial account(s).");

        return self::SUCCESS;
    }

    private function purge(int $userId): void
    {
        $tradeIds    = DB::table('trades')->where('created_by', $userId)->pluck('id');
        $finTradeIds = DB::table('financial_trades')->where('created_by', $userId)->pluck('id');

        // Physical trade children (FKs without cascade), including any raised against trial trades
        $invoiceIds = DB::table('invoices')
            ->where('created_by', $userId)
            ->orWhereIn('trade_id', $tradeIds)
            ->pluck('id');
        DB::table('settlements')->where('created_by', $userId)->orWhereIn('invoice_id', $invoiceIds)->delete();
        DB::table('invoices')->whereIn('id', $invoiceIds)->delete();
        DB::table('nominations')->where('created_by', $userId)->orWhereIn('trade_id', $tradeIds)->delete();
        DB::table('shipments')->where('created_by', $userId)->orWhereIn('trade_id', $tradeIds)->delete();

        DB::table('financial_settlements')
            ->where('created_by', $userId)
            ->orWhereIn('financial_trade_id', $finTradeIds)
            ->delete();

        DB::table('audit_logs')
            ->where('user_id', $userId)
            ->orWhere(fn ($q) => $q->where('auditable_type', Trade::class)->whereIn('auditable_id', $tradeIds))
            ->orWhere(fn ($q) => $q->where('auditable_type', FinancialTrade::class)->whereIn('auditable_id', $finTradeIds))
            ->delete();

        DB::table('financial_trades')->whereIn('id', $finTradeIds)->delete();
        DB::table('trades')->whereIn('id', $tradeIds)->delete();

        DB::table('index_grid_points')->where('entered_by', $userId)->delete();
        DB::table('reports')->where('generated_by', $userId)->delete();

        // Records the trial user touched but did not create
        DB::table('trades')->where('validated_by', $userId)->update(['validated_by' => null]);
        DB::table('eob_checklists')->where('signed_off_by', $userId)->update(['signed_off_by' => null]);

        DB::table('sessions')->where('user_id', $userId)->delete();
        DB::table('users')->where('id', $userId)->delete();
    }
}
