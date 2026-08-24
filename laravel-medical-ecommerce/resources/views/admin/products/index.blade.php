@extends('admin.layout.app')

@section('title', 'Manage Products')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Products Management</h2>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Add Product</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4 py-3 text-secondary">ID</th>
                        <th class="border-0 px-4 py-3 text-secondary">Image</th>
                        <th class="border-0 px-4 py-3 text-secondary">Name (EN)</th>
                        <th class="border-0 px-4 py-3 text-secondary">Name (AR)</th>
                        <th class="border-0 px-4 py-3 text-secondary">Concerns</th>
                        <th class="border-0 px-4 py-3 text-secondary">Price</th>
                        <th class="border-0 px-4 py-3 text-secondary">Cost</th>
                        <th class="border-0 px-4 py-3 text-secondary text-end">Actions</th>
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
                                <span class="text-muted small">No concerns</span>
                            @endforelse
                        </td>
                        <td class="align-middle px-4"><span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1">{{ number_format($product->price, 2) }} ل.س</span></td>
                        <td class="align-middle px-4"><span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle px-2 py-1">{{ number_format($product->cost, 2) }} ل.س</span></td>
                        <td class="align-middle px-4 text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Product">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline delete-confirm">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Product">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-box-open fa-3x mb-3 text-light"></i>
                            <p class="mb-0 fs-5">No products found</p>
                            <small>Click "Add Product" to create your first item.</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($products->hasPages())
    <div class="card-footer bg-white border-top-0 pt-4 pb-3 px-4">
        {{ $products->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
