@component('mail::message')
# Hello, {{ $company->name }}

Total **({{ $offlineDevicesCount }})** of your devices are currently offline.

Devices location {{ $devicesLocation }}.

Please take a look and address the issue as needed to avoid any errors in reports.

If you have any questions or need assistance, feel free to reach out.

@component('mail::button', ['url' => 'https://mytime2cloud.com/login'])
Open App
@endcomponent

Best regards,<br>
{{ config('app.name') }}
@endcomponent
