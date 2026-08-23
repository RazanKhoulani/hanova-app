import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:pinput/pinput.dart';

import '../../../../core/theme/app_colors.dart';
import '../../../../core/utils/syrian_phone_number.dart';
import '../../../../core/widgets/hanova_auth_shell.dart';
import '../bloc/auth_bloc.dart';
import '../bloc/auth_event.dart';
import '../bloc/auth_state.dart';

class OtpScreen extends StatefulWidget {
  const OtpScreen({super.key});

  @override
  State<OtpScreen> createState() => _OtpScreenState();
}

class _OtpScreenState extends State<OtpScreen> {
  final _pinController = TextEditingController();
  final _focusNode = FocusNode();

  bool get _isArabic => Localizations.localeOf(context).languageCode == 'ar';
  String _label(String ar, String en) => _isArabic ? ar : en;

  @override
  void dispose() {
    _pinController.dispose();
    _focusNode.dispose();
    super.dispose();
  }

  void _verify(String phone) {
    if (_pinController.text.length != 5) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            _label('أدخلي الرمز المؤلف من 5 أرقام', 'Enter the 5-digit code'),
          ),
        ),
      );
      return;
    }
    context.read<AuthBloc>().add(
      AuthVerifyOtpRequested(phone, _pinController.text),
    );
  }

  @override
  Widget build(BuildContext context) {
    final extra = GoRouterState.of(context).extra;
    final payload = extra is Map<String, dynamic> ? extra : <String, dynamic>{};
    final phone = (payload['phone'] ?? '').toString();
    final otpSimulated = payload['otp_simulated']?.toString();
    final deliveryStatus = payload['delivery_status']?.toString();

    final defaultPinTheme = PinTheme(
      width: 56,
      height: 58,
      textStyle: const TextStyle(
        fontSize: 22,
        color: AppColors.textPrimary,
        fontWeight: FontWeight.w800,
      ),
      decoration: BoxDecoration(
        color: AppColors.surfaceMuted,
        borderRadius: BorderRadius.circular(17),
        border: Border.all(color: AppColors.divider),
      ),
    );

    return BlocConsumer<AuthBloc, AuthState>(
      listener: (context, state) {
        if (state is AuthAuthenticated) {
          context.go('/home');
        } else if (state is AuthFailure) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(state.message),
              backgroundColor: AppColors.danger,
            ),
          );
        } else if (state is AuthActionSuccess) {
          ScaffoldMessenger.of(
            context,
          ).showSnackBar(SnackBar(content: Text(state.message)));
        }
      },
      builder: (context, state) {
        final isLoading = state is AuthLoading;

        return HanovaAuthShell(
          title: _label('تأكيد الرقم', 'Verify your number'),
          subtitle: phone.isEmpty
              ? _label(
                  'أكملي التحقق للمتابعة',
                  'Complete verification to continue',
                )
              : _label(
                  'أرسلنا الرمز عبر واتساب إلى ${SyrianPhoneNumber.display(phone)}',
                  'We sent a WhatsApp code to ${SyrianPhoneNumber.display(phone)}',
                ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 58,
                  height: 58,
                  decoration: BoxDecoration(
                    color: AppColors.primaryLight,
                    borderRadius: BorderRadius.circular(18),
                  ),
                  child: const Icon(
                    Icons.mark_chat_read_outlined,
                    color: AppColors.primary,
                    size: 28,
                  ),
                ),
              ),
              const SizedBox(height: 20),
              Center(
                child: Pinput(
                  length: 5,
                  controller: _pinController,
                  focusNode: _focusNode,
                  defaultPinTheme: defaultPinTheme,
                  focusedPinTheme: defaultPinTheme.copyWith(
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(17),
                      border: Border.all(color: AppColors.primary, width: 2),
                    ),
                  ),
                  submittedPinTheme: defaultPinTheme.copyWith(
                    decoration: BoxDecoration(
                      color: AppColors.primaryLight,
                      borderRadius: BorderRadius.circular(17),
                      border: Border.all(color: AppColors.primary),
                    ),
                  ),
                  onCompleted: (_) {
                    if (phone.isNotEmpty && !isLoading) _verify(phone);
                  },
                ),
              ),
              if (deliveryStatus == 'accepted') ...[
                const SizedBox(height: 18),
                _StatusNote(
                  icon: Icons.schedule_rounded,
                  text: _label(
                    'تم قبول الإرسال. قد يحتاج وصول رسالة واتساب حتى دقيقة.',
                    'WhatsApp accepted the request. Delivery can take up to one minute.',
                  ),
                ),
              ],
              if (otpSimulated != null && otpSimulated.isNotEmpty) ...[
                const SizedBox(height: 12),
                _StatusNote(
                  icon: Icons.developer_mode_rounded,
                  text: 'Dev OTP: $otpSimulated',
                ),
              ],
              const SizedBox(height: 10),
              Center(
                child: TextButton.icon(
                  onPressed: (phone.isEmpty || isLoading)
                      ? null
                      : () => context.read<AuthBloc>().add(
                          AuthResendRegistrationOtpRequested(phone),
                        ),
                  icon: const Icon(Icons.refresh_rounded, size: 18),
                  label: Text(_label('إعادة إرسال الرمز', 'Resend code')),
                ),
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: (phone.isEmpty || isLoading)
                    ? null
                    : () => _verify(phone),
                child: isLoading
                    ? const SizedBox.square(
                        dimension: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
                      )
                    : Text(_label('تأكيد ومتابعة', 'Verify & continue')),
              ),
            ],
          ),
        );
      },
    );
  }
}

class _StatusNote extends StatelessWidget {
  final IconData icon;
  final String text;

  const _StatusNote({required this.icon, required this.text});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(13),
      decoration: BoxDecoration(
        color: const Color(0xFFFFF6E8),
        borderRadius: BorderRadius.circular(15),
      ),
      child: Row(
        children: [
          Icon(icon, size: 19, color: const Color(0xFF9B6414)),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              text,
              style: const TextStyle(color: Color(0xFF79501A), height: 1.4),
            ),
          ),
        ],
      ),
    );
  }
}
