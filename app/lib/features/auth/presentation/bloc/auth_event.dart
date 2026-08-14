abstract class AuthEvent {}

class AuthLoginRequested extends AuthEvent {
  final String phone;
  final String password;
  AuthLoginRequested(this.phone, this.password);
}

class AuthRegisterRequested extends AuthEvent {
  final String name;
  final String phone;
  final String password;
  final String passwordConfirmation;
  AuthRegisterRequested(
    this.name,
    this.phone,
    this.password,
    this.passwordConfirmation,
  );
}

class AuthVerifyOtpRequested extends AuthEvent {
  final String phone;
  final String otp;
  AuthVerifyOtpRequested(this.phone, this.otp);
}

class AuthResendRegistrationOtpRequested extends AuthEvent {
  final String phone;
  AuthResendRegistrationOtpRequested(this.phone);
}

class AuthLogoutRequested extends AuthEvent {}

class AuthCheckStatus extends AuthEvent {}

class AuthUpdateProfileRequested extends AuthEvent {
  final String name;
  final String email;
  AuthUpdateProfileRequested(this.name, this.email);
}

class AuthForgotPasswordRequested extends AuthEvent {
  final String phone;
  AuthForgotPasswordRequested(this.phone);
}
