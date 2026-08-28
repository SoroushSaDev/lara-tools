<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation rules shared by note create/update actions.
 */
class NoteRequest extends FormRequest
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
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:20000'],
        ];
    }

    /**
     * Get the validated data, trimming inputs on the way through.
     *
     * @return array{title: string|null, body: string}
     */
    public function validated($key = null, $default = null): array
    {
        $title = trim((string) $this->input('title'));

        return [
            'title' => $title === '' ? null : $title,
            'body' => trim((string) $this->input('body')),
        ];
    }
}
