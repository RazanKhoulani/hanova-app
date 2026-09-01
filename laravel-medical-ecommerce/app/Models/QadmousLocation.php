<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class QadmousLocation extends Model { protected $fillable=['governorate_ar','governorate_en','branch_ar','branch_en','is_active','sort_order']; protected $casts=['is_active'=>'boolean']; }
