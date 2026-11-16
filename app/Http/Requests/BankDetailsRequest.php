<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BankDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isFarmer() ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'farm_name' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:600'],
            'bank_name' => ['required', 'string', 'max:120'],
            'branch' => ['required', 'string', 'max:120'],
            'account_no' => ['required', 'string', 'max:60'],
        ];
    }
}
