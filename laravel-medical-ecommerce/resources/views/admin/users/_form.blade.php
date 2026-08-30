<div class="row g-4">
    <div class="col-md-6">
        <label for="name" class="form-label">{{ __('admin.full_name') }} <span class="text-danger">*</span></label>
        <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $user?->name) }}" required maxlength="150">
    </div>
    <div class="col-md-6">
        <label for="phone" class="form-label">{{ __('admin.phone_number') }} <span class="text-danger">*</span></label>
        <input id="phone" name="phone" type="text" class="form-control" value="{{ old('phone', $user?->phone) }}" required maxlength="30" dir="ltr">
    </div>
    <div class="col-md-6">
        <label for="email" class="form-label">{{ __('admin.email') }}</label>
        <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $user?->email) }}" maxlength="255" dir="ltr">
    </div>
    <div class="col-md-6">
        <label for="role" class="form-label">{{ __('admin.role') }} <span class="text-danger">*</span></label>
        <select id="role" name="role" class="form-select" required>
            @foreach(['user', 'doctor', 'admin', 'delivery'] as $role)
                <option value="{{ $role }}" @selected(old('role', $user?->getRoleNames()->first() ?? 'user') === $role)>{{ __('admin.role_' . $role) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label for="password" class="form-label">{{ __('admin.password') }} @if(!$user)<span class="text-danger">*</span>@endif</label>
        <input id="password" name="password" type="password" class="form-control" minlength="6" @required(!$user)>
        <div class="form-text">{{ $user ? __('admin.password_change_hint') : __('admin.password_create_hint') }}</div>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 justify-content-end mt-4">
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">{{ __('admin.cancel') }}</a>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-1"></i> {{ $user ? __('admin.save_changes') : __('admin.create_user') }}
    </button>
</div>
