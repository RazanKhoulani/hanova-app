import '../../data/models/user_model.dart';

abstract class AuthState {}

class AuthInitial extends AuthState {}

class AuthLoading extends AuthState {}

class AuthAuthenticated extends AuthState {
  final UserModel user;
  AuthAuthenticated(this.user);
}

class AuthOtpRequired extends AuthState {
  final String phone;
  final String? otpSimulated;
  final String? message;

  AuthOtpRequired({required this.phone, this.otpSimulated, this.message});
}

class AuthUnauthenticated extends AuthState {}

class AuthActionSuccess extends AuthState {
  final String message;
  AuthActionSuccess(this.message);
}

class AuthFailure extends AuthState {
  final String message;
  AuthFailure(this.message);
}
