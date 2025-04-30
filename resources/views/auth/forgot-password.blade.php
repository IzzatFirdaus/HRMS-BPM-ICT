@php
    $customizerHidden = 'customizer-hide';
    // Ensure $configData is available, maybe from a view composer or base controller
    // Provide a fallback if $configData is not set
    $configData = $configData ?? Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', __('Forgot Password')) {{-- Localized title --}}

@section('page-style')
    {{-- Page Css files --}}
    {{-- Assume Mix is used based on the original code --}}
    {{-- Check if mix() helper exists before using it, for robustness --}}
    @if (function_exists('mix'))
        <link rel="stylesheet" href="{{ asset(mix('assets/vendor/css/pages/page-auth.css')) }}">
    @else
        {{-- Fallback or alternative CSS include if mix is not used (e.g., Vite) --}}
        {{-- Adjust this based on your actual asset bundling setup --}}
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
                    <img src="{{ asset('assets/img/illustrations/auth-forgot-password-illustration-' . $style . '.png') }}"
                        alt="auth-forgot-password-cover" class="img-fluid my-5 auth-illustration"
                        data-app-light-img="illustrations/auth-forgot-password-illustration-light.png"
                        data-app-dark-img="illustrations/auth-forgot-password-illustration-dark.png">

                    <img src="{{ asset('assets/img/illustrations/bg-shape-image-' . $style . '.png') }}"
                        alt="auth-forgot-password-cover" class="platform-bg"
                        data-app-light-img="illustrations/bg-shape-image-light.png"
                        data-app-dark-img="illustrations/bg-shape-image-dark.png">
                </div>
            </div>
            <div class="d-flex col-12 col-lg-5 align-items-center p-sm-5 p-4">
                <div class="w-px-400 mx-auto">
                    <div class="app-brand mb-4">
                        <a href="{{ url('/') }}" class="app-brand-link">
                            <span class="app-brand-logo demo">@include('_partials.macros', ['height' => 20, 'withbg' => 'fill: #fff;'])</span>
                        </a>
                    </div>
                    <h3 class="mb-1">{{ __('Forgot Password?') }} 🔒</h3> {{-- Localized --}}
                    <p class="mb-4">{{ __('Enter your email and we\'ll send you instructions to reset your password') }}
                    </p> {{-- Localized --}}

                    @if (session('status'))
                        <div class="mb-1 text-success" role="alert"> {{-- Added role="alert" for accessibility --}}
                            {{ session('status') }}
                        </div>
                    @endif

                    <form id="formAuthentication" class="mb-3" action="{{ route('password.email') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('Email') }}</label> {{-- Localized --}}
                            {{-- Added type, required, autocomplete, and is-invalid class directly --}}
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                name="email" value="{{ old('email') }}" placeholder="john@example.com" autofocus required
                                autocomplete="email"> {{-- Added old('email') for convenience --}}
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <span class="fw-medium">{{ $message }}</span>
                                </span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary d-grid w-100">{{ __('Send Reset Link') }}</button>
                        {{-- Localized --}}
                    </form>
                    <div class="text-center">
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="d-flex align-items-center justify-content-center">
                                <i class="ti ti-chevron-left scaleX-n1-rtl"></i>
                                {{ __('Back to login') }} {{-- Localized --}}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
