import 'package:intl/intl.dart';
import '../settings/app_settings_cubit.dart';

class CurrencyFormatter {
  static final NumberFormat _format = NumberFormat('#,##0.##', 'en');

  static String syp(num value, {String languageCode = 'ar'}) =>
      languageCode == 'ar'
      ? '${_format.format(value)} ل.س جديدة'
      : '${_format.format(value)} new SYP';

  static String dual(
    num sypValue,
    num? usdValue, {
    String languageCode = 'ar',
  }) {
    final sypPrice = syp(sypValue, languageCode: languageCode);
    if (usdValue == null) return sypPrice;
    return '$sypPrice  •  \$${_format.format(usdValue)} USD';
  }

  static String display(num value, AppSettingsState settings) {
    return syp(value, languageCode: settings.locale.languageCode);
  }
}
