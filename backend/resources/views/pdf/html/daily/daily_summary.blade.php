{{-- @php
phpinfo();
die();
@endphp --}}
<!DOCTYPE html>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<head>
    <style>
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            border: none;
            width: 100%;
        }

        td,
        th {
            border: 1px solid #eeeeee;
            text-align: left;
        }

        tr:nth-child(even) {
            /* background-color: #eeeeee; */
            border: 1px solid #eeeeee;
        }

        th {
            font-size: 9px;

        }

        td {
            font-size: 7px;
        }

        footer {
            width: 100%;
            position: fixed;
            bottom: 0;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
@php
    $totPresent = [];
    $totAbsent = [];
    $totMissing = [];
@endphp

<body>
    <table>
        <tr>
            <td style="text-align: center; border: none">
                <div>
                    <h1>Daily Summary</h1>
                </div>
            </td>
        </tr>
        {{-- border-width: 5px 20px --}}
    </table>
    {{-- <hr style="border-top: 1px  solid #8c8b8b;border-width: 1px 1px"> --}}
    <table style="margin-top: -20px !important;">
        <tr>
            <td style="text-align: left; border :none; padding:15px;">
                <div style="display: flex">

                    <img src="https://seeklogo.com/images/B/business-company-logo-C561B48365-seeklogo.com.png"
                        height="70px" width="100">


                    <table style="text-align: left; border :none; margin-top:10px">
                        <tr style="text-align: left; border :none;">
                            <td style="text-align: left; border :none;"><strong>{{ $company->name ?? 'Sample Company' }}
                                </strong></td>
                        </tr>
                        <tr style="text-align: left; border :none;">
                            <td style="text-align: left; border :none;"><strong>{{ $company->company_code ?? 'AE0001' }}
                                </strong>
                            </td>
                        </tr>
                        <tr style="text-align: left; border :none;">
                            <td style="text-align: left; border :none;">
                                <strong>{{ 'Akil Security & Alarm Systemss LLC BR.' ?? 'Waleed Road Burdubai' }}
                                </strong>
                            </td>
                        </tr>
                    </table>

                </div>
            </td>
            <td style="text-align: right; border :none; width:120px;">
                <table style="text-align: left; border :none; margin-top:0px;">

                    <tr style="text-align: left; border :none;">
                        <td style="text-align: left; border :none; color: green;"><b>Present: </b></td>
                        <td style="text-align: left; border :none; color: green;">
                            {{ $info->total_present ?? 22 }}</td>
                    </tr>
                    <tr style="text-align: left; border :none;">
                        <td style="text-align: left; border :none; color: red;"><b>Absent: </b></td>
                        <td style="text-align: left; border :none; color: red;">{{ $info->total_absent ?? 7 }}</td>
                    </tr>
                    <tr style="text-align: left; border :none;">
                        <td style="text-align: left; border :none; color: #f34100ed;"><b>Missing: </b></td>
                        <td style="text-align: left; border :none; color: #f34100ed;">{{ $info->total_missing ?? 13 }}
                        </td>
                    </tr>

                    <tr style="text-align: left; border :none;">
                        <td style="text-align: left; border :none;"><strong>Date: </strong></td>
                        <td style="text-align: left; border :none;">{{ $info->daily_date ?? '2022-10-21' }}</td>
                    </tr>
                    <tr style="text-align: left; border :none;">
                        <td style="text-align: left; border :none;"><b>Department: </b></td>
                        <td style="text-align: left; border :none;">{{ $info->department ?? 'IT' }}</td>
                    </tr>

                </table>
            </td>

            </td>
        </tr>
    </table>
    {{-- <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left; width:120px;"><b>Date: </b>{{ $req->daily_date }}</td>
            <td style="text-align: left; width:120px;"><b>Department: </b>{{ $info->department }}</td>
            <td style="text-align: left; width:120px; color: green;"><b>Present</b>: {{ $info->total_present }}</td>
            <td style="text-align: left; width:120px; color: red;"><b>Absent</b>: {{ $info->total_absent }}</td>
            <td style="text-align: left; width:120px; color: #f34100ed;"><b>Missing</b>: {{ $info->total_missing }}
            </td>
        </tr>
    </table> --}}

    @php
        $dep = ['Ifix', 'Warehouse', 'Bff Head Office', 'Zawaya Walk', 'Vbrand', 'Silicon', 'Magnify'];
        $eid = ['553', '270', '274', '199', '583', '618', '584', '282', '501', '23', '582', '290'];
        $name = ['Joan Tabinas', 'Shinoos', 'Faisal', 'Zawaya Walk', 'Sufail', 'Najeeb', 'Arshad'];
        $in = ['14:07', '15:59', '13:55', '01:56', '13:42', '11:02', '21:42'];
        $out = ['14:07', '15:59', '13:55', '01:56', '13:42', '11:02', '21:42'];
        $total_hours = ['14:07', '15:59', '13:55', '01:56', '13:42', '11:02', '21:42'];
        $ot = ['14:07', '15:59', '13:55', '01:56', '13:42', '11:02', '21:42'];

        $device_out = ['CMD', 'AFD', 'BMD', 'MOD', 'RKMD', 'MBD', 'RKMD'];
        $device_in = ['CMD', 'AFD', 'BMD', 'MOD', 'RKMD', 'MBD', 'RKMD'];
        $total_hours = ['14:07', '15:59', '13:55', '01:56', '13:42', '11:02', '21:42'];
        $status = ['A', 'P', '---'];

        $statusColor = '';
        // $i = 0;
    @endphp
    <table style="margin-top: 5px !important;">
        <tr style="text-align: left;font-weight:bold">
            <td style="text-align:  center;"> # </td>
            <td style="text-align:  center;"> Name </td>
            <td style="text-align:  center;width:80px"> EID </td>
            <td style="text-align:  center;width:80px"> In </td>
            <td style="text-align:  center;width:80px"> Out </td>
            <td style="text-align:  center;width:80px"> Total Hours </td>
            <td style="text-align:  center;width:80px"> OT </td>
            <td style="text-align:  center;width:80px"> Status </td>
            <td style="text-align:  center;width:150px"> Device In </td>
            <td style="text-align:  center;width:150px"> Device Out </td>
        </tr>
        @for ($i = 1; $i <= 100; $i++)
            @php
                $resStatus = $status[array_rand($status, 1)];
                if ($resStatus == 'P') {
                    $totPresent[] = $i;
                    $statusColor = 'green';
                } elseif ($resStatus == 'A') {
                    $totAbsent[] = $i;
                    $statusColor = 'red';
                } elseif ($resStatus == '---') {
                    $totMissing[] = $i;
                    $statusColor = '#f34100ed';
                }

            @endphp
            <tbody>

                <tr style="text-align:  center; ">
                    <td>{{ $i }}</td>
                    <td style="text-align:  center; width:120px"> {{ $name[array_rand($name, 1)] ?? '---' }}</td>
                    <td style="text-align:  center;">{{ $eid[array_rand($eid, 1)] ?? '---' }}</td>
                    <td style="text-align:  center;"> {{ $in[array_rand($in, 1)] ?? '---' }} </td>
                    <td style="text-align:  center;"> {{ $out[array_rand($out, 1)] ?? '---' }} </td>
                    <td style="text-align:  center;"> {{ $total_hours[array_rand($total_hours, 1)] ?? '---' }} </td>
                    <td style="text-align:  center;"> {{ $ot[array_rand($ot, 1)] ?? '---' }} </td>
                    <td style="text-align:  center; color:{{ $statusColor }}">
                        {{ $resStatus }}
                    </td>
                    <td style="text-align:  center;"> {{ $device_in[array_rand($device_in, 1)] ?? '---' }} </td>
                    <td style="text-align:  center;"> {{ $device_out[array_rand($device_out, 1)] ?? '---' }} </td>
                </tr>
            </tbody>
        @endfor

        @php

            $p = count($totPresent);
            // dd($p);
            function totPresent($p = null)
            {
                return $p;
            }

        @endphp
        <h1>
            {{ count($totPresent) }}
            {{ count($totAbsent) }}
            {{ count($totMissing) }}
        </h1>
    </table>

</body>

</html>
