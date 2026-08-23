import 'package:app/core/network/api_error_message.dart';
import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  RequestOptions options({String language = 'ar'}) {
    return RequestOptions(
      path: '/test',
      headers: {'Accept-Language': language},
    );
  }

  test('hides server details for 500 responses', () {
    final error = DioException(
      requestOptions: options(),
      response: Response<dynamic>(
        requestOptions: options(),
        statusCode: 500,
        data: {'message': 'SQLSTATE secret details'},
      ),
    );

    final message = ApiErrorMessage.from(error);

    expect(message, isNot(contains('SQLSTATE')));
    expect(message, contains('\u0627\u0644\u062e\u0627\u062f\u0645'));
  });

  test('translates known appointment validation messages', () {
    final requestOptions = options();
    final error = DioException(
      requestOptions: requestOptions,
      response: Response<dynamic>(
        requestOptions: requestOptions,
        statusCode: 422,
        data: {
          'message': 'Validation failed',
          'errors': {
            'appointment': ['You already have an active appointment.'],
          },
        },
      ),
    );

    expect(
      ApiErrorMessage.from(error),
      contains('\u0645\u0648\u0639\u062f \u0641\u0639\u0627\u0644'),
    );
  });

  test('returns an English connection message when requested', () {
    final error = DioException(
      requestOptions: options(language: 'en'),
      type: DioExceptionType.connectionError,
    );

    expect(ApiErrorMessage.from(error), contains('connect to the server'));
  });

  test('shows a localized credentials error for failed login', () {
    final requestOptions = RequestOptions(
      path: '/auth/login',
      headers: {'Accept-Language': 'ar'},
    );
    final error = DioException(
      requestOptions: requestOptions,
      response: Response<dynamic>(
        requestOptions: requestOptions,
        statusCode: 401,
        data: {'message': 'Invalid credentials'},
      ),
    );

    expect(
      ApiErrorMessage.from(error),
      'رقم الموبايل أو كلمة المرور غير صحيحة.',
    );
  });
}
