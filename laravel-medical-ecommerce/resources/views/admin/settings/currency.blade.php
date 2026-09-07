@extends('admin.layout.app')

@section('title', __('admin.currency_settings'))

@section('content')
    <div class="page-header">
        <div>
            <p class="eyebrow">{{ __('admin.system') }}</p>
            <h1>{{ __('admin.currency_settings') }}</h1>
            <p>{{ __('admin.currency_settings_hint') }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.currency.update') }}" class="panel-card settings-shell">
        @csrf
        @method('PUT')

        <div class="card-body p-4">
            <div class="settings-section-label">{{ __('admin.product_pricing_model') }}</div>
            <div class="alert alert-info border-0 mb-0 d-flex gap-3 align-items-start"><i class="fas fa-circle-info mt-1"></i><div><strong>{{ __('admin.independent_dual_prices') }}</strong><p class="mb-2 mt-1">{{ __('admin.independent_prices_hint') }}</p><a href="{{ route('admin.products.create') }}" class="btn btn-sm btn-outline-primary">{{ __('admin.add_product') }}</a></div></div>

        <hr class="my-4">
        <div class="settings-section-label">{{ __('admin.delivery_fees') }}</div>
        <p class="text-muted small">{{ __('admin.delivery_fees_hint') }}</p>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>{{ __('admin.delivery_area') }}</th><th>{{ __('admin.fee_syp') }}</th><th>{{ __('admin.fee_usd') }}</th><th>{{ __('admin.active') }}</th></tr></thead>
                <tbody>
                    @forelse($deliveryAreas as $area)
                        <tr>
                            <td><strong>{{ app()->getLocale() === 'ar' ? $area->name_ar : $area->name_en }}</strong></td>
                            <td><input name="delivery_areas[{{ $area->id }}][fee]" type="number" min="0" step="0.01" class="form-control" value="{{ old("delivery_areas.{$area->id}.fee", $area->fee) }}" required></td>
                            <td><input name="delivery_areas[{{ $area->id }}][fee_usd]" type="number" min="0" step="0.01" class="form-control" value="{{ old("delivery_areas.{$area->id}.fee_usd", $area->fee_usd) }}"></td>
                            <td><input type="hidden" name="delivery_areas[{{ $area->id }}][is_active]" value="0"><input class="form-check-input" name="delivery_areas[{{ $area->id }}][is_active]" type="checkbox" value="1" @checked(old("delivery_areas.{$area->id}.is_active", $area->is_active))></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted text-center py-4">{{ __('admin.no_delivery_areas') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <hr class="my-4">
        <div class="settings-section-label">{{ __('admin.review_rewards') }}</div>
        <p class="text-muted small">{{ __('admin.review_rewards_hint') }}</p>
        <div class="row g-4">
            <div class="col-md-6">
                <label for="review_reward_percentage">{{ __('admin.review_reward_percentage') }}</label>
                <input
                    id="review_reward_percentage"
                    name="review_reward_percentage"
                    class="form-control"
                    type="number"
                    min="0"
                    max="100"
                    step="0.01"
                    value="{{ old('review_reward_percentage', $settings['review_reward_percentage']) }}"
                    required
                >
            </div>
            <div class="col-md-6">
                <label for="review_reward_expiry_days">{{ __('admin.review_reward_expiry_days') }}</label>
                <input
                    id="review_reward_expiry_days"
                    name="review_reward_expiry_days"
                    class="form-control"
                    type="number"
                    min="1"
                    max="365"
                    step="1"
                    value="{{ old('review_reward_expiry_days', $settings['review_reward_expiry_days']) }}"
                    required
                >
            </div>
        </div>

        <hr class="my-4">
        <div class="settings-section-label">{{ __('admin.home_consultation_content') }}</div>
        <p class="text-muted small">{{ __('admin.home_consultation_content_hint') }}</p>
        <div class="row g-4">
            <div class="col-md-6">
                <label for="home_consultation_title_ar">{{ __('admin.home_consultation_title_ar') }}</label>
                <input id="home_consultation_title_ar" name="home_consultation_title_ar" class="form-control" dir="rtl" maxlength="120" value="{{ old('home_consultation_title_ar', $settings['home_consultation_title_ar']) }}" required>
            </div>
            <div class="col-md-6">
                <label for="home_consultation_title_en">{{ __('admin.home_consultation_title_en') }}</label>
                <input id="home_consultation_title_en" name="home_consultation_title_en" class="form-control" dir="ltr" maxlength="120" value="{{ old('home_consultation_title_en', $settings['home_consultation_title_en']) }}" required>
            </div>
            <div class="col-md-6">
                <label for="home_consultation_description_ar">{{ __('admin.home_consultation_description_ar') }}</label>
                <textarea id="home_consultation_description_ar" name="home_consultation_description_ar" class="form-control" dir="rtl" rows="3" maxlength="500" required>{{ old('home_consultation_description_ar', $settings['home_consultation_description_ar']) }}</textarea>
            </div>
            <div class="col-md-6">
                <label for="home_consultation_description_en">{{ __('admin.home_consultation_description_en') }}</label>
                <textarea id="home_consultation_description_en" name="home_consultation_description_en" class="form-control" dir="ltr" rows="3" maxlength="500" required>{{ old('home_consultation_description_en', $settings['home_consultation_description_en']) }}</textarea>
            </div>
        </div>

        <hr class="my-4">
        <div class="settings-section-label">{{ __('admin.site_content') }}</div>
        <div class="row g-4">
            <div class="col-md-6">
                <label for="site_about_ar">{{ __('admin.site_about_ar') }}</label>
                <textarea id="site_about_ar" name="site_about_ar" class="form-control" rows="4">{{ old('site_about_ar', $settings['site_about_ar']) }}</textarea>
            </div>
            <div class="col-md-6">
                <label for="site_about_en">{{ __('admin.site_about_en') }}</label>
                <textarea id="site_about_en" name="site_about_en" class="form-control" rows="4">{{ old('site_about_en', $settings['site_about_en']) }}</textarea>
            </div>
            <div class="col-md-6">
                <label for="site_goal_ar">{{ __('admin.site_goal_ar') }}</label>
                <textarea id="site_goal_ar" name="site_goal_ar" class="form-control" rows="4">{{ old('site_goal_ar', $settings['site_goal_ar']) }}</textarea>
            </div>
            <div class="col-md-6">
                <label for="site_goal_en">{{ __('admin.site_goal_en') }}</label>
                <textarea id="site_goal_en" name="site_goal_en" class="form-control" rows="4">{{ old('site_goal_en', $settings['site_goal_en']) }}</textarea>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary">{{ __('admin.save_currency_settings') }}</button>
        </div>
        </div>
    </form>
@endsection
