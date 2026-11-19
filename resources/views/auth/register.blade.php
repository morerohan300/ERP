@extends('layouts.guest')

@section('title', __('Create account'))

@section('content')
    <h2 class="font-heading text-xl text-navy-900 dark:text-white">{{ __('Create your account') }}</h2>
    <p class="mt-1 text-sm text-slate-500">{{ __('You will set up your first company after registering.') }}</p>

    <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-4">
        @csrf
        <x-form.input name="name" :label="__('Full name')" :value="old('name')" required autofocus autocomplete="name" />
        <x-form.input name="email" :label="__('Email address')" type="email" :value="old('email')" required autocomplete="username" />
        <x-form.input name="password" :label="__('Password')" type="password" required autocomplete="new-password" />
        <x-form.input name="password_confirmation" :label="__('Confirm password')" type="password" required autocomplete="new-password" />

        <x-button type="submit" class="w-full">{{ __('Create account') }}</x-button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        {{ __('Already have an account?') }}
        <a href="{{ route('login') }}" class="font-medium text-gold-600 hover:underline">{{ __('Sign in') }}</a>
    </p>
@endsection
