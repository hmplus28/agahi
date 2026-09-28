<?php

declare(strict_types=1);

namespace App\Domains\Notifications\Contracts;

interface SmsProvider
{

    public function send(string $mobile, string $message): array;


    public function sendPattern(string $mobile, string $patternCode, array $params): array;
}
