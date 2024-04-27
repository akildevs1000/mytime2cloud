@component('mail::message')
# System Notification: Absent Employees Update

Dear Admin,

This is an automated message to inform you that ({{ count($absentEmployees) }}) employees were absent today ({{ $date }}).

Employee List:
@foreach ($absentEmployees as $key => $employee)
{{ $key + 1 }}. {{ $employee->first_name ?? "Employee" }}
@endforeach

For any further information or action required, please let us know.

Thank you,<br>
{{ config('app.name') }}
@endcomponent
