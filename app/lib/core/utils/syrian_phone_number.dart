class SyrianPhoneNumber {
  const SyrianPhoneNumber._();

  static String? tryInternational(
    String value, {
    String callingCode = '+963',
  }) {
    final raw = value.trim();
    final selectedCode = callingCode.replaceAll(RegExp(r'\D'), '');
    var digits = _digits(raw);
    final explicitlyInternational = raw.startsWith('+') || raw.startsWith('00');

    if (digits.startsWith('00')) digits = digits.substring(2);
    if (!explicitlyInternational && !digits.startsWith(selectedCode)) {
      if (selectedCode == '963' && RegExp(r'^09\d{8}$').hasMatch(digits)) {
        digits = digits.substring(1);
      } else if (digits.startsWith('0')) {
        digits = digits.substring(1);
      }
      digits = '$selectedCode$digits';
    }

    return RegExp(r'^[1-9]\d{7,14}$').hasMatch(digits) ? '+$digits' : null;
  }

  static String international(String value) {
    final result = tryInternational(value);
    if (result == null) {
      throw const FormatException('Enter a valid international phone number.');
    }
    return result;
  }

  static String display(String value) {
    final raw = value.trim();
    final digits = _digits(raw);
    if (raw.startsWith('+') || digits.startsWith('963') || digits.length >= 10) {
      return RegExp(r'^[1-9]\d{7,14}$').hasMatch(digits) ? '+$digits' : value;
    }
    return tryInternational(value) ?? value;
  }

  static String _digits(String value) {
    return value
        .trim()
        .replaceAllMapped(
          RegExp('[٠-٩]'),
          (match) => '${match.group(0)!.codeUnitAt(0) - '٠'.codeUnitAt(0)}',
        )
        .replaceAll(RegExp(r'\D'), '');
  }
}
