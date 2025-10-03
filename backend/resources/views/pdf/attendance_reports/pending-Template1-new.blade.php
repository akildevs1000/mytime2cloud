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
            border-bottom: 1px solid #e2e8f0;
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
            font-size: 24px;
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
            margin-bottom: 8px;
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

        /* --- Attendance Data Table - Added rounding logic --- */
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            text-align: left;
            color: #64748b;
            border: 1px solid #e2e8f0;
            /* --- Rounded Corners Added --- */
            border-radius: 8px;
            overflow: hidden;
            /* Crucial to clip internal content for rounded corners */
            /* ----------------------------- */
        }

        .attendance-table th,
        .attendance-table td {
            padding: 10px 12px;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            line-height: 1.2;
        }

        .attendance-table thead th {
            background-color: #f3f4f6;
            color: #374151;
            text-transform: uppercase;
            font-weight: bold;
            /* Remove top borders for a clean top edge */
            border-top: none;
        }

        /* Ensure the footer total row uses the correct background and rounding */
        .attendance-table tfoot tr:last-child {
            background-color: #e2e8f0;
            /* Slightly darker footer */
        }

        .attendance-table thead tr:first-child th:first-child {
            border-top-left-radius: 8px;
        }

        .attendance-table thead tr:first-child th:last-child {
            border-top-right-radius: 8px;
        }

        .attendance-table tfoot tr:last-child td:first-child {
            border-bottom-left-radius: 8px;
        }

        .attendance-table tfoot tr:last-child td:last-child {
            border-bottom-right-radius: 8px;
        }


        .attendance-table tbody tr {
            background-color: #ffffff;
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

        /* Footer Totals */
        .attendance-table tfoot td {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #1e293b;
        }

        .footer-total-label {
            text-align: right;
        }

        /* Custom background for total row to contrast better */
        .attendance-table tfoot tr {
            background-color: #e2e8f0;
        }

        /* Add this class to your <style> block */
        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            font-size: 10px;
            color: #64748b;
            font-family: Arial, sans-serif;

            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 40px;
            border-top: 1px solid #e5e7eb;
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
    </style>
</head>

<body>

    <footer>
        <div class="no-print"
            style="
              margin-top: 20px;
              display: table;
              width: 100%;
              font-size: 12px;
              color: #64748b;
            ">
            <div style="display: table-row">
                <div style="display: table-cell; width: 33%">
                    Printed on: {{ date('d M Y') }}
                </div>
                <div style="display: table-cell; width: 34%; text-align: center">
                    Powered by MyTime2Cloud.com
                </div>
                <div style="display: table-cell; width: 33%; text-align: right">
                    <div><span class="pagenum"></span></div>
                </div>
            </div>
        </div>
        {{-- <div>Printed on: 02-Oct-2025</div>
        <div>Powered by MyTime2Cloud.com</div>
        <div><span class="pagenum"></span></div> --}}
    </footer>

    <main>

        {{-- FIRST PAGE --}}
        <div class="">
            {{-- Your special header with logo, details, stats... --}}
            <table class="header-table">
                <tr>
                    <td style="width: 50%">
                        <table style="border-collapse: collapse">
                            <tr>
                                <td>
                                    <img alt="Company Logo" class="header-logo"
                                        src="{{ env('BASE_URL', 'https://backend.mytime2cloud.com') . '/' . $company->logo_raw }}"
                                        onerror="this.onerror=null; this.src='https://placehold.co/64x64/4f46e5/ffffff?text=Logo';" />
                                </td>
                                <td>
                                    <p class="header-title">Monthly Attendance Report</p>
                                    <p class="header-subtitle"> {{ date('d M Y', strtotime($from_date)) }} -
                                        {{ date('d M Y', strtotime($to_date)) }}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td class="company-info" style="width: 50%">
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
                    <td>
                        <p class="detail-label">EMPLOYEE</p>
                        <p class="detail-value">{{ $employee->full_name }} ({{ $employee->employee_id }})</p>
                    </td>
                    <td>
                        <p class="detail-label">DEPARTMENT</p>
                        <p class="detail-value">{{ $employee?->department?->name ?? '---' }}</p>
                    </td>
                    <td>
                        <p class="detail-label">BRANCH NAME</p>
                        <p class="detail-value">{{ $employee?->branch?->branch_name ?? 'Default Branch' }}</p>
                    </td>
                    <td>
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
                            <p class="stat-label text-purple-600">Manual Punches {{ $shift_type_id }}</p>
                            <p class="stat-value text-purple-700">{{ rand(1, 5) }}</p>
                        </div>
                    </td>
                </tr>
            </table>

            {{-- Attendance Data Table (only 7 data) --}}
            <div class="table-container">
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th style="text-align: left">Date</th>
                            @if ($shift_type_id != 2)
                                <th style="text-align: left">Shift</th>
                                <th style="text-align: left">In Time</th>
                                <th style="text-align: left">Out Time</th>
                                <th style="text-align: left">Late In</th>
                                <th style="text-align: left">Early Out</th>
                            @else
                                @for ($i = 1; $i < 8; $i++)
                                    <th style="text-align: left">In{{ $i}}</th>
                                    <th style="text-align: left">Out{{ $i}}</th>
                                @endfor
                            @endif
                            <th style="text-align: left">T.Hrs</th>
                            <th style="text-align: left">OT</th>
                            <th style="text-align: left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data->take(7) as $date)
                            <tr>
                                <td> {{ date('d M Y', strtotime($date->date)) ?? '---' }}</td>
                                @if ($shift_type_id != 2)
                                    <td>
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
                                    </td>
                                    <td>{{ $date->in }}</td>
                                    <td>{{ $date->out }}</td>
                                    <td>{{ $date->late_coming }}</td>
                                    <td>{{ $date->early_going }}</td>
                                @else
                                    @for ($i = 1; $i < 8; $i++)
                                        <td class="text-center">
                                            {{ $date->logs[$i]['in'] ?? '---' }}
                                            <div class="secondary-value"
                                                style="font-size:6px; color: {{ ($date->logs[$i]['device_in'] ?? '') === 'Manual' ? 'red' : '' }}">
                                                {{ $date->logs[$i]['device_in'] ?? '---' }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            {{ $date->logs[$i]['out'] ?? '---' }}
                                            <div class="secondary-value"
                                                style="font-size:6px; color: {{ ($date->logs[$i]['device_out'] ?? '') === 'Manual' ? 'red' : '' }}">
                                                {{ $date->logs[$i]['device_out'] ?? '---' }}
                                            </div>
                                        </td>
                                    @endfor
                                @endif


                                <td>{{ $date->total_hrs }}</td>
                                <td>{{ $date->ot }}</td>
                                <td>
                                    {{ $date->status }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PAGE BREAK --}}
        <div class="force-break"></div>



        {{-- REMAINING PAGES (10 data per page) --}}
        @foreach ($data->skip(7)->chunk(10) as $chunk)
            <table class="header-table" style="padding: 20px">
                <tr>
                    <td style="width: 50%">
                        <table style="border-collapse: collapse">
                            <tr>
                                <td>
                                    <img alt="Company Logo" class="header-logo"
                                        src="{{ env('BASE_URL', 'https://backend.mytime2cloud.com') . '/' . $company->logo_raw }}"
                                        onerror="this.onerror=null; this.src='https://placehold.co/64x64/4f46e5/ffffff?text=Logo';" />
                                </td>
                                <td>
                                    <p class="header-title">Monthly Attendance Report (Cont.)</p>
                                    <p class="header-subtitle"> {{ date('d M Y', strtotime($from_date)) }} -
                                        {{ date('d M Y', strtotime($to_date)) }}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td class="company-info" style="width: 50%">
                        <p class="company-name">{{ $company->name ?? '' }}</p>
                        <p class="header-subtitle">{{ $employee->full_name }} ({{ $employee->employee_id }})</p>
                    </td>
                </tr>
            </table>
            <div class="table-container">
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th style="text-align: left">Date</th>
                            <th style="text-align: left">Shift</th>
                            <th style="text-align: left">In Time</th>
                            <th style="text-align: left">Out Time</th>
                            <th style="text-align: left">Late In</th>
                            <th style="text-align: left">Early Out</th>
                            <th style="text-align: left">Total Hours</th>
                            <th style="text-align: left">OT</th>
                            <th style="text-align: left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($chunk as $date)
                            <tr>
                                <td> {{ date('d M Y', strtotime($date->date)) ?? '---' }}</td>
                                <td>
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
                                </td>
                                <td>{{ $date->in }}</td>
                                <td>{{ $date->out }}</td>
                                <td>{{ $date->late_coming }}</td>
                                <td>{{ $date->early_going }}</td>
                                <td>{{ $date->total_hrs }}</td>
                                <td>{{ $date->ot }}</td>
                                <td>
                                    {{ $date->status }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if (!$loop->last)
                <div class="force-break"></div>
            @endif
        @endforeach

    </main>

</body>

</html>
