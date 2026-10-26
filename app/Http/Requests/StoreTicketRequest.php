<?php

declare(strict_types=1);
namespace App\Http\Requests;
use App\Support\PersianNormalizer;
use Illuminate\Foundation\Http\FormRequest;
class StoreTicketRequest extends FormRequest { public function authorize(): bool { return $this->user() !== null; } protected function prepareForValidation(): void { $this->merge(['subject'=>PersianNormalizer::text($this->input('subject')), 'message'=>PersianNormalizer::text($this->input('message'))]); } public function rules(): array { return ['subject'=>['required','string','max:190'],'message'=>['required','string','max:5000'],'priority'=>['nullable','in:low,normal,high']]; } }
