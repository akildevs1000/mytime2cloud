<!DOCTYPE html>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<body>

    <div id="footer">
        <div class="pageCounter">
            <p></p>
            @if (count($data) > 1)
                @foreach (range(1, count($data)) as $_)
                    <span></span>
                @endforeach
            @else
                <span></span>
            @endif
        </div>
        <div id="pageNumbers">
            <div class="page-number" style="font-size: 9px"></div>
        </div>
    </div>
    <footer id="page-bottom-line" style="padding-top: 100px!important">
        <hr style="width: 100%;">
        <table class="footer-main-table">
            <tr style="border :none">
                <td style="text-align: left;border :none;">
                    <b>Powered by</b>: <span style="color:blue">
                        <a href="https://ideahrms.com/" target="_blank">ideahrms.com</a>
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

    <table class="main-table">

        @foreach ($data as $empID => $employee)
            @php
                // ld(count($employee));
                // die;
                $emp = $employee[key(reset($employee))][0]->visitor;
                $empName = $emp->full_name ?? '---';
            @endphp
            <tr style="border:none;">
                <td style="width:22%;background:reds;text-align:center;border:none;">
                    <div style="margin-top:40px;">
                        <img style="width:100%;"
                            src="https://th.bing.com/th/id/R.b4e3fb857db675de7df59ab6f4cf30ab?rik=gbQLvTh9DaC6tQ&pid=ImgRaw&r=0">

                        {{-- @if (env('APP_ENV') !== 'local')
                            <img src="{{ $company->logo }}">
                        @else
                            <img style="width:100%;"
                                src="https://th.bing.com/th/id/R.b4e3fb857db675de7df59ab6f4cf30ab?rik=gbQLvTh9DaC6tQ&pid=ImgRaw&r=0">
                        @endif --}}
                    </div>
                </td>
                <td style="width:;border:none;">
                    <div>
                        <table style="text-align: left; border :none;  ">
                            <tr style="text-align: left; border :none;">
                                <td style="text-align: center; border :none">
                                    <span class="title-font">
                                        Visitor {{ $info['report_type'] }} Report
                                    </span>
                                    <hr style="width: 230px">
                                </td>
                            </tr>
                            <tr style="text-align: left; border :none;">
                                <td style="text-align: center; border :none">
                                    <span style="font-size: 11px">
                                        {{ $info['from_date'] }} - {{ $info['to_date'] }}
                                    </span>
                                    <hr style="width: 230px">
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td style="width:22%;background:reds;border:none;">
                    <table class="summary-table"
                        style="border-top: 1px #c5c2c2 solid;border-bottom: 1px #c5c2c2 solid;margin-top:20px">

                        <tr class="summary-header" style="background-color:#eeeeee">
                            <th style="text-align: center; border :none; padding:5px">Visitor ID</th>
                            <th style="text-align: center; border :none">Name</th>
                        </tr>
                        <tr style="border: none">
                            <td style="text-align: center; border :none; padding:5px;font-size:11px">
                                {{ $empID ?? '---' }}
                            </td>
                            <td style="text-align: center; border:none;font-size:11px">
                                {{ $empName }}
                            </td>

                        </tr>
                        <tr class="summary-header" style="background-color:#eeeeee">
                            <th style="text-align: center; border :none">Visited Day</th>
                            <th style="text-align: center; border :none">Status</th>
                        </tr>
                        <tr style="border: none">

                            <td style="text-align: center; border:none;font-size:11px">
                                {{ count($employee) }}
                            </td>
                            <td style="text-align: center; border:none;font-size:11px">
                                <b>{{ $info['status'] }}</b>
                            </td>
                        </tr>
                    </table>

                </td>
            </tr>

            <td colspan="3" style="border: none;padding-top:10px;">
                <table>
                    <tr style="text-align: left;font-weight:bold;margin-top:20px;background:; width:100%;">
                        <td style="text-align: left;"> # </td>
                        <td style="text-align: center;"> Date </td>
                        <td style="text-align: center;"> Day </td>
                        <td style="text-align: center;"> In </td>
                        <td style="text-align: center;"> Out </td>
                        <td style="text-align: center;"> Total Hours </td>
                        <td style="text-align: center;"> Status </td>
                        <td style="text-align: center;"> Device In </td>
                        <td style="text-align: center;"> Device Out </td>
                        <td style="text-align: center;"> Reason </td>
                    </tr>
                    @foreach ($employee as $date)
                        @php
                            $employee = $date[0];
                            if ($employee->status == 'P') {
                                $statusColor = 'green';
                            } elseif ($employee->status == 'A') {
                                $statusColor = 'red';
                            } elseif ($employee->status == 'M') {
                                $statusColor = 'orange';
                            } elseif ($employee->status == 'O') {
                                $statusColor = 'gray';
                            } elseif ($employee->status == 'L') {
                                $statusColor = 'blue';
                            } elseif ($employee->status == 'H') {
                                $statusColor = 'pink';
                            } elseif ($employee->status == '---') {
                                $statusColor = '#f34100ed';
                            }
                        @endphp

                        <tbody>
                            <tr style="text-align:  center">
                                <td>{{ ++$i }}</td>
                                <td style="text-align:  center;">{{ $employee->date ?? '---' }}</td>
                                <td style="text-align:  center;">
                                    {{ date('D', strtotime($employee->date)) ?? '---' }}</td>

                                <td style="text-align:  center;"> {{ $employee->in ?? '---' }} </td>
                                <td style="text-align:  center;"> {{ $employee->out ?? '---' }} </td>
                                <td style="text-align:  center;"> {{ $employee->total_hrs ?? '---' }} </td>
                                <td style="text-align:  center; color:{{ $statusColor }}">
                                    {{ $employee->status ?? '---' }}
                                </td>
                                <td style="text-align:  center;">
                                    {{ $employee->device_in->short_name ?? '---' }} </td>
                                <td style="text-align:  center;">
                                    {{ $employee->device_out->short_name ?? '---' }} </td>
                                <td style="text-align:  center;">Reason</td>

                            </tr>
                        </tbody>
                    @endforeach
                </table>
            </td>
            @php $i = 0; @endphp
        @endforeach
    </table>
    @php
        
        function getStatus($employeeData)
        {
            $countA = 0;
            $countP = 0;
            $countM = 0;
            $countO = 0;
            $countL = 0;
            $countH = 0;
            foreach ($employeeData as $employee) {
                if (!is_array($employee) || empty($employee[0]) || !isset($employee[0]['total_hrs'])) {
                    throw new InvalidArgumentException("Invalid employee data: each employee must be an array with a 'total_hrs' key");
                }
                $status = $employee[0]['status'];
                if ($status == 'A') {
                    $countA++;
                } elseif ($status == 'P') {
                    $countP++;
                } elseif ($status == 'M') {
                    $countM++;
                } elseif ($status == 'O') {
                    $countO++;
                } elseif ($status == 'L') {
                    $countL++;
                } elseif ($status == 'H') {
                    $countH++;
                }
            }
            return [
                'A' => $countA,
                'P' => $countP,
                'M' => $countM,
                'O' => $countO,
                'L' => $countL,
                'H' => $countH,
            ];
        }
        
    @endphp



