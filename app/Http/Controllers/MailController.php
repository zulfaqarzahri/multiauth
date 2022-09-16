<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MailController extends Controller
{
    public function sendMail(Request $request)
    {
        $mail_data = [
            'subject' => 'Bulk Message notification.'
        ];

        $job = (new \App\Jobs\SendEmail($mail_data))
            ->delay(now()->addSeconds(2));

        dispatch($job);

        dd("Job dispatched.");
    }
}
