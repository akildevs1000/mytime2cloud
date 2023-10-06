<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailNotificationForOfflineDevices extends Mailable
{
    use SerializesModels;

    public $company;
    public $offlineDevicesCount;
    public $devicesLocation;

    public function __construct($company, $offlineDevicesCount, $devicesLocation)
    {
        $this->company = $company;
        $this->offlineDevicesCount = $offlineDevicesCount;
        $this->devicesLocation = $devicesLocation;
    }

    public function build()
    {
        return $this->subject('Notification for Offline Devices')
            ->markdown('emails.NotificationForOffliveDevices'); // Use the email template you created
    }
}
