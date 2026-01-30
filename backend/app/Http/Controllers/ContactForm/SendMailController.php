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

        $data = $request->all();

        // 1. Destination Email
        $to = "francisgill1000@gmail.com";
        $subject = "New Lead: " . ($data['company'] ?? 'Contact Form');

        // 2. Build Plain Text Body
        $message = "Name: " . ($data['name'] ?? 'N/A') . "\r\n";
        $message .= "Email: " . ($data['email'] ?? 'N/A') . "\r\n";
        $message .= "Message: " . ($data['message'] ?? 'N/A');

        // 3. Set Headers (This is how you "spoof" the sender info)
        $headers = "From: system@yourdomain.com\r\n";
        $headers .= "Reply-To: " . ($data['email'] ?? 'system@yourdomain.com') . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        // 4. Use NATIVE PHP mail()
        // This bypasses Symfony Mailer and Laravel SMTP configs
        if (mail($to, $subject, $message, $headers)) {
            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'error', 'msg' => 'Server failed to send']);
        }
     

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
