<?php

namespace App\Api\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BroadcastNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|string|in:info,success,warning,error,system,promo,news,order',
            'recipients' => 'required|string|in:all,selected',
            'user_ids' => 'required_if:recipients,selected|array',
            'user_ids.*' => 'integer|exists:users,id',
            'action_url' => 'nullable|string|max:255',
        ];
    }
}
