<?php

declare(strict_types=1);

$root = dirname(__DIR__);

$files = [
'app/Models/AdService.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AdService extends Model { protected $fillable=['ad_id','tariff_id','starts_at','expires_at','status','metadata']; protected function casts(): array { return ['starts_at'=>'datetime','expires_at'=>'datetime','metadata'=>'array']; } public function ad(): BelongsTo { return $this->belongsTo(Ad::class); } public function tariff(): BelongsTo { return $this->belongsTo(Tariff::class); } }
PHP,
'app/Http/Requests/PurchaseTariffRequest.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class PurchaseTariffRequest extends FormRequest { public function authorize(): bool { return $this->user() !== null; } public function rules(): array { return ['tariff_id'=>['required','exists:tariffs,id'],'ad_id'=>['required','exists:ads,id']]; } }
PHP,
'app/Http/Requests/UpdateProfileRequest.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Requests;
use App\Support\PersianNormalizer;
use Illuminate\Foundation\Http\FormRequest;
class UpdateProfileRequest extends FormRequest { public function authorize(): bool { return $this->user() !== null; } protected function prepareForValidation(): void { $this->merge(['first_name'=>PersianNormalizer::text($this->input('first_name')),'last_name'=>PersianNormalizer::text($this->input('last_name')),'business_name'=>PersianNormalizer::text($this->input('business_name')),'address'=>PersianNormalizer::text($this->input('address'))]); } public function rules(): array { return ['first_name'=>['required','string','max:80'],'last_name'=>['required','string','max:80'],'email'=>['nullable','email','max:255','unique:users,email,'.$this->user()->id],'business_name'=>['nullable','string','max:160'],'address'=>['nullable','string','max:500'],'province_id'=>['nullable','exists:provinces,id'],'city_id'=>['nullable','exists:cities,id'],'postal_code'=>['nullable','string','max:20']]; } }
PHP,
'app/Domains/Ads/Services/AdSubmissionService.php' => <<<'PHP'
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
PHP,
'app/Domains/Billing/PaymentService.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Domains\Billing;
use App\Domains\Ads\Enums\AdStatus;
use App\Domains\Ads\Services\AdWorkflow;
use App\Domains\Billing\Contracts\PaymentGateway;
use App\Models\Ad;
use App\Models\AdService;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LogicException;
final class PaymentService {
    public function __construct(private readonly PaymentGateway $gateway, private readonly AdWorkflow $workflow) {}
    public function createInvoice(User $user, Ad $ad, Tariff $tariff): Invoice { if($ad->user_id!==$user->id) abort(403); if(!$tariff->is_active) throw new LogicException('تعرفهٔ انتخابی فعال نیست.'); return DB::transaction(function() use($user,$ad,$tariff): Invoice { $invoice=Invoice::query()->create(['invoice_number'=>'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),'user_id'=>$user->id,'ad_id'=>$ad->id,'status'=>'pending','subtotal'=>$tariff->price,'discount'=>0,'total'=>$tariff->price]); InvoiceItem::query()->create(['invoice_id'=>$invoice->id,'title'=>$tariff->title,'quantity'=>1,'unit_price'=>$tariff->price,'total_price'=>$tariff->price,'metadata'=>['tariff_id'=>$tariff->id,'service_type'=>$tariff->service_type]]); return $invoice->load('items'); }); }
    /** @return array{payment:Payment,redirect_url:string} */
    public function begin(Invoice $invoice, User $user): array { return DB::transaction(function() use($invoice,$user): array { $locked=Invoice::query()->lockForUpdate()->findOrFail($invoice->id); if($locked->user_id!==$user->id || $locked->status==='paid') throw new LogicException('فاکتور قابل پرداخت نیست.'); if($locked->total===0){ $payment=Payment::query()->create(['user_id'=>$user->id,'ad_id'=>$locked->ad_id,'invoice_id'=>$locked->id,'amount'=>0,'method'=>'free','status'=>'pending','gateway'=>'free','authority'=>'FREE-'.Str::upper(Str::random(20))]); return ['payment'=>$this->settle($payment, 'FREE-'.Str::upper(Str::random(10))),'redirect_url'=>route('user.payments.index')]; } $payment=Payment::query()->create(['user_id'=>$user->id,'ad_id'=>$locked->ad_id,'invoice_id'=>$locked->id,'amount'=>$locked->total,'method'=>'online','status'=>'pending','gateway'=>'fake']); $intent=$this->gateway->create($payment); $payment->update(['authority'=>$intent['authority']]); return ['payment'=>$payment->refresh(),'redirect_url'=>route('user.payments.callback',['authority'=>$intent['authority']])]; }); }
    public function verify(string $authority, User $user): Payment { return DB::transaction(function() use($authority,$user): Payment { $payment=Payment::query()->where('authority',$authority)->lockForUpdate()->firstOrFail(); if($payment->user_id!==$user->id) abort(403); if($payment->status==='successful') return $payment; $result=$this->gateway->verify($payment); if(!$result['successful']){ $payment->update(['status'=>'failed']); return $payment->refresh(); } return $this->settle($payment,$result['reference_id']??null); }); }
    private function settle(Payment $payment, ?string $reference): Payment { if($payment->status==='successful') return $payment; $payment->update(['status'=>'successful','reference_id'=>$reference,'paid_at'=>now(),'verified_at'=>now()]); $invoice=$payment->invoice()->lockForUpdate()->firstOrFail(); $invoice->update(['status'=>'paid','paid_at'=>now()]); $ad=Ad::query()->lockForUpdate()->findOrFail($invoice->ad_id); foreach($invoice->items as $item){ $tariffId=$item->metadata['tariff_id']??null; $tariff=$tariffId ? Tariff::query()->find($tariffId) : null; if(!$tariff) continue; $start=now(); $expires=$tariff->duration_days ? now()->addDays($tariff->duration_days) : null; AdService::query()->create(['ad_id'=>$ad->id,'tariff_id'=>$tariff->id,'starts_at'=>$start,'expires_at'=>$expires,'status'=>'active','metadata'=>['invoice_id'=>$invoice->id]]); match($tariff->service_type){ 'renewal','ad' => $this->activateOrExtend($ad,$expires), 'featured' => $ad->update(['is_featured'=>true]), 'urgent' => $ad->update(['is_urgent'=>true]), 'colored' => $ad->update(['is_colored'=>true]), 'ladder' => $ad->update(['last_ladder_at'=>now(),'sort_at'=>now()]), default => null }; } return $payment->refresh(); }
    private function activateOrExtend(Ad $ad, ?\DateTimeInterface $expires): void { if($expires) $ad->update(['expires_at'=>$expires]); if($ad->status===AdStatus::Expired) $this->workflow->transition($ad,AdStatus::Active,null,'تمدید پس از پرداخت'); }
}
PHP,
'app/Http/Controllers/User/AdController.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Controllers\User;
use App\Domains\Ads\Enums\AdStatus;
use App\Domains\Ads\Services\AdSubmissionService;
use App\Domains\Ads\Services\AdWorkflow;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdRequest;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class AdController extends Controller { private function formData(): array { return ['categories'=>Category::query()->active()->orderBy('sort_order')->get(['id','title']),'cities'=>City::query()->where('is_active',true)->orderBy('name')->get(['id','name'])]; } public function create(): View { return view('user.ads.create',$this->formData()); } public function store(StoreAdRequest $request,AdSubmissionService $service): RedirectResponse { $ad=$service->create($request->user(),$request->safe()->except('images'),$request->file('images',[]),$request->ip()); return redirect()->route('user.dashboard')->with('success','آگهی با کد '.$ad->code.' برای تأیید ثبت شد.'); } public function edit(Ad $ad): View { $this->authorize('update',$ad); return view('user.ads.edit',[...$this->formData(),'ad'=>$ad->load('images')]); } public function update(StoreAdRequest $request,Ad $ad,AdSubmissionService $service): RedirectResponse { $this->authorize('update',$ad); $service->update($ad,$request->user(),$request->safe()->except('images'),$request->file('images',[])); return redirect()->route('user.dashboard')->with('success','ویرایش آگهی ثبت شد؛ در صورت فعال بودن دوباره بررسی می‌شود.'); } public function destroy(Ad $ad,AdWorkflow $workflow): RedirectResponse { $this->authorize('view',$ad); $workflow->transition($ad,AdStatus::Deleted,auth()->user(),'حذف توسط کاربر'); return redirect()->route('user.dashboard')->with('success','آگهی حذف شد.'); } }
PHP,
'app/Http/Controllers/User/BillingController.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Controllers\User;
use App\Domains\Billing\PaymentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseTariffRequest;
use App\Models\Ad;
use App\Models\Invoice;
use App\Models\Tariff;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class BillingController extends Controller { public function index(): View { return view('user.payments.index',['tariffs'=>Tariff::query()->where('is_active',true)->orderBy('sort_order')->get(),'ads'=>Ad::query()->where('user_id',auth()->id())->whereNotIn('status',['deleted'])->get(['id','title','code','status']),'invoices'=>Invoice::query()->where('user_id',auth()->id())->latest()->paginate(20)]); } public function purchase(PurchaseTariffRequest $request,PaymentService $service): RedirectResponse { $invoice=$service->createInvoice($request->user(),Ad::query()->findOrFail($request->integer('ad_id')),Tariff::query()->findOrFail($request->integer('tariff_id'))); $result=$service->begin($invoice,$request->user()); return redirect()->to($result['redirect_url']); } public function callback(string $authority,PaymentService $service): RedirectResponse { $payment=$service->verify($authority,request()->user()); return redirect()->route('user.payments.index')->with('success',$payment->status==='successful'?'پرداخت و فعال‌سازی سرویس با موفقیت انجام شد.':'تأیید پرداخت ناموفق بود.'); } }
PHP,
'app/Http/Controllers/User/ProfileController.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\City;
use App\Models\Province;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class ProfileController extends Controller { public function edit(): View { return view('user.profile.edit',['profile'=>auth()->user()->profile,'cities'=>City::query()->where('is_active',true)->orderBy('name')->get(),'provinces'=>Province::query()->where('is_active',true)->orderBy('name')->get()]); } public function update(UpdateProfileRequest $request): RedirectResponse { $user=$request->user(); $user->update($request->safe()->only(['first_name','last_name','email'])); $user->profile()->updateOrCreate(['user_id'=>$user->id],$request->safe()->only(['business_name','address','province_id','city_id','postal_code'])); return back()->with('success','پروفایل به‌روزرسانی شد.'); } }
PHP,
'app/Console/Commands/SendExpiryNotifications.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Console\Commands;
use App\Domains\Ads\Enums\AdStatus;
use App\Domains\Notifications\SmsService;
use App\Models\Ad;
use Illuminate\Console\Command;
class SendExpiryNotifications extends Command { protected $signature='ads:send-expiry-notifications {--limit=50} {--dry-run}'; protected $description='یادآوری انقضای آگهی را به‌صورت محدود و idempotent ارسال می‌کند.'; public function handle(SmsService $sms): int { $ads=Ad::query()->where('status',AdStatus::Expired)->whereNotNull('expires_at')->orderBy('id')->limit((int)$this->option('limit'))->get(); if($this->option('dry-run')){ $this->info((string)$ads->count()); return self::SUCCESS; } foreach($ads as $ad){ $key='expiry:'.$ad->id.':'.$ad->expires_at?->format('Ymd'); $sms->send($key,'expiry_reminder',$ad->mobile_1,'آگهی شما منقضی شده است. برای تمدید به پنل کاربری مراجعه کنید.',$ad->user,$ad); } $this->info($ads->count().' پیام یادآوری پردازش شد.'); return self::SUCCESS; } }
PHP,
];

foreach ($files as $relative => $content) {
    $path = $root.'/'.$relative;
    if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0775, true) && !is_dir(dirname($path))) throw new RuntimeException("Cannot create {$relative}");
    file_put_contents($path, $content."\n");
}

echo 'Generated '.count($files)." shared-hosting completion files.\n";
