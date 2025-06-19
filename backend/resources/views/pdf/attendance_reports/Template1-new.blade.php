<!DOCTYPE html>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<body>
    <div id="footer">
        <div class="pageCounter">
            {{-- <p class="page"> </p> --}}
            <p></p>
            @php
                $p = count($data);
                if ($p <= 1) {
                    echo '<span></span>';
                } else {
                    for ($a = 1; $a <= $p; $a++) {
                        echo '<span></span>';
                    }
            } @endphp
        </div>
        <div id="pageNumbers">
            <div class="page-number" style="font-size: 9px"></div>
        </div>
    </div>
    <footer id="page-bottom-line" style="padding-top: 100px!important">
        <hr style="width: 100%;">
        <table class="footer-main-table">
            <tr style="border :none">
                <td style="text-align: left;border :none"><b>Device</b>: Main Entrance = MED, Back Entrance = BED</td>
                <td style="text-align: left;border :none"><b>Shift Type</b>: Manual = MA, Auto = AU, NO = NO</td>
                <td style="text-align: left;border :none"><b>Shift</b>: Morning = Mor, Evening = Eve, Evening2 = Eve2
                </td>
                <td style="text-align: right;border :none;">
                    <b>Powered by</b>: <span style="color:blue">
                        <a href="{{ env('APP_URL') }}" target="_blank">{{ env('APP_NAME') }}</a>
                    </span>
                </td>
                <td style="text-align: right;border :none">
                    Printed on : {{ date('d-M-Y ') }}
                </td>
            </tr>
        </table>
    </footer>
    @php
        $statusColor = '';
        $i = 0;
    @endphp

    <table width="100%" border="0" cellspacing="0" cellpadding="5"
        style="border-bottom: 1px solid #000; margin-bottom: 10px;">
        <tr>
            <td width="33%" style="">
                <img src="{{ env('BASE_URL', 'https://backend.mytime2cloud.com') . '/' . $company->logo_raw }}"
                    alt="Company Logo" height="150">
            </td>
            <td width="34%" style="font-size: 18px; : bold;text-align: center">
                <b style="color: #005edf">MONTHLY ATTENDANCE REPORT</b> <br><br>  {{ $employee->full_name }} ({{ $employee->employee_id ?? '---' }}) <br>
                <small style="font-size:12px;"> {{ date('d-M-Y') }} -
                    {{ date('d-M-Y') }}</small>
            </td>
            <td width="33%" style="font-size: 18px;  bold;text-align: right">
                <b>{{ $company->name ?? '' }}</b><br>
                <small style="font-size:12px;">
                    {{ $company->user->email ?? '' }}<br>
                    {{ $company->contact->number ?? '' }}
                    
                </small>
                <div style="font-size: 12px"> 
                   <small> {{ $company->location }}</small>
                </div>
            </td>
        </tr>
    </table>

    <hr>
    <table class="main-table">
        <tr style="text-align: left;margin-top:20px;width:100%">
            <td style="font-size:12px;color: #005edf;text-align:  left;width:10px"> # </td>
            <td colspan="2" style="font-size:12px;color: #005edf;text-align:  center; "> Date </td>
            <td colspan="2" style="font-size:12px;color: #005edf;text-align:  center; "> Shift </td>

            @if ($shift_type_id == 2)
                @for ($i = 0; $i < 7; $i++)
                    <td style="font-size:12px;color: #005edf;text-align:  center; "> In{{ $i + 1 }} </td>
                    <td style="font-size:12px;color: #005edf;text-align:  center;width:40px"> Out{{ $i + 1 }} </td>
                @endfor
            @else
                <td colspan="4" style="font-size:12px;color: #005edf;text-align: center; padding:5px;"> In Time </td>
                <td colspan="4" style="font-size:12px;color: #005edf;text-align: center; padding:5px;"> Out Time </td>
                <td colspan="3" style="font-size:12px;color: #005edf;text-align: center; padding:5px;"> Late In </td>
                <td colspan="3" style="font-size:12px;color: #005edf;text-align: center; padding:5px;"> Early Out </td>
            @endif

            <td style="font-size:12px;color: #005edf;text-align:center; "> Total Hours </td>
            <td style="font-size:12px;color: #005edf;text-align:center"> OT </td>
            <td style="font-size:12px;color: #005edf;text-align:center"> Status </td>
        </tr>

        @foreach ($data as $index => $date)
            @php
                if ($date->status == 'P') {
                    $statusColor = 'green';
                } elseif ($date->status == 'A') {
                    $statusColor = 'red';
                } elseif ($date->status == 'M') {
                    $statusColor = 'orange';
                } elseif ($date->status == 'O') {
                    $statusColor = 'gray';
                } elseif ($date->status == 'L') {
                    $statusColor = 'blue';
                } elseif ($date->status == 'H') {
                    $statusColor = 'pink';
                } elseif ($date->status == '---') {
                    $statusColor = '#f34100ed';
                }
            @endphp

            <tbody>
                <tr style="text-align:  center">
                    <td>{{ $index + 1 }}</td>
                    <td colspan="2" style="text-align:  center;">{{ $date->date ?? '---' }}
                        <div class="secondary-value" style="font-size:6px">{{ $date->day ?? '---' }}</div>
                    </td>
                    <td colspan="2" style="text-align:  center;">
                        <div>
                            @if ($date->status == 'O')
                                Week-Off
                            @else
                                @if ($date->schedule)
                                    {{ $date->schedule->shift->on_duty_time }} -
                                    {{ $date->schedule->shift->off_duty_time }}
                                    <div class="secondary-value" style="font-size:6px">
                                        {{ $date->schedule->shift->name }}
                                    </div>
                                @endif
                            @endif
                        </div>
                    </td>
                    @if ($shift_type_id == 2)
                        @for ($i = 0; $i < 7; $i++)
                            <td style="text-align:  center;"> {{ $date->logs[$i]['in'] ?? '---' }}
                                <div class="secondary-value" style="font-size:6px">
                                    {{ $date->logs[$i]['device_in'] ?? '---' }}
                                </div>
                            </td>
                            <td style="text-align:  center;"> {{ $date->logs[$i]['out'] ?? '---' }}
                                <div class="secondary-value" style="font-size:6px">
                                    {{ $date->logs[$i]['device_out'] ?? '---' }}
                                </div>
                            </td>
                        @endfor
                    @else
                        <td colspan="4" style="text-align:  center;"> {{ $date->in ?? '---' }}</td>
                        <td colspan="4" style="text-align:  center;"> {{ $date->out ?? '---' }}</td>
                        <td colspan="3" style="text-align:  center;"> {{ $date->late_coming ?? '---' }}</td>
                        <td colspan="3" style="text-align:  center;"> {{ $date->early_going ?? '---' }}</td>
                    @endif

                    <td style="text-align:  center;"> {{ $date->total_hrs ?? '---' }} </td>
                    <td style="text-align:  center;"> {{ $date->ot ?? '---' }} </td>
                    <td style="text-align:  center; color:{{ $statusColor }}">
                        {{ str_replace('O', 'W', $date->status) ?? '---' }}

                        <div class="secondary-value" style="font-size:6px">
                            @if ($date['shift'] && $date->status == 'P')
                                @php
                                    $shiftWorkingHours = $date['shift']['working_hours'];
                                    $dateHours = $date['total_hrs'];

                                    if (
                                        $shiftWorkingHours !== '' &&
                                        $dateHours !== '' &&
                                        $shiftWorkingHours !== '---' &&
                                        $dateHours !== '---'
                                    ) {
                                        [$hours, $minutes] = explode(':', $shiftWorkingHours);
                                        $shiftWorkingHours = $hours * 60 + $minutes;

                                        [$hours, $minutes] = explode(':', $dateHours);
                                        $dateHours = $hours * 60 + $minutes;

                                        if ($dateHours < $shiftWorkingHours) {
                                            echo 'Short Shift';
                                        }
                                } @endphp
                            @endif
                        </div>
                    </td>
                </tr>
            </tbody>
            <tr class="my-break">
                <td colspan="21" style="border: none;"></td>
            </tr>
            @php $i = 0; @endphp
        @endforeach
    </table>


    <table class="summary-table" style="backgroudnd-color:red; margin-top:20px">
                    <tr class="summary-header" style="border: none;background-color:#eeeeee">
                        <th style="text-align: center; border :none; padding:5px">EID</th>
                        <th style="text-align: center; border :none">Name</th>
                        <th style="text-align: center; border :none">Department</th>
                        <th style="text-align: center; border :none">Shift Type </th>

                    </tr>
                    <tr style="border: none">
                        <td style="text-align: center; border :none; padding:5px;font-size:11px">
                            {{ $employee->employee_id ?? '---' }}
                        </td>
                        <td style="text-align: center; border:none;font-size:11px">
                            {{ $empName ?? '---' }}
                        </td>
                        <td style="text-align: center; border:none;font-size:11px">
                            {{ $employee->department->name ?? '---' }}
                        </td>
                        <td style="text-align: center; border:none;font-size:11px">
                            Multi In/Out
                        </td>
                    </tr>

                    <tr class="summary-header" style="border: none;background-color:#eeeeee">
                        <th style="text-align: center; border :none; padding:5px">Present</th>
                        <th style="text-align: center; border :none">Absent</th>
                        <th style="text-align: center; border :none">Week Off</th>
                        <th style="text-align: center; border :none">Leaves</th>
                    </tr>
                    <tr style="border: none">




                        <td style="text-align: center; border :none; padding:5px;color:green">
                            {{ $info->total_present }}
                        </td>
                        <td style="text-align: center; border :none;color:red">
                            {{ $info->total_absent }}
                        </td>

                        <td style="text-align: center; border :none;color:gray">
                            {{ $info->total_off }}
                        </td>
                        <td style="text-align: center; border :none;color:blue">
                            {{ $info->total_leave }}
                        </td>
                    </tr>
                    <tr class="summary-header" style="border: none;background-color:#eeeeee ">
                        <th style="text-align: center; border :none">Holidays</th>
                        <th style="text-align: center; border :none">Missing</th>

                        <th style="text-align: center; border :none; padding:5px">Work Hours</th>
                        <th style="text-align: center; border :none">OT Hours</th>
                        <th style="text-align: center; border :none"> </th>
                        {{-- <th style="text-align: center; border :none">Department</th> --}}
                    </tr>
                    <tr style="border: none">
                        <td style="text-align: center; border :none;color:pink">
                            {{ $info->total_holiday }}
                        </td>
                        <td style="text-align: center; border :none;color:orange">
                            {{ $info->total_missing }}
                        </td>
                        <td style="text-align: center; border :none; padding:5px;color:black">
                            {{ $info->total_hours ?? 0 }}
                        </td>
                        <td style="text-align: center; border :none;color:black">
                            {{ $info->total_ot_hours ?? 0 }}
                        </td>
                        <td style="text-align: center; border :none;color:black"> </td>
                    </tr>
                    <tr style="border: none">
                        <th style="text-align: center; border :none" colspan="4">
                            <hr>
                        </th>
                    </tr>
                </table>
    @php

    @endphp

