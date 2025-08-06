@extends('layouts.auth_layout')

@section('content')
<div class="container d-flex flex-column">
    <div class="row outer-box pt-5 pt-md-0">
        <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
            <div class="d-table-cell align-middle">
                <div class="card" style="background: #EFF5F8;">
                    <div class="card-body">
                        <div class="m-sm-3">
                            <div class="text-center mt-4">
                                <br>
                                <img src="{{ asset('images/logo.png') }}" width="125px">
                                <br>
                                <br>
                                <h4 class="text-blue">
                                    <b>Login and get tracking!</b>
                                </h4>
                            </div>
                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="email" class="fw-semibold px-2 form-label">{{ __('E-mail') }}</label>
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>

                                <div class="position-relative">
                                    <label for="password" class="fw-semibold px-2 form-label">{{ __('Password') }}</label>
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                    <span id="togglePassword" class="position-absolute">
                                        <i class="text-blue fa-solid fa-eye-slash" id="eyeIcon"></i>
                                    </span>

                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    @if (Route::has('password.request'))
                                    <a class="text-blue fs-12 fw-medium" href="{{ route('password.request') }}">
                                        {{ __('Reset Password?') }}
                                    </a>
                                    @endif
                                </div>

                                <div class="d-grid gap-2 mt-3">
                                    <button type="submit" class="btn btn-gray">
                                        {{ __('Continue') }}
                                    </button>
                                </div>
                                <div class="text-center mt-3">
                                    <span>{{ __('OR') }}</span>
                                </div>
                                <div class="d-grid gap-2 mt-3">
                                    <a href="{{ route('microsoft.login') }}" class="btn btn-primary">
                                        <i class="fab fa-microsoft"></i> {{ __('Continue using Microsoft') }}
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="{{ asset('js/login.js') }}"></script>
@endpush