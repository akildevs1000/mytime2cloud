<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Multi-Page Report Example</title>
    <style>
        @page {
            margin: 20px 5px;
            /* leave space for header/footer */
        }

        body {
            font-family: 'Inter', Arial, Helvetica, sans-serif;
            color: #1e293b;
            /* dark text */
            margin: 0;
            padding: 0;
            /* Added padding to body for better look */
            font-size: 14px;
        }

        /* --- Header Styles --- */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            border-bottom: 1px solid #949494;
        }

        .header-table td {
            padding-bottom: 16px;
            vertical-align: top;
        }

        .header-logo {
            height: 64px;
            margin-right: 16px;
            border-radius: 8px;
            /* Added slight rounding to logo placeholder */
        }

        .header-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }

        .header-subtitle {
            font-size: 14px;
            color: #64748b;
            margin: 0;
        }

        .company-info {
            text-align: right;
            font-size: 14px;
        }

        .company-name {
            font-weight: bold;
            font-size: 18px;
            color: #1e293b;
            margin: 0;
        }

        /* --- Details Table (4 Columns) --- */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            /* Reduced from 24px */
        }

        /* Target paragraphs inside detail cells and reset their margins */
        .details-table p {
            margin-top: 2px;
            /* A tiny bit of spacing for readability */
            margin-bottom: 2px;
        }

        .details-table td {
            padding-top: 5px;
            padding-bottom: 5px;
            width: 25%;
            vertical-align: top;
        }

        .detail-label {
            font-size: 12px;
            font-weight: bold;
            color: #64748b;
            margin: 0;
        }

        .detail-value {
            font-size: 16px;
            font-weight: bold;
            color: #1e293b;
            margin: 0;
        }

        /* --- Summary Stats Table (7 Columns) ---
       Updated for Card look: Removed outer border/rounding, added spacing.
      */
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            /* ⬅️ Change to collapse for stability */
            border-spacing: 0;
            /* ⬅️ Change to zero */
            margin-bottom: 32px;
            text-align: center;
            border: none;
        }

        .stats-table td {
            width: 14.28%;
            padding: 0 4px;
            /* ⬅️ Add horizontal padding for spacing */
            border: none;
            box-shadow: none;
            vertical-align: top;
        }

        /* New class for the inner element that acts as the card/block */
        .stat-card-inner {
            padding: 16px 4px;
            /* Padding moved inside the div */
            border-radius: 8px;
            /* Subtle shadow for lift effect, making it look like a card */
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            border: 1px solid #fff;
            /* Very light inner border */
        }

        .stat-label {
            font-size: 12px;
            margin: 0;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }

        /* Specific Stat Colors (applied to .stat-card-inner) */
        .bg-green-100 {
            background-color: #dcfce7;
        }

        .text-green-600 {
            color: #16a34a;
        }

        .text-green-700 {
            color: #047857;
        }

        .bg-red-100 {
            background-color: #fee2e2;
        }

        .text-red-600 {
            color: #dc2626;
        }

        .text-red-700 {
            color: #b91c1c;
        }

        .bg-blue-100 {
            background-color: #dbeafe;
        }

        .text-blue-600 {
            color: #2563eb;
        }

        .text-blue-700 {
            color: #1d4ed8;
        }

        .bg-yellow-100 {
            background-color: #fef9c3;
        }

        .text-yellow-600 {
            color: #ca8a04;
        }

        .text-yellow-700 {
            color: #a16207;
        }

        .bg-indigo-100 {
            background-color: #e0e7ff;
        }

        .text-indigo-600 {
            color: #4f46e5;
        }

        .text-indigo-700 {
            color: #4338ca;
        }

        .bg-gray-100 {
            background-color: #f3f4f6;
        }

        .text-gray-600 {
            color: #4b5563;
        }

        .text-gray-700 {
            color: #374151;
        }

        .bg-purple-100 {
            background-color: #f3e8ff;
        }

        .text-purple-600 {
            color: #9333ea;
        }

        .text-purple-700 {
            color: #7e22ce;
        }







        .date-col {
            font-weight: bold;
            color: #1e293b;
            white-space: nowrap;
        }

        .device-info {
            font-size: 10px;
            color: #64748b;
        }

        .late-early-time {
            color: #f97316;
        }

        .status-badge {
            display: inline-block;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 5px;
            white-space: nowrap;
        }

        .badge-present {
            background-color: #dcfce7;
            color: #047857;
        }

        .badge-absent {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .badge-weekoff {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        .badge-missing {
            background-color: #f3f4f6;
            color: #374151;
        }

        .badge-holiday {
            background-color: #e0e7ff;
            color: #4338ca;
        }

        .footer-total-label {
            text-align: right;
        }

        /* Custom background for total row to contrast better */


        /* Add this class to your <style> block */
        footer {
            bottom: 0px;
            position: absolute;
            width: 100%;
        }

        #footer .page:before {
            content: counter(page, decimal);
        }

        #footer .page:after {
            counter-increment: counter(page, decimal);
        }

        .pagenum:before {
            content: "Page " counter(page);
            /* Only current page works in browsers */
        }

        /* Demo content */
        main {
            padding: 20px 40px;
        }

        h1 {
            page-break-after: avoid;
        }

        .force-break {
            page-break-after: always;
        }


        .pageCounter span {
            counter-increment: pageTotal;
        }

        #pageNumbers div:before {
            counter-increment: currentPage;
            content: "Page " counter(currentPage) " of 1";
            font-size: 9px
        }

        #page-bottom-line {
            width: 94%;
            /* your desired width */
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            margin: 0 auto;
            /* centers the footer */
            text-align: center;
            font-size: 12px;
            counter-reset: pageTotal;
            border-top: #949494 1px solid;
        }

        table {
            border-collapse: collapse;
            border: none;
            width: 100%;
        }

        .th-font-size {
            font-size: 11px !important;
        }

        .text-center {
            text-align: center;
        }

        th {
            font-size: 9px;

        }

        td {
            font-size: 9px;
            padding: 3px;
        }

        td,
        th {
            border: 1px solid #eeeeee;
        }
    </style>
