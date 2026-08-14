import 'package:app/core/utils/syrian_phone_number.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('normalizes supported Syrian mobile formats for the API', () {
    for (final input in [
      '+963945345844',
      '963945345844',
      '00963945345844',
      '0945345844',
      '945345844',
      '+963 945 345 844',
      '٠٩٤٥٣٤٥٨٤٤',
    ]) {
      expect(SyrianPhoneNumber.international(input), '+963945345844');
    }
  });

  test('rejects invalid Syrian mobile numbers', () {
    expect(SyrianPhoneNumber.tryInternational('+96170123456'), isNull);
    expect(SyrianPhoneNumber.tryInternational('123'), isNull);
  });
}
