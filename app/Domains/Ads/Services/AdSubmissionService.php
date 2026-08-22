<?php

declare(strict_types=1);
namespace App\Domains\Ads\Services;
use App\Domains\Ads\Enums\AdStatus;
use App\Models\Ad;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
final class AdSubmissionService {
    public function __construct(private readonly DuplicateDetector $duplicates, private readonly ForbiddenWordGuard $forbiddenWords, private readonly AdWorkflow $workflow, private readonly AdImageProcessor $images) {}
    /** @param array<string,mixed> $data @param array<UploadedFile> $uploads */
    public function create(User $user, array $data, array $uploads, string $ip): Ad { $this->guardContent($data); $fingerprints=$this->duplicates->fingerprints($data['title'],$data['description']); if($this->duplicates->exists($fingerprints)) throw ValidationException::withMessages(['title'=>'آگهی مشابهی با همین عنوان و توضیحات قبلاً ثبت شده است.']); return DB::transaction(function() use($user,$data,$uploads,$ip,$fingerprints): Ad { $ad=Ad::query()->create([...$data,'user_id'=>$user->id,'code'=>strtoupper(Str::random(10)),'slug'=>Str::slug($data['title'],'-','fa') ?: Str::random(8),'normalized_title'=>$fingerprints['title'],'normalized_description'=>$fingerprints['description'],'normalized_title_hash'=>$fingerprints['title_hash'],'normalized_description_hash'=>$fingerprints['description_hash'],'status'=>AdStatus::Draft,'source'=>'user_panel','submit_ip'=>$ip,'sort_at'=>now()]); foreach($uploads as $index=>$upload) $this->images->store($ad,$upload,$index); return $this->workflow->transition($ad,AdStatus::PendingApproval,$user,'ثبت توسط کاربر'); }); }
    /** @param array<string,mixed> $data @param array<UploadedFile> $uploads */
    public function update(Ad $ad, User $user, array $data, array $uploads): Ad { if($ad->user_id!==$user->id) abort(403); $this->guardContent($data); $fingerprints=$this->duplicates->fingerprints($data['title'],$data['description']); if($this->duplicates->exists($fingerprints,$ad->id)) throw ValidationException::withMessages(['title'=>'آگهی مشابهی با همین عنوان و توضیحات قبلاً ثبت شده است.']); if(($ad->images()->count()+count($uploads))>(int)config('agahi.max_images',5)) throw ValidationException::withMessages(['images'=>'حداکثر تعداد تصویر مجاز رعایت نشده است.']); return DB::transaction(function() use($ad,$user,$data,$uploads,$fingerprints): Ad { $locked=Ad::query()->lockForUpdate()->findOrFail($ad->id); $locked->fill([...$data,'slug'=>Str::slug($data['title'],'-','fa') ?: $locked->slug,'normalized_title'=>$fingerprints['title'],'normalized_description'=>$fingerprints['description'],'normalized_title_hash'=>$fingerprints['title_hash'],'normalized_description_hash'=>$fingerprints['description_hash']])->save(); foreach($uploads as $index=>$upload) $this->images->store($locked,$upload,$locked->images()->count()+$index); if($locked->status===AdStatus::Active) return $this->workflow->transition($locked,AdStatus::PendingApproval,$user,'ویرایش توسط کاربر'); return $locked->refresh(); }); }
    /** @param array<string,mixed> $data */
    private function guardContent(array $data): void { $this->forbiddenWords->assertAllowed([$data['title']??'', $data['description']??'', $data['business_name']??'', implode(' ',$data['keywords']??[])]); }
}
