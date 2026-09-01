import 'package:dio/dio.dart';

class ApiErrorMessage {
  const ApiErrorMessage._();

  static String from(Object error) {
    if (error is! DioException) {
      return error.toString().replaceFirst('Exception: ', '');
    }

    final isEnglish = error.requestOptions.headers['Accept-Language']
        .toString()
        .toLowerCase()
        .startsWith('en');
    final statusCode = error.response?.statusCode;

    if (statusCode == 401 && _isLoginRequest(error.requestOptions.path)) {
      return isEnglish
          ? 'The mobile number or password is incorrect.'
          : 'رقم الموبايل أو كلمة المرور غير صحيحة.';
    }
    if (statusCode == 401) {
      return isEnglish
          ? 'Your session has expired. Please log in again.'
          : '\u0627\u0646\u062a\u0647\u062a \u062c\u0644\u0633\u0629 \u0627\u0644\u062f\u062e\u0648\u0644. \u064a\u0631\u062c\u0649 \u062a\u0633\u062c\u064a\u0644 \u0627\u0644\u062f\u062e\u0648\u0644 \u0645\u0631\u0629 \u0623\u062e\u0631\u0649.';
    }
    if (statusCode == 403) {
      return isEnglish
          ? 'You do not have permission to perform this action.'
          : '\u0644\u064a\u0633 \u0644\u062f\u064a\u0643 \u0635\u0644\u0627\u062d\u064a\u0629 \u0644\u062a\u0646\u0641\u064a\u0630 \u0647\u0630\u0627 \u0627\u0644\u0625\u062c\u0631\u0627\u0621.';
    }
    if (statusCode == 404) {
      return isEnglish
          ? 'The requested data was not found.'
          : '\u0644\u0645 \u064a\u062a\u0645 \u0627\u0644\u0639\u062b\u0648\u0631 \u0639\u0644\u0649 \u0627\u0644\u0628\u064a\u0627\u0646\u0627\u062a \u0627\u0644\u0645\u0637\u0644\u0648\u0628\u0629.';
    }
    if (statusCode != null && statusCode >= 500) {
      return isEnglish
          ? 'The server is temporarily unavailable. Please try again.'
          : '\u0627\u0644\u062e\u0627\u062f\u0645 \u063a\u064a\u0631 \u0645\u062a\u0627\u062d \u0645\u0624\u0642\u062a\u0627\u064b. \u062d\u0627\u0648\u0644\u064a \u0645\u0631\u0629 \u0623\u062e\u0631\u0649.';
    }

    final serverMessage = _extractServerMessage(error.response?.data);
    if (serverMessage != null) {
      return _friendlyValidationMessage(serverMessage, isEnglish);
    }

    switch (error.type) {
      case DioExceptionType.connectionTimeout:
      case DioExceptionType.sendTimeout:
      case DioExceptionType.receiveTimeout:
        return isEnglish
            ? 'The request took too long. Please try again.'
            : '\u0627\u0633\u062a\u063a\u0631\u0642 \u0627\u0644\u0637\u0644\u0628 \u0648\u0642\u062a\u0627\u064b \u0637\u0648\u064a\u0644\u0627\u064b. \u062d\u0627\u0648\u0644\u064a \u0645\u0631\u0629 \u0623\u062e\u0631\u0649.';
      case DioExceptionType.connectionError:
        return isEnglish
            ? 'Could not connect to the server. Check your internet connection.'
            : '\u062a\u0639\u0630\u0631 \u0627\u0644\u0627\u062a\u0635\u0627\u0644 \u0628\u0627\u0644\u062e\u0627\u062f\u0645. \u062a\u0623\u0643\u062f\u064a \u0645\u0646 \u0627\u0644\u0625\u0646\u062a\u0631\u0646\u062a.';
      default:
        return isEnglish
            ? 'The request could not be completed. Please try again.'
            : '\u062a\u0639\u0630\u0631 \u0625\u0643\u0645\u0627\u0644 \u0627\u0644\u0637\u0644\u0628. \u062d\u0627\u0648\u0644\u064a \u0645\u0631\u0629 \u0623\u062e\u0631\u0649.';
    }
  }

  static bool _isLoginRequest(String path) {
    final normalized = path.toLowerCase().split('?').first;
    return normalized.endsWith('/auth/login');
  }

  static String? _extractServerMessage(dynamic data) {
    if (data is! Map) return null;

    final errors = data['errors'];
    if (errors is Map) {
      for (final value in errors.values) {
        if (value is List && value.isNotEmpty) return value.first.toString();
        if (value != null) return value.toString();
      }
    }

    final message = data['message']?.toString().trim();
    return message == null || message.isEmpty ? null : message;
  }

  static String _friendlyValidationMessage(String message, bool isEnglish) {
    if (isEnglish) return message;

    final normalized = message.toLowerCase();
    if (normalized.contains('active appointment')) {
      return '\u0644\u062f\u064a\u0643 \u0645\u0648\u0639\u062f \u0641\u0639\u0627\u0644 \u0628\u0627\u0644\u0641\u0639\u0644. \u064a\u0645\u0643\u0646\u0643 \u0627\u0644\u062d\u062c\u0632 \u0628\u0639\u062f \u0627\u0646\u062a\u0647\u0627\u0626\u0647 \u0623\u0648 \u0625\u0644\u063a\u0627\u0626\u0647.';
    }
    if (normalized.contains('no longer available')) {
      return '\u0627\u0644\u0648\u0642\u062a \u0627\u0644\u0645\u062d\u062f\u062f \u0644\u0645 \u064a\u0639\u062f \u0645\u062a\u0627\u062d\u0627\u064b. \u0627\u062e\u062a\u0627\u0631\u064a \u0648\u0642\u062a\u0627\u064b \u0622\u062e\u0631.';
    }
    if (normalized.contains('after or equal to today')) {
      return '\u0644\u0627 \u064a\u0645\u0643\u0646 \u0627\u062e\u062a\u064a\u0627\u0631 \u062a\u0627\u0631\u064a\u062e \u0645\u0636\u0649.';
    }
    if (normalized.contains('cart is empty')) {
      return '\u0627\u0644\u0633\u0644\u0629 \u0641\u0627\u0631\u063a\u0629.';
    }
    if (normalized.contains('delivery_area_id')) {
      return '\u064a\u0631\u062c\u0649 \u0627\u062e\u062a\u064a\u0627\u0631 \u0645\u0646\u0637\u0642\u0629 \u062a\u0648\u0635\u064a\u0644 \u0645\u062a\u0627\u062d\u0629.';
    }
    if (normalized.contains('file field must be a file')) {
      return '\u0646\u0648\u0639 \u0627\u0644\u0645\u0644\u0641 \u063a\u064a\u0631 \u0645\u0633\u0645\u0648\u062d. اختاري صورة JPG أو PNG أو تسجيلاً صوتياً أو ملف PDF.';
    }

    return message.replaceFirst('Exception: ', '');
  }
}
