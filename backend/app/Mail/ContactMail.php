<?php


namespace App\Mail;

use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        // MUST be an email from your server's domain (e.g., info@mytime2cloud.com)
        return $this->subject('New Lead: ' . ($this->data['company'] ?? 'Contact'))
            ->html("
            <h3>New Contact Form Submission</h3>
            <p><strong>Name:</strong> {$this->data['name']}</p>
            <p><strong>Email:</strong> {$this->data['email']}</p>
            <p><strong>Message:</strong> {$this->data['message']}</p>
        ");
    }
}
