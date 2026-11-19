@extends('layouts.guest')

@section('title', __('Two-factor challenge'))

@section('content')
    <h2 class="font-heading text-xl text-navy-900 dark:text-white">{{ __('Two-factor authentication') }}</h2>
    <p class="mt-1 text-sm text-slate-500">{{ __('Enter the six-digit code from your authenticator app, or a recovery code.') }}</p>

    @if ($errors->any())
        <div class="mt-4 rounded-control border border-danger/30 bg-danger/10 p-3 text-sm text-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('two-factor.login.store') }}" class="mt-6 space-y-4" x-data="{ recovery: false }">
        @csrf
        <div x-show="! recovery">
            <x-form.input name="code" :label="__('Authentication code')" inputmode="numeric" autocomplete="one-time-code" autofocus />
        </div>
        <div x-show="recovery" x-cloak>
            <x-form.input name="recovery_code" :label="__('Recovery code')" autocomplete="one-time-code" />
        </div>

        <button type="button" class="text-xs text-gold-600 hover:underline" @click="recovery = ! recovery">
            <span x-show="! recovery">{{ __('Use a recovery code') }}</span>
            <span x-show="recovery" x-cloak>{{ __('Use an authentication code') }}</span>
        </button>

        <x-button type="submit" class="w-full">{{ __('Verify') }}</x-button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="text-sm text-danger hover:underline">{{ __('Log out') }}</button>
    </form>
@endsection
