<?php

declare(strict_types=1);
namespace App\Policies;
use App\Domains\Ads\Enums\AdStatus;
use App\Models\Ad;
use App\Models\User;
class AdPolicy { public function view(User $user, Ad $ad): bool { return $user->id === $ad->user_id || $user->isModerator(); } public function update(User $user, Ad $ad): bool { return ($user->id === $ad->user_id && !in_array($ad->status, [AdStatus::Expired, AdStatus::Deleted], true)) || $user->isModerator(); } public function moderate(User $user): bool { return $user->isModerator(); } }
