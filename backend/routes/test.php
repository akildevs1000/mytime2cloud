<?php

use App\Mail\TestMail;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/test', function (Request $request) {
    return "Awesome APIs";
});

Route::get('/test-re', function (Request $request) {
    Employee::truncate();
    DB::statement('DELETE FROM users WHERE id > 2');

    return 'done';

});

Route::get('/test-date', function (Request $request) {

    // $start = date('Y-m-d');
    // $end = date('Y-m-d');

    $start = date('Y-m-1'); // hard-coded '01' for first day
    $end = date('Y-m-t');

    $model = Attendance::query();
    return $model->whereBetween('date', [$start, $end])
        ->get();

    return 'done';

});

Route::get('/storage', function (Request $request) {
    Storage::put('example.csv', 'francis');
});

Route::post('/upload', function (Request $request) {
    $file = $request->file->getClientOriginalName();
    $request->file->move(public_path('media/employee/file/'), $file);
    return $product_image = url('media/employee/file/' . $file);
    $data['file'] = $file;
});

Route::post('/fahath', function (Request $request) {
    // upload/1664210353.png
    // return Storage::disk('do')->path('upload/1664210353.png');

    // echo '<img src="' . Storage::disk('do')->path('upload/1664210353.png') . '" alt="">';
    // return Storage::disk('do')->get('upload/1664210353.png');
    $request->file('logo');
    $file = $request->file('logo');
    $ext = $file->getClientOriginalExtension();
    $fileName = time() . '.' . $ext;
    $path = $request->file('logo')->storePubliclyAs('upload', $fileName, "do");

    return isset($path);

});

Route::get('/test/{email}', function (Request $request, $email) {

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

Route::get('/do_spaces', function (Request $request) {
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