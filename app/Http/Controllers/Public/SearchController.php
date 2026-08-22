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
