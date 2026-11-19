@extends('layouts.guest')

@section('title', __('Sign in'))

@section('content')
    <h2 class="font-heading text-xl text-navy-900 dark:text-white">{{ __('Welcome back') }}</h2>
    <p class="mt-1 text-sm text-slate-500">{{ __('Sign in to your Ledgerline workspace.') }}</p>

    <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
        @csrf
        <x-form.input name="email" :label="__('Email address')" type="email" :value="old('email')" required autofocus autocomplete="username" />
        <div>
            <div class="mb-1 flex items-center justify-between">
                <label for="password" class="block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Password') }}</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs text-gold-600 hover:underline">{{ __('Forgot?') }}</a>
                @endif
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="w-full rounded-control border-slate-300 bg-white text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-navy-700 dark:bg-navy-950 dark:text-white">
            @error('password')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
        </div>

        <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
            <input type="checkbox" name="remember" class="rounded-control border-slate-300 text-gold-500 focus:ring-gold-500">
            {{ __('Remember me') }}
        </label>

        <x-button type="submit" class="w-full">{{ __('Sign in') }}</x-button>
    </form>

    @if (Route::has('register'))
        <p class="mt-6 text-center text-sm text-slate-500">
            {{ __('New to Ledgerline?') }}
            <a href="{{ route('register') }}" class="font-medium text-gold-600 hover:underline">{{ __('Create an account') }}</a>
        </p>
    @endif
@endsection