</head>

<body>
    @php
        $manualRecordCounter = $data
            ->filter(function ($item) {
                return $item->device_out?->short_name === 'Manual' || $item->device_in?->short_name === 'Manual';
            })
            ->count();

        $statusMap = [
            'P' => [
                'text' => 'Present',
                'class' => 'bg-green-100',
                'color' => 'text-green-600',
            ],
            'A' => ['text' => 'Absent', 'class' => 'bg-red-100', 'color' => 'text-red-600'],
            'M' => [
                'text' => 'Incomplete',
                'class' => 'bg-gray-100',
                'color' => 'text-gray-600',
            ],
            'O' => [
                'text' => 'Week Off',
                'class' => 'bg-yellow-100',
                'color' => 'text-yellow-600',
            ],
            'L' => [
                'text' => 'Leave',
                'class' => 'bg-yellow-100',
                'color' => 'text-yellow-600',
            ],
            'H' => [
                'text' => 'Holiday',
                'class' => 'bg-indigo-100',
                'color' => 'text-indigo-600',
            ],
        ];

        $defaultStatus = ['text' => '---', 'class' => 'bg-gray-50', 'color' => 'text-gray-400'];
    @endphp
    <footer id="page-bottom-line">
        <table style="width: 100%">
            <tr style="border :none">
                <td style="text-align: left;border :none;width:33%;font-size: 9px">
                    Printed on : {{ date('d-M-Y ') }}
                </td>

                <td style="text-align: center;border :none;width:33%;font-size: 9px">
                    Powered by https://mytime2cloud.com</a>
                </td>
                <td style="text-align: right;border :none;width:33%;font-size: 9px">
                    <div id="footer">
                        <div id="pageNumbers">
                            <div style="font-size: 9px"></div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </footer>

    <main>

        {{-- FIRST PAGE --}}
        <div class="">
            {{-- Your special header with logo, details, stats... --}}
            <table class="header-table">
                <tr>
                    <td style="border: none;padding:0;width:50%">
                        <table style="border-collapse: collapse; width: 100%;">
                            <tr>
                                <!-- Logo -->
                                <td style="border: none; padding: 0; width: 70px; vertical-align: middle;">
                                    <img alt="Company Logo"
                                        src="{{ env('BASE_URL', 'https://backend.mytime2cloud.com') . '/' . $company->logo_raw }}"
                                        onerror="this.onerror=null; this.src='https://placehold.co/64x64/4f46e5/ffffff?text=Logo';"
                                        style="width: 60px; height: auto; display: block; margin: 0;" />
                                </td>

                                <!-- Text -->
                                <td style="border: none; padding: 0; vertical-align: middle;">
                                    <p style="margin: 0; font-size: 16px; font-weight: bold;">
                                        Monthly Attendance Report
                                    </p>
                                    <p style="margin: 0; font-size: 12px; color: #555;">
                                        {{ date('d M Y', strtotime($from_date)) }} -
                                        {{ date('d M Y', strtotime($to_date)) }}
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td class="company-info" style="border: none;padding:0;width:50%">
                        <p class="company-name">{{ $company->name ?? '' }}</p>
                        <p class="header-subtitle">{{ $company->user->email ?? '' }}</p>
                        <p class="header-subtitle">{{ $company->contact->number ?? '' }}</p>
                        <p class="header-subtitle">
                            {{ $employee?->branch?->branch_name ?? 'Default Branch' }}
                        </p>
                    </td>
                </tr>
            </table>

            <!-- Employee Details -->
            <table class="details-table">
                <tr>
                    <td style="border: none;padding:0;">
                        <p class="detail-label">EMPLOYEE</p>
                        <p class="detail-value">{{ $employee->full_name }} ({{ $employee->employee_id }})</p>
                    </td>
                    <td style="border: none;padding:0;">
                        <p class="detail-label">DEPARTMENT</p>
                        <p class="detail-value">{{ $employee?->department?->name ?? '---' }}</p>
                    </td>
                    <td style="border: none;padding:0;">
                        <p class="detail-label">BRANCH NAME</p>
                        <p class="detail-value">{{ $employee?->branch?->branch_name ?? 'Default Branch' }}</p>
                    </td>
                    <td style="border: none;padding:0;">
                        <p class="detail-label">SHIFT TYPE</p>
                        <p class="detail-value">{{ $data[0]->schedule->shift_type->name }}
                        </p>
                    </td>
                </tr>
            </table>

            <!-- Summary Stats -->
            <table class="stats-table">
                <tr>
                    <td>
                        <div class="stat-card-inner bg-green-100">
                            <p class="stat-label text-green-600">Present</p>
                            <p class="stat-value text-green-700">{{ $info->total_present }}</p>
                        </div>
                    </td>
                    <td>
                        <div class="stat-card-inner bg-red-100">
                            <p class="stat-label text-red-600">Absent</p>
                            <p class="stat-value text-red-700">{{ $info->total_absent }}</p>
                        </div>
                    </td>
                    <td>
                        <div class="stat-card-inner bg-blue-100">
                            <p class="stat-label text-blue-600">Week Off</p>
                            <p class="stat-value text-blue-700">{{ $info->total_off }}</p>
                        </div>
                    </td>
                    <td>
                        <div class="stat-card-inner bg-yellow-100">
                            <p class="stat-label text-yellow-600">Leaves</p>
                            <p class="stat-value text-yellow-700">{{ $info->total_leave }}</p>
                        </div>
                    </td>
                    <td>
                        <div class="stat-card-inner bg-indigo-100">
                            <p class="stat-label text-indigo-600">Holidays</p>
                            <p class="stat-value text-indigo-700">{{ $info->total_holiday }}</p>
                        </div>
                    </td>
                    <td>
                        <div class="stat-card-inner bg-gray-100">
                            <p class="stat-label text-gray-600">Missing</p>
                            <p class="stat-value text-gray-700">{{ $info->total_missing }}</p>
                        </div>
                    </td>
                    <td>
                        <div class="stat-card-inner bg-purple-100">
                            <p class="stat-label text-purple-600">Manual Punches</p>
                            <p class="stat-value text-purple-700">{{ $manualRecordCounter }}</p>
                        </div>
                    </td>
                </tr>
            </table>

            <table style="margin-top: 15px !important; margin-bottom:20px !important;width:100% !important; ">
                <tr>
                    <tbody>
                        <tr>
                            <td style="background-color:#eeeeee !important;color: #005edf !important;"
                                class="text-center">
                                Dates
                            </td>
                            @foreach ($data as $date)
                                <td style="background-color:#eeeeee !important;color: #005edf !important;"
                                    class="text-center">
                                    {{ date('d', strtotime($date->date)) ?? '---' }}
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </tr>

                <tr>
                    <td class="text-center" style="background-color:#fff !important;color: #005edf !important;"> Days
                    </td>

                    @foreach ($data as $date)
                        <td class="text-center" style="background-color:#fff !important;color: #005edf !important;">
                            {{ date('D', strtotime($date->date)) ?? '---' }}
                        </td>
                    @endforeach
                </tr>

                <?php if (in_array($shift_type_id, [1, 4, 6])) { ?>
                <tr style="background-color: none;">
                    <td class="text-center">In </td>

                    @foreach ($data as $date)
                        <td class="text-center"
                            style="{{ $date?->device_in?->name == 'Manual' ? 'color:#f6607b !important;' : '' }}">
                            {{ $date->in ?? '---' }}</td>
                    @endforeach
                </tr>
                <tr style="background-color: none;">
                    <td class="text-center"> Out </td>
                    @foreach ($data as $date)
                        <td class="text-center"
                            style="{{ $date?->device_out?->name == 'Manual' ? 'color:#f6607b !important;' : '' }}">
                            {{ $date->out ?? '---' }} </td>
                    @endforeach
                </tr>
                <?php } ?>

                @if ($shift_type_id == 2)
                    <tr style="background-color: none;">
                        <td class="text-center"> In1 </td>

                        @foreach ($data as $date)
                            <td class="text-center"> {{ $date->logs[0]['in'] ?? '---' }} </td>
                        @endforeach
                    </tr>
                    <tr style="background-color: none;">
                        <td class="text-center"> Out1 </td>

                        @foreach ($data as $date)
                            <td class="text-center"> {{ $date->logs[0]['out'] ?? '---' }} </td>
                        @endforeach
                    </tr>

                    <tr style="background-color: none;">
                        <td class="text-center"> In2 </td>

                        @foreach ($data as $date)
                            <td class="text-center"> {{ $date->logs[1]['in'] ?? '---' }} </td>
                        @endforeach
                    </tr>
                    <tr style="background-color: none;">
                        <td class="text-center"> Out2 </td>

                        @foreach ($data as $date)
                            <td class="text-center"> {{ $date->logs[1]['out'] ?? '---' }} </td>
                        @endforeach
                    </tr>

                    <tr style="background-color: none;">
                        <td class="text-center"> In3 </td>

                        @foreach ($data as $date)
                            <td class="text-center"> {{ $date->logs[2]['in'] ?? '---' }} </td>
                        @endforeach
                    </tr>
                    <tr style="background-color: none;">
                        <td class="text-center"> Out3 </td>

                        @foreach ($data as $date)
                            <td class="text-center"> {{ $date->logs[2]['out'] ?? '---' }} </td>
                        @endforeach
                    </tr>

                    <tr style="background-color: none;">
                        <td class="text-center"> In4 </td>

                        @foreach ($data as $date)
                            <td class="text-center"> {{ $date->logs[3]['in'] ?? '---' }} </td>
                        @endforeach
                    </tr>
                    <tr style="background-color: none;">
                        <td class="text-center"> Out4 </td>

                        @foreach ($data as $date)
                            <td class="text-center"> {{ $date->logs[3]['out'] ?? '---' }} </td>
                        @endforeach
                    </tr>

                    <tr style="background-color: none;">
                        <td class="text-center"> In5 </td>

                        @foreach ($data as $date)
                            <td class="text-center"> {{ $date->logs[4]['in'] ?? '---' }} </td>
                        @endforeach
                    </tr>
                    <tr style="background-color: none;">
                        <td class="text-center"> Out5 </td>

                        @foreach ($data as $date)
                            <td class="text-center"> {{ $date->logs[4]['out'] ?? '---' }} </td>
                        @endforeach
                    </tr>
                @endif


                @if ($shift_type_id == 4 || $shift_type_id == 6)
                    <tr>
                        <td class="text-center"> Late In </td>
                        @foreach ($data as $date)
                            <td class="text-center"> {{ $date->late_coming ?? '---' }}
                            </td>
                        @endforeach
                    </tr>

                    <tr>
                        <td class="text-center"> Early Out </td>
                        @foreach ($data as $date)
                            <td class="text-center"> {{ $date->early_going ?? '---' }}
                            </td>
                        @endforeach
                    </tr>
                @endif

                <tr>
                    <td class="text-center" style="color: #005edf !important;"> Total Hrs </td>
                    @foreach ($data as $date)
                        <td class="text-center" style="color: #005edf !important;"> {{ $date->total_hrs ?? '---' }}
                        </td>
                    @endforeach
                </tr>

                <tr>
                    <td class="text-center" style="color: #005edf !important;"> OT </td>
                    @foreach ($data as $date)
                        <td class="text-center" style="color: #005edf !important;"> {{ $date->ot ?? '---' }}
                        </td>
                    @endforeach
                </tr>
                <tr style="background-color:#eeeeee !important;">
                    <td class="text-center"> Status </td>
                    @foreach ($data as $date)
                        @php
                            $statusColor = null;
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
                                $statusColor = '';
                            }

                        @endphp
                        <td class="text-center" style="color:{{ $statusColor }} !important;">
                            {{ $date->status ?? '---' }}
                            <div style="font-size:6px">
                                @if ($date['shift'] && $date->status == 'P')
                                    @php

                                        $shiftWorkingHours = $date['shift']['working_hours'];
                                        $employeeHours = $date['total_hrs'];

                                        if (
                                            $shiftWorkingHours !== '' &&
                                            $employeeHours !== '' &&
                                            $shiftWorkingHours !== '---' &&
                                            $employeeHours !== '---'
                                        ) {
                                            [$hours, $minutes] = explode(':', $shiftWorkingHours);
                                            $shiftWorkingHours = $hours * 60 + $minutes;

                                            [$hours, $minutes] = explode(':', $employeeHours);
                                            $employeeHours = $hours * 60 + $minutes;

                                            if ($employeeHours < $shiftWorkingHours) {
                                                echo 'Short Shift';
                                            }
                                    } @endphp
                                @endif
                            </div>
                        </td>
                    @endforeach
                </tr>
            </table>

            <table style="margin-top: 60px">
                <tr>
                    <td style="border: none">
                        <span style="color:green !important; font-size:10px; ">
                            P = Present,
                        </span>
                        <span style="color:red !important; font-size:10px; ">
                            A = Absent,
                        </span>
                        <span style="color:gray !important; font-size:10px; ">
                            W = Weekoff,
                        </span>
                        <span style="color:blue !important; font-size:10px; ">
                            L = Leaves,
                        </span>
                        <span style="color:pink !important; font-size:10px; ">
                            H = Holiday,
                        </span>
                        <span style="color:orange !important; font-size:10px; ">
                            M = Missing
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    </main>

</body>

</html>