</body>
<style>
    .my-break {
        page-break-after: always;
        /* background-color: red !important; */
    }

    .pageCounter span {
        counter-increment: pageTotal;
    }

    #pageNumbers div:before {
        counter-increment: currentPage;
        content: "Page " counter(currentPage) " of ";
    }

    #pageNumbers div:after {
        content: counter(pageTotal);
    }

    #footer {
        position: fixed;
        top: 720px;
        right: 0px;
        bottom: 0px;
        text-align: center;
        font-size: 12px;
    }

    #page-bottom-line {
        position: fixed;
        right: 0px;
        bottom: -6px;
        text-align: center;
        font-size: 12px;
        counter-reset: pageTotal;
    }

    #footer .page:before {
        content: counter(page, decimal);
    }

    #footer .page:after {
        counter-increment: counter(page, decimal);
    }


    /* @page {
        margin: -10px 30px 25px 50px;
    } */
    @page {
        margin: 5px 30px 25px 50px;
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
        font-size: 9px;
    }

    footer {
        bottom: 0px;
        position: absolute;
        width: 100%;
    }

    /* .page-break {
        page-break-after: always;
    } */

    .main-table {
        padding-bottom: 20px;
        padding-top: 10px;
        padding-right: 15px;
        padding-left: 15px;
    }

    .footer-main-table {
        padding-bottom: 7px;
        padding-top: 0px;
        padding-right: 15px;
        padding-left: 15px;
    }

    hr {
        position: relative;
        border: none;
        height: 2px;
        background: #c5c2c2;
        padding: 0px
    }

    .title-font {
        font-family: Arial, Helvetica, sans-serif !important;
        font-size: 14px;
        font-weight: bold
    }

    .summary-header th {
        font-size: 10px
    }

    .summary-table td {
        font-size: 9px
    }

    /* --------------------------- */

    .m-1 {
        margin: 0.25rem;
    }

    .m-2 {
        margin: 0.5rem;
    }

    .m-3 {
        margin: 1rem;
    }

    .mt-2 {
        margin-top: 0.5rem;
    }

    .mt-3 {
        margin-top: 1rem;
    }

    .mr-1 {
        margin-right: 0.25rem;
    }

    .ml-3 {
        margin-left: 1rem;
    }

    .mx-4 {
        margin-right: 1.5rem;
        margin-left: 1.5rem;
    }

    .my-5 {
        margin-top: 2.5rem;
        margin-bottom: 2.5rem;
    }

    .pr-1 {
        padding-right: 0.25rem;
    }

    .pt-2 {
        padding-top: 0.5rem;
    }

    .pl-3 {
        padding-left: 1rem;
    }

    .px-4 {
        padding-right: 1.5rem;
        padding-left: 1.5rem;
    }

    .py-5 {
        padding-top: 2.5rem;
        padding-bottom: 2.5rem;
    }

    .row::after {
        content: "";
        clear: both;
        display: table;
    }

    .col {
        width: 5%;
        float: left;
        padding: 5px;
    }


    .col-1 {
        width: 8.33%;
        float: left;
        padding: 5px;
    }

    .col-2 {
        width: 16.66%;
        float: left;
        padding: 5px;
    }

    .col-3 {
        width: 24.99%;
        float: left;
        padding: 5px;
    }

    .col-4 {
        width: 33.32%;
        float: left;
        padding: 5px;
    }

    .col-5 {
        width: 41.65%;
        float: left;
        padding: 5px;
    }

    .col-6 {
        width: 49.98%;
        float: left;
        padding: 5px;
    }

    .col-7 {
        width: 58.31%;
        float: left;
        padding: 5px;
    }

    .col-8 {
        width: 66.64%;
        float: left;
        padding: 5px;
    }

    .col-9 {
        width: 74.97%;
        float: left;
        padding: 5px;
    }

    .col-10 {
        width: 83.3%;
        float: left;
        padding: 5px;
    }

    .col-11 {
        width: 91.63%;
        float: left;
        padding: 5px;
    }

    .col-12 {
        width: 100%;
        float: left;
        padding: 5px;
    }

    .form-input {
        width: 100%;
        padding: 2px 5px;
        border-radius: 0px;
        resize: vertical;
        outline: 0;
    }

    .secondary-value1 {
        font-size: 6px !important;
        padding-top: 5px;
        vertical-align: top !important;
    }
</style>

</html>
