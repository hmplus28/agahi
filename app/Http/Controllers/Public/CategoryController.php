<?php

declare(strict_types=1);
namespace App\Http\Controllers\Public;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Category;
use Illuminate\View\View;
class CategoryController extends Controller { public function show(Category $category): View { abort_unless($category->is_active,404); return view('public.categories.show',['category'=>$category->load(['children'=>fn($q)=>$q->active()]),'ads'=>Ad::query()->publiclyVisible()->where('category_id',$category->id)->with(['city','images'])->orderedForListing()->paginate(24)]); } }
