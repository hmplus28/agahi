<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\PersianNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'mobile'     => PersianNormalizer::mobile((string) $this->input('mobile')),
            'first_name' => PersianNormalizer::text($this->input('first_name')),
            'last_name'  => PersianNormalizer::text($this->input('last_name')),
        ]);
    }

    public function rules(): array
    {
        // No password is required from the user — the system generates one
        // and SMSs it to them on registration. The mobile number is the user
        // identifier; the password is delivered via SMS.
        return [
            'mobile'     => ['required', 'regex:/^09\d{9}$/', 'unique:users,mobile'],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name'  => ['required', 'string', 'max:80'],
            'email'      => ['nullable', 'email', 'max:255', 'unique:users,email'],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.unique' => 'این شمارهٔ موبایل قبلاً ثبت شده است. در صورت فراموشی رمز، از صفحهٔ بازیابی استفاده کنید.',
        ];
    }
}
