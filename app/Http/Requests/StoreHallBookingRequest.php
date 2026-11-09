<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHallBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'start_time' => ['required', 'date', 'after:now'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'guests' => ['required', 'integer', 'min:1', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'rules_ack' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_time.after' => 'Please choose a start time in the future.',
            'rules_ack.accepted' => 'Please confirm you accept the club rules and cancellation policy.',
        ];
    }
}