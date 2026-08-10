import 'user_model.dart';

class AuthResponseModel {
  final String? token;
  final UserModel? user;
  final String? message;
  final String? phone;
  final String? otpSimulated;
  final bool requiresOtpVerification;

  AuthResponseModel({
    this.token,
    this.user,
    this.message,
    this.phone,
    this.otpSimulated,
    this.requiresOtpVerification = false,
  });

  factory AuthResponseModel.fromJson(Map<String, dynamic> json) {
    final rawUser = json['user'];
    Map<String, dynamic>? userPayload;
    if (rawUser is Map<String, dynamic>) {
      userPayload = rawUser['data'] is Map<String, dynamic>
          ? rawUser['data'] as Map<String, dynamic>
          : rawUser;
    }

    return AuthResponseModel(
      token: json['token'] ?? json['access_token'],
      user: userPayload != null ? UserModel.fromJson(userPayload) : null,
      message: json['message'],
      phone: json['phone'],
      otpSimulated: json['otp_simulated']?.toString(),
      requiresOtpVerification: json['requires_otp_verification'] == true,
    );
  }
}
