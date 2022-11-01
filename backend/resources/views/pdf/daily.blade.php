<!DOCTYPE html>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<body>


    <table style="margin-top: -20px !important;backgroundd-color:blue;padding-bottom:0px ">
        <tr>
            <td style="text-align: left;width: 300px; border :none; padding:15px;   backgrozund-color: red">
                <div style=";">
                    <br> <br> <br>
                    @if (env('APP_ENV') !== 'local')
                        <img src="{{ $company->logo }}" height="100px" width="100px">
                    @else
                        <img src="{{ getcwd() . '/upload/app-logo.jpeg' }}" height="100px" width="100">
                    @endif

                    <table style="text-align: right; border :none; width:200px; margin-top:5px;baczkground-color:blue">
                        <tr style="text-align: left; border :none;">
                            <td style="text-align: right; border :none;font-size:10px">
                                <b>
                                    {{ $company->name }}
                                    {{-- <>{{ $company->name ?? 'Akkil Security & Alarm System LLC' }} --}}
                                </b>
                                <br>
                            </td>
                        </tr>
                        <tr style="text-align: left; border :none;">
                            <td style="text-align: right; border :none;font-size:10px">
                                <span style="margin-right: 3px">P.O.Box {{ $company->p_o_box_no }}</span>
                                <br>
                            </td>
                        </tr>
                        <tr style="text-align: left; border :none;">
                            <td style="text-align: right; border :none;font-s ize:10px">
                                <span style="margin-right: 3px">{{ $company->location }}</span>
                                <br>
                            </td>
                        </tr>
                        <tr style="text-align: left; border :none;">
                            <td style="text-align: right; border :none;font-size:10px">
                                <span style="margin-right: 3px">{{ $company->contact->number ?? '' }}</span>
                                <br>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            <td style="text-align: left;width: 333px; border :none; padding:15px; backgrozusnd-color:blue">
                <div>
                    <table style="text-align: left; border :none;  ">
                        <tr style="text-align: left; border :none;">
                            <td style="text-align: center; border :none">
                                <span class="title-font">
                                    Daily Attendance {{ $info->report_type }} Report
                                </span>
                                <hr style="width: 230px">
                            </td>
                        </tr>
                        <tr style="text-align: left; border :none;">
                            <td style="text-align: center; border :none">
                                <span style="font-size: 11px">
                                    {{ date('d M Y', strtotime($info->daily_date)) }}
                                </span>
                                <hr style="width: 230px">
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            <td style="text-align: right;width: 300px; border :none; backgrounsd-color: red">
                <table class="summary-table"
                    style="border:none; padding:0px 50px; margin-left:35px;margin-top:20px;margin-bottom:0px">
                    <tr style="border: none">
                        <th style="text-align: center; border :none;padding:10px;font-size: 12px " colspan="3">
                            <hr style="width: 200px">
                            Total Number of Employees : {{ $info->total_employee }}
                        </th>
                    </tr>
                    <tr class="summary-header" style="border: none;background-color:#eeeeee">
                        <th style="text-align: center; border :none; padding:5px">Present</th>
                        <th style="text-align: center; border :none">Absent</th>
                        <th style="text-align: center; border :none">Leave</th>
                    </tr>
                    <tr style="border: none">
                        <td style="text-align: center; border :none; padding:5px;color:green">{{ $info->total_present }}
                        </td>
                        <td style="text-align: center; border :none;color:red">{{ $info->total_absent }}</td>
                        <td style="text-align: center; border :none;color:red">{{ $info->total_leave }}</td>
                    </tr>
                    <tr class="summary-header" style="border: none;background-color:#eeeeee ">
                        <th style="text-align: center; border :none; padding:5px">Late</th>
                        <th style="text-align: center; border :none">Early</th>
                        <th style="text-align: center; border :none">Missing</th>
                    </tr>
                    <tr style="border: none">
                        <td style="text-align: center; border :none; padding:5px;color:red">{{ $info->total_late }}
                        </td>
                        <td style="text-align: center; border :none;color:green">{{ $info->total_early }}</td>
                        <td style="text-align: center; border :none;color:orange">{{ $info->total_missing }}</td>
                    </tr>
                    <tr style="border: none">
                        <th style="text-align: center; border :none" colspan="3">
                            <hr style="width: 200px">
                        </th>
                    </tr>
                </table>
                <br>
            </td>
            </td>
        </tr>
    </table>
    <hr style="margin:0px;padding:0">
    @php
        $statusColor = '';
        $i = 0;
    @endphp
    <table class="main-table">
        <tr style="text-align: left;font-weight:bold">
            <td style="text-align:  left;"> Name </td>
            <td style="text-align:  center;width:80px"> EID </td>
            <td style="text-align:  center;width:80px"> In </td>
            <td style="text-align:  center;width:80px"> Out </td>
            <td style="text-align:  center;width:80px"> Total Hours </td>
            <td style="text-align:  center;width:80px"> OT </td>
            <td style="text-align:  center;width:80px"> Status </td>
            <td style="text-align:  center;width:150px"> Device In </td>
            <td style="text-align:  center;width:150px"> Device Out </td>
        </tr>
        @foreach ($data as $data)
            @php
                if ($data->status == 'P') {
                    $statusColor = 'green';
                } elseif ($data->status == 'A') {
                    $statusColor = 'red';
                } elseif ($data->status == '---') {
                    $statusColor = '#f34100ed';
                }
            @endphp
            <tbody>
                <tr style="text-align:  center;">
                    <td style="text-align:  left; width:120px">{{ $data->employee->first_name ?? '---' }}</td>
                    <td style="text-align:  center;">{{ $data->employee_id ?? '---' }}</td>
                    <td style="text-align:  center;"> {{ $data->in ?? '---' }} </td>
                    <td style="text-align:  center;"> {{ $data->out ?? '---' }} </td>
                    <td style="text-align:  center;"> {{ $data->total_hrs ?? '---' }} </td>
                    <td style="text-align:  center;"> {{ $data->ot ?? '---' }} </td>
                    <td style="text-align:  center; color:{{ $statusColor }}"> {{ $data->status ?? '---' }} </td>
                    <td style="text-align:  center;"> {{ $data->device_in->short_name ?? '---' }} </td>
                    <td style="text-align:  center;"> {{ $data->device_out->short_name ?? '---' }} </td>
                </tr>
            </tbody>
        @endforeach
        {{-- <tfoot>
            <tr style="border :none">
                <td style="text-align: left;border :none" colspan="9">
                    <b>Device</b>:
                    Main Entrance = MED, Back
                    Entrance = BED
                </td>
                <td style="text-align: left;"><b>Shift Type</b>: Manual = MA, Auto = AU, NO = NO</td>
                <td style="text-align: left;"><b>Shift</b>: Morning = Mor, Evening = Eve, Evening2 = Eve2</td>
                <td style="text-align: right;">
                    Date : {{ date('d/M/Y H:i:s') }}
                </td>
            </tr>
        </tfoot> --}}
    </table>
    <hr style=" bottom: 0px; position: absolute; width: 100%; margin-bottom:40px">
    <footer style="padding-top: 100px!important">
        <table class="main-table">
            <tr style="border :none">
                <td style="text-align: left;border :none"><b>Device</b>: Main Entrance = MED, Back Entrance = BED</td>
                <td style="text-align: left;border :none"><b>Shift Type</b>: Manual = MA, Auto = AU, NO = NO</td>
                <td style="text-align: left;border :none"><b>Shift</b>: Morning = Mor, Evening = Eve, Evening2 = Eve2
                </td>
                <td style="text-align: right;border :none;">
                    <b>Powered by</b>: <span style="color:blue"> www.ideahrms.com</span>
                </td>
                <td style="text-align: right;border :none">
                    Printed on : {{ date('d-M-Y ') }}
                </td>
            </tr>
        </table>
    </footer>
</body>
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
</style>

</html>
