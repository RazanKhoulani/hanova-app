import 'package:intl/intl.dart';
import '../settings/app_settings_cubit.dart';

class CurrencyFormatter {
  static final NumberFormat _format = NumberFormat('#,##0.##', 'en');

  static String syp(num value) => '${_format.format(value)} ل.س';

  static String dual(num sypValue, num? usdValue) {
    final sypPrice = syp(sypValue);
    if (usdValue == null) return sypPrice;
    return '$sypPrice  •  \$${_format.format(usdValue)}';
  }

  static String display(num value, AppSettingsState settings) {
    // Transaction totals are stored in Syrian pounds. USD product prices are
    // entered independently and must never be derived from an exchange rate.
    return syp(value);
  }
}
