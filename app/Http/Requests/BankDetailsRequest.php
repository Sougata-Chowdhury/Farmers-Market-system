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
            'bank_name' => ['required', 'string', 'max:120'],
            'branch' => ['required', 'string', 'max:120'],
            'account_no' => ['required', 'string', 'max:60'],
        ];
    }
}
