import 'package:intl/intl.dart';

class CurrencyFormatter {
  static final NumberFormat _format = NumberFormat('#,##0.##', 'en');

  static String syp(num value) => '${_format.format(value)} ل.س';
}
