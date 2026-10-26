<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\PersianNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class StoreAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $keywords = array_values(array_filter(array_map(
            static fn (mixed $keyword): string => PersianNormalizer::text((string) $keyword),
            (array) $this->input('keywords', []),
        )));

        $this->merge([
            'title' => PersianNormalizer::text($this->input('title')),
            'description' => PersianNormalizer::text($this->input('description')),
            'full_name' => PersianNormalizer::text($this->input('full_name')),
            'business_name' => PersianNormalizer::text($this->input('business_name')),
            'mobile_1' => $this->filled('mobile_1') ? PersianNormalizer::mobile((string) $this->input('mobile_1')) : null,
            'mobile_2' => $this->filled('mobile_2') ? PersianNormalizer::mobile((string) $this->input('mobile_2')) : null,
            'keywords' => $keywords,
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:300'],
            'description' => ['required', 'string', 'max:6000'],
            'keywords' => ['nullable', 'array', 'max:11'],
            'keywords.*' => ['string', 'max:80', 'distinct'],
            'price' => ['nullable', 'integer', 'min:0'],
            'full_name' => ['nullable', 'string', 'max:160'],
            'business_name' => ['nullable', 'string', 'max:160'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'address' => ['nullable', 'string', 'max:500'],
            'mobile_1' => ['required', 'regex:/^09\d{9}$/'],
            'mobile_2' => ['nullable', 'regex:/^09\d{9}$/'],
            'phone_1' => ['nullable', 'string', 'max:30'],
            'phone_2' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['file', 'image', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'city_id.required' => 'انتخاب شهر الزامی است.',
            'category_id.required' => 'انتخاب دسته‌بندی الزامی است.',
            'images.max' => 'حداکثر پنج تصویر قابل بارگذاری است.',
        ];
    }
}
