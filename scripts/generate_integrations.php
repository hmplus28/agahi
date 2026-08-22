<?php

declare(strict_types=1);

$root = dirname(__DIR__);

$files = [
'app/Models/AdPermit.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AdPermit extends Model { protected $fillable=['ad_id','permit_number','issuer','issued_at','image_path','status','admin_note']; protected function casts(): array { return ['issued_at'=>'date']; } public function ad(): BelongsTo { return $this->belongsTo(Ad::class); } }
PHP,
'app/Models/AdReport.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AdReport extends Model { protected $fillable=['ad_id','reason','description','reporter_user_id','reporter_ip','status','admin_note','reviewed_at']; protected function casts(): array { return ['reviewed_at'=>'datetime']; } public function ad(): BelongsTo { return $this->belongsTo(Ad::class); } }
PHP,
'app/Models/Invoice.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Invoice extends Model { protected $fillable=['invoice_number','user_id','ad_id','status','subtotal','discount','total','paid_at']; protected function casts(): array { return ['paid_at'=>'datetime']; } public function user(): BelongsTo { return $this->belongsTo(User::class); } public function ad(): BelongsTo { return $this->belongsTo(Ad::class); } public function items(): HasMany { return $this->hasMany(InvoiceItem::class); } public function payments(): HasMany { return $this->hasMany(Payment::class); } }
PHP,
'app/Models/InvoiceItem.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class InvoiceItem extends Model { protected $fillable=['invoice_id','title','quantity','unit_price','total_price','metadata']; protected function casts(): array { return ['metadata'=>'array']; } public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); } }
PHP,
'app/Models/Payment.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Payment extends Model { protected $fillable=['user_id','ad_id','invoice_id','amount','method','status','gateway','authority','reference_id','paid_at','verified_at','admin_note']; protected function casts(): array { return ['paid_at'=>'datetime','verified_at'=>'datetime']; } public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); } public function user(): BelongsTo { return $this->belongsTo(User::class); } }
PHP,
'app/Models/SmsLog.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SmsLog extends Model { protected $fillable=['user_id','ad_id','mobile','type','idempotency_key','provider_id','status','sent_at','response']; protected function casts(): array { return ['sent_at'=>'datetime','response'=>'array']; } }
PHP,
'app/Domains/Billing/Contracts/PaymentGateway.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Domains\Billing\Contracts;
use App\Models\Payment;
interface PaymentGateway { /** @return array{authority:string,redirect_url:string} */ public function create(Payment $payment): array; /** @return array{successful:bool,reference_id:?string} */ public function verify(Payment $payment): array; }
PHP,
'app/Domains/Billing/Gateways/FakePaymentGateway.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Domains\Billing\Gateways;
use App\Domains\Billing\Contracts\PaymentGateway;
use App\Models\Payment;
use Illuminate\Support\Str;
final class FakePaymentGateway implements PaymentGateway { public function create(Payment $payment): array { return ['authority'=>'DEV-'.Str::upper(Str::random(24)),'redirect_url'=>route('user.dashboard')]; } public function verify(Payment $payment): array { return ['successful'=>true,'reference_id'=>'DEV-'.Str::upper(Str::random(12))]; } }
PHP,
'app/Domains/Notifications/Contracts/SmsProvider.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Domains\Notifications\Contracts;
interface SmsProvider { /** @return array{provider_id:?string,response:array<string,mixed>} */ public function send(string $mobile,string $message): array; }
PHP,
'app/Domains/Notifications/Providers/LogSmsProvider.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Domains\Notifications\Providers;
use App\Domains\Notifications\Contracts\SmsProvider;
use Illuminate\Support\Facades\Log;
final class LogSmsProvider implements SmsProvider { public function send(string $mobile,string $message): array { Log::channel('stack')->info('Development SMS simulated',['mobile'=>$mobile,'message'=>$message]); return ['provider_id'=>null,'response'=>['mode'=>'log']]; } }
PHP,
'app/Domains/Notifications/SmsService.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Domains\Notifications;
use App\Domains\Notifications\Contracts\SmsProvider;
use App\Models\Ad;
use App\Models\SmsLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
final class SmsService { public function __construct(private readonly SmsProvider $provider) {} public function send(string $key,string $type,string $mobile,string $message,?User $user=null,?Ad $ad=null): SmsLog { return DB::transaction(function() use($key,$type,$mobile,$message,$user,$ad): SmsLog { $log=SmsLog::query()->firstOrCreate(['idempotency_key'=>$key],['type'=>$type,'mobile'=>$mobile,'user_id'=>$user?->id,'ad_id'=>$ad?->id,'status'=>'pending']); if($log->status==='sent') return $log; $result=$this->provider->send($mobile,$message); $log->forceFill(['status'=>'sent','provider_id'=>$result['provider_id'],'response'=>$result['response'],'sent_at'=>now()])->save(); return $log; }); } }
PHP,
'app/Domains/Billing/PaymentService.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Domains\Billing;
use App\Domains\Billing\Contracts\PaymentGateway;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LogicException;
final class PaymentService { public function __construct(private readonly PaymentGateway $gateway) {} /** @return array{payment:Payment,redirect_url:string} */ public function begin(Invoice $invoice,User $user): array { return DB::transaction(function() use($invoice,$user): array { $locked=Invoice::query()->lockForUpdate()->findOrFail($invoice->id); if($locked->user_id!==$user->id || $locked->status==='paid') throw new LogicException('فاکتور قابل پرداخت نیست.'); $payment=Payment::query()->create(['user_id'=>$user->id,'ad_id'=>$locked->ad_id,'invoice_id'=>$locked->id,'amount'=>$locked->total,'method'=>'online','status'=>'pending','gateway'=>'fake']); $intent=$this->gateway->create($payment); $payment->update(['authority'=>$intent['authority']]); return ['payment'=>$payment->refresh(),'redirect_url'=>$intent['redirect_url']]; }); } public function verify(string $authority): Payment { return DB::transaction(function() use($authority): Payment { $payment=Payment::query()->where('authority',$authority)->lockForUpdate()->firstOrFail(); if($payment->status==='successful') return $payment; $result=$this->gateway->verify($payment); if(!$result['successful']) { $payment->update(['status'=>'failed']); return $payment->refresh(); } $payment->update(['status'=>'successful','reference_id'=>$result['reference_id'],'paid_at'=>now(),'verified_at'=>now()]); $payment->invoice?->update(['status'=>'paid','paid_at'=>now()]); return $payment->refresh(); }); } }
PHP,
'app/Http/Controllers/User/PermitController.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Controllers\User;
use App\Domains\Ads\Enums\AdStatus;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\AdPermit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
class PermitController extends Controller { public function store(Request $request,Ad $ad): RedirectResponse { $this->authorize('view',$ad); abort_unless($ad->status===AdStatus::NeedsPermit,422); $data=$request->validate(['permit_number'=>['nullable','string','max:160'],'issuer'=>['nullable','string','max:190'],'issued_at'=>['nullable','date'],'image'=>['nullable','image','max:5120']]); $path=$request->file('image')?->store('permits','local'); AdPermit::query()->create([...$data,'ad_id'=>$ad->id,'image_path'=>$path,'status'=>'pending']); return back()->with('success','مجوز برای بررسی ارسال شد.'); } }
PHP,
'app/Http/Controllers/Public/AdReportController.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Controllers\Public;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\AdReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
class AdReportController extends Controller { public function store(Request $request,string $ad): RedirectResponse { $listing=Ad::query()->publiclyVisible()->where('code',$ad)->firstOrFail(); $data=$request->validate(['reason'=>['required','in:no_response,fraud,false_information,illegal,inappropriate,overpriced,other'],'description'=>['nullable','string','max:2000']]); AdReport::query()->create([...$data,'ad_id'=>$listing->id,'reporter_user_id'=>$request->user()?->id,'reporter_ip'=>$request->ip(),'status'=>'new']); return back()->with('success','گزارش شما ثبت شد و بررسی خواهد شد.'); } }
PHP,
'app/Providers/AppServiceProvider.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Providers;
use App\Domains\Billing\Contracts\PaymentGateway;
use App\Domains\Billing\Gateways\FakePaymentGateway;
use App\Domains\Notifications\Contracts\SmsProvider;
use App\Domains\Notifications\Providers\LogSmsProvider;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider { public function register(): void { $this->app->bind(PaymentGateway::class,FakePaymentGateway::class); $this->app->bind(SmsProvider::class,LogSmsProvider::class); } public function boot(): void {} }
PHP,
];

foreach ($files as $relative => $content) {
    $path = $root.'/'.$relative;
    if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0775, true) && !is_dir(dirname($path))) throw new RuntimeException("Cannot create {$relative}");
    file_put_contents($path, $content."\n");
}

echo 'Generated '.count($files)." integration and moderation files.\n";
