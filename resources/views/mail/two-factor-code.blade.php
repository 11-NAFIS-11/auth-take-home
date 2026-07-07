@component('mail::message')
# {{ __('mail.two_factor.greeting') }}

{{ __('mail.two_factor.intro', ['minutes' => $ttlMinutes]) }}

@component('mail::panel')
<div style="font-size: 32px; font-weight: 700; letter-spacing: 8px; text-align: center;{{ $rtl ? ' direction: ltr;' : '' }}">
{{ $code }}
</div>
@endcomponent

{{ __('mail.two_factor.footer') }}

{{ config('app.name') }}
@endcomponent
