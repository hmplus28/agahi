<?php

declare(strict_types=1);
namespace App\Http\Requests;
use App\Support\PersianNormalizer;
use Illuminate\Foundation\Http\FormRequest;
class UpdateProfileRequest extends FormRequest { public function authorize(): bool { return $this->user() !== null; } protected function prepareForValidation(): void { $this->merge(['first_name'=>PersianNormalizer::text($this->input('first_name')),'last_name'=>PersianNormalizer::text($this->input('last_name')),'business_name'=>PersianNormalizer::text($this->input('business_name')),'address'=>PersianNormalizer::text($this->input('address'))]); } public function rules(): array { return ['first_name'=>['required','string','max:80'],'last_name'=>['required','string','max:80'],'email'=>['nullable','email','max:255','unique:users,email,'.$this->user()->id],'business_name'=>['nullable','string','max:160'],'address'=>['nullable','string','max:500'],'province_id'=>['nullable','exists:provinces,id'],'city_id'=>['nullable','exists:cities,id'],'postal_code'=>['nullable','string','max:20']]; } }
