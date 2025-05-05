<!DOCTYPE html>
<html>

<head>
    <style>
        * {
            margin: 0;
            padding: 0;
            font-size: 9px;
        }

        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            border: none;
            width: 100%;
        }

        td,
        th {
            border: 1px solid #eeeeee;
            text-align: center;
            font-size: 9px;
        }

        footer {
            border-top: 1px solid #eeeeee;
            position: fixed;
            bottom: 5px;
            left: 0;
            width: 100%;
            padding: 0px 5px;
        }

        .container {
            margin: 25px
        }
    </style>
</head>

<body class="container">

    <table>
        <tr>
            <td style="border: none;width:33%;">
                <div class="row">
                    <div style="background-coldor: rgb(253, 246, 246);padding:0px;margin:0px 5px">
                        <table style="padding:0px;margin:0px">
                            <tr style="text-align: left; border :none; ">
                                <td style="text-align: left; border :none;font-size:11px;">
                                    <b>
                                        {{ $company['name'] }}
                                    </b>
                                    <br>
                                </td>
                            </tr>
                            <tr style="text-align: left; border :none;">
                                <td style="text-align: left; border :none;font-size:11px;">
                                    <span style="font-size:9px;">Email
                                        {{ $company['email'] }}</span>
                                    <br>
                                </td>
                            </tr>
                            <tr style="text-align: left; border :none;">
                                <td style="text-align: left; border :none;font-size:11px;">
                                    <span style="font-size:9px;">{{ $company['location'] }}</span>
                                    <br>
                                </td>
                            </tr>

                            <tr style="text-align: left; border :none;">
                                <td style="text-align: left; border :none;font-size:11px;">
                                    <span style="font-size:9px;">{{ $company['contact'] }}</span>
                                    <br>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
            <td style="border: none;width:33%;text-align:center">
                <div style="text-align:center;height:60px;">
                    @if (env('APP_ENV') !== 'production')
                        <img src="data:image/png;base64,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"
                            style="width:100px;">
                    @else
                        <img src="{{ $company['logo'] }}" />
                    @endif

                </div>
                <div style="margin-top: 5px;">
                    <b style="font-size: 11px">
                        Summary Report
                    </b>
                    <div style="font-size: 11px;margin-top:5px;">
                        <span
                            style="border-top: 2px solid #eeeeee;border-bottom: 2px solid #eeeeee; padding-top:3px;padding-bottom:3px;">
                            {{ date('d-M-Y', strtotime($company['from_date'])) }} -
                            {{ date('d-M-Y', strtotime($company['to_date'])) }}
                        </span>
                    </div>
                </div>

            </td>
            <td style="border: none">
            </td>
        </tr>
    </table>

    <table style="margin-top:30px">
        <tr>
            <td style="text-align: left;padding:5px;">Employee</td>
            <td style="text-align: center; padding:5px;">Branch</td>
            <td style="text-align: center; padding:5px;">Shift</td>

            @if ($shift_type == 'General')
                <td style="text-align: center; padding:5px;"> In Time </td>
                <td style="text-align: center; padding:5px;"> Out Time </td>
                <td style="text-align: center; padding:5px;"> Late In </td>
                <td style="text-align: center; padding:5px;"> Early Out </td>
            @endif


            @if ($shift_type == 'Multi')
                @for ($i = 0; $i < 5; $i++)
                    <td style="text-align: center; padding:5px;">
                        In{{ $i + 1 }}
                    </td>
                    <td style="text-align: center; padding:5px;">
                        Out{{ $i + 1 }}
                    </td>
                @endfor
            @endif

            <td>Total Hrs</td>
            <td>Status</td>
        </tr>
        @foreach ($attendances as $empID => $attendance)
            @php
                $statusColor = null;
                $statusName = $attendance->status ?? '---';
                if ($attendance->status == 'P' || $attendance->status == 'LC' || $attendance->status == 'EG') {
                    $statusColor = 'green';
                    $statusName = 'P';
                } elseif ($attendance->status == 'A' || $attendance->status == 'M') {
                    $statusColor = 'red';
                    $statusName = 'A';
                } elseif ($attendance->status == 'O') {
                    $statusColor = 'gray';
                } elseif ($attendance->status == 'L') {
                    $statusColor = 'blue';
                } elseif ($attendance->status == 'H') {
                    $statusColor = 'pink';
                } elseif ($attendance->status == '---') {
                    $statusColor = '';
                }

                $pic = getcwd() . '/no-profile-image.jpg';

                if ($attendance->employee->profile_picture) {
                    $pic = getcwd() . '/media/employee/profile_picture/' . $attendance->employee->profile_picture_raw;
                }

                if (env('APP_ENV') !== 'production') {
                    $pic = 'https://backend.mytime2cloud.com/media/employee/profile_picture/1722679555.jpg';
                }

            @endphp
            <tr>
                <td style="text-align:left;width:200px;">
                    <table>
                        <tr>
                            <td
                                style="border: none;text-align:left;width:25px;padding-left:5px;padding-top:2px;padding-bottom:2px;">
                                <img src="{{ $pic }}" style="border-radius:50%;width:25px; " />
                            </td>
                            <td style="border: none;text-align:left;padding-left:8px;">
                                {{ $attendance->employee->first_name }}
                                <br>
                                <small>{{ $attendance->employee->employee_id }}</small>
                            </td>
                        </tr>
                    </table>
                </td>

                <td>
                    {{ $attendance->employee->branch->branch_name }}
                    {{-- / {{ $attendance->employee->department->name }} --}}
                </td>

                <td style="text-align:  center;">
                    <div>
                        {{ $attendance->schedule->shift->on_duty_time }} -
                        {{ $attendance->schedule->shift->off_duty_time }}
                        <smal>
                            {{ $attendance->schedule->shift->name }}
                        </smal>
                    </div>
                </td>

                @if ($shift_type == 'General')
                    <td>
                        {{ $attendance->in }}
                    </td>
                    <td>
                        {{ $attendance->out }}
                    </td>
                @endif

                @if ($shift_type == 'Multi')
                    @for ($i = 0; $i < 5; $i++)
                        <td>
                            {{ $attendance->logs[$i]['in'] ?? '-' }}
                        </td>
                        <td>
                            {{ $attendance->logs[$i]['out'] ?? '-' }}
                        </td>
                    @endfor
                @endif
                <td>{{ $attendance->total_hrs }}</td>
                <td style="color:{{ $statusColor }}">
                    {{ $statusName }}
                </td>

            </tr>
            </tr>
        @endforeach

    </table>

    <footer>
        <table>
            <tr style="border :none">
                <td style="text-align: left;border :none;width:33%;">
                    <span style="color: green">P = Present</span>, <span style="color: red">A = Absent</span>, <span
                        style="color: grey">O = WeekOff</span>, <span style="color: blue">L = Leave</span>
                </td>

                <td style="text-align: center;border :none;width:33%;">
                    <b>Powered by</b>: <span style="color:blue">
                        <a href="{{ env('APP_URL') }}" target="_blank">{{ env('APP_NAME') }}</a>
                    </span> Printed on : {{ date('d-M-Y ') }}
                </td>

                <td style="text-align: right;border :none;width:33%;padding-right:15px;">
                    Page : {{ $counter }}
                </td>
            </tr>
        </table>
    </footer>
</body>

</html>
