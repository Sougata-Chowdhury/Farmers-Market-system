<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCustomer() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'payment_method' => $this->input('payment_method', PaymentMethod::Cod->value),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $usingSavedAddress = filled($this->input('address_id'));

        return [
            'address_id' => ['nullable', 'integer'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'name' => [$usingSavedAddress ? 'nullable' : 'required', 'string', 'max:120'],
            'phone' => [$usingSavedAddress ? 'nullable' : 'required', 'string', 'regex:/^01[3-9]\d{8}$/'],
            'address' => [$usingSavedAddress ? 'nullable' : 'required', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:500'],
            'save_address' => ['nullable', 'boolean'],
            'label' => ['nullable', 'string', 'max:40'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid 11-digit Bangladeshi mobile number (e.g. 01712345678).',
        ];
    }
}
