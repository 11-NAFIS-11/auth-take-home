@component('mail::message')
# {{ __('mail.password_reset.greeting') }}

{{ __('mail.password_reset.intro') }}

@component('mail::button', ['url' => $url])
{{ __('mail.password_reset.action') }}
@endcomponent

{{ __('mail.password_reset.expires', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]) }}

{{ __('mail.password_reset.footer') }}

{{ config('app.name') }}
@endcomponent
