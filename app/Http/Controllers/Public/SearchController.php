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
class SearchController extends Controller { public function __invoke(Request $request): View { $data=$request->validate(['q'=>['nullable','string','max:120'],'category'=>['nullable','exists:categories,id'],'city'=>['nullable','exists:cities,id'],'province'=>['nullable','exists:provinces,id'],'country'=>['nullable','exists:countries,id'],'min_price'=>['nullable','integer','min:0'],'max_price'=>['nullable','integer','min:0']]); $query=Ad::query()->publiclyVisible()->with(['city','images','category']); if ($term=PersianNormalizer::text($data['q']??'')) $query->where(fn($q)=>$q->where('normalized_title','like','%'.$term.'%')->orWhere('normalized_description','like','%'.$term.'%')->orWhere('business_name','like','%'.$term.'%')); if (!empty($data['category'])) $query->where('category_id',$data['category']); if (!empty($data['city'])) $query->where('city_id',$data['city']); if (!empty($data['province'])) $query->whereHas('city',fn($q)=>$q->where('province_id',$data['province'])); if (!empty($data['country'])) $query->whereHas('city',fn($q2)=>$q2->whereHas('province',fn($q3)=>$q3->where('country_id',$data['country']))); if (isset($data['min_price'])) $query->where('price','>=',$data['min_price']); if (isset($data['max_price'])) $query->where('price','<=',$data['max_price']); $allCategories=\App\Models\Category::query()->active()->select(['id','parent_id','title'])->orderBy('title')->get(); $provinces=\App\Models\Province::query()->where('is_active',true)->orderBy('name')->get(['id','name','country_id']); $countries=\App\Models\Country::query()->where('is_active',true)->orderBy('name')->get(['id','name']); return view('public.search',['ads'=>$query->orderedForListing()->paginate(24)->withQueryString(),'categories'=>Category::query()->active()->orderBy('sort_order')->get(['id','title']),'allCategories'=>$allCategories,'cities'=>City::query()->where('is_active',true)->orderBy('name')->get(['id','name','province_id']),'provinces'=>$provinces,'countries'=>$countries,'filters'=>$data]); } }
