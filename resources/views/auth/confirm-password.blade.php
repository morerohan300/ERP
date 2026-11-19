@extends('layouts.guest')

@section('title', __('Confirm password'))

@section('content')
    <h2 class="font-heading text-xl text-navy-900 dark:text-white">{{ __('Confirm your password') }}</h2>
    <p class="mt-1 text-sm text-slate-500">{{ __('This area requires you to confirm your password.') }}</p>

    <form method="POST" action="{{ route('password.confirm.store') }}" class="mt-6 space-y-4">
        @csrf
        <x-form.input name="password" :label="__('Password')" type="password" required autofocus autocomplete="current-password" />
        <x-button type="submit" class="w-full">{{ __('Confirm') }}</x-button>
    </form>
@endsection
