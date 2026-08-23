import 'package:app/core/widgets/hanova_auth_shell.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  Widget appWithKeyboard({required bool visible}) {
    return MaterialApp(
      home: MediaQuery(
        data: MediaQueryData(
          size: const Size(390, 844),
          viewInsets: EdgeInsets.only(bottom: visible ? 320 : 0),
        ),
        child: const HanovaAuthShell(
          title: 'Welcome',
          subtitle: 'Subtitle',
          showBack: false,
          child: SizedBox(height: 360),
        ),
      ),
    );
  }

  testWidgets('compacts the auth interface when the keyboard opens', (
    tester,
  ) async {
    await tester.pumpWidget(appWithKeyboard(visible: false));
    await tester.pumpAndSettle();

    expect(tester.getSize(find.byType(HanovaBrandMark)).height, 58);
    expect(find.text('Subtitle'), findsOneWidget);

    await tester.pumpWidget(appWithKeyboard(visible: true));
    await tester.pumpAndSettle();

    expect(tester.getSize(find.byType(HanovaBrandMark)).height, 44);
    expect(find.text('Subtitle'), findsNothing);
    expect(tester.takeException(), isNull);
  });
}
