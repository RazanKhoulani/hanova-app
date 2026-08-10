import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class ApiInterceptor extends Interceptor {
  final FlutterSecureStorage storage;
  String? _token;
  String? _locale;
  bool _tokenLoaded = false;
  bool _localeLoaded = false;
  int _sessionVersion = 0;

  ApiInterceptor(this.storage);

  int get sessionVersion => _sessionVersion;

  void setToken(String? token) {
    if (!_tokenLoaded || _token != token) {
      _sessionVersion++;
    }
    _token = token;
    _tokenLoaded = true;
  }

  void clearToken() {
    _sessionVersion++;
    _token = null;
    _tokenLoaded = true;
  }

  void setLocale(String locale) {
    _locale = locale;
    _localeLoaded = true;
  }

  @override
  void onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    if (!_tokenLoaded) {
      final storedToken = await storage.read(key: 'access_token');
      if (_token != storedToken) {
        _sessionVersion++;
      }
      _token = storedToken;
      _tokenLoaded = true;
    }

    if (!_localeLoaded) {
      _locale = await storage.read(key: 'app_locale') ?? 'ar';
      _localeLoaded = true;
    }

    options.headers['Accept'] = 'application/json';
    if (options.data is FormData) {
      options.headers.remove('Content-Type');
    } else {
      options.headers['Content-Type'] = 'application/json';
    }
    options.headers['Accept-Language'] = _locale ?? 'ar';

    if (_token != null) {
      options.headers['Authorization'] = 'Bearer $_token';
    }

    return handler.next(options);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    if (err.response?.statusCode == 401) {
      // Handle Unauthorized (e.g., Clear token and navigate to login)
      clearToken();
      storage.delete(key: 'access_token');
    }
    return handler.next(err);
  }
}
