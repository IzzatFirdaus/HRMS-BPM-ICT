@php
    // Assuming Helper is a facade or globally available
    // use App\Helpers\Helper; // Uncomment if Helper is a facade
    $customizerHidden = 'customizer-hide';
    // Although $configData is not used in the visible part of this basic layout,
    // keep the definition if it's part of a standard template structure.
    $configData = $configData ?? Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', __('Verify Email')) {{-- Localized title --}}

@section('page-style')
    {{-- Page Css files --}}
    {{-- Check if mix() helper exists before using it --}}
    @if (function_exists('mix'))
        <link rel="stylesheet" href="{{ asset(mix('assets/vendor/css/pages/page-auth.css')) }}">
    @else
        {{-- Fallback or alternative CSS include (e.g., Vite) --}}
        {{-- <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}"> --}}
    @endif
@endsection

@section('content')
    <div class="authentication-wrapper authentication-basic px-4">
        <div class="authentication-inner py-4">

            <div class="app-brand mb-4">
                <a href="{{ url('/') }}" class="app-brand-link">
                    <span class="app-brand-logo demo">@include('_partials.macros', ['height' => 20, 'withbg' => 'fill: #fff;'])</span>
                </a>
            </div>
            <div class="card">
                <div class="card-body">
                    {{-- Localized title --}}
                    <h3 class="mb-1">{{ __('Verify your email') }} ✉️</h3>

                    {{-- Session status message, localized and with alert role --}}
                    @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success" role="alert">
                            <div class="alert-body">
                                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                                {{-- Localized --}}
                            </div>
                        </div>
                    @endif
                    <p class="text-start">
                        {{-- Localized instruction text with user email --}}
                        {{ __('Account activation link sent to your email address:') }} <span
                            class="fw-medium">{{ Auth::user()?->email ?? 'N/A' }}</span>
                        {{ __('Please follow the link inside to continue.') }} {{-- Added null-safe operator and fallback --}}
                    </p>
                    <div class="mt-4 d-flex flex-column justify-content-between gap-2">
                        {{-- Form to request new verification link --}}
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            {{-- Localized button text --}}
                            <button type="submit" class="w-100 btn btn-label-secondary">
                                {{ __('click here to request another') }}
                            </button>
                        </form>

                        {{-- Form to log out --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            {{-- Localized button text --}}
                            <button type="submit" class="w-100 btn btn-danger">
                                {{ __('Log Out') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
