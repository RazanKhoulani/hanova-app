@extends('admin.layout.app')

@section('title', __('admin.products_management'))

@section('content')
<div class="page-header"><div><p class="eyebrow">{{ __('admin.store') }}</p><h1>{{ __('admin.products_management') }}</h1><p>{{ __('admin.products_management_hint') }}</p></div><a href="{{ route('admin.products.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i>{{ __('admin.add_product') }}</a></div>


<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3"><article class="metric-card h-100"><div class="metric-top"><div class="metric-icon"><i class="fas fa-boxes-stacked"></i></div></div><div class="metric-value">{{ $inventorySummary['tracked'] }}</div><div class="metric-label">{{ __('admin.tracked_products') }}</div></article></div>
    <div class="col-sm-6 col-xl-3"><article class="metric-card h-100" style="--metric-color:#2f8f64;--metric-soft:#e8f6ef"><div class="metric-top"><div class="metric-icon"><i class="fas fa-circle-check"></i></div></div><div class="metric-value">{{ $inventorySummary['available'] }}</div><div class="metric-label">{{ __('admin.available') }}</div></article></div>
    <div class="col-sm-6 col-xl-3"><article class="metric-card h-100" style="--metric-color:#c78636;--metric-soft:#fff3df"><div class="metric-top"><div class="metric-icon"><i class="fas fa-triangle-exclamation"></i></div></div><div class="metric-value">{{ $inventorySummary['low'] }}</div><div class="metric-label">{{ __('admin.low_stock') }}</div></article></div>
    <div class="col-sm-6 col-xl-3"><article class="metric-card h-100" style="--metric-color:#c44747;--metric-soft:#fdecec"><div class="metric-top"><div class="metric-icon"><i class="fas fa-circle-xmark"></i></div></div><div class="metric-value">{{ $inventorySummary['out'] }}</div><div class="metric-label">{{ __('admin.out_of_stock') }}</div></article></div>
</div>

<form method="GET" class="panel-card form-panel mb-4" role="search">
    <div class="row g-3 align-items-end">
        <div class="col-lg-5"><label class="form-label" for="product-search">{{ __('admin.search_products') }}</label><div class="input-group"><span class="input-group-text"><i class="fas fa-search"></i></span><input id="product-search" type="search" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('admin.search_products_hint') }}"></div></div>
        <div class="col-sm-6 col-lg-3"><label class="form-label" for="stock-filter">{{ __('admin.stock_status') }}</label><select id="stock-filter" name="stock" class="form-select"><option value="">{{ __('admin.all_stock_statuses') }}</option><option value="available" @selected(($filters['stock'] ?? '') === 'available')>{{ __('admin.available') }}</option><option value="low" @selected(($filters['stock'] ?? '') === 'low')>{{ __('admin.low_stock') }}</option><option value="out" @selected(($filters['stock'] ?? '') === 'out')>{{ __('admin.out_of_stock') }}</option><option value="untracked" @selected(($filters['stock'] ?? '') === 'untracked')>{{ __('admin.not_tracked') }}</option></select></div>
        <div class="col-sm-6 col-lg-2"><label class="form-label" for="type-filter">{{ __('admin.catalog_type') }}</label><select id="type-filter" name="catalog_type" class="form-select"><option value="">{{ __('admin.all_types') }}</option>@foreach(['product','bundle','session','nutrition'] as $type)<option value="{{ $type }}" @selected(($filters['catalog_type'] ?? '') === $type)>{{ __('admin.catalog_' . $type) }}</option>@endforeach</select></div>
        <div class="col-lg-2"><div class="d-flex gap-2"><button class="btn btn-primary flex-grow-1" type="submit">{{ __('admin.filter') }}</button>@if(array_filter($filters ?? []))<a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary" title="{{ __('admin.clear_filters') }}"><i class="fas fa-rotate-left"></i></a>@endif</div></div>
    </div>
</form>

<section class="panel-card data-panel">
    <div class="panel-heading"><div><h3>{{ __('admin.products') }}</h3><p>{{ __('admin.products_table_hint') }}</p></div><span class="soft-count">{{ $products->total() }}</span></div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.id') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.image') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.name_english') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.name_arabic') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.concern') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.price') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.cost') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary">{{ __('admin.stock') }}</th>
                        <th class="border-0 px-4 py-3 text-secondary text-end">{{ __('admin.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr class="clickable-row" data-href="{{ route('admin.products.show', $product->id) }}">
                        <td class="align-middle px-4">{{ $product->id }}</td>
                        <td class="align-middle px-4">
                            @if($product->image)
                                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name_en }}" width="40" height="40" class="rounded object-fit-cover shadow-sm">
                            @else
                                <div class="bg-light text-secondary rounded d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                                    <i class="fas fa-image"></i>
                                </div>
                            @endif
                        </td>
                        <td class="align-middle px-4 fw-medium text-dark">{{ $product->name_en }}</td>
                        <td class="align-middle px-4 text-muted">{{ $product->name_ar }}</td>
                        <td class="align-middle px-4">
                            @forelse($product->concerns as $concern)
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle mb-1">{{ $concern->name_ar }}</span>
                            @empty
                                <span class="text-muted small">{{ __('admin.no_concerns') }}</span>
                            @endforelse
                        </td>
                        <td class="align-middle px-4"><strong class="d-block text-success">{{ number_format((float)($product->price_syp ?? $product->price), 2) }} ل.س</strong><small class="text-muted">{{ $product->price_usd !== null ? '$'.number_format((float)$product->price_usd, 2) : '— USD' }}</small></td>
                        <td class="align-middle px-4"><strong class="d-block">{{ number_format((float)($product->cost_syp ?? $product->cost), 2) }} ل.س</strong><small class="text-muted">{{ $product->cost_usd !== null ? '$'.number_format((float)$product->cost_usd, 2) : '— USD' }}</small></td>
                        <td class="align-middle px-4" style="min-width: 190px;">
                            @if(!$product->track_inventory)
                                <span class="badge bg-secondary">{{ __('admin.not_tracked') }}</span>
                            @else
                                @php($stockClass = $product->stock_quantity <= 0 ? 'danger' : ($product->stock_quantity <= $product->low_stock_threshold ? 'warning text-dark' : 'success'))
                                <span class="badge bg-{{ $stockClass }} mb-2">
                                    {{ $product->stock_quantity <= 0 ? __('admin.out_of_stock') : $product->stock_quantity . ' ' . __('admin.units') }}
                                </span>
                                <form action="{{ route('admin.products.stock.update', $product->id) }}" method="POST" class="d-flex gap-1" onclick="event.stopPropagation()">
                                    @csrf
                                    @method('PUT')
                                    <input type="number" min="0" step="1" name="stock_quantity" class="form-control form-control-sm" value="{{ $product->stock_quantity }}" aria-label="{{ __('admin.stock_quantity_for', ['name' => $product->name_en]) }}">
                                    <button class="btn btn-sm btn-outline-primary" type="submit" title="{{ __('admin.save_stock') }}"><i class="fas fa-check"></i></button>
                                </form>
                            @endif
                        </td>
                        <td class="align-middle px-4 text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-outline-info" title="{{ __('admin.view_details') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary" title="{{ __('admin.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline delete-confirm">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('admin.delete') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-box-open fa-3x mb-3 text-light"></i>
                            <p class="mb-0 fs-5">{{ __('admin.no_products_found') }}</p>
                            <small>{{ __('admin.add_first_product') }}</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @if($products->hasPages())<div class="pt-3">{{ $products->links('pagination::bootstrap-5') }}</div>@endif
</section>
@endsection
