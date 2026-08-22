<?php

declare(strict_types=1);
namespace App\Domains\Notifications\Contracts;
interface SmsProvider { /** @return array{provider_id:?string,response:array<string,mixed>} */ public function send(string $mobile,string $message): array; }
