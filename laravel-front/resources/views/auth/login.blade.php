@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="container-fluid p-0">
    <div class="auth-page">
        <div class="pt-lg-6 p-4 justify-content-center">
            <div class="card auth-card">
                <div class="row g-0">
                    <div class="col-xl-5 left-side">
                        <div class="left-side-text d-flex flex-column align-items-center">
                            <div class="p-5">
                                <div class="p-5">
                                    <p>&nbsp;</p>
                                    <p>&nbsp;</p>
                                </div>
                                <h3 class="welcome-msg">Good to See you!</h3>
                                <p class="text-center">Ready to evaluate?</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-7 right-side bg-white">
                        <div class="d-flex flex-column h-100">
                            <div class="pt-5 mb-3 text-center">
                                <a href="javascript:void(0)" class="d-block auth-logo">
                                    <img src="{{ asset('assets/images/dcismicon.png') }}" alt="" width="130">
                                </a>
                            </div>
                            <div class="auth-content my-auto">
                                <div class="form-container slide-left" id="formContainer">
                                    <div class="form" id="form1">
                                        <div class="text-center">
                                            <h4 style="color: #333333;" class="mt-2">Sign in to continue.</h4>
                                        </div>

                                        {{-- Session Messages --}}
                                        @if (session('success'))
                                            <div class="alert alert-success mt-4">{{ session('success') }}</div>
                                        @endif 
                                        @if (session('error'))
                                            <div class="alert alert-danger mt-4">{{ session('error') }}</div>
                                        @endif
                                        
                                        <form class="mt-4 pt-2" method="POST" action="{{ route('login.submit') }}">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" name="email" id="email" placeholder="Enter Your Email" required>
                                                @error('email')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <div class="d-flex align-items-start">
                                                    <div class="flex-grow-1">
                                                        <label class="form-label">Password</label>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        {{-- If you need a "Forgot Password" link, you can add it here --}}
                                                        {{-- <div><a href="#" class="text-muted">Forgot password?</a></div> --}}
                                                    </div>
                                                </div>
                                                <div class="input-group auth-pass-inputgroup">
                                                    <input type="password" class="form-control" name="password" id="password" placeholder="Enter Your Password" required>
                                                    <button class="btn btn-light shadow-none ms-0" type="button" id="password-addon"><i class="mdi mdi-eye-outline"></i></button>
                                                </div>
                                                @error('password')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="row mb-4">
                                                <div class="col">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="remember-check" name="remember">
                                                        <label class="form-check-label" for="remember-check">Remember me</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <button class="btn btn-sign-in w-100 waves-effect waves-light" type="submit">Sign In</button>
                                            </div>
                                        </form>
                                    </div>
                                </div> 
                            </div>      
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection