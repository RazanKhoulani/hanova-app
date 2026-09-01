import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:dio/dio.dart';

import 'package:app/core/localization/app_localizations.dart';
import 'package:app/core/network/api_interceptor.dart';
import 'package:app/core/network/dio_client.dart';
import 'package:app/core/settings/app_settings_cubit.dart';
import 'package:app/features/store/presentation/pages/order_confirmation_screen.dart';

void main() {
  testWidgets('order confirmation screen renders localized copy', (
    WidgetTester tester,
  ) async {
    const storage = FlutterSecureStorage();

    await tester.pumpWidget(
      BlocProvider(
        create: (_) {
          final interceptor = ApiInterceptor(storage);
          return AppSettingsCubit(
            storage,
            interceptor,
            DioClient(Dio(), interceptor),
          );
        },
        child: const MaterialApp(home: OrderConfirmationScreen()),
      ),
    );

    expect(
      find.text(AppLocalizations.text('order_confirmed_title', 'ar')),
      findsOneWidget,
    );
    expect(
      find.text(AppLocalizations.text('view_my_orders', 'ar')),
      findsOneWidget,
    );
  });
}
