import 'package:app/core/localization/app_localizations.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('Hanova is the application name in every supported language', () {
    expect(AppLocalizations.text('app_name', 'en'), 'Hanova');
    expect(AppLocalizations.text('app_name', 'ar'), 'Hanova');
  });
}
