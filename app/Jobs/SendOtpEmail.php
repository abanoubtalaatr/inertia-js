<?php

namespace App\Jobs;

use App\Services\SendGridService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOtpEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;

    protected $otp;

    /**
     * Create a new job instance.
     */
    public function __construct(string $email, string $otp)
    {
        $this->email = $email;
        $this->otp = $otp;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SendGridService $sendGridService)
    {
        $data = ['otp' => $this->otp];

        try {
            $sendGridService->sendMail(
                'Your OTP Code', // Subject
                $this->email,    // Recipient
                $data,           // Data to pass to the view
                'emails.otp'     // Blade view path
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP: '.$e->getMessage());
        }
    }
}
