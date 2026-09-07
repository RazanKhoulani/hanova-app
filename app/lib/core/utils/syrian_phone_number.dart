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

    if (selectedCode.isEmpty) return null;
    if (digits.startsWith('00')) digits = digits.substring(2);

    // Phone autofill may provide the selected calling code together with the
    // local trunk prefix (for example 9630945...). Keep the selected country
    // code once and remove only that redundant national leading zero.
    if (selectedCode == '963' && digits.startsWith(selectedCode)) {
      final nationalNumber = digits.substring(selectedCode.length);
      if (nationalNumber.startsWith('0')) {
        digits = '$selectedCode${nationalNumber.substring(1)}';
      }
    }

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

  /// Restores the leading plus for a canonical E.164 value returned by the API.
  /// This is deliberately separate from [international] because a bare
  /// nine-digit value is ambiguous with a Syrian local number.
  static String storedInternational(String value) {
    var digits = _digits(value);
    if (digits.startsWith('00')) digits = digits.substring(2);
    if (!RegExp(r'^[1-9]\d{7,14}$').hasMatch(digits)) {
      throw const FormatException('Invalid stored international phone number.');
    }
    return '+$digits';
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
