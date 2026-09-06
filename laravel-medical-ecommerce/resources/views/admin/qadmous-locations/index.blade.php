@extends('admin.layout.app')
@section('title', __('admin.qadmous_locations'))
@section('content')
<div class="page-header"><div><p class="eyebrow">{{ __('admin.shipping') }}</p><h1>{{ __('admin.qadmous_locations') }}</h1><p>{{ __('admin.qadmous_locations_hint') }}</p></div></div>
<form method="POST" action="{{ route('admin.qadmous-locations.store') }}" class="panel-card mb-4">@csrf
<div class="row g-3 align-items-end">
@foreach(['governorate_ar','governorate_en','branch_ar','branch_en'] as $field)<div class="col-md-{{ str_contains($field, 'governorate') ? 3 : 2 }}"><label class="form-label">{{ __('admin.'.$field) }}</label><input class="form-control" name="{{ $field }}" value="{{ old($field) }}" @required(str_ends_with($field, '_ar')) @if(str_ends_with($field, '_en')) dir="ltr" @endif></div>@endforeach
<div class="col-md-1"><label class="form-label">{{ __('admin.sort_order') }}</label><input class="form-control" type="number" min="0" max="9999" name="sort_order" value="{{ old('sort_order', 0) }}"></div>
<div class="col-md-1"><input type="hidden" name="is_active" value="1"><button class="btn btn-primary w-100">{{ __('admin.add') }}</button></div>
</div></form>
<div class="panel-card table-responsive"><table class="table align-middle"><thead><tr><th>{{ __('admin.governorate') }}</th><th>{{ __('admin.branch') }}</th><th>{{ __('admin.sort_order') }}</th><th>{{ __('admin.status') }}</th><th>{{ __('admin.actions') }}</th></tr></thead><tbody>
@forelse($locations as $location)<tr><td colspan="5" class="p-0"><form method="POST" action="{{ route('admin.qadmous-locations.update', $location) }}" class="row g-2 align-items-center m-0 p-2">@csrf @method('PUT')
<div class="col-lg-2"><input class="form-control" name="governorate_ar" value="{{ $location->governorate_ar }}" aria-label="{{ __('admin.governorate_ar') }}" required></div><div class="col-lg-2"><input class="form-control" name="governorate_en" value="{{ $location->governorate_en }}" aria-label="{{ __('admin.governorate_en') }}" dir="ltr"></div>
<div class="col-lg-2"><input class="form-control" name="branch_ar" value="{{ $location->branch_ar }}" aria-label="{{ __('admin.branch_ar') }}" required></div><div class="col-lg-2"><input class="form-control" name="branch_en" value="{{ $location->branch_en }}" aria-label="{{ __('admin.branch_en') }}" dir="ltr"></div>
<div class="col-lg-1"><input class="form-control" type="number" min="0" max="9999" name="sort_order" value="{{ $location->sort_order }}" aria-label="{{ __('admin.sort_order') }}"></div><div class="col-lg-1 form-check form-switch"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($location->is_active)><label class="form-check-label">{{ __('admin.active') }}</label></div>
<div class="col-lg-2 d-flex gap-2 justify-content-end"><button class="btn btn-sm btn-outline-primary">{{ __('admin.save') }}</button><button class="btn btn-sm btn-outline-danger" form="delete-qadmous-{{ $location->id }}">{{ __('admin.delete') }}</button></div></form><form id="delete-qadmous-{{ $location->id }}" method="POST" action="{{ route('admin.qadmous-locations.destroy', $location) }}" onsubmit="return confirm('{{ __('admin.confirm_delete_qadmous') }}')">@csrf @method('DELETE')</form></td></tr>
@empty<tr><td colspan="5" class="text-center py-5 text-muted">{{ __('admin.no_qadmous_locations') }}</td></tr>@endforelse
</tbody></table>{{ $locations->links() }}</div>
@endsection
