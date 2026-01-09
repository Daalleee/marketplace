@extends('layouts.app')

@section('title', 'Konfirmasi Password')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary-blue text-white text-center">
                    <h4 class="mb-0">Konfirmasi Password</h4>
                </div>
                <div class="card-body">
                    <p class="mb-4">Silakan konfirmasi password Anda sebelum melanjutkan.</p>

                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                                   name="password" required autocomplete="current-password"
                                   placeholder="Masukkan password Anda">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary-blue">
                                Konfirmasi Password
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        @if (Route::has('password.request'))
                            <a class="text-primary-blue" href="{{ route('password.request') }}">
                                Lupa Password?
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
