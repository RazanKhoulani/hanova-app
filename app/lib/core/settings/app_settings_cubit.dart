import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../network/api_interceptor.dart';
import '../network/dio_client.dart';

enum DisplayCurrency { sypOld, sypNew, usd }

class AppSettingsState {
  final Locale locale;
  final DisplayCurrency currency;
  final double sypOldPerNew;
  final double sypOldPerUsd;
  final bool showDualSyp;

  const AppSettingsState({
    required this.locale,
    this.currency = DisplayCurrency.sypNew,
    this.sypOldPerNew = 0,
    this.sypOldPerUsd = 0,
    this.showDualSyp = false,
  });

  bool get isArabic => locale.languageCode == 'ar';
  String get languageLabel =>
      isArabic ? '\u0627\u0644\u0639\u0631\u0628\u064a\u0629' : 'English';

  String get currencyLabel => switch (currency) {
    DisplayCurrency.sypOld => isArabic ? 'ل.س قديمة' : 'Old SYP',
    DisplayCurrency.sypNew => isArabic ? 'ل.س جديدة' : 'New SYP',
    DisplayCurrency.usd => 'USD',
  };

  AppSettingsState copyWith({
    Locale? locale,
    DisplayCurrency? currency,
    double? sypOldPerNew,
    double? sypOldPerUsd,
    bool? showDualSyp,
  }) {
    return AppSettingsState(
      locale: locale ?? this.locale,
      currency: currency ?? this.currency,
      sypOldPerNew: sypOldPerNew ?? this.sypOldPerNew,
      sypOldPerUsd: sypOldPerUsd ?? this.sypOldPerUsd,
      showDualSyp: showDualSyp ?? this.showDualSyp,
    );
  }
}

class AppSettingsCubit extends Cubit<AppSettingsState> {
  final FlutterSecureStorage _storage;
  final ApiInterceptor _apiInterceptor;
  final DioClient _dioClient;

  AppSettingsCubit(this._storage, this._apiInterceptor, this._dioClient)
    : super(const AppSettingsState(locale: Locale('ar')));

  Future<void> load() async {
    final languageCode = await _storage.read(key: 'app_locale') ?? 'ar';
    _apiInterceptor.setLocale(languageCode);
    final pricing = await _loadPricing();
    emit(
      AppSettingsState(
        locale: Locale(languageCode),
        currency: pricing.$4,
        sypOldPerNew: pricing.$1,
        sypOldPerUsd: pricing.$2,
        showDualSyp: pricing.$3,
      ),
    );
  }

  Future<void> setLanguage(String languageCode) async {
    await _storage.write(key: 'app_locale', value: languageCode);
    _apiInterceptor.setLocale(languageCode);
    emit(state.copyWith(locale: Locale(languageCode)));
  }

  Future<(double, double, bool, DisplayCurrency)> _loadPricing() async {
    try {
      final response = await _dioClient.get('/app-settings');
      final root = response.data;
      final data = root is Map ? root['data'] : null;
      if (data is! Map) {
        return (0.0, 0.0, false, DisplayCurrency.sypNew);
      }

      return (
        _asDouble(data['syp_old_per_new']),
        _asDouble(data['syp_old_per_usd']),
        data['show_dual_syp'] == true ||
            data['show_dual_syp'] == 1 ||
            data['show_dual_syp'] == '1',
        _currencyFromServer(data['display_currency']),
      );
    } catch (_) {
      return (0.0, 0.0, false, DisplayCurrency.sypNew);
    }
  }

  DisplayCurrency _currencyFromServer(dynamic value) => switch (value) {
    'syp_new' => DisplayCurrency.sypNew,
    'usd' => DisplayCurrency.usd,
    _ => DisplayCurrency.sypNew,
  };

  double _asDouble(dynamic value) {
    if (value is num) return value.toDouble();
    return double.tryParse(value?.toString() ?? '') ?? 0;
  }
}
