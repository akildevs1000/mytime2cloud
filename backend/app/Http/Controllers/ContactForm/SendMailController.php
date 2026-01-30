<?php

namespace App\Http\Controllers\ContactForm;

use App\Http\Controllers\Controller;
use App\Mail\ContactMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SendMailController extends Controller
{
    public function send(Request $request)
    {
        // Validate payload
        $data = $request->validate([
            'name' => 'required|string',
            'company' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        $userEmail = $request->input('email'); // The address the user input
        $userName = $request->input('name');

        // Mail::raw($data["message"], function ($message) use ($userEmail, $userName) {
        //     $message->to('akildevs1000@gmail.com')
        //         ->from($userEmail, $userName) // Dynamic From Address
        //         ->subject('New User Inquiry');
        // });


        $userInputEmail = $request->input('email'); // "customer@gmail.com"

        Mail::send([], [], function ($message) use ($userInputEmail) {
            $message->to('your-admin-email@domain.com')
                ->from('verified-system-email@domain.com', 'Website Contact Form')
                ->replyTo($userInputEmail) // This is the magic line
                ->subject('New Inquiry from ' . $userInputEmail)
                ->setBody('The user sent this message...');
        });


        // Mail::mailer('dynamic_from')
        //     ->to("akildevs1000@gmail.com")
        //     ->send(new ContactMail($data));

        // Send to a fixed address (e.g., your inbox)
        // Mail::to($data["email"])->send(new ContactMail($data));

        return response()->json(['message' => 'Mail sent successfully!'], 200);
    }
}
