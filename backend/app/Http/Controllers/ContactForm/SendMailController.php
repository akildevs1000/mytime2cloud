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

        // 1. Get your payload
        $data = $request->only(['name', 'company', 'email', 'phone', 'message']);

        // 2. Format the plain text message body
        $body = "New Contact Form Submission:\n\n" .
            "Name: {$data['name']}\n" .
            "Company: {$data['company']}\n" .
            "Email: {$data['email']}\n" .
            "Phone: {$data['phone']}\n\n" .
            "Message:\n{$data['message']}";

        // 3. Send using 'sendmail' driver explicitly (bypasses default SMTP)
        Mail::mailer('sendmail')->raw($body, function ($message) use ($data) {
            $message->to('francisgill1000@gmail.com') // Your fixed receiver
                ->subject('New Lead from ' . $data['company'])
                ->replyTo($data['email'], $data['name']); // So you can reply to the user
        });

        return response()->json(['status' => 'Email sent via local mailer!']);

        // Validate payload
        $data = $request->validate([
            'name' => 'required|string',
            'company' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        // Send to a fixed address (e.g., your inbox)
        Mail::to("francisgill1000@gmail.com")->send(new ContactMail($data));

        return response()->json(['message' => 'Mail sent successfully!'], 200);
    }
}
