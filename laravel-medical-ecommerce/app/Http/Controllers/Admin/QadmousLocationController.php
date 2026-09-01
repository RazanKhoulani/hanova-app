<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\QadmousLocation;
use Illuminate\Http\Request;
class QadmousLocationController extends Controller {
 public function index(){ return view('admin.qadmous-locations.index',['locations'=>QadmousLocation::orderBy('sort_order')->paginate(30)]); }
 public function store(Request $r){ QadmousLocation::create($this->data($r)); return back()->with('success','تمت إضافة فرع قدموس.'); }
 public function update(Request $r,QadmousLocation $qadmous_location){ $qadmous_location->update($this->data($r)); return back()->with('success','تم تحديث الفرع.'); }
 public function destroy(QadmousLocation $qadmous_location){ $qadmous_location->delete(); return back()->with('success','تم حذف الفرع.'); }
 private function data(Request $r): array { $d=$r->validate(['governorate_ar'=>'required|string|max:100','governorate_en'=>'nullable|string|max:100','branch_ar'=>'required|string|max:150','branch_en'=>'nullable|string|max:150','sort_order'=>'nullable|integer|min:0','is_active'=>'nullable|boolean']); $d['is_active']=$r->boolean('is_active'); return $d; }
}
