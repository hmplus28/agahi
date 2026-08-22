<?php

declare(strict_types=1);
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class PurchaseTariffRequest extends FormRequest { public function authorize(): bool { return $this->user() !== null; } public function rules(): array { return ['tariff_id'=>['required','exists:tariffs,id'],'ad_id'=>['required','exists:ads,id']]; } }
