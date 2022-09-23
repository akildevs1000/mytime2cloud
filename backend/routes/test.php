<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::post('/do_spaces', function (Request $request) {
    return $request->file("file")->storePublicly("upload", "do") ? 1 : "0";
});

Route::post('/log_payload', function (Request $request) {
    return $request->all();
});

Route::get('/php_mail', function (Request $request) {
    // ini_set('smtp_port', "25");

    return mail("akildevs1000@gmail.com","My subject","francis");
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
