<x-mail::message>
# Welcome to Print Works LK, {{ $name }}!

Thank you for registering a new account with us. We're thrilled to have you here!

With your new account, you can quickly check out and track your orders directly from your customer dashboard.

<x-mail::button :url="config('app.url').'/dashboard'">
Go to Dashboard
</x-mail::button>

Thanks, and happy shopping!<br>
{{ config('app.name') }}
</x-mail::message>
