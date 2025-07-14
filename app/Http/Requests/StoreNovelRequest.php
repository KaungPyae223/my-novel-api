<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNovelRequest extends FormRequest
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
            'title' => 'required|string',
            'description' => 'required|string',
            'synopsis' => 'required|string',
            'tags' => 'required|string',
            'visibility' => 'required|in:'.implode(',', config('base.visibility')),
            'genre_id' => 'required|exists:genres,id',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ];
    }
}
