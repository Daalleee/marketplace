@extends('layouts.app')

@section('title', 'Chat dengan ' . $otherUser->name . ' - Marketplace Barang Bekas')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white text-dark d-flex align-items-center border-bottom">
                    <a href="{{ $orderId ? route('messages.index', ['order_id' => $orderId]) : route('messages.index') }}" class="text-dark me-3">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div class="flex-grow-1">
                        <h4 class="mb-0 text-dark">{{ $otherUser->name }}</h4>
                        @if($orderId)
                            <small class="text-muted">Untuk Order #{{ $orderId }}</small>
                        @endif
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="chat-container" style="height: 400px; overflow-y: auto; padding: 20px;">
                        @forelse($messages as $message)
                            <div class="d-flex mb-3 {{ $message->sender_id == Auth::id() ? 'justify-content-end' : 'justify-content-start' }}">
                                <div class="message-bubble {{ $message->sender_id == Auth::id() ? 'message-sent' : 'message-received' }} p-3 rounded"
                                     style="max-width: 70%;">
                                    <p class="mb-0">{{ $message->message }}</p>
                                    <small class="d-block text-end {{ $message->sender_id == Auth::id() ? 'text-light-gray' : 'text-dark-gray' }}">
                                        {{ $message->created_at->format('H:i') }}
                                        @if($message->order_id)
                                            <span class="ms-2 badge bg-medium-gray" style="font-size: 0.7em;">Order #{{ $message->order_id }}</span>
                                        @endif
                                    </small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada pesan dalam percakapan ini</p>
                                @if($orderId)
                                    <p class="text-muted">Percakapan ini untuk order #{{ $orderId }}</p>
                                @endif
                            </div>
                        @endforelse
                    </div>

                    <div class="card-footer bg-white border-top">
                        <form action="{{ route('messages.send', $otherUser->id) }}@if($orderId)?order_id={{ $orderId }}@endif" method="POST">
                            @csrf
                            <div class="input-group">
                                <input type="text" name="message" class="form-control bg-light-input" placeholder="Ketik pesan..." required>
                                @if($orderId)
                                    <input type="hidden" name="order_id" value="{{ $orderId }}">
                                @endif
                                <button type="submit" class="btn btn-primary-blue">
                                    <i class="fas fa-paper-plane"></i> Kirim
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .chat-container {
        background-color: var(--white) !important;
    }

    .message-bubble {
        word-wrap: break-word;
        border: 1px solid var(--medium-gray);
        border-radius: 10px !important;
    }

    .message-sent {
        background-color: var(--near-black) !important;
        color: var(--white) !important;
    }

    .message-received {
        background-color: white !important;
        color: var(--black) !important;
        border: 1px solid var(--medium-gray) !important;
    }

    .text-light-gray {
        color: #cccccc !important; /* Light gray for sender timestamps */
    }

    .text-dark-gray {
        color: var(--dark-gray) !important;
    }

    .bg-medium-gray {
        background-color: var(--medium-gray) !important;
        color: var(--darker-gray) !important;
    }

    .bg-light-input {
        background-color: var(--light-gray) !important;
        border: 1px solid var(--medium-gray) !important;
        border-right: none !important;
    }

    .btn-primary-blue {
        border: 1px solid var(--medium-gray) !important;
    }
</style>
@endsection