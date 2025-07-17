<?php

namespace App\Http\Requests;

use App\Enums\BookingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BookingListingFormRequest extends FormRequest
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
            'service_id' => ['nullable', 'integer', Rule::exists("services", "id")],
            'status' => ['nullable', Rule::in(BookingStatus::values())],
            'date_from' => ['nullable', 'required_with:date_to', 'date_format:Y-m-d', ],
            'date_to' => ['nullable', 'after_or_equal:date_from', 'date_format:Y-m-d'],
        ];
    }

    public function messages(){
        return [
            'status' => "Status should be in: " . BookingStatus::commaSeparatedValues(),
            'date_from.date_format' => 'Format must be in YYYY-MM-DD',
            'date_to.date_format' => 'Format must be in YYYY-MM-DD',
            'date_to.after_or_equal' => 'The date_to field must be a date after or equal to date_from.',
        ];
    }
}
