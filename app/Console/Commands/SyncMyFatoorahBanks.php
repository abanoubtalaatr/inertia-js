<?php

namespace App\Console\Commands;

use App\Models\Bank;
use App\Services\MyFatoorahService;
use Illuminate\Console\Command;

class SyncMyFatoorahBanks extends Command
{
    protected $signature = 'myfatoorah:sync-banks';

    protected $description = 'Sync banks from MyFatoorah';

    public function handle(MyFatoorahService $myFatoorahService)
    {
        try {
            $this->info('Starting bank synchronization from MyFatoorah...');

            $result = $myFatoorahService->syncBanks();

            if ($result) {
                $banksCount = Bank::count();
                $this->info("Successfully synced banks! Total banks: {$banksCount}");
                $this->info("Successfully synced banks! Total banks: {$result}");

                return 0;
            }

            $this->error('Failed to sync banks!');

            return 1;

        } catch (\Exception $e) {
            $this->error('Error occurred while syncing banks:');
            $this->error($e->getMessage());

            \Log::error('MyFatoorah Banks Sync Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }
    }
}
