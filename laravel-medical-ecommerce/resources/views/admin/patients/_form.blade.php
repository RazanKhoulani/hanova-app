<div class="row g-4">
    <div class="col-md-6">
        <label for="patient_name" class="form-label">{{ __('admin.full_name') }} <span class="text-danger">*</span></label>
        <input id="patient_name" name="name" type="text" class="form-control" value="{{ old('name', $patient?->name) }}" required maxlength="150">
    </div>
    <div class="col-md-6">
        <label for="patient_phone" class="form-label">{{ __('admin.phone_number') }} <span class="text-danger">*</span></label>
        <input id="patient_phone" name="phone" type="text" class="form-control" value="{{ old('phone', $patient?->phone) }}" required maxlength="30" dir="ltr">
    </div>
    <div class="col-md-4">
        <label for="age" class="form-label">{{ __('admin.age') }}</label>
        <input id="age" name="age" type="number" class="form-control" value="{{ old('age', $patient?->age) }}" min="0" max="130">
    </div>
    <div class="col-md-8">
        <label for="user_id" class="form-label">{{ __('admin.linked_user') }}</label>
        <select id="user_id" name="user_id" class="form-select">
            <option value="">{{ __('admin.no_linked_user') }}</option>
            @foreach($users as $linkedUser)
                <option value="{{ $linkedUser->id }}" @selected((string) old('user_id', $patient?->user_id) === (string) $linkedUser->id)>
                    {{ $linkedUser->name }} · {{ $linkedUser->phone }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label for="address" class="form-label">{{ __('admin.address') }}</label>
        <textarea id="address" name="address" class="form-control" rows="2" maxlength="1000">{{ old('address', $patient?->address) }}</textarea>
    </div>
    <div class="col-12">
        <label for="notes" class="form-label">{{ __('admin.notes') }}</label>
        <textarea id="notes" name="notes" class="form-control" rows="5" maxlength="5000">{{ old('notes', $patient?->notes) }}</textarea>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 justify-content-end mt-4">
    <a href="{{ $patient ? route('admin.patients.show', $patient->id) : route('admin.patients.index') }}" class="btn btn-outline-secondary">{{ __('admin.cancel') }}</a>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-1"></i> {{ $patient ? __('admin.save_changes') : __('admin.create_patient') }}
    </button>
</div>
