<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation rules shared by event create/update actions.
 */
class EventRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'starts_at_date' => ['required', 'date_format:Y-m-d'],
            'starts_at_time' => ['required', 'date_format:H:i'],
            'description' => ['nullable', 'string', 'max:5000'],
            'address' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Merge the date and time inputs into a single datetime attribute.
     *
     * @return array<string, mixed>
     */
    public function validatedPayload(): array
    {
        return [
            'title' => trim((string) $this->input('title')),
            'starts_at' => $this->input('starts_at_date').' '.$this->input('starts_at_time'),
            'description' => $this->filled('description') ? trim((string) $this->input('description')) : null,
            'address' => $this->filled('address') ? trim((string) $this->input('address')) : null,
        ];
    }
}
