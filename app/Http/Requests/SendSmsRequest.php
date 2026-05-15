<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendSmsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'recipient' => ['required', 'string', 'regex:/^\+?[1-9]\d{6,14}$/'],
            'message' => ['required', 'string', 'max:918'],
        ];
    }
}
