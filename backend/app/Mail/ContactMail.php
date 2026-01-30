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


        // If you want it to come FROM the user's email:
        return $this->from($this->data['email'], $this->data['name'])
            ->subject('New Lead: ' . $this->data['company'])
            ->html("
                    <h3>New Contact Form Submission</h3>
                    <p><strong>Name:</strong> {$this->data['name']}</p>
                    <p><strong>Email:</strong> {$this->data['email']}</p>
                    <p><strong>Message:</strong> {$this->data['message']}</p>
                ");
    }
}
