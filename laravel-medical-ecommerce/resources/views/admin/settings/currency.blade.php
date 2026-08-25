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

    <form method="POST" action="{{ route('admin.settings.currency.update') }}" class="card shadow-sm border-0">
        @csrf
        @method('PUT')

        <div class="card-body p-4">
            <div class="row g-4">
            <div class="col-md-6">
                <label for="syp_old_per_new">{{ __('admin.old_syp_per_new_syp') }}</label>
                <input
                    id="syp_old_per_new"
                    name="syp_old_per_new"
                    class="form-control"
                    type="number"
                    min="0.0001"
                    step="any"
                    value="{{ old('syp_old_per_new', $settings['syp_old_per_new']) }}"
                    required
                >
                <small class="form-text text-muted">{{ __('admin.old_syp_per_new_syp_hint') }}</small>
            </div>

            <div class="col-md-6">
                <label for="syp_old_per_usd">{{ __('admin.old_syp_per_usd') }}</label>
                <input
                    id="syp_old_per_usd"
                    name="syp_old_per_usd"
                    class="form-control"
                    type="number"
                    min="0.0001"
                    step="any"
                    value="{{ old('syp_old_per_usd', $settings['syp_old_per_usd']) }}"
                    required
                >
                <small class="form-text text-muted">{{ __('admin.old_syp_per_usd_hint') }}</small>
            </div>
            </div>

        <div class="form-check form-switch mt-4">
            <input
                id="show_dual_syp"
                name="show_dual_syp"
                class="form-check-input"
                type="checkbox"
                value="1"
                @checked(old('show_dual_syp', $settings['show_dual_syp']) === '1')
            >
            <label class="form-check-label" for="show_dual_syp">{{ __('admin.show_dual_syp') }}</label>
        </div>

        <hr class="my-4">
        <h2 class="h5 mb-3">{{ __('admin.review_rewards') }}</h2>
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
        <h2 class="h5 mb-3">{{ __('admin.site_content') }}</h2>
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
