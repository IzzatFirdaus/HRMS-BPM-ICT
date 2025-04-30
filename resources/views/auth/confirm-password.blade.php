@php
    $customizerHidden = 'customizer-hide';
    // Ensure $configData is available, maybe from a view composer or base controller
    // Provide a fallback if $configData is not set
    $configData = $configData ?? Helper::appClasses();
@endphp


@extends('layouts/blankLayout')

@section('title', __('Confirm Password')) {{-- Localized title --}}

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
            <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg p-sm-5 p-4">
                <div class="w-px-400 mx-auto">
                    <div class="app-brand mb-4">
                        <a href="{{ url('/') }}" class="app-brand-link">
                            <span class="app-brand-logo demo">@include('_partials.macros', ['height' => 20, 'withbg' => 'fill: #fff;'])</span>
                        </a>
                    </div>
                    <h3 class="mb-1">{{ __('Confirm Password') }}</h3> {{-- Localized --}}
                    <p class="text-start mb-4">{{ __('Please confirm your password before continuing.') }}</p>
                    {{-- Localized --}}
                    <form id="twoStepsForm" action="{{ route('password.confirm') }}" method="POST">
                        @csrf
                        <div class="mb-3 form-password-toggle">
                            <label class="form-label" for="password">{{ __('Enter Password') }}</label>
                            {{-- Localized --}}
                            {{-- Add is-invalid class to the input itself as well for direct styling --}}
                            <div class="input-group input-group-merge @error('password') is-invalid @enderror">
                                <input type="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror" name="password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="password" required autocomplete="current-password" />
                                {{-- Added required and autocomplete --}}
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
                        <button type="submit"
                            class="btn btn-primary d-grid w-100 mb-3">{{ __('Confirm Password') }}</button>
                        {{-- Localized --}}
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
