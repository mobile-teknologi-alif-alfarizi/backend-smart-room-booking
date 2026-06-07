<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Events\MessageSent;
use App\Events\MessageSeen;
use App\Events\MessageDeleted;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{
    /**
     * Get all messages between two users
     */
    public function getConversation($userId)
    {
        try {
            $authUser = auth('api')->user();

            $messages = Message::with(['sender', 'receiver', 'creator'])
                ->where(function ($query) use ($authUser, $userId) {
                    $query->where('sent_id', $authUser->id)->where('receive_id', $userId)
                        ->orWhere('sent_id', $userId)->where('receive_id', $authUser->id);
                })
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data percakapan berhasil diambil',
                'data' => $messages,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data percakapan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all conversations for authenticated user
     */
    public function listConversations()
    {
        try {
            $authUser = auth('api')->user();

            // Get unique conversations
            $conversations = Message::where(function ($query) use ($authUser) {
                    $query->where('sent_id', $authUser->id)
                        ->orWhere('receive_id', $authUser->id);
                })
                ->with(['sender', 'receiver', 'creator'])
                ->latest('created_at')
                ->get()
                ->groupBy(function ($message) use ($authUser) {
                    return $message->sent_id == $authUser->id 
                        ? $message->receive_id 
                        : $message->sent_id;
                })
                ->map(function ($messages) use ($authUser) {
                    $lastMessage = $messages->first();
                    $otherUserId = $lastMessage->sent_id == $authUser->id 
                        ? $lastMessage->receive_id 
                        : $lastMessage->sent_id;
                    
                    $unreadCount = $messages->where('receive_id', $authUser->id)
                        ->where('status_seen', false)
                        ->count();

                    return [
                        'user_id' => $otherUserId,
                        'user' => $lastMessage->sent_id == $authUser->id 
                            ? $lastMessage->receiver 
                            : $lastMessage->sender,
                        'last_message' => $lastMessage->message,
                        'last_message_at' => $lastMessage->created_at,
                        'unread_count' => $unreadCount,
                        'last_sender_id' => $lastMessage->sent_id,
                    ];
                })
                ->sortByDesc('last_message_at')
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Data percakapan berhasil diambil',
                'data' => $conversations,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data percakapan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send a new message
     */
    public function sendMessage(Request $request)
    {
        try {
            $validated = $request->validate([
                'receive_id' => 'required|exists:users,id|different:user_id',
                'message' => 'required|string|min:1|max:5000',
            ]);

            $authUser = auth('api')->user();
            $validated['sent_id'] = $authUser->id;
            $validated['created_by'] = $authUser->id;
            $validated['status_seen'] = false;

            $message = Message::create($validated);
            $message->load(['sender', 'receiver', 'creator']);

            // Broadcast event untuk real-time chat
            broadcast(new MessageSent($message))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Pesan berhasil dikirim',
                'data' => $message,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim pesan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark message as seen
     */
    public function markAsSeen($messageId)
    {
        try {
            $authUser = auth('api')->user();
            
            $message = Message::findOrFail($messageId);

            // Verify that authenticated user is the receiver
            if ($message->receive_id != $authUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menandai pesan ini',
                ], 403);
            }

            $message->update([
                'status_seen' => true,
                'seen_at' => now(),
            ]);

            // Broadcast event untuk real-time update status
            broadcast(new MessageSeen($message))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Pesan berhasil ditandai sebagai sudah dilihat',
                'data' => $message,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai pesan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark all messages in conversation as seen
     */
    public function markConversationAsSeen($userId)
    {
        try {
            $authUser = auth('api')->user();

            Message::where('sent_id', $userId)
                ->where('receive_id', $authUser->id)
                ->where('status_seen', false)
                ->update([
                    'status_seen' => true,
                    'seen_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Semua pesan dalam percakapan berhasil ditandai sebagai sudah dilihat',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai percakapan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get messages that need follow-up (unread and older than 10 minutes)
     */
    public function getFollowUpMessages()
    {
        try {
            $authUser = auth('api')->user();

            // Get unread messages older than 10 minutes for authenticated user
            $messages = Message::where('receive_id', $authUser->id)
                ->where('status_seen', false)
                ->where('created_at', '<=', now()->subMinutes(10))
                ->with(['sender', 'receiver', 'creator'])
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data pesan yang memerlukan follow-up berhasil diambil',
                'data' => $messages,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data follow-up: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get unread message count
     */
    public function unreadCount()
    {
        try {
            $authUser = auth('api')->user();

            $unreadCount = Message::where('receive_id', $authUser->id)
                ->where('status_seen', false)
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'unread_count' => $unreadCount,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil jumlah pesan belum dibaca: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a message
     */
    public function deleteMessage($messageId)
    {
        try {
            $authUser = auth('api')->user();
            
            $message = Message::findOrFail($messageId);

            // Verify that authenticated user is the sender
            if ($message->sent_id != $authUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda hanya bisa menghapus pesan yang Anda kirim',
                ], 403);
            }

            $messageId = $message->uuid;
            $senderId = $message->sent_id;
            $receiverId = $message->receive_id;
            $message->delete();

            // Broadcast event untuk real-time delete
            broadcast(new MessageDeleted($messageId, $senderId, $receiverId))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Pesan berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pesan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
