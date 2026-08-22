<?php

declare(strict_types=1);

$root = dirname(__DIR__);

$files = [
'app/Http/Requests/RegisterRequest.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Requests;
use App\Support\PersianNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
class RegisterRequest extends FormRequest {
    public function authorize(): bool { return true; }
    protected function prepareForValidation(): void { $this->merge(['mobile' => PersianNormalizer::mobile((string) $this->input('mobile')), 'first_name' => PersianNormalizer::text($this->input('first_name')), 'last_name' => PersianNormalizer::text($this->input('last_name'))]); }
    public function rules(): array { return ['mobile' => ['required','regex:/^09\d{9}$/','unique:users,mobile'], 'first_name' => ['required','string','max:80'], 'last_name' => ['required','string','max:80'], 'email' => ['nullable','email','max:255','unique:users,email'], 'password' => ['required','confirmed',Password::min(10)->mixedCase()->numbers()]]; }
    public function messages(): array { return ['mobile.unique'=>'این شمارهٔ موبایل قبلاً ثبت شده است.']; }
}
PHP,
'app/Http/Requests/LoginRequest.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Requests;
use App\Support\PersianNormalizer;
use Illuminate\Foundation\Http\FormRequest;
class LoginRequest extends FormRequest { public function authorize(): bool { return true; } protected function prepareForValidation(): void { $this->merge(['mobile' => PersianNormalizer::mobile((string) $this->input('mobile'))]); } public function rules(): array { return ['mobile'=>['required','regex:/^09\d{9}$/'],'password'=>['required','string']]; } }
PHP,
'app/Http/Requests/StoreAdRequest.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Requests;
use App\Support\PersianNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreAdRequest extends FormRequest {
    public function authorize(): bool { return $this->user() !== null; }
    protected function prepareForValidation(): void { $this->merge(['title'=>PersianNormalizer::text($this->input('title')), 'description'=>PersianNormalizer::text($this->input('description')), 'full_name'=>PersianNormalizer::text($this->input('full_name')), 'business_name'=>PersianNormalizer::text($this->input('business_name')), 'mobile_1'=>PersianNormalizer::mobile((string) $this->input('mobile_1')), 'mobile_2'=>$this->filled('mobile_2') ? PersianNormalizer::mobile((string) $this->input('mobile_2')) : null, 'keywords'=>array_values(array_filter(array_map(PersianNormalizer::text(...), (array) $this->input('keywords', [])))]); }
    public function rules(): array { return ['title'=>['required','string','max:300'], 'description'=>['required','string','max:6000'], 'keywords'=>['nullable','array','max:11'], 'keywords.*'=>['string','max:80','distinct'], 'price'=>['nullable','integer','min:0'], 'full_name'=>['nullable','string','max:160'], 'business_name'=>['nullable','string','max:160'], 'country_id'=>['nullable','exists:countries,id'], 'province_id'=>['nullable','exists:provinces,id'], 'city_id'=>['required','exists:cities,id'], 'address'=>['nullable','string','max:500'], 'mobile_1'=>['required','regex:/^09\d{9}$/'], 'mobile_2'=>['nullable','regex:/^09\d{9}$/'], 'phone_1'=>['nullable','string','max:30'], 'phone_2'=>['nullable','string','max:30'], 'email'=>['nullable','email','max:255'], 'category_id'=>['required','exists:categories,id'], 'images'=>['nullable','array','max:5'], 'images.*'=>['file','image','max:5120']]; }
    public function messages(): array { return ['city_id.required'=>'انتخاب شهر الزامی است.','category_id.required'=>'انتخاب دسته‌بندی الزامی است.','images.max'=>'حداکثر پنج تصویر قابل بارگذاری است.']; }
}
PHP,
'app/Http/Requests/StoreTicketRequest.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Requests;
use App\Support\PersianNormalizer;
use Illuminate\Foundation\Http\FormRequest;
class StoreTicketRequest extends FormRequest { public function authorize(): bool { return $this->user() !== null; } protected function prepareForValidation(): void { $this->merge(['subject'=>PersianNormalizer::text($this->input('subject')), 'message'=>PersianNormalizer::text($this->input('message'))]); } public function rules(): array { return ['subject'=>['required','string','max:190'],'message'=>['required','string','max:5000'],'priority'=>['nullable','in:low,normal,high']]; } }
PHP,
'app/Http/Middleware/EnsureStaff.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class EnsureStaff { public function handle(Request $request, Closure $next): Response { abort_unless($request->user()?->is_staff, 403); return $next($request); } }
PHP,
'app/Policies/AdPolicy.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Policies;
use App\Domains\Ads\Enums\AdStatus;
use App\Models\Ad;
use App\Models\User;
class AdPolicy { public function view(User $user, Ad $ad): bool { return $user->id === $ad->user_id || $user->isModerator(); } public function update(User $user, Ad $ad): bool { return ($user->id === $ad->user_id && !in_array($ad->status, [AdStatus::Expired, AdStatus::Deleted], true)) || $user->isModerator(); } public function moderate(User $user): bool { return $user->isModerator(); } }
PHP,
'app/Policies/TicketPolicy.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Policies;
use App\Models\Ticket;
use App\Models\User;
class TicketPolicy { public function view(User $user, Ticket $ticket): bool { return $ticket->user_id === $user->id || $user->is_staff; } public function reply(User $user, Ticket $ticket): bool { return $ticket->user_id === $user->id || $user->is_staff; } }
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
    public function create(User $user, array $data, array $uploads, string $ip): Ad {
        $this->forbiddenWords->assertAllowed([$data['title'] ?? '', $data['description'] ?? '', $data['business_name'] ?? '', implode(' ', $data['keywords'] ?? [])]);
        $fingerprints = $this->duplicates->fingerprints($data['title'], $data['description']);
        if ($this->duplicates->exists($fingerprints)) throw ValidationException::withMessages(['title'=>'آگهی مشابهی با همین عنوان و توضیحات قبلاً ثبت شده است.']);
        return DB::transaction(function () use ($user, $data, $uploads, $ip, $fingerprints): Ad {
            $ad = Ad::query()->create([...$data, 'user_id'=>$user->id, 'code'=>strtoupper(Str::random(10)), 'slug'=>Str::slug($data['title'], '-', 'fa') ?: Str::random(8), 'normalized_title'=>$fingerprints['title'], 'normalized_description'=>$fingerprints['description'], 'normalized_title_hash'=>$fingerprints['title_hash'], 'normalized_description_hash'=>$fingerprints['description_hash'], 'status'=>AdStatus::Draft, 'source'=>'user_panel', 'submit_ip'=>$ip, 'sort_at'=>now()]);
            foreach ($uploads as $index => $upload) $this->images->store($ad, $upload, $index);
            return $this->workflow->transition($ad, AdStatus::PendingApproval, $user, 'ثبت توسط کاربر');
        });
    }
}
PHP,
'app/Http/Controllers/AuthController.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Controllers;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
class AuthController extends Controller {
    public function create(): View { return view('auth.register'); }
    public function store(RegisterRequest $request): RedirectResponse { $user = User::query()->create($request->safe()->only(['mobile','email','first_name','last_name','password'])); Profile::query()->create(['user_id'=>$user->id]); Auth::login($user); $request->session()->regenerate(); return redirect()->route('user.dashboard')->with('success','حساب کاربری شما ایجاد شد.'); }
    public function loginForm(): View { return view('auth.login'); }
    public function login(LoginRequest $request): RedirectResponse { $key='login:'.$request->ip().'|'.$request->input('mobile'); if (RateLimiter::tooManyAttempts($key, 5)) return back()->withErrors(['mobile'=>'تلاش‌های ناموفق زیاد است؛ چند دقیقه دیگر امتحان کنید.']); if (!Auth::attempt(['mobile'=>$request->input('mobile'),'password'=>$request->input('password'),'is_active'=>true], $request->boolean('remember'))) { RateLimiter::hit($key, 300); return back()->withErrors(['mobile'=>'شمارهٔ موبایل یا گذرواژه نادرست است.'])->onlyInput('mobile'); } RateLimiter::clear($key); $request->session()->regenerate(); $request->user()->forceFill(['last_login_at'=>now()])->save(); return redirect()->intended(route('user.dashboard')); }
    public function logout(Request $request): RedirectResponse { Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('home'); }
}
PHP,
'app/Http/Controllers/Public/HomeController.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Controllers\Public;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
class HomeController extends Controller { public function __invoke(): View { $categories=Cache::remember('public.root_categories.v1', now()->addHour(), fn()=>Category::query()->active()->whereNull('parent_id')->with(['children'=>fn($q)=>$q->active()])->orderBy('sort_order')->get()); $featured=Ad::query()->publiclyVisible()->with(['city','images'])->where('is_featured',true)->orderedForListing()->limit(8)->get(); $latest=Ad::query()->publiclyVisible()->with(['city','images'])->orderedForListing()->limit(12)->get(); return view('public.home',compact('categories','featured','latest')); } }
PHP,
'app/Http/Controllers/Public/AdController.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Controllers\Public;
use App\Domains\Seo\SeoPolicy;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class AdController extends Controller { public function show(Request $request, string $ad, string $slug, SeoPolicy $seo): View|RedirectResponse { $listing=Ad::query()->publiclyVisible()->where('code',$ad)->with(['images','category','city','province','country','links'])->firstOrFail(); if ($listing->slug !== $slug) return redirect()->to($listing->publicUrl(),301); $viewKey='ad-viewed-'.$listing->id; if (!$request->session()->has($viewKey)) { $listing->increment('views_count'); $request->session()->put($viewKey,true); } $related=Ad::query()->publiclyVisible()->whereKeyNot($listing->id)->where('category_id',$listing->category_id)->when($listing->city_id,fn($q)=>$q->where('city_id',$listing->city_id))->with(['city','images'])->orderedForListing()->limit(6)->get(); return view('public.ads.show',['ad'=>$listing,'related'=>$related,'seo'=>$seo]); } }
PHP,
'app/Http/Controllers/Public/SearchController.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Controllers\Public;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use App\Support\PersianNormalizer;
use Illuminate\Http\Request;
use Illuminate\View\View;
class SearchController extends Controller { public function __invoke(Request $request): View { $data=$request->validate(['q'=>['nullable','string','max:120'],'category'=>['nullable','exists:categories,id'],'city'=>['nullable','exists:cities,id'],'min_price'=>['nullable','integer','min:0'],'max_price'=>['nullable','integer','min:0']]); $query=Ad::query()->publiclyVisible()->with(['city','images','category']); if ($term=PersianNormalizer::text($data['q']??'')) $query->where(fn($q)=>$q->where('normalized_title','like','%'.$term.'%')->orWhere('normalized_description','like','%'.$term.'%')->orWhere('business_name','like','%'.$term.'%')); foreach (['category'=>'category_id','city'=>'city_id'] as $input=>$column) if (!empty($data[$input])) $query->where($column,$data[$input]); if (isset($data['min_price'])) $query->where('price','>=',$data['min_price']); if (isset($data['max_price'])) $query->where('price','<=',$data['max_price']); return view('public.search',['ads'=>$query->orderedForListing()->paginate(24)->withQueryString(),'categories'=>Category::query()->active()->orderBy('sort_order')->get(['id','title']),'cities'=>City::query()->where('is_active',true)->orderBy('name')->get(['id','name']),'filters'=>$data]); } }
PHP,
'app/Http/Controllers/Public/CategoryController.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Controllers\Public;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Category;
use Illuminate\View\View;
class CategoryController extends Controller { public function show(Category $category): View { abort_unless($category->is_active,404); return view('public.categories.show',['category'=>$category->load(['children'=>fn($q)=>$q->active()]),'ads'=>Ad::query()->publiclyVisible()->where('category_id',$category->id)->with(['city','images'])->orderedForListing()->paginate(24)]); } }
PHP,
'app/Http/Controllers/User/DashboardController.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\View\View;
class DashboardController extends Controller { public function __invoke(Request $request): View { $status=$request->string('status')->toString(); $ads=Ad::query()->where('user_id',$request->user()->id)->with(['city','images'])->when($status,fn($q)=>$q->where('status',$status))->latest()->paginate(20)->withQueryString(); return view('user.dashboard',compact('ads','status')); } }
PHP,
'app/Http/Controllers/User/AdController.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Controllers\User;
use App\Domains\Ads\Services\AdSubmissionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdRequest;
use App\Models\Category;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class AdController extends Controller { public function create(): View { return view('user.ads.create',['categories'=>Category::query()->active()->orderBy('sort_order')->get(['id','title']),'cities'=>City::query()->where('is_active',true)->orderBy('name')->get(['id','name'])]); } public function store(StoreAdRequest $request, AdSubmissionService $service): RedirectResponse { $data=$request->safe()->except('images'); $ad=$service->create($request->user(),$data,$request->file('images',[]),$request->ip()); return redirect()->route('user.dashboard')->with('success','آگهی با کد '.$ad->code.' برای تأیید ثبت شد.'); } }
PHP,
'app/Http/Controllers/User/TicketController.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class TicketController extends Controller { public function index(): View { return view('user.tickets.index',['tickets'=>Ticket::query()->where('user_id',auth()->id())->latest()->paginate(20)]); } public function store(StoreTicketRequest $request): RedirectResponse { $ticket=Ticket::query()->create(['user_id'=>$request->user()->id,'subject'=>$request->input('subject'),'priority'=>$request->input('priority','normal')]); TicketMessage::query()->create(['ticket_id'=>$ticket->id,'sender_id'=>$request->user()->id,'message'=>$request->input('message'),'created_at'=>now()]); return back()->with('success','تیکت شما ثبت شد.'); } }
PHP,
'app/Http/Controllers/Admin/DashboardController.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Controllers\Admin;
use App\Domains\Ads\Enums\AdStatus;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Ticket;
use Illuminate\View\View;
class DashboardController extends Controller { public function __invoke(): View { $counts=[]; foreach (AdStatus::cases() as $status) $counts[$status->value]=Ad::query()->where('status',$status)->count(); return view('admin.dashboard',['counts'=>$counts,'openTickets'=>Ticket::query()->whereIn('status',['open','waiting_support'])->count()]); } }
PHP,
'app/Http/Controllers/Admin/ModerationController.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Controllers\Admin;
use App\Domains\Ads\Enums\AdStatus;
use App\Domains\Ads\Services\AdWorkflow;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class ModerationController extends Controller { public function index(Request $request): View { $status=$request->string('status')->toString(); $term=$request->string('q')->trim()->toString(); $ads=Ad::query()->with(['user','city','category'])->when($status,fn($q)=>$q->where('status',$status))->when($term,fn($q)=>$q->where(fn($q)=>$q->where('code','like',$term.'%')->orWhere('mobile_1','like',$term.'%')->orWhere('title','like','%'.$term.'%')))->latest()->paginate(50)->withQueryString(); return view('admin.ads.index',compact('ads','status','term')); } public function transition(Request $request, Ad $ad, AdWorkflow $workflow): RedirectResponse { $data=$request->validate(['status'=>['required','in:'.implode(',',array_map(fn($status)=>$status->value,AdStatus::cases()))],'reason'=>['nullable','string','max:1000']]); $workflow->transition($ad,AdStatus::from($data['status']),$request->user(),$data['reason']??null); return back()->with('success','وضعیت آگهی به‌روزرسانی شد.'); } }
PHP,
'app/Http/Controllers/SeoController.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Http\Controllers;
use App\Domains\Seo\SeoPolicy;
use App\Models\Ad;
use App\Models\Category;
use Illuminate\Http\Response;
class SeoController extends Controller { public function robots(): Response { return response("User-agent: *\nDisallow: /admin\nDisallow: /user\nDisallow: /login\nDisallow: /register\nSitemap: ".route('sitemap.index')."\n",200,['Content-Type'=>'text/plain; charset=UTF-8']); } public function sitemapIndex(SeoPolicy $seo): Response { $pages=max(1,(int)ceil(Ad::query()->publiclyVisible()->count()/1000)); return response()->view('seo.sitemap-index',['pages'=>range(1,$pages)] ,200,['Content-Type'=>'application/xml; charset=UTF-8']); } public function adsSitemap(int $page, SeoPolicy $seo): Response { abort_if($page < 1,404); $ads=Ad::query()->publiclyVisible()->with(['city'])->orderedForListing()->forPage($page,1000)->get(); abort_if($ads->isEmpty() && $page > 1,404); return response()->view('seo.ads-sitemap',compact('ads','seo'),200,['Content-Type'=>'application/xml; charset=UTF-8']); } public function categoriesSitemap(): Response { return response()->view('seo.categories-sitemap',['categories'=>Category::query()->active()->get()],200,['Content-Type'=>'application/xml; charset=UTF-8']); } }
PHP,
];

foreach ($files as $relative => $content) {
    $path = $root.'/'.$relative;
    if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0775, true) && !is_dir(dirname($path))) throw new RuntimeException("Cannot create {$relative}");
    file_put_contents($path, $content."\n");
}

echo 'Generated '.count($files)." HTTP and workflow files.\n";
