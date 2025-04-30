@php
    // Assuming Helper is a facade or globally available
    // use App\Helpers\Helper; // Uncomment if Helper is a facade
    $customizerHidden = 'customizer-hide';
    // Ensure $configData is available, provide a fallback
    // Provide a fallback if $configData is not set
    $configData = $configData ?? Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', __('Register Page')) {{-- Localized title --}}

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
                    <img src="{{ asset('assets/img/illustrations/auth-register-illustration-' . $style . '.png') }}"
                        alt="auth-register-cover" class="img-fluid my-5 auth-illustration"
                        data-app-light-img="illustrations/auth-register-illustration-light.png"
                        data-app-dark-img="illustrations/auth-register-illustration-dark.png">

                    <img src="{{ asset('assets/img/illustrations/bg-shape-image-' . $style . '.png') }}"
                        alt="auth-register-cover" class="platform-bg"
                        data-app-light-img="illustrations/bg-shape-image-light.png"
                        data-app-dark-img="illustrations/bg-shape-image-dark.png">
                </div>
            </div>
            <div class="d-flex col-12 col-lg-5 align-items-center p-sm-5 p-4">
                <div class="w-px-400 mx-auto">
                    <div class="app-brand mb-4">
                        <a href="{{ url('/') }}" class="app-brand-link gap-2">
                            <span class="app-brand-logo demo">@include('_partials.macros', ['height' => 20, 'withbg' => 'fill: #fff;'])</span>
                        </a>
                    </div>
                    {{-- Localized welcome messages --}}
                    <h3 class="mb-1">{{ __('Adventure starts here') }} 🚀</h3>
                    <p class="mb-4">{{ __('Make your app management easy and fun!') }}</p>

                    <form id="formAuthentication" class="mb-3" action="{{ route('register') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            {{-- Localized label --}}
                            <label for="username" class="form-label">{{ __('Username') }}</label>
                            {{-- Added required, autocomplete, and is-invalid class directly --}}
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="username"
                                name="name" placeholder="johndoe" autofocus value="{{ old('name') }}" required
                                autocomplete="name" />
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <span class="fw-medium">{{ $message }}</span>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            {{-- Localized label --}}
                            <label for="email" class="form-label">{{ __('Email') }}</label>
                            {{-- Updated type, added required, autocomplete, and is-invalid class directly --}}
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                name="email" placeholder="john@example.com" value="{{ old('email') }}" required
                                autocomplete="email" />
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <span class="fw-medium">{{ $message }}</span>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-3 form-password-toggle">
                            {{-- Localized label --}}
                            <label class="form-label" for="password">{{ __('Password') }}</label>
                            <div class="input-group input-group-merge @error('password') is-invalid @enderror">
                                {{-- Added required and autocomplete --}}
                                <input type="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror" name="password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="password" required autocomplete="new-password" />
                                <span class="input-group-text cursor-pointer">
                                    <i class="ti ti-eye-off"></i>
                                </span>
                            </div>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <span class="fw-medium">{{ $message }}</span>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3 form-password-toggle">
                            {{-- Localized label --}}
                            <label class="form-label" for="password-confirm">{{ __('Confirm Password') }}</label>
                            <div class="input-group input-group-merge">
                                {{-- Added required and autocomplete --}}
                                <input type="password" id="password-confirm" class="form-control"
                                    name="password_confirmation"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="password" required autocomplete="new-password" />
                                <span class="input-group-text cursor-pointer">
                                    <i class="ti ti-eye-off"></i>
                                </span>
                            </div>
                            {{-- No @error for password_confirmation specifically, error messages are typically associated with the 'password' field --}}
                        </div>

                        @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                            <div class="mb-3">
                                <div class="form-check @error('terms') is-invalid @enderror">
                                    {{-- Added required attribute and value --}}
                                    <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox"
                                        id="terms" name="terms" value="1" required />
                                    <label class="form-check-label" for="terms">
                                        {{-- Localized label parts --}}
                                        {{ __('I agree to the') }}
                                        <a href="{{ route('policy.show') }}"
                                            target="_blank">{{ __('privacy policy') }}</a> &
                                        <a href="{{ route('terms.show') }}" target="_blank">{{ __('terms') }}</a>
                                    </label>
                                </div>
                                {{-- --- START OF CHANGE --- --}}
                                @error('terms')
                                    <div class="invalid-feedback" role="alert">
                                        <span class="fw-medium">{{ $errors->first('terms') }}</span> {{-- Changed $message to $errors->first('terms') --}}
                                    </div>
                                @enderror
                                {{-- --- END OF CHANGE --- --}}
                            </div>
                        @endif
                        {{-- Localized Sign up button --}}
                        <button type="submit" class="btn btn-primary d-grid w-100">{{ __('Sign up') }}</button>
                    </form>

                    <p class="text-center mt-2">
                        {{-- Localized link text --}}
                        <span>{{ __('Already have an account?') }}</span>
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}">
                                <span>{{ __('Sign in instead') }}</span>
                            </a>
                        @endif
                    </p>

                    <div class="divider my-4">
                        {{-- Localized divider text --}}
                        <div class="divider-text">{{ __('or') }}</div>
                    </div>

                    {{-- Social login links (kept as is, localization of icon titles or links might be needed) --}}
                    <div class="d-flex justify-content-center">
                        <a href="javascript:;" class="btn btn-icon btn-label-facebook me-3">
                            <i class="tf-icons fa-brands fa-facebook-f fs-5"></i>
                        </a>

                        <a href="javascript:;" class="btn btn-icon btn-label-google-plus me-3">
                            <i class="tf-icons fa-brands fa-google fs-5"></i>
                        </a>

                        <a href="javascript:;" class="btn btn-icon btn-label-twitter">
                            <i class="tf-icons fa-brands fa-twitter fs-5"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
