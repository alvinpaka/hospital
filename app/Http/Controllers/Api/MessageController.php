<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Notifications\MessageNotification;

class MessageController extends Controller
{
    /**
     * Send a message to another user (must be assigned doctor/nurse or patient)
     */
    public function send(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:2000',
        ]);

        $receiver = User::findOrFail($validated['receiver_id']);

        // Check assignment (doctor/nurse <-> patient)
        if (!$this->canMessage($user, $receiver)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $msg = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $receiver->id,
            'message' => $validated['message'],
        ]);

        // Send notification to receiver
        $receiver->notify(new MessageNotification($msg));
        // Optionally, notify sender as well (for their own notification list)
        // $user->notify(new MessageNotification($msg));

        return response()->json(['message' => 'Message sent', 'data' => $msg], 201);
    }

    /**
     * Long poll for new messages between current user and another user
     * Params: with_user_id, since (ISO8601 timestamp or message id)
     */
    public function poll(Request $request)
    {
        $user = Auth::user();
        $withUserId = $request->query('with_user_id');
        $since = $request->query('since');

        if (!$withUserId || !User::find($withUserId)) {
            return response()->json(['error' => 'Invalid user'], 422);
        }
        $withUser = User::find($withUserId);
        if (!$this->canMessage($user, $withUser)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = Message::where(function($q) use ($user, $withUserId) {
            $q->where('sender_id', $user->id)->where('receiver_id', $withUserId);
        })->orWhere(function($q) use ($user, $withUserId) {
            $q->where('sender_id', $withUserId)->where('receiver_id', $user->id);
        });

        if ($since) {
            if (is_numeric($since)) {
                $query->where('id', '>', $since);
            } else {
                $query->where('created_at', '>', Carbon::parse($since));
            }
        }

        $messages = $query->orderBy('id')->with(['sender', 'receiver'])->get();
        return response()->json(['messages' => $messages]);
    }

    /**
     * Mark all messages from a specific user as read for the authenticated user
     * Params: with_user_id
     */
    public function markAsRead(Request $request)
    {
        $user = Auth::user();
        $withUserId = $request->input('with_user_id');
        if (!$withUserId || !User::find($withUserId)) {
            return response()->json(['error' => 'Invalid user'], 422);
        }
        $withUser = User::find($withUserId);
        if (!$this->canMessage($user, $withUser)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        // Mark all messages received from withUser as read
        $updated = Message::where('sender_id', $withUserId)
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        return response()->json(['success' => true, 'updated' => $updated]);
    }

    /**
     * Check if two users are allowed to message each other (assigned doctor/nurse <-> patient)
     */
    protected function canMessage(User $a, User $b): bool
    {
        // Allow admin <-> patient chat
        if (
            ($a->role === 'admin' && $b->role === 'patient') ||
            ($a->role === 'patient' && $b->role === 'admin')
        ) {
            return true;
        }
        // Doctor/nurse can message their assigned patients
        if ($a->doctorPatients->contains($b->id)) return true;
        // Patient can message their assigned doctors/nurses
        if ($a->patientDoctors->contains($b->id)) return true;
        // Allow self-messaging (optional)
        if ($a->id === $b->id) return true;
        return false;
    }
}
