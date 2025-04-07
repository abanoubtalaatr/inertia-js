<?php

namespace App\Console\Commands;

use App\Models\Otp;
use Illuminate\Console\Command;

class CleanExpiredOtps extends Command
{
    protected $signature = 'otps:clean';

    protected $description = 'Delete expired OTP records';

    public function handle()
    {
        $batchSize = 1000;
        $totalDeleted = 0;

        do {
            $deleted = Otp::where('expires_at', '<', now())->limit($batchSize)->delete();
            $totalDeleted += $deleted;

            \Log::info("Batch deleted: $deleted OTPs.");
        } while ($deleted > 0);

        \Log::info("Total deleted OTPs: $totalDeleted");
        $this->info("Total Deleted OTPs: $totalDeleted");
    }
}
