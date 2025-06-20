<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "notify_tokens" => "boolean",
            "notify_refs" => "boolean",
            "notify_refs_buys" => "boolean",
            "notify_about_updates" => "boolean",
            "model" => "string",
            "audio_answer" => "string|in:no,all,text,audio",
            "audio_voice" => "string",
            "need_model_name" => "boolean",
            "instructions" => "string|max:3000",
            "midjourney_promt" => "string",
        ];
    }
}
