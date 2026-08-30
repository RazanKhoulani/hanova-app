import 'package:intl/intl.dart';
import '../settings/app_settings_cubit.dart';

class CurrencyFormatter {
  static final NumberFormat _format = NumberFormat('#,##0.##', 'en');

  static String syp(num value) => '${_format.format(value)} ل.س';

  static String display(num value, AppSettingsState settings) {
    final primary = switch (settings.currency) {
      DisplayCurrency.sypOld =>
        _converted(value, settings.sypOldPerNew, 'ل.س جديدة') ?? _unavailable(),
      DisplayCurrency.sypNew =>
        _converted(value, settings.sypOldPerNew, 'ل.س جديدة') ?? _unavailable(),
      DisplayCurrency.usd =>
        _converted(value, settings.sypOldPerUsd, r'$') ?? _unavailable(),
    };

    return primary;
  }

  static String _unavailable() => '—';

  static String? _converted(num value, double rate, String suffix) {
    if (rate <= 0) return null;
    return '${_format.format(value / rate)} $suffix';
  }
}
