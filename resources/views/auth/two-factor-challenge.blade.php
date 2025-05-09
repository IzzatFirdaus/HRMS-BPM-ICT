@php
    // Assuming Helper is a facade or globally available
    // use App\Helpers\Helper; // Uncomment if Helper is a facade
    $customizerHidden = 'customizer-hide';
    // Ensure $configData is available, provide a fallback
    // Provide a fallback if $configData is not set
    $configData = $configData ?? Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', __('Two Factor Challenge')) {{-- Localized title --}}

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
    <div class="authentication-wrapper authentication-cover authentication-bg">
        <div class="authentication-inner row">

            <div class="d-none d-lg-flex col-lg-7 p-0">
                <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
                    {{-- Safely access $configData['style'] --}}
                    @php
                        // Provide a default style if $configData or its 'style' key is not set
                        $style = $configData['style'] ?? 'light';
                    @endphp
                    <img src="{{ asset('assets/img/illustrations/auth-two-step-illustration-' . $style . '.png') }}"
                        alt="auth-two-steps-cover" class="img-fluid my-5 auth-illustration"
                        data-app-light-img="illustrations/auth-two-step-illustration-light.png"
                        data-app-dark-img="illustrations/auth-two-step-illustration-dark.png">

                    <img src="{{ asset('assets/img/illustrations/bg-shape-image-' . $style . '.png') }}"
                        alt="auth-two-steps-cover" class="platform-bg"
                        data-app-light-img="illustrations/bg-shape-image-light.png"
                        data-app-dark-img="illustrations/bg-shape-image-dark.png">
                </div>
            </div>
            <div class="d-flex col-12 col-lg-5 align-items-center p-4 p-sm-5">
                <div class="w-px-400 mx-auto">
                    <div class="app-brand mb-4">
                        <a href="{{ url('/') }}" class="app-brand-link">
                            <span class="app-brand-logo demo">@include('_partials.macros', ['height' => 20, 'withbg' => 'fill: #fff;'])</span>
                        </a>
                    </div>
                    {{-- Localized title --}}
                    <h3 class="mb-1">{{ __('Two Step Verification') }} 💬</h3>
                    <div x-data="{ recovery: false }">
                        <div class="mb-3" x-show="! recovery">
                            {{-- Localized instruction text --}}
                            {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
                        </div>

                        <div class="mb-3" x-show="recovery">
                            {{-- Localized instruction text --}}
                            {{ __('Please confirm access to your account by entering one of your emergency recovery codes.') }}
                        </div>

                        {{-- Jetstream validation errors component (already included) --}}
                        <x-validation-errors class="mb-1" />

                        <form method="POST" action="{{ route('two-factor.login') }}">
                            @csrf

                            <div class="mb-3" x-show="! recovery">
                                {{-- Jetstream label component (already localized) --}}
                                <x-label class="form-label" value="{{ __('Code') }}" />
                                {{-- Jetstream input component (add required) --}}
                                <x-input class="{{ $errors->has('code') ? 'is-invalid' : '' }}" type="text"
                                    inputmode="numeric" name="code" autofocus x-ref="code" autocomplete="one-time-code"
                                    required /> {{-- Added required --}}
                                {{-- Jetstream input error component (already included) --}}
                                <x-input-error for="code"></x-input-error>
                            </div>

                            <div class="mb-3" x-show="recovery">
                                {{-- Jetstream label component (already localized) --}}
                                <x-label class="form-label" value="{{ __('Recovery Code') }}" />
                                {{-- Jetstream input component (add required) --}}
                                <x-input class="{{ $errors->has('recovery_code') ? 'is-invalid' : '' }}" type="text"
                                    name="recovery_code" x-ref="recovery_code" autocomplete="one-time-code" required />
                                {{-- Added required --}}
                                {{-- Jetstream input error component (already included) --}}
                                <x-input-error for="recovery_code"></x-input-error>
                            </div>

                            <div class="d-flex justify-content-end my-2 gap-2">
                                <div x-show="! recovery"
                                    x-on:click="recovery = true; $nextTick(() => { $refs.recovery_code.focus()})">
                                    {{-- Localized button text --}}
                                    <button type="button"
                                        class="btn btn-outline-secondary me-1">{{ __('Use a recovery code') }}</button>
                                </div>
                                <div x-cloak x-show="recovery"
                                    x-on:click="recovery = false; $nextTick(() => { $refs.code.focus() })">
                                    {{-- Localized button text --}}
                                    <button type="button"
                                        class="btn btn-outline-secondary me-1">{{ __('Use an authentication code') }}</button>
                                </div>
                                {{-- Jetstream button component (localize text) --}}
                                <x-button>{{ __('Log in') }}</x-button> {{-- Localized button text --}}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
