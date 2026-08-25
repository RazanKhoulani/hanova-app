import 'package:intl/intl.dart';
import '../settings/app_settings_cubit.dart';

class CurrencyFormatter {
  static final NumberFormat _format = NumberFormat('#,##0.##', 'en');

  static String syp(num value) => '${_format.format(value)} ل.س';

  static String display(num value, AppSettingsState settings) {
    final primary = switch (settings.currency) {
      DisplayCurrency.sypOld => _oldSyp(value),
      DisplayCurrency.sypNew =>
        _converted(value, settings.sypOldPerNew, 'ل.س جديد') ?? _oldSyp(value),
      DisplayCurrency.usd =>
        _converted(value, settings.sypOldPerUsd, r'$') ?? _oldSyp(value),
    };

    if (!settings.showDualSyp || settings.currency == DisplayCurrency.usd) {
      return primary;
    }

    final secondary = settings.currency == DisplayCurrency.sypOld
        ? _converted(value, settings.sypOldPerNew, 'ل.س جديد')
        : _oldSyp(value);

    return secondary == null ? primary : '$primary  |  $secondary';
  }

  static String _oldSyp(num value) => '${_format.format(value)} ل.س قديم';

  static String? _converted(num value, double rate, String suffix) {
    if (rate <= 0) return null;
    return '${_format.format(value / rate)} $suffix';
  }
}
