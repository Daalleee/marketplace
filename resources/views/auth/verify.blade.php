@extends('layouts.app')

@section('title', 'Verifikasi Email')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary-blue text-white text-center">
                    <h4 class="mb-0">Verifikasi Email</h4>
                </div>
                <div class="card-body text-center">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            Link verifikasi baru telah dikirim ke alamat email Anda.
                        </div>
                    @endif

                    <p class="mb-4">Sebelum melanjutkan, silakan periksa email Anda untuk tautan verifikasi.</p>
                    <p class="mb-4">Jika Anda tidak menerima email,</p>

                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary-blue">
                            Klik di sini untuk meminta tautan baru
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
