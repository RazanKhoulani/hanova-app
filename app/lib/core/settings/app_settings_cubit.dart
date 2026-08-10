import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../network/api_interceptor.dart';

class AppSettingsState {
  final Locale locale;

  const AppSettingsState({required this.locale});

  bool get isArabic => locale.languageCode == 'ar';
  String get languageLabel =>
      isArabic ? '\u0627\u0644\u0639\u0631\u0628\u064a\u0629' : 'English';
}

class AppSettingsCubit extends Cubit<AppSettingsState> {
  final FlutterSecureStorage _storage;
  final ApiInterceptor _apiInterceptor;

  AppSettingsCubit(this._storage, this._apiInterceptor)
    : super(const AppSettingsState(locale: Locale('ar')));

  Future<void> load() async {
    final languageCode = await _storage.read(key: 'app_locale') ?? 'ar';
    _apiInterceptor.setLocale(languageCode);
    emit(AppSettingsState(locale: Locale(languageCode)));
  }

  Future<void> setLanguage(String languageCode) async {
    await _storage.write(key: 'app_locale', value: languageCode);
    _apiInterceptor.setLocale(languageCode);
    emit(AppSettingsState(locale: Locale(languageCode)));
  }
}
