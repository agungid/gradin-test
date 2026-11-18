<?php

namespace App\Http\Requests;

use App\Models\Courier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourierRequest extends FormRequest
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
        $courierId = $this->route('courier')?->id;

        return [
            'code' => ['sometimes', 'required', 'string', 'max:32', 'alpha_dash', Rule::unique('couriers', 'code')->ignore($courierId)],
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email:rfc', 'max:255', Rule::unique('couriers', 'email')->ignore($courierId)],
            'phone' => ['sometimes', 'required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{8,20}$/', Rule::unique('couriers', 'phone')->ignore($courierId)],
            'level' => ['sometimes', 'required', 'integer', 'between:'.Courier::MIN_LEVEL.','.Courier::MAX_LEVEL],
            'vehicle_type' => ['sometimes', 'required', 'string', Rule::in(Courier::VEHICLE_TYPES)],
            'vehicle_plate' => ['nullable', 'string', 'max:20'],
            'service_area' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'required', 'string', Rule::in(Courier::STATUSES)],
            'registered_at' => ['nullable', 'date'],
        ];
    }
}
