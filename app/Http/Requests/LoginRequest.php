<?php

declare(strict_types=1);
namespace App\Http\Requests;
use App\Support\PersianNormalizer;
use Illuminate\Foundation\Http\FormRequest;
class LoginRequest extends FormRequest { public function authorize(): bool { return true; } protected function prepareForValidation(): void { $this->merge(['mobile' => PersianNormalizer::mobile((string) $this->input('mobile'))]); } public function rules(): array { return ['mobile'=>['required','regex:/^09\d{9}$/'],'password'=>['required','string']]; } }
