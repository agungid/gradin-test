<?php

namespace App\Http\Requests;

use App\Models\Courier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:32', 'alpha_dash', Rule::unique('couriers', 'code')],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique('couriers', 'email')],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{8,20}$/', Rule::unique('couriers', 'phone')],
            'level' => ['required', 'integer', 'between:'.Courier::MIN_LEVEL.','.Courier::MAX_LEVEL],
            'vehicle_type' => ['required', 'string', Rule::in(Courier::VEHICLE_TYPES)],
            'vehicle_plate' => ['nullable', 'string', 'max:20'],
            'service_area' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'required', 'string', Rule::in(Courier::STATUSES)],
            'registered_at' => ['nullable', 'date'],
        ];
    }
}
