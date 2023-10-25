@component('mail::message')
# Hello, {{ $manager->name }}
# Company: {{ $company->name }}
Total **({{ $offlineDevicesCount }})** of your devices are currently offline.

<b>Devices location(s) :</b> {{ $devicesLocation }}.

Please take a look and address the issue as needed to avoid any errors in reports.

If you have any questions or need assistance, feel free to reach out.



@component('mail::button', ['url' => config("app.url").'/login'])
Visit Website
@endcomponent


Best regards,<br>
{{ config('app.name') }}
@endcomponent