<?php

namespace App\Http\Requests;

use App\Support\RichText;
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
     * Get the validation rules that apply to this request.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            // Rich HTML costs several characters per visible one (its tags plus
            // the entities the editor emits), so the old plain-text limit is tight.
            'body' => ['required', 'string', 'max:60000'],
        ];
    }

    /**
     * Get the validated data, trimming inputs on the way through and running
     * the body past the whitelist sanitiser so the database never stores
     * unsanitised HTML.
     *
     * @return array{title: string|null, body: string}
     */
    public function validated($key = null, $default = null): array
    {
        $title = trim((string) $this->input('title'));
        $body = trim(RichText::sanitize((string) $this->input('body')));

        return [
            'title' => $title === '' ? null : $title,
            'body' => $body,
        ];
    }
}
