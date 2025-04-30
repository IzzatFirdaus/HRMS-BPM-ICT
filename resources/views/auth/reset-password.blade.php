@php
    // Assuming Helper is a facade or globally available
    // use App\Helpers\Helper; // Uncomment if Helper is a facade
    $customizerHidden = 'customizer-hide';
    // Ensure $configData is available, provide a fallback
    // Provide a fallback if $configData is not set
    $configData = $configData ?? Helper::appClasses();

    // Assuming $request variable is available in the view (e.g., passed from controller)
    // Safely get the email from the request or old input
    $email = $request->email ?? old('email');
@endphp

@extends('layouts/blankLayout')

@section('title', __('Reset Password')) {{-- Localized title --}}

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
                    {{-- Localized title --}}
                    <h3 class="mb-1">{{ __('Reset Password') }} 🔒</h3>
                    <form id="formAuthentication" class="mb-3" action="{{ route('password.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <div class="mb-3">
                            {{-- Localized label --}}
                            <label for="email" class="form-label">{{ __('Email') }}</label>
                            {{-- Pre-filled from request/old, readonly, with is-invalid class --}}
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                name="email" placeholder="john@example.com" value="{{ $email }}" readonly />
                            @error('email')
                                <span class="invalid-feedback" role="alert"> {{-- Added role="alert" for accessibility --}}
                                    <span class="fw-medium">{{ $message }}</span>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3 form-password-toggle">
                            {{-- Localized label --}}
                            <label class="form-label" for="password">{{ __('New Password') }}</label>
                            <div class="input-group input-group-merge @error('password') is-invalid @enderror">
                                {{-- Added required and autocomplete --}}
                                <input type="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror" name="password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="password" autofocus required autocomplete="new-password" />
                                <span class="input-group-text cursor-pointer">
                                    <i class="ti ti-eye-off"></i>
                                </span>
                            </div>
                            @error('password')
                                <span class="invalid-feedback" role="alert"> {{-- Added role="alert" --}}
                                    <span class="fw-medium">{{ $message }}</span>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-3 form-password-toggle">
                            {{-- Localized label --}}
                            <label class="form-label" for="confirm-password">{{ __('Confirm Password') }}</label>
                            <div class="input-group input-group-merge">
                                {{-- Added required and autocomplete --}}
                                <input type="password" id="confirm-password" class="form-control"
                                    name="password_confirmation"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="password" required autocomplete="new-password" />
                                <span class="input-group-text cursor-pointer">
                                    <i class="ti ti-eye-off"></i>
                                </span>
                            </div>
                            {{-- No @error for password_confirmation specifically, handled by password error typically --}}
                        </div>
                        {{-- Localized button --}}
                        <button type="submit" class="btn btn-primary d-grid w-100 mb-3">
                            {{ __('Set new password') }}
                        </button>
                        <div class="text-center">
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}">
                                    <i class="ti ti-chevron-left scaleX-n1-rtl"></i>
                                    {{ __('Back to login') }} {{-- Localized --}}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
