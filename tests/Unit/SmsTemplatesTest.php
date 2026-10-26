<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\SiteSetting;
use App\Support\SmsTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmsTemplatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_starts_with_an_empty_list(): void
    {
        $this->assertSame([], SmsTemplates::all());
        $this->assertNull(SmsTemplates::find('missing'));
    }

    public function test_it_saves_and_finds_a_template(): void
    {
        $id = SmsTemplates::save('هشدار تخلف', 'آگهی شما در حال بررسی است.');

        $this->assertCount(1, SmsTemplates::all());
        $template = SmsTemplates::find($id);
        $this->assertSame('هشدار تخلف', $template['label']);
        $this->assertSame('آگهی شما در حال بررسی است.', $template['text']);
    }

    public function test_it_updates_in_place_when_the_same_id_is_given(): void
    {
        $id = SmsTemplates::save('قدیمی', 'متن قدیمی');
        SmsTemplates::save('جدید', 'متن جدید', $id);

        $this->assertCount(1, SmsTemplates::all());
        $this->assertSame('متن جدید', SmsTemplates::find($id)['text']);
    }

    public function test_it_deletes_only_the_target_template(): void
    {
        $first = SmsTemplates::save('اول', 'یک');
        $second = SmsTemplates::save('دوم', 'دو');

        SmsTemplates::delete($first);

        $this->assertNull(SmsTemplates::find($first));
        $remaining = SmsTemplates::find($second);
        $this->assertSame('دوم', $remaining['label']);
        $this->assertSame('دو', $remaining['text']);
    }

    public function test_templates_are_persisted_in_site_settings(): void
    {
        SmsTemplates::save('قالب', 'متن');

        $stored = SiteSetting::get('sms.templates');
        $this->assertIsArray($stored);
        $this->assertSame('قالب', $stored[0]['label']);
    }
}
