<?php

declare(strict_types=1);

$root=dirname(__DIR__);
$files=[
'app/Http/Controllers/Admin/CatalogController.php'=><<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\ForbiddenWord;
use App\Models\Province;
use App\Models\Tariff;
use App\Support\PersianNormalizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class CatalogController extends Controller { private const MAP=['categories'=>Category::class,'countries'=>Country::class,'provinces'=>Province::class,'cities'=>City::class,'tariffs'=>Tariff::class,'forbidden-words'=>ForbiddenWord::class]; private function model(string $type): string { abort_unless(isset(self::MAP[$type]),404); return self::MAP[$type]; } public function index(string $type): View { $model=$this->model($type); return view('admin.catalog.index',['type'=>$type,'records'=>$model::query()->latest()->paginate(50),'parents'=>$type==='categories'?Category::query()->orderBy('title')->get(['id','title']):collect(),'countries'=>$type==='provinces'?Country::query()->orderBy('name')->get(['id','name']):collect(),'provinces'=>$type==='cities'?Province::query()->orderBy('name')->get(['id','name']):collect()]); } public function store(Request $request,string $type): RedirectResponse { $model=$this->model($type); $model::query()->create($this->data($request,$type)); return back()->with('success','رکورد جدید ثبت شد.'); } public function update(Request $request,string $type,int $id): RedirectResponse { $model=$this->model($type); $record=$model::query()->findOrFail($id); $record->update($this->data($request,$type,$record)); return back()->with('success','رکورد به‌روزرسانی شد.'); } public function toggle(string $type,int $id): RedirectResponse { $model=$this->model($type); $record=$model::query()->findOrFail($id); $record->update(['is_active'=>!$record->is_active]); return back()->with('success','وضعیت رکورد تغییر کرد.'); } private function data(Request $request,string $type,?Model $record=null): array { $base=['is_active'=>['nullable','boolean'],'sort_order'=>['nullable','integer','min:0']]; $rules=match($type){ 'categories'=>[...$base,'title'=>['required','string','max:160'],'slug'=>['required','string','max:190','unique:categories,slug,'.($record?->id??'NULL')],'parent_id'=>['nullable','exists:categories,id'],'description'=>['nullable','string'],'seo_title'=>['nullable','string','max:180'],'seo_description'=>['nullable','string','max:320']], 'countries'=>[...$base,'name'=>['required','string','max:120'],'slug'=>['required','string','max:160','unique:countries,slug,'.($record?->id??'NULL')]], 'provinces'=>[...$base,'country_id'=>['required','exists:countries,id'],'name'=>['required','string','max:120'],'slug'=>['required','string','max:160']], 'cities'=>[...$base,'province_id'=>['required','exists:provinces,id'],'name'=>['required','string','max:120'],'slug'=>['required','string','max:160']], 'tariffs'=>[...$base,'code'=>['required','string','max:80','unique:tariffs,code,'.($record?->id??'NULL')],'title'=>['required','string','max:160'],'description'=>['nullable','string'],'price'=>['required','integer','min:0'],'service_type'=>['required','in:ad,renewal,featured,colored,urgent,ladder,auto_ladder,extra_link,extra_image'],'duration_days'=>['nullable','integer','min:1']], 'forbidden-words'=>['word'=>['required','string','max:190','unique:forbidden_words,word,'.($record?->id??'NULL')],'is_active'=>['nullable','boolean']],}; $data=$request->validate($rules); foreach(['title','name','word','description'] as $field) if(isset($data[$field])) $data[$field]=PersianNormalizer::text($data[$field]); if($type==='forbidden-words') $data['normalized_word']=PersianNormalizer::text($data['word']); if(isset($data['is_active'])) $data['is_active']=$request->boolean('is_active'); if(isset($data['sort_order'])) $data['sort_order']=(int)$data['sort_order']; return $data; } }
PHP,
'app/Http/Controllers/Admin/ReportController.php'=><<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\AdReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class ReportController extends Controller { public function index(): View { return view('admin.reports.index',['reports'=>AdReport::query()->with(['ad','ad.user'])->latest()->paginate(50)]); } public function update(Request $request,AdReport $report): RedirectResponse { $data=$request->validate(['status'=>['required','in:new,reviewing,resolved,rejected'],'admin_note'=>['nullable','string','max:1500']]); $report->update([...$data,'reviewed_at'=>in_array($data['status'],['resolved','rejected'],true)?now():null]); return back()->with('success','گزارش به‌روزرسانی شد.'); } }
PHP,
'app/Http/Controllers/Admin/PermitController.php'=><<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Controllers\Admin;
use App\Domains\Ads\Enums\AdStatus;
use App\Domains\Ads\Services\AdWorkflow;
use App\Http\Controllers\Controller;
use App\Models\AdPermit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class PermitController extends Controller { public function index(): View { return view('admin.permits.index',['permits'=>AdPermit::query()->with('ad')->latest()->paginate(50)]); } public function update(Request $request,AdPermit $permit,AdWorkflow $workflow): RedirectResponse { $data=$request->validate(['status'=>['required','in:pending,approved,rejected'],'admin_note'=>['nullable','string','max:1500']]); $permit->update($data); if($data['status']==='approved' && $permit->ad->status===AdStatus::NeedsPermit) $workflow->transition($permit->ad,AdStatus::Active,$request->user(),'مجوز تأیید شد'); return back()->with('success','وضعیت مجوز به‌روزرسانی شد.'); } }
PHP,
'app/Http/Controllers/Admin/TicketController.php'=><<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class TicketController extends Controller { public function index(): View { return view('admin.tickets.index',['tickets'=>Ticket::query()->with('user')->latest()->paginate(50)]); } public function show(Ticket $ticket): View { return view('admin.tickets.show',['ticket'=>$ticket->load(['user','messages.sender'])]); } public function reply(Request $request,Ticket $ticket): RedirectResponse { $data=$request->validate(['message'=>['required','string','max:5000'],'status'=>['required','in:open,waiting_user,waiting_support,closed']]); TicketMessage::query()->create(['ticket_id'=>$ticket->id,'sender_id'=>$request->user()->id,'message'=>$data['message'],'created_at'=>now()]); $ticket->update(['status'=>$data['status'],'closed_at'=>$data['status']==='closed'?now():null]); return back()->with('success','پاسخ ثبت شد.'); } }
PHP,
'app/Http/Controllers/Admin/BillingController.php'=><<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\View\View;
class BillingController extends Controller { public function index(): View { return view('admin.billing.index',['payments'=>Payment::query()->with(['user','invoice','ad'])->latest()->paginate(50)]); } }
PHP,
'resources/views/admin/catalog/index.blade.php'=><<<'BLADE'
@extends('layouts.app',['title'=>'مدیریت داده‌ها | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')
@php($labels=['categories'=>'دسته‌بندی','countries'=>'کشور','provinces'=>'استان','cities'=>'شهر','tariffs'=>'تعرفه','forbidden-words'=>'لغت غیرمجاز'])
<div class="section-heading"><div><h1>مدیریت {{ $labels[$type] }}</h1><p class="muted">داده‌های عمومی و تنظیمات قابل مدیریت سامانه.</p></div><a href="{{ route('admin.dashboard') }}">داشبورد</a></div>
<section class="panel"><h2>افزودن رکورد</h2><form method="post" action="{{ route('admin.catalog.store',$type) }}" class="form-grid">@csrf @include('admin.catalog.fields')<div><button class="button">ثبت</button></div></form></section>
<section><h2>رکوردها</h2><div class="table-wrap"><table><thead><tr><th>عنوان</th><th>شناسه / slug</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody>@forelse($records as $record)<tr><td>{{ $record->title ?? $record->name ?? $record->word }}</td><td>{{ $record->slug ?? $record->code ?? $record->normalized_word }}</td><td>{{ $record->is_active ? 'فعال' : 'غیرفعال' }}</td><td><form method="post" action="{{ route('admin.catalog.toggle',[$type,$record->id]) }}">@csrf @method('PATCH')<button class="link-button">{{ $record->is_active ? 'غیرفعال‌کردن' : 'فعال‌کردن' }}</button></form></td></tr>@empty<tr><td colspan="4">رکوردی وجود ندارد.</td></tr>@endforelse</tbody></table></div>{{ $records->links() }}</section>
@endsection
BLADE,
'resources/views/admin/catalog/fields.blade.php'=><<<'BLADE'
@if($type==='categories')<label>عنوان<input name="title" required></label><label>slug<input name="slug" required></label><label>دستهٔ والد<select name="parent_id"><option value="">ندارد</option>@foreach($parents as $parent)<option value="{{ $parent->id }}">{{ $parent->title }}</option>@endforeach</select></label><label>ترتیب<input type="number" name="sort_order" value="0"></label><label class="wide">توضیح<textarea name="description"></textarea></label>
@elseif($type==='countries')<label>نام<input name="name" required></label><label>slug<input name="slug" required></label><label>ترتیب<input type="number" name="sort_order" value="0"></label>
@elseif($type==='provinces')<label>کشور<select name="country_id" required>@foreach($countries as $country)<option value="{{ $country->id }}">{{ $country->name }}</option>@endforeach</select></label><label>نام<input name="name" required></label><label>slug<input name="slug" required></label>
@elseif($type==='cities')<label>استان<select name="province_id" required>@foreach($provinces as $province)<option value="{{ $province->id }}">{{ $province->name }}</option>@endforeach</select></label><label>نام<input name="name" required></label><label>slug<input name="slug" required></label>
@elseif($type==='tariffs')<label>کد<input name="code" required></label><label>عنوان<input name="title" required></label><label>قیمت ریال<input name="price" type="number" min="0" required></label><label>نوع<select name="service_type" required><option value="ad">آگهی</option><option value="renewal">تمدید</option><option value="featured">ویژه</option><option value="colored">رنگی</option><option value="urgent">فوری</option><option value="ladder">نردبان</option></select></label><label>مدت (روز)<input name="duration_days" type="number" min="1"></label><label class="wide">توضیح<textarea name="description"></textarea></label>
@else<label>عبارت غیرمجاز<input name="word" required></label>@endif<label class="check"><input type="checkbox" name="is_active" value="1" checked>فعال</label>
BLADE,
'resources/views/admin/reports/index.blade.php'=><<<'BLADE'
@extends('layouts.app',['title'=>'گزارش‌های تخلف | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')<h1>گزارش‌های تخلف</h1><div class="table-wrap"><table><thead><tr><th>آگهی</th><th>دلیل</th><th>توضیح</th><th>وضعیت</th><th>اقدام</th></tr></thead><tbody>@forelse($reports as $report)<tr><td>{{ $report->ad?->title }}</td><td>{{ $report->reason }}</td><td>{{ $report->description }}</td><td>{{ $report->status }}</td><td><form method="post" action="{{ route('admin.reports.update',$report) }}">@csrf @method('PATCH')<select name="status"><option value="new">جدید</option><option value="reviewing">در حال بررسی</option><option value="resolved">حل‌شده</option><option value="rejected">ردشده</option></select><input name="admin_note" placeholder="یادداشت"><button class="button button-small">ذخیره</button></form></td></tr>@empty<tr><td colspan="5">گزارشی وجود ندارد.</td></tr>@endforelse</tbody></table></div>{{ $reports->links() }}@endsection
BLADE,
'resources/views/admin/permits/index.blade.php'=><<<'BLADE'
@extends('layouts.app',['title'=>'مجوزها | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')<h1>مجوزهای آگهی</h1><div class="table-wrap"><table><thead><tr><th>آگهی</th><th>شماره</th><th>صادرکننده</th><th>وضعیت</th><th>اقدام</th></tr></thead><tbody>@forelse($permits as $permit)<tr><td>{{ $permit->ad?->title }}</td><td>{{ $permit->permit_number }}</td><td>{{ $permit->issuer }}</td><td>{{ $permit->status }}</td><td><form method="post" action="{{ route('admin.permits.update',$permit) }}">@csrf @method('PATCH')<select name="status"><option value="pending">در انتظار</option><option value="approved">تأیید</option><option value="rejected">رد</option></select><input name="admin_note" placeholder="یادداشت"><button class="button button-small">ذخیره</button></form></td></tr>@empty<tr><td colspan="5">مجوزی وجود ندارد.</td></tr>@endforelse</tbody></table></div>{{ $permits->links() }}@endsection
BLADE,
'resources/views/admin/tickets/index.blade.php'=><<<'BLADE'
@extends('layouts.app',['title'=>'تیکت‌های مدیریت | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')<h1>تیکت‌ها</h1><div class="table-wrap"><table><thead><tr><th>کاربر</th><th>موضوع</th><th>اولویت</th><th>وضعیت</th><th></th></tr></thead><tbody>@forelse($tickets as $ticket)<tr><td>{{ $ticket->user->mobile }}</td><td>{{ $ticket->subject }}</td><td>{{ $ticket->priority }}</td><td>{{ $ticket->status }}</td><td><a href="{{ route('admin.tickets.show',$ticket) }}">مشاهده و پاسخ</a></td></tr>@empty<tr><td colspan="5">تیکتی وجود ندارد.</td></tr>@endforelse</tbody></table></div>{{ $tickets->links() }}@endsection
BLADE,
'resources/views/admin/tickets/show.blade.php'=><<<'BLADE'
@extends('layouts.app',['title'=>'پاسخ تیکت | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')<section class="panel"><h1>{{ $ticket->subject }}</h1><p class="muted">کاربر: {{ $ticket->user->mobile }}</p><div class="messages">@foreach($ticket->messages as $message)<article class="message"><strong>{{ $message->sender->name }}</strong><p>{{ $message->message }}</p><small>{{ $message->created_at }}</small></article>@endforeach</div><form method="post" action="{{ route('admin.tickets.reply',$ticket) }}">@csrf<label>پاسخ<textarea name="message" rows="5" required></textarea></label><label>وضعیت<select name="status"><option value="waiting_user">در انتظار کاربر</option><option value="waiting_support">در انتظار پشتیبانی</option><option value="closed">بسته</option></select></label><button class="button">ارسال پاسخ</button></form></section>@endsection
BLADE,
'resources/views/admin/billing/index.blade.php'=><<<'BLADE'
@extends('layouts.app',['title'=>'پرداخت‌های مدیریت | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')<h1>پرداخت‌ها</h1><div class="table-wrap"><table><thead><tr><th>کاربر</th><th>فاکتور</th><th>مبلغ</th><th>درگاه</th><th>شناسه</th><th>وضعیت</th><th>تاریخ</th></tr></thead><tbody>@forelse($payments as $payment)<tr><td>{{ $payment->user?->mobile }}</td><td>{{ $payment->invoice?->invoice_number }}</td><td>{{ number_format($payment->amount) }}</td><td>{{ $payment->gateway }}</td><td>{{ $payment->reference_id }}</td><td>{{ $payment->status }}</td><td>{{ $payment->created_at->format('Y/m/d') }}</td></tr>@empty<tr><td colspan="7">پرداختی وجود ندارد.</td></tr>@endforelse</tbody></table></div>{{ $payments->links() }}@endsection
BLADE,
];
foreach($files as $relative=>$content){$path=$root.'/'.$relative;if(!is_dir(dirname($path))&&!mkdir(dirname($path),0775,true)&&!is_dir(dirname($path)))throw new RuntimeException("Cannot create {$relative}");file_put_contents($path,$content."\n");}
echo 'Generated '.count($files)." admin completion files.\n";
