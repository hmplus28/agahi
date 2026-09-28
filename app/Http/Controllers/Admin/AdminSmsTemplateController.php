<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SmsTemplates;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminSmsTemplateController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:160'],
            'text'  => ['required', 'string', 'max:1000'],
        ]);

        SmsTemplates::save($data['label'], $data['text']);

        return back()->with('success', 'الگوی پیامک ذخیره شد.');
    }
}
