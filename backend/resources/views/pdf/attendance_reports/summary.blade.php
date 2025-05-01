<!DOCTYPE html>
<html>

<head>
    <style>
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        td,
        th {

            border: 1px solid #dddddd;
            text-align: center;
            padding: 5px;
            font-size: 10px
        }

        .left {
            text-align: left
        }

        .center {
            text-align: center
        }

        .no-border {
            border: none;
            border-collapse: separate;
        }

        .no-border td,
        .no-border th {
            border: none;
        }
    </style>
</head>

<body>

    <table class="no-border">
        <tr>
            <td class="left">
                <div class="col-12">
                    <b>
                        {{ $company->name }}
                    </b>
                    <br>
                    <span>P.O.Box
                        {{ $company->p_o_box_no == 'null' ? '---' : $company->p_o_box_no }}</span>
                    <br>
                    <span>{{ $company->location }}</span>
                    <br>
                    <span>{{ $company->contact->number ?? '' }}</span>
                    <br>
                </div>
            </td>
            <td class="center" style="width: 33%">
                <h2>Summary Report</h2>
                {{ date('d-M-y', strtotime($company->start)) }} to {{ date('d-M-y', strtotime($company->end)) }}
            </td>
            <td style="width: 33%"></td>
        </tr>
    </table>

    <table style="margin-top:20px">
        <tr>
            <td class="left" style="width: 30px">Employee</td>
            <td class="left">Department</td>

            @if ($shift_type == 'General')
                <td>
                    In
                </td>
                <td>
                    Out
                </td>
            @endif


            @if ($shift_type == 'Multi')
                @for ($i = 0; $i < 5; $i++)
                    <td>
                        In{{ $i + 1 }}
                    </td>
                    <td>
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
            @endphp
            <tr>
                <td class="left">{{ $attendance->employee->display_name }}
                    <br>
                    <small>{{ $attendance->employee_id }}</small>
                </td>
                <td class="left">{{ $attendance->employee->department->name }}</td>

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

</body>

</html>
