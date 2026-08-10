import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import '../constants/api_constants.dart';
import 'api_interceptor.dart';

class DioClient {
  final Dio _dio;

  DioClient(Dio dio, ApiInterceptor interceptor) : _dio = dio {
    _dio.options = BaseOptions(
      baseUrl: ApiConstants.baseUrl,
      connectTimeout: const Duration(seconds: 30),
      receiveTimeout: const Duration(seconds: 30),
      responseType: ResponseType.json,
    );
    _dio.interceptors.add(interceptor);
    if (kDebugMode) {
      _dio.interceptors.add(
        LogInterceptor(requestBody: false, responseBody: false),
      );
    }
  }

  Dio get dio => _dio;

  Future<Response> get(
    String path, {
    Map<String, dynamic>? queryParameters,
  }) async {
    try {
      return await _dio.get(path, queryParameters: queryParameters);
    } on DioException catch (error) {
      if (!_shouldRetryGet(error)) rethrow;

      await Future<void>.delayed(const Duration(milliseconds: 350));
      return _dio.get(path, queryParameters: queryParameters);
    }
  }

  Future<Response> post(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
  }) {
    return _dio.post(path, data: data, queryParameters: queryParameters);
  }

  Future<Response> put(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
  }) {
    return _dio.put(path, data: data, queryParameters: queryParameters);
  }

  Future<Response> delete(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
  }) {
    return _dio.delete(path, data: data, queryParameters: queryParameters);
  }

  bool _shouldRetryGet(DioException error) {
    final statusCode = error.response?.statusCode;
    return error.type == DioExceptionType.connectionError ||
        error.type == DioExceptionType.connectionTimeout ||
        (statusCode != null && [502, 503, 504].contains(statusCode));
  }
}
