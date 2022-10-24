<?php

use App\Mail\TestMail;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\ReportNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/test', function (Request $request) {
    return "Awesome APIs";
});

Route::get('/generate_attendance_log', function (Request $request) {

    $arr = [];
    for ($i = 1; $i <= 5; $i++) {
        for ($j = 13; $j <= 13; $j++) {
            for ($k = 1; $k <= 1; $k++) {
                $time =  rand(8, 20);
                $time = $time < 10 ? '0' . $time : $time;
                $arr[] = [
                    'UserID' => $i,
                    'LogTime' => "2022-10-$j $time:00:00",
                    'DeviceID' => "OX-8862021010097",
                    'company_id' => "1",
                ];
            }
        }
    }
    // return $arr;
    DB::table('attendance_logs')->insert($arr);
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

Route::get('/test/whatsapp', function () {
    $curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://graph.facebook.com/v14.0/102482416002121/messages',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "messaging_product": "whatsapp",
    "to": "923108559858",
    "type": "template",
    "template": {
        "name": "hello_world",
        "language": {
            "code": "en_US"
        }
    }
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: Bearer EAAP9IfKKSo0BAC10TcenQ4ZBxqsLpIbtyAPdIDZC7g1bfM33lKcrZAfSnrPWeCSwjPqyj7UtOiZC3nxhcdlUqHVSpesIPf1GDA3fLC7U02Kc38TyQZAplMBKar6Ie5GfD8wbIoyhqZCcjOcykO57LS8wWjbU1637WkZByup90rFfZC3C3FryoQlFZAyZB80cilbV2LVrRcHsf8zpcP2lYpywPC'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
});

Route::get('/test/{email}', function (Request $request, $email) {

    if (!env('IS_MAIL')) {
        return "mail not allowed";
    }

    $models = ReportNotification::get();

    foreach ($models as $model) {
        if ($model->frequency == "Daily") {
            if (in_array("Email", $model->mediums)) {
                Mail::to($model->tos)
                    ->cc($model->ccs)
                    ->bcc($model->bccs)
                    ->queue(new TestMail($model->subject, $model->body));
            }
            if (in_array("Whatsapp", $model->mediums)) {
                Mail::to($model->tos)->send(new TestMail($model->subject, $model->body));
            }
        }
    }

    // "reports": [
    // "Daily Summary",
    // "Weekly Summary",
    // "Monthly Summary",
    // "Yearly Summary"
    // ],

    return $model;
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
