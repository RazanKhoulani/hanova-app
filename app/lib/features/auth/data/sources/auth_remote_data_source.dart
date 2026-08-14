import '../../../../core/network/dio_client.dart';
import '../../../../core/constants/api_constants.dart';
import '../../../../core/utils/syrian_phone_number.dart';
import '../models/auth_response_model.dart';
import '../models/user_model.dart';

class AuthRemoteDataSource {
  final DioClient _dioClient;

  AuthRemoteDataSource(this._dioClient);

  Future<AuthResponseModel> login(String phone, String password) async {
    final response = await _dioClient.post(
      ApiConstants.login,
      data: {
        'phone': SyrianPhoneNumber.international(phone),
        'password': password,
      },
    );
    return AuthResponseModel.fromJson(response.data);
  }

  Future<AuthResponseModel> register(
    String name,
    String phone,
    String password,
    String passwordConfirmation,
  ) async {
    final response = await _dioClient.post(
      ApiConstants.register,
      data: {
        'name': name,
        'phone': SyrianPhoneNumber.international(phone),
        'password': password,
        'password_confirmation': passwordConfirmation,
      },
    );
    return AuthResponseModel.fromJson(response.data);
  }

  Future<AuthResponseModel> verifyRegistrationOtp(
    String phone,
    String otp,
  ) async {
    final response = await _dioClient.post(
      ApiConstants.verifyRegistrationOtp,
      data: {'phone': SyrianPhoneNumber.international(phone), 'otp': otp},
    );
    return AuthResponseModel.fromJson(response.data);
  }

  Future<void> resendRegistrationOtp(String phone) async {
    await _dioClient.post(
      ApiConstants.resendRegistrationOtp,
      data: {'phone': SyrianPhoneNumber.international(phone)},
    );
  }

  Future<UserModel> updateProfile(UserModel user) async {
    final response = await _dioClient.put(
      ApiConstants.updateProfile,
      data: {
        ...user.toJson(),
        'phone': SyrianPhoneNumber.international(user.phone),
      },
    );
    final payload = response.data['data'] ?? response.data;
    return UserModel.fromJson(payload);
  }

  Future<void> forgotPassword(String phone) async {
    await _dioClient.post(
      ApiConstants.forgotPassword,
      data: {'phone': SyrianPhoneNumber.international(phone)},
    );
  }

  Future<UserModel> getProfile() async {
    final response = await _dioClient.get(ApiConstants.profile);
    final payload = response.data['data'] ?? response.data;
    return UserModel.fromJson(payload);
  }

  Future<void> logout() async {
    await _dioClient.post(ApiConstants.logout);
  }
}
