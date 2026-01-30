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

        // Send to a fixed address (e.g., your inbox)
        Mail::to($data["email"])->send(new ContactMail($data));

        return response()->json(['message' => 'Mail sent successfully!'], 200);
    }
}
