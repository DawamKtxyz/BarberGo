<?php
// File: app/Http/Controllers/Api/ChatController.php - Update untuk chat sebelum booking

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\TukangCukur;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    /**
     * Get chats for authenticated user
     */
    public function getChats(Request $request)
    {
        try {
            $user = $request->user();
            $userType = $this->getUserType($user);

            $chats = Chat::with(['barber', 'pelanggan'])
                ->where($userType . '_id', $user->id)
                ->orderBy('last_message_at', 'desc')
                ->get();

            $formattedChats = $chats->map(function ($chat) use ($userType) {
                $otherUser = $userType === 'barber' ? $chat->pelanggan : $chat->barber;
                $unreadCount = $userType === 'barber' ? $chat->barber_unread_count : $chat->pelanggan_unread_count;

                return [
                    'id' => $chat->id,
                    'barber_id' => $chat->barber_id,
                    'pelanggan_id' => $chat->pelanggan_id,
                    'other_user' => [
                        'id' => $otherUser->id,
                        'nama' => $otherUser->nama,
                        'profile_photo' => $otherUser->profile_photo,
                    ],
                    'last_message' => $chat->last_message,
                    'last_message_at' => $chat->last_message_at,
                    'unread_count' => $unreadCount,
                    'chat_type' => 'direct', // Langsung antara barber dan pelanggan
                ];
            });

            return response()->json([
                'success' => true,
                'chats' => $formattedChats,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting chats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting chats: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get or create direct chat between barber and pelanggan
     */
    public function getOrCreateDirectChat(Request $request)
{
    try {
        $user = $request->user();
        $userType = $this->getUserType($user);

        // Validasi input berdasarkan userType
        $rules = $userType === 'barber'
            ? ['pelanggan_id' => 'required|exists:pelanggan,id']
            : ['barber_id' => 'required|exists:tukang_cukur,id'];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Tetapkan ID berdasarkan userType
        if ($userType === 'barber') {
            $barberId = $user->id;
            $pelangganId = $request->pelanggan_id;
        } else {
            $barberId = $request->barber_id;
            $pelangganId = $user->id;
        }

        // Ambil atau buat chat langsung
        $chat = Chat::firstOrCreate(
            [
                'barber_id' => $barberId,
                'pelanggan_id' => $pelangganId,
                'booking_id' => null, // NULL untuk direct chat
            ],
            [
                'barber_unread_count' => 0,
                'pelanggan_unread_count' => 0,
            ]
        );

        // Ambil pesan
        $messages = $chat->messages()->orderBy('created_at', 'asc')->get();

        $formattedMessages = $messages->map(function ($message) {
            return [
                'id' => $message->id,
                'message' => $message->message,
                'message_type' => $message->message_type,
                'file_path' => $message->file_path,
                'sender_type' => $message->sender_type,
                'sender_id' => $message->sender_id,
                'is_read' => $message->is_read,
                'created_at' => $message->created_at,
            ];
        });

        // Ambil info lawan bicara
        $barber = TukangCukur::find($barberId);

        // Tandai pesan sebagai dibaca
        $this->markMessagesAsRead($chat, $userType, $user->id);

        return response()->json([
            'success' => true,
            'chat' => [
                'id' => $chat->id,
                'barber_id' => $chat->barber_id,
                'pelanggan_id' => $chat->pelanggan_id,
                'barber_info' => [
                    'id' => $barber->id,
                    'nama' => $barber->nama,
                    'spesialisasi' => $barber->spesialisasi,
                    'profile_photo' => $barber->profile_photo,
                ],
                'messages' => $formattedMessages,
            ],
        ]);
    } catch (\Exception $e) {
        Log::error('Error getting/creating direct chat: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error getting chat: ' . $e->getMessage(),
        ], 500);
    }
}


    /**
     * Get chat by chat ID
     */
    public function getChatById(Request $request, $chatId)
    {
        try {
            $user = $request->user();
            $userType = $this->getUserType($user);

            // Verify user has access to this chat
            $chat = Chat::where('id', $chatId)
                ->where($userType . '_id', $user->id)
                ->with(['barber', 'pelanggan'])
                ->first();

            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat not found or access denied',
                ], 404);
            }

            // Get messages
            $messages = $chat->messages()->orderBy('created_at', 'asc')->get();

            $formattedMessages = $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'message_type' => $message->message_type,
                    'file_path' => $message->file_path,
                    'sender_type' => $message->sender_type,
                    'sender_id' => $message->sender_id,
                    'is_read' => $message->is_read,
                    'created_at' => $message->created_at,
                ];
            });

            // Mark messages as read
            $this->markMessagesAsRead($chat, $userType, $user->id);

            return response()->json([
                'success' => true,
                'chat' => [
                    'id' => $chat->id,
                    'barber_id' => $chat->barber_id,
                    'pelanggan_id' => $chat->pelanggan_id,
                    'other_user' => $userType === 'barber' ? [
                        'id' => $chat->pelanggan->id,
                        'nama' => $chat->pelanggan->nama,
                        'profile_photo' => $chat->pelanggan->profile_photo,
                    ] : [
                        'id' => $chat->barber->id,
                        'nama' => $chat->barber->nama,
                        'profile_photo' => $chat->barber->profile_photo,
                        'spesialisasi' => $chat->barber->spesialisasi,
                    ],
                    'messages' => $formattedMessages,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting chat by ID: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting chat: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send message
     */
    public function sendMessage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'chat_id' => 'required|exists:chats,id',
                'message' => 'required|string|max:1000',
                'message_type' => 'in:text,image,file',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = $request->user();
            $userType = $this->getUserType($user);

            // Verify user has access to this chat
            $chat = Chat::where('id', $request->chat_id)
                ->where($userType . '_id', $user->id)
                ->first();

            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat not found or access denied',
                ], 404);
            }

            // Create message
            $message = Message::create([
                'chat_id' => $chat->id,
                'sender_type' => $userType,
                'sender_id' => $user->id,
                'message' => $request->message,
                'message_type' => $request->message_type ?? 'text',
                'is_read' => false,
            ]);

            event(new \App\Events\MessageSent($message));

            // Update chat
            $otherUserUnreadField = $userType === 'barber' ? 'pelanggan_unread_count' : 'barber_unread_count';
            $chat->update([
                'last_message' => $request->message,
                'last_message_at' => now(),
                $otherUserUnreadField => $chat->$otherUserUnreadField + 1,
            ]);

            $formattedMessage = [
                'id' => $message->id,
                'chat_id' => $chat->id,
                'message' => $message->message,
                'message_type' => $message->message_type,
                'sender_type' => $message->sender_type,
                'sender_id' => $user->id,
                'is_read' => $message->is_read,
                'created_at' => $message->created_at,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $formattedMessage,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending message: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark messages as read
     */
    private function markMessagesAsRead($chat, $userType, $userId)
    {
        // Mark unread messages as read
        Message::where('chat_id', $chat->id)
            ->where('sender_type', '!=', $userType)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        // Reset unread count
        $unreadField = $userType . '_unread_count';
        $chat->update([$unreadField => 0]);
    }

    /**
     * Get user type from authenticated user
     */
    private function getUserType($user)
    {
        return $user instanceof \App\Models\TukangCukur ? 'barber' : 'pelanggan';
    }
}
