class SyrianPhoneNumber {
  const SyrianPhoneNumber._();

  static String? tryInternational(String value) {
    final normalized = _normalizeDigits(value);
    return RegExp(r'^9639\d{8}$').hasMatch(normalized) ? '+$normalized' : null;
  }

  static String international(String value) {
    final result = tryInternational(value);
    if (result == null) {
      throw const FormatException('Enter a valid Syrian mobile number.');
    }
    return result;
  }

  static String display(String value) {
    return tryInternational(value) ?? value;
  }

  static String _normalizeDigits(String value) {
    var digits = value
        .trim()
        .replaceAllMapped(
          RegExp('[٠-٩]'),
          (match) => '${match.group(0)!.codeUnitAt(0) - '٠'.codeUnitAt(0)}',
        )
        .replaceAll(RegExp(r'\D'), '');

    if (digits.startsWith('00963')) {
      digits = digits.substring(2);
    }
    if (RegExp(r'^09\d{8}$').hasMatch(digits)) {
      digits = '963${digits.substring(1)}';
    } else if (RegExp(r'^9\d{8}$').hasMatch(digits)) {
      digits = '963$digits';
    }

    return digits;
  }
}
