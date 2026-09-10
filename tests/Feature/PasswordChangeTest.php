<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Notifications\Contracts\SmsProvider;
use App\Models\SmsLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_open_password_change_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get(route('user.password.edit'))
            ->assertOk()
            ->assertSee('تغییر رمز عبور', false)
            ->assertSee('تولید رمز جدید و ارسال پیامک', false);
    }

    public function test_user_can_rotate_password_and_new_one_is_smsed(): void
    {
        $user = User::factory()->create([
            'password'            => 'old-password',
            'plaintext_password' => 'old-password',
        ]);

        $oldHash = $user->password;
        $oldPlain = $user->plaintext_password;

        $response = $this->actingAs($user)
            ->put(route('user.password.update'), ['confirm' => '1']);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $fresh = $user->fresh();

        // Hash changed (new password generated).
        $this->assertNotSame($oldHash, $fresh->password);
        $this->assertNotSame($oldPlain, $fresh->plaintext_password);

        // Old password no longer works for authentication.
        $this->assertFalse(auth()->validate([
            'mobile'   => $fresh->mobile,
            'password' => $oldPlain,
        ]));

        // SMS log created with type=password_changed.
        $log = SmsLog::query()->where('user_id', $fresh->id)
            ->where('type', 'password_changed')
            ->latest('id')
            ->first();
        $this->assertNotNull($log);
        $this->assertSame('sent', $log->status);
        $this->assertSame($fresh->mobile, $log->mobile);
    }

    public function test_password_change_is_rate_limited(): void
    {
        $user = User::factory()->create([
            'password'            => 'original',
            'plaintext_password' => 'original',
        ]);

        // 3 changes should succeed.
        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($user)
                ->put(route('user.password.update'), ['confirm' => '1'])
                ->assertRedirect();
        }

        // 4th attempt should be rate-limited.
        $this->actingAs($user)
            ->put(route('user.password.update'), ['confirm' => '1'])
            ->assertSessionHasErrors('password');
    }

    public function test_confirmation_checkbox_is_required(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->put(route('user.password.update'), ['confirm' => '0'])
            ->assertSessionHasErrors('confirm');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('user.password.edit'))->assertRedirect('/login');
        $this->put(route('user.password.update'), ['confirm' => '1'])->assertRedirect('/login');
    }

    public function test_forgot_password_can_rotate_anonymously(): void
    {
        $user = User::factory()->create([
            'password'            => 'old-permanent',
            'plaintext_password' => 'old-permanent',
        ]);
        $oldHash = $user->password;

        $response = $this->post(route('password.phone'), [
            'mobile' => $user->mobile,
            'action' => 'rotate',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $fresh = $user->fresh();
        $this->assertNotSame($oldHash, $fresh->password);
        $this->assertNotSame('old-permanent', $fresh->plaintext_password);

        // SMS log created with type=password_changed (rotation).
        $this->assertDatabaseHas('sms_logs', [
            'user_id' => $fresh->id,
            'type'    => 'password_changed',
            'status'  => 'sent',
        ]);
    }

    public function test_forgot_password_remind_does_not_rotate(): void
    {
        $user = User::factory()->create([
            'password'            => 'stable-pw',
            'plaintext_password' => 'stable-pw',
        ]);
        $oldHash = $user->password;

        $response = $this->post(route('password.phone'), [
            'mobile' => $user->mobile,
            'action' => 'remind',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Hash unchanged in remind mode.
        $fresh = $user->fresh();
        $this->assertSame($oldHash, $fresh->password);
        $this->assertSame('stable-pw', $fresh->plaintext_password);

        // SMS log created with type=forgot_password (no rotation).
        $this->assertDatabaseHas('sms_logs', [
            'user_id' => $fresh->id,
            'type'    => 'forgot_password',
            'status'  => 'sent',
        ]);
    }
}