</body>
<style>
    .my-break {
        page-break-after: always
    }

    .pageCounter span {
        counter-increment: pageTotal
    }

    #pageNumbers div:before {
        counter-increment: currentPage;
        content: "Page " counter(currentPage) " of "
    }

    #pageNumbers div:after {
        content: counter(pageTotal)
    }

    #footer {
        position: fixed;
        top: 720px;
        right: 0;
        bottom: 0;
        text-align: center;
        font-size: 12px
    }

    #page-bottom-line {
        position: fixed;
        right: 0;
        bottom: -6px;
        text-align: center;
        font-size: 12px;
        counter-reset: pageTotal
    }

    #footer .page:before {
        content: counter(page, decimal)
    }

    #footer .page:after {
        counter-increment: counter(page, decimal)
    }

    @page {
        margin: -10px 30px 25px 30px;
    }

    table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        border: none;
        width: 100%
    }

    td,
    th {
        border: 1px solid #eee;
        text-align: left
    }

    tr:nth-child(even) {
        border: 1px solid #eee
    }

    th {
        font-size: 9px
    }

    td {
        font-size: 9px
    }

    footer {
        bottom: 0;
        position: absolute;
        width: 100%
    }

    .main-table {
        padding-bottom: 20px;
        padding-top: 10px;
        padding-right: 15px;
        padding-left: 15px
    }

    .footer-main-table {
        padding-bottom: 7px;
        padding-top: 0;
        padding-right: 15px;
        padding-left: 15px
    }

    .title-font {
        font-family: Arial, Helvetica, sans-serif !important;
        font-size: 14px;
        font-weight: 700
    }

    .summary-header th {
        font-size: 10px
    }

    .summary-table td {
        font-size: 9px
    }
</style>

</html>
