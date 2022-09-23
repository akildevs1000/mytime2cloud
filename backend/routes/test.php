<?php

use App\Mail\TestMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/test/{email}', function (Request $request,$email) {

    $data = [
        'title' => 'for test mail',
        'body' => 'this is from akil security system',
    ];

    if (!env('IS_MAIL')) {
       return "mail not allowed";
    }

    Mail::to($email)->send(new TestMail($data));

});

Route::post('/do_spaces', function (Request $request) {
    return $request->file("file")->storePublicly("upload", "do") ? 1 : "0";
});

Route::post('/log_payload', function (Request $request) {
    return $request->all();
});

Route::get('/php_mail', function (Request $request) {

    $to = "akildevs1000@gmail.com";
    $subject = "My subject";
    $txt = "Hello world!";
    $headers = "From: francisgill1000@gmail.com";


    $mail = mail($to, $subject, $txt, $headers);
    if ($mail) {
        return "mail sent";
    }
    return "not sent";
    // ini_set('SMTP', "server.com");
    // ini_set('smtp_port', "25");
    // ini_set('sendmail_from', "email@domain.com");


    $to = env("RECIPIENT_LIST");
    $subject = "HTML email";

    $message = "
                <html>
                <head>
                <title>HTML email</title>
                </head>
                <body>
                <p>This email contains HTML Tags!</p>
                <table>
                <tr>
                <th>Firstname</th>
                <th>Lastname</th>
                </tr>
                <tr>
                <td>John</td>
                <td>Doe</td>
                </tr>
                </table>
                </body>
                </html>
                ";

    // Always set content-type when sending HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

    // More headers
    $headers .= 'From: <akildevs1000@gmail.com>' . "\r\n";

    return mail($to, $subject, $message, $headers);
});
