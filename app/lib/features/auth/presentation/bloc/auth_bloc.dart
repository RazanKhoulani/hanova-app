import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../../core/network/api_error_message.dart';
import '../../domain/repositories/auth_repository.dart';
import 'auth_event.dart';
import 'auth_state.dart';
import '../../data/models/user_model.dart';

class AuthBloc extends Bloc<AuthEvent, AuthState> {
  final AuthRepository _authRepository;

  AuthBloc(this._authRepository) : super(AuthInitial()) {
    on<AuthCheckStatus>(_onCheckStatus);
    on<AuthLoginRequested>(_onLoginRequested);
    on<AuthRegisterRequested>(_onRegisterRequested);
    on<AuthVerifyOtpRequested>(_onVerifyOtpRequested);
    on<AuthResendRegistrationOtpRequested>(_onResendRegistrationOtpRequested);
    on<AuthLogoutRequested>(_onLogoutRequested);
    on<AuthUpdateProfileRequested>(_onUpdateProfileRequested);
    on<AuthForgotPasswordRequested>(_onForgotPasswordRequested);
  }

  Future<void> _onCheckStatus(
    AuthCheckStatus event,
    Emitter<AuthState> emit,
  ) async {
    emit(AuthLoading());
    try {
      final user = await _authRepository.getProfile();
      if (user != null) {
        emit(AuthAuthenticated(user));
      } else {
        emit(AuthUnauthenticated());
      }
    } catch (e) {
      emit(AuthUnauthenticated());
    }
  }

  Future<void> _onLoginRequested(
    AuthLoginRequested event,
    Emitter<AuthState> emit,
  ) async {
    emit(AuthLoading());
    try {
      final response = await _authRepository.login(event.phone, event.password);
      if (response.user == null) {
        emit(AuthFailure('Login failed, please try again.'));
        return;
      }

      emit(AuthAuthenticated(response.user!));
    } catch (e) {
      emit(AuthFailure(ApiErrorMessage.from(e)));
    }
  }

  Future<void> _onRegisterRequested(
    AuthRegisterRequested event,
    Emitter<AuthState> emit,
  ) async {
    emit(AuthLoading());
    try {
      final response = await _authRepository.register(
        event.name,
        event.phone,
        event.password,
        event.passwordConfirmation,
      );
      emit(
        AuthOtpRequired(
          phone: response.phone ?? event.phone,
          otpSimulated: response.otpSimulated,
          message: response.message,
        ),
      );
    } catch (e) {
      emit(AuthFailure(ApiErrorMessage.from(e)));
    }
  }

  Future<void> _onVerifyOtpRequested(
    AuthVerifyOtpRequested event,
    Emitter<AuthState> emit,
  ) async {
    emit(AuthLoading());
    try {
      final response = await _authRepository.verifyRegistrationOtp(
        event.phone,
        event.otp,
      );
      if (response.user == null) {
        emit(AuthFailure('OTP verification failed.'));
        return;
      }

      emit(AuthAuthenticated(response.user!));
    } catch (e) {
      emit(AuthFailure(ApiErrorMessage.from(e)));
    }
  }

  Future<void> _onResendRegistrationOtpRequested(
    AuthResendRegistrationOtpRequested event,
    Emitter<AuthState> emit,
  ) async {
    emit(AuthLoading());
    try {
      await _authRepository.resendRegistrationOtp(event.phone);
      emit(AuthActionSuccess('A new code was sent by WhatsApp.'));
    } catch (e) {
      emit(AuthFailure(ApiErrorMessage.from(e)));
    }
  }

  Future<void> _onLogoutRequested(
    AuthLogoutRequested event,
    Emitter<AuthState> emit,
  ) async {
    emit(AuthLoading());
    try {
      await _authRepository.logout();
      emit(AuthUnauthenticated());
    } catch (e) {
      emit(AuthFailure(ApiErrorMessage.from(e)));
    }
  }

  Future<void> _onUpdateProfileRequested(
    AuthUpdateProfileRequested event,
    Emitter<AuthState> emit,
  ) async {
    final currentState = state;
    if (currentState is AuthAuthenticated) {
      emit(AuthLoading());
      try {
        final updatedUser = await _authRepository.updateProfile(
          UserModel(
            id: currentState.user.id,
            name: event.name,
            phone: currentState.user.phone,
            email: event.email,
          ),
        );
        emit(AuthAuthenticated(updatedUser));
      } catch (e) {
        emit(AuthFailure(ApiErrorMessage.from(e)));
      }
    }
  }

  Future<void> _onForgotPasswordRequested(
    AuthForgotPasswordRequested event,
    Emitter<AuthState> emit,
  ) async {
    emit(AuthLoading());
    try {
      await _authRepository.forgotPassword(event.phone);
      emit(AuthActionSuccess('Password reset instructions were sent.'));
    } catch (e) {
      emit(AuthFailure(ApiErrorMessage.from(e)));
    }
  }
}
