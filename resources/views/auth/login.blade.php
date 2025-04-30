@php
    use App\Helpers\Helpers;
@endphp

@php
    $customizerHidden = 'customizer-hide';
    // Ensure $configData is available, provide a fallback
    $configData = $configData ?? Helpers::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', __('Login')) {{-- Localized title --}}

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
                    <img src="{{ asset('assets/img/illustrations/auth-login-illustration-' . $style . '.png') }}"
                        alt="auth-login-cover" class="img-fluid my-5 auth-illustration"
                        data-app-light-img="illustrations/auth-login-illustration-light.png"
                        data-app-dark-img="illustrations/auth-login-illustration-dark.png">

                    <img src="{{ asset('assets/img/illustrations/bg-shape-image-' . $style . '.png') }}"
                        alt="auth-login-cover" class="platform-bg"
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
                    {{-- Localized Welcome message --}}
                    <h3 class="mb-1">{{ __('Welcome to') . ' ' . __(env('APP_NAME', 'HRMS')) }}! 👋</h3>
                    {{-- Localized sign-in prompt --}}
                    <p class="mb-4">{{ __('Please sign-in to your account') }}</p>

                    {{-- Session status message, localized and with alert role --}}
                    @if (session('status'))
                        <div class="alert alert-success mb-1 rounded-0" role="alert">
                            <div class="alert-body">
                                {{ session('status') }}
                            </div>
                        </div>
                    @endif

                    <form id="formAuthentication" class="mb-3" action="{{ route('login') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            {{-- Localized email label --}}
                            <label for="login-email" class="form-label">{{ __('Email') }}</label>
                            {{-- Updated type, added required, autocomplete --}}
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="login-email"
                                name="email" placeholder="example@namaa.sy" autofocus value="{{ old('email') }}" required
                                autocomplete="email">
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <span class="fw-medium">{{ $message }}</span>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <div class="d-flex justify-content-between">
                                {{-- Localized password label --}}
                                <label class="form-label" for="login-password">{{ __('Password') }}</label>
                                {{-- Commented out Forgot Password link - kept as in original --}}
                                {{-- @if (Route::has('password.request'))
                  <a href="{{ route('password.request') }}">
                    <small>Forgot Password?</small>
                  </a>
                @endif --}}
                            </div>
                            <div class="input-group input-group-merge @error('password') is-invalid @enderror">
                                {{-- Added required and autocomplete --}}
                                <input type="password" id="login-password"
                                    class="form-control @error('password') is-invalid @enderror" name="password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="password" required autocomplete="current-password" />
                                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                            </div>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <span class="fw-medium">{{ $message }}</span>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember-me" name="remember"
                                    {{ old('remember') ? 'checked' : '' }} checked>
                                {{-- Localized Remember Me label --}}
                                <label class="form-check-label" for="remember-me">
                                    {{ __('Remember Me') }}
                                </label>
                            </div>
                        </div>
                        {{-- Localized Sign in button --}}
                        <button class="btn btn-primary d-grid w-100" type="submit">{{ __('Sign in') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
