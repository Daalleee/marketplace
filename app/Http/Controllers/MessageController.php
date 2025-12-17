<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index()
    {
        // Ambil percakapan berdasarkan order jika ada parameter order_id
        $orderId = request()->query('order_id');

        if ($orderId) {
            // Jika ada order_id, tampilkan percakapan spesifik untuk order tersebut
            $conversations = Message::where(function($query) {
                    $query->where('sender_id', Auth::id())
                          ->orWhere('receiver_id', Auth::id());
                })
                ->where('order_id', $orderId)
                ->select('sender_id', 'receiver_id', 'order_id')
                ->with(['sender', 'receiver', 'order'])
                ->get()
                ->flatMap(function($message) {
                    $otherUserId = $message->sender_id == Auth::id() ? $message->receiver_id : $message->sender_id;
                    return [['user_id' => $otherUserId, 'order_id' => $message->order_id]];
                })
                ->unique('user_id')
                ->values();

            $userIds = $conversations->pluck('user_id');
            $users = User::whereIn('id', $userIds)->get();

            $latestMessages = collect();
            foreach($users as $user) {
                $latestMessage = Message::where(function($q) use($user) {
                        $q->where('sender_id', Auth::id())->where('receiver_id', $user->id);
                    })
                    ->orWhere(function($q) use($user) {
                        $q->where('sender_id', $user->id)->where('receiver_id', Auth::id());
                    })
                    ->where('order_id', $orderId) // Filter berdasarkan order
                    ->latest()
                    ->first();

                if($latestMessage) {
                    $latestMessages->push([
                        'user' => $user,
                        'message' => $latestMessage,
                        'order' => $latestMessage->order,
                        'unread_count' => Message::where('sender_id', $user->id)
                                                ->where('receiver_id', Auth::id())
                                                ->where('is_read', false)
                                                ->where('order_id', $orderId) // Filter juga di sini
                                                ->count()
                    ]);
                }
            }
        } else {
            // Tampilkan semua percakapan seperti sebelumnya
            $conversations = Message::where(function($query) {
                    $query->where('sender_id', Auth::id())
                          ->orWhere('receiver_id', Auth::id());
                })
                ->select('sender_id', 'receiver_id', 'order_id')
                ->with(['sender', 'receiver', 'order'])
                ->get()
                ->flatMap(function($message) {
                    $otherUserId = $message->sender_id == Auth::id() ? $message->receiver_id : $message->sender_id;
                    return [['user_id' => $otherUserId, 'order_id' => $message->order_id]];
                })
                ->unique('user_id')
                ->values();

            $userIds = $conversations->pluck('user_id');
            $users = User::whereIn('id', $userIds)->get();

            $latestMessages = collect();
            foreach($users as $user) {
                $latestMessage = Message::where(function($q) use($user) {
                        $q->where('sender_id', Auth::id())->where('receiver_id', $user->id);
                    })
                    ->orWhere(function($q) use($user) {
                        $q->where('sender_id', $user->id)->where('receiver_id', Auth::id());
                    })
                    ->latest()
                    ->first();

                if($latestMessage) {
                    $latestMessages->push([
                        'user' => $user,
                        'message' => $latestMessage,
                        'order' => $latestMessage->order,
                        'unread_count' => Message::where('sender_id', $user->id)
                                                ->where('receiver_id', Auth::id())
                                                ->where('is_read', false)
                                                ->count()
                    ]);
                }
            }
        }

        return view('messages.index', compact('latestMessages', 'orderId'));
    }

    public function chat($userId)
    {
        $otherUser = User::findOrFail($userId);

        // Ambil order_id dari query parameter jika ada
        $orderId = request()->query('order_id');

        // Tandai pesan dari user ini sebagai sudah dibaca
        $readQuery = Message::where('sender_id', $userId)
               ->where('receiver_id', Auth::id());

        // Jika ada order_id, filter juga berdasarkan order_id
        if ($orderId) {
            $readQuery->where('order_id', $orderId);
        }

        $readQuery->update(['is_read' => true]);

        $messageQuery = Message::where(function($query) use($userId) {
                $query->where('sender_id', Auth::id())->where('receiver_id', $userId);
            })
            ->orWhere(function($query) use($userId) {
                $query->where('sender_id', $userId)->where('receiver_id', Auth::id());
            });

        // Jika ada order_id, filter juga pesan berdasarkan order_id
        if ($orderId) {
            $messageQuery->where('order_id', $orderId);
        }

        $messages = $messageQuery->with(['sender', 'receiver', 'order'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('messages.chat', compact('otherUser', 'messages', 'orderId'));
    }

    public function send(Request $request, $userId)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = new Message();
        $message->sender_id = Auth::id();
        $message->receiver_id = $userId;
        $message->message = $request->message;

        // Jika ada order_id di request, tambahkan ke pesan
        if ($request->has('order_id')) {
            $message->order_id = $request->order_id;
        }

        $message->save();

        // Redirect kembali ke chat dengan order_id jika ada
        $redirectUrl = route('messages.chat', $userId);
        if ($request->has('order_id')) {
            $redirectUrl .= '?order_id=' . $request->order_id;
        }

        return redirect($redirectUrl)->with('success', 'Pesan berhasil dikirim');
    }

    public function getUnreadCount()
    {
        $unreadCount = Message::where('receiver_id', Auth::id())
                             ->where('is_read', false)
                             ->count();

        return response()->json(['unread_count' => $unreadCount]);
    }
}
