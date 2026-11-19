@extends('layouts.guest')

@section('title', __('Verify email'))

@section('content')
    <h2 class="font-heading text-xl text-navy-900 dark:text-white">{{ __('Verify your email address') }}</h2>

    @if (session('status') === 'verification-link-sent')
        <div class="mt-4 rounded-control border border-success/30 bg-success/10 p-3 text-sm text-success">
            {{ __('A new verification link has been sent to your email address.') }}
        </div>
    @endif

    <p class="mt-3 text-sm text-slate-500">
        {{ __('Please click the verification link we sent to your email before continuing.') }}
    </p>

    <div class="mt-6 flex items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-button type="submit" variant="secondary">{{ __('Resend verification email') }}</x-button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-danger hover:underline">{{ __('Log out') }}</button>
        </form>
    </div>
@endsection
