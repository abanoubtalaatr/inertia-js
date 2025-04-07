<?php

namespace App\Services;

class SendGridService
{
    public $email;

    public function __construct()
    {
        $this->email = new \SendGrid\Mail\Mail;
    }

    public function sendMail($subject, $to, $data, $viewPath, $attachmentUrl = '', $file_name = '')
    {

        if (! view()->exists($viewPath)) {
            throw new \Exception("View {$viewPath} does not exist.");
        }

        $this->email->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        $this->email->setSubject($subject);
        $this->email->addTo($to);
        $this->email->addContent('text/html', view($viewPath, compact('data'))->render());

        if (! empty($attachmentUrl)) {
            $file_encoded = base64_encode(file_get_contents($attachmentUrl));
            $this->email->addAttachment(
                $file_encoded,
                'application/pdf',
                $file_name,
                'attachment'
            );
        }

        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        $sendgrid->send($this->email);
    }
}
