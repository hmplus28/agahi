<?php

declare(strict_types=1);
namespace App\Http\Requests;
use App\Support\PersianNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
class RegisterRequest extends FormRequest {
    public function authorize(): bool { return true; }
    protected function prepareForValidation(): void { $this->merge(['mobile' => PersianNormalizer::mobile((string) $this->input('mobile')), 'first_name' => PersianNormalizer::text($this->input('first_name')), 'last_name' => PersianNormalizer::text($this->input('last_name'))]); }
    public function rules(): array { return ['mobile' => ['required','regex:/^09\d{9}$/','unique:users,mobile'], 'first_name' => ['required','string','max:80'], 'last_name' => ['required','string','max:80'], 'email' => ['nullable','email','max:255','unique:users,email'], 'password' => ['required','confirmed',Password::min(10)->mixedCase()->numbers()]]; }
    public function messages(): array { return ['mobile.unique'=>'این شمارهٔ موبایل قبلاً ثبت شده است.']; }
}
