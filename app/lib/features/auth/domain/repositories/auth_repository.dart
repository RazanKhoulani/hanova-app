import '../../data/models/auth_response_model.dart';
import '../../data/models/user_model.dart';

abstract class AuthRepository {
  Future<AuthResponseModel> login(String phone, String password);
  Future<AuthResponseModel> register(
    String name,
    String phone,
    String password,
    String passwordConfirmation,
  );
  Future<AuthResponseModel> verifyRegistrationOtp(String phone, String otp);
  Future<UserModel> updateProfile(UserModel user);
  Future<void> forgotPassword(String phone);
  Future<void> logout();
  Future<UserModel?> getProfile();
}
