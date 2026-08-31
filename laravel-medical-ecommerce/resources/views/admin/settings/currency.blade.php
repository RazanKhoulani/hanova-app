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
