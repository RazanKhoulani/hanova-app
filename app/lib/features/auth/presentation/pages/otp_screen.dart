import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:pinput/pinput.dart';
import '../../../../core/theme/app_colors.dart';
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

  @override
  void dispose() {
    _pinController.dispose();
    _focusNode.dispose();
    super.dispose();
  }

  void _verify(String phone) {
    if (_pinController.text.length != 4) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Enter the 4-digit OTP code')),
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

    final defaultPinTheme = PinTheme(
      width: 58,
      height: 58,
      textStyle: const TextStyle(
        fontSize: 22,
        color: AppColors.textPrimary,
        fontWeight: FontWeight.w700,
      ),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.divider),
      ),
    );

    return BlocConsumer<AuthBloc, AuthState>(
      listener: (context, state) {
        if (state is AuthAuthenticated) {
          context.go('/home');
        } else if (state is AuthFailure) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(state.message), backgroundColor: AppColors.danger),
          );
        }
      },
      builder: (context, state) {
        final isLoading = state is AuthLoading;

        return Scaffold(
          backgroundColor: AppColors.background,
          appBar: AppBar(title: const Text('Verify OTP')),
          body: Padding(
            padding: const EdgeInsets.fromLTRB(24, 14, 24, 24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  phone.isEmpty ? 'Complete registration verification.' : 'Code sent to $phone',
                  style: const TextStyle(color: AppColors.textSecondary),
                ),
                const SizedBox(height: 24),
                if (otpSimulated != null && otpSimulated.isNotEmpty)
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFFF5E6),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      'Dev OTP: $otpSimulated',
                      style: const TextStyle(
                        color: Color(0xFF8A5B00),
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                const SizedBox(height: 26),
                Center(
                  child: Pinput(
                    length: 4,
                    controller: _pinController,
                    focusNode: _focusNode,
                    defaultPinTheme: defaultPinTheme,
                    focusedPinTheme: defaultPinTheme.copyWith(
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: AppColors.primary, width: 2),
                      ),
                    ),
                    submittedPinTheme: defaultPinTheme.copyWith(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: AppColors.primary),
                      ),
                    ),
                    onCompleted: (_) {
                      if (phone.isNotEmpty && !isLoading) {
                        _verify(phone);
                      }
                    },
                  ),
                ),
                const Spacer(),
                ElevatedButton(
                  onPressed: (phone.isEmpty || isLoading) ? null : () => _verify(phone),
                  child: isLoading
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                        )
                      : const Text('Verify & Continue'),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}

