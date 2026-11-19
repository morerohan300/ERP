@extends('layouts.guest')

@section('title', __('Reset password'))

@section('content')
    <h2 class="font-heading text-xl text-navy-900 dark:text-white">{{ __('Forgot your password?') }}</h2>
    <p class="mt-1 text-sm text-slate-500">{{ __('We will email you a password reset link.') }}</p>

    @if (session('status'))
        <div class="mt-4 rounded-control border border-success/30 bg-success/10 p-3 text-sm text-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
        @csrf
        <x-form.input name="email" :label="__('Email address')" type="email" :value="old('email')" required autofocus autocomplete="email" />
        <x-button type="submit" class="w-full">{{ __('Email password reset link') }}</x-button>
    </form>

    <p class="mt-6 text-center text-sm"><a href="{{ route('login') }}" class="text-gold-600 hover:underline">{{ __('Back to sign in') }}</a></p>
@endsection
