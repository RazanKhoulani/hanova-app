import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../../../../core/network/api_interceptor.dart';
import '../../../../core/notifications/push_notification_service.dart';
import '../../domain/repositories/auth_repository.dart';
import '../models/auth_response_model.dart';
import '../models/user_model.dart';
import '../sources/auth_remote_data_source.dart';

class AuthRepositoryImpl implements AuthRepository {
  final AuthRemoteDataSource _remoteDataSource;
  final FlutterSecureStorage _storage;
  final ApiInterceptor _apiInterceptor;
  final PushNotificationService _pushNotifications;

  AuthRepositoryImpl(
    this._remoteDataSource,
    this._storage,
    this._apiInterceptor,
    this._pushNotifications,
  );

  @override
  Future<AuthResponseModel> login(String phone, String password) async {
    final response = await _remoteDataSource.login(phone, password);
    if (response.token != null) {
      await _storage.write(key: 'access_token', value: response.token);
      _apiInterceptor.setToken(response.token);
      await _pushNotifications.syncToken();
    }
    return response;
  }

  @override
  Future<AuthResponseModel> register(
    String name,
    String phone,
    String password,
    String passwordConfirmation,
  ) async {
    return _remoteDataSource.register(
      name,
      phone,
      password,
      passwordConfirmation,
    );
  }

  @override
  Future<AuthResponseModel> verifyRegistrationOtp(
    String phone,
    String otp,
  ) async {
    final response = await _remoteDataSource.verifyRegistrationOtp(phone, otp);
    if (response.token != null) {
      await _storage.write(key: 'access_token', value: response.token);
      _apiInterceptor.setToken(response.token);
      await _pushNotifications.syncToken();
    }
    return response;
  }

  @override
  Future<void> resendRegistrationOtp(String phone) {
    return _remoteDataSource.resendRegistrationOtp(phone);
  }

  @override
  Future<UserModel> updateProfile(UserModel user) {
    return _remoteDataSource.updateProfile(user);
  }

  @override
  Future<void> forgotPassword(String phone) {
    return _remoteDataSource.forgotPassword(phone);
  }

  @override
  Future<UserModel?> getProfile() async {
    final user = await _remoteDataSource.getProfile();
    await _pushNotifications.syncToken();
    return user;
  }

  @override
  Future<void> logout() async {
    await _pushNotifications.removeCurrentToken();
    await _remoteDataSource.logout();
    await _storage.delete(key: 'access_token');
    _apiInterceptor.clearToken();
  }
}
