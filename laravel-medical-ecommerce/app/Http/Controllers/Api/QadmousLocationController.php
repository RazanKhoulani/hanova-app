<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\QadmousLocation;
use Illuminate\Http\Request;
class QadmousLocationController extends Controller { public function index(Request $request){ $en=str_starts_with($request->header('Accept-Language','ar'),'en'); return response()->json(['data'=>QadmousLocation::where('is_active',true)->orderBy('sort_order')->get()->map(fn($x)=>['id'=>$x->id,'governorate'=>$en?($x->governorate_en?:$x->governorate_ar):$x->governorate_ar,'branch'=>$en?($x->branch_en?:$x->branch_ar):$x->branch_ar])]); } }
