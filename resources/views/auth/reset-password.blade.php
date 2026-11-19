@extends('layouts.guest')

@section('title', __('Choose a new password'))

@section('content')
    <h2 class="font-heading text-xl text-navy-900 dark:text-white">{{ __('Choose a new password') }}</h2>

    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-form.input name="email" :label="__('Email address')" type="email" :value="old('email', request('email'))" required autofocus autocomplete="username" />
        <x-form.input name="password" :label="__('New password')" type="password" required autocomplete="new-password" />
        <x-form.input name="password_confirmation" :label="__('Confirm password')" type="password" required autocomplete="new-password" />
        <x-button type="submit" class="w-full">{{ __('Reset password') }}</x-button>
    </form>
@endsection
