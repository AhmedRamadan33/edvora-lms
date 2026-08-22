<?php

namespace App\Http\Requests\Chat;

use App\Models\ChatConversation;
use Illuminate\Foundation\Http\FormRequest;

class SendChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $conversation = $this->route('conversation');

        return $user !== null
            && $conversation instanceof ChatConversation
            && $conversation->hasParticipant($user);
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:2000'],
        ];
    }
}
