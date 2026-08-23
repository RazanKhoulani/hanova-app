import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/theme/app_colors.dart';
import '../../../../core/utils/syrian_phone_number.dart';
import '../../../../core/widgets/hanova_auth_shell.dart';
import '../bloc/auth_bloc.dart';
import '../bloc/auth_event.dart';
import '../bloc/auth_state.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _obscurePassword = true;

  bool get _isArabic => Localizations.localeOf(context).languageCode == 'ar';
  String _label(String ar, String en) => _isArabic ? ar : en;

  @override
  void dispose() {
    _phoneController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _submit() {
    final phone = SyrianPhoneNumber.tryInternational(_phoneController.text);
    if (phone == null || _passwordController.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            _label(
              'أدخلي رقم موبايل سوري صحيح وكلمة المرور',
              'Enter a valid Syrian mobile number and password',
            ),
          ),
        ),
      );
      return;
    }
    context.read<AuthBloc>().add(
      AuthLoginRequested(phone, _passwordController.text),
    );
  }

  @override
  Widget build(BuildContext context) {
    return BlocConsumer<AuthBloc, AuthState>(
      listener: (context, state) {
        if (state is AuthAuthenticated) {
          context.go('/home');
        } else if (state is AuthActionSuccess) {
          ScaffoldMessenger.of(
            context,
          ).showSnackBar(SnackBar(content: Text(state.message)));
        } else if (state is AuthFailure) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(state.message),
              backgroundColor: AppColors.danger,
            ),
          );
        }
      },
      builder: (context, state) {
        final isLoading = state is AuthLoading;

        return HanovaAuthShell(
          title: _label('أهلاً بعودتك', 'Welcome back'),
          subtitle: _label(
            'سجلي الدخول لمتابعة مواعيدك وطلباتك',
            'Sign in to manage appointments and orders',
          ),
          showBack: false,
          topActionLabel: _label('الدخول كزائرة', 'Continue as guest'),
          onTopAction: () => context.go('/home'),
          child: AutofillGroup(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                HanovaFieldLabel(_label('رقم الموبايل', 'Phone number')),
                TextField(
                  controller: _phoneController,
                  keyboardType: TextInputType.phone,
                  textInputAction: TextInputAction.next,
                  autofillHints: const [AutofillHints.telephoneNumber],
                  inputFormatters: [
                    FilteringTextInputFormatter.digitsOnly,
                    LengthLimitingTextInputFormatter(9),
                  ],
                  decoration: const InputDecoration(
                    hintText: '9XXXXXXXX',
                    prefixText: '+963  ',
                    prefixIcon: Icon(Icons.phone_iphone_rounded),
                  ),
                ),
                const SizedBox(height: 18),
                HanovaFieldLabel(_label('كلمة المرور', 'Password')),
                TextField(
                  controller: _passwordController,
                  obscureText: _obscurePassword,
                  textInputAction: TextInputAction.done,
                  autofillHints: const [AutofillHints.password],
                  onSubmitted: (_) {
                    if (!isLoading) _submit();
                  },
                  decoration: InputDecoration(
                    hintText: _label(
                      'أدخلي كلمة المرور',
                      'Enter your password',
                    ),
                    prefixIcon: const Icon(Icons.lock_outline_rounded),
                    suffixIcon: IconButton(
                      icon: Icon(
                        _obscurePassword
                            ? Icons.visibility_off_outlined
                            : Icons.visibility_outlined,
                      ),
                      onPressed: () =>
                          setState(() => _obscurePassword = !_obscurePassword),
                    ),
                  ),
                ),
                Align(
                  alignment: AlignmentDirectional.centerEnd,
                  child: TextButton(
                    onPressed: isLoading
                        ? null
                        : () {
                            final phone = SyrianPhoneNumber.tryInternational(
                              _phoneController.text,
                            );
                            if (phone == null) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(
                                    _label(
                                      'أدخلي رقم الموبايل الصحيح أولاً',
                                      'Enter a valid Syrian number first',
                                    ),
                                  ),
                                ),
                              );
                              return;
                            }
                            context.read<AuthBloc>().add(
                              AuthForgotPasswordRequested(phone),
                            );
                          },
                    child: Text(
                      _label('نسيت كلمة المرور؟', 'Forgot password?'),
                    ),
                  ),
                ),
                const SizedBox(height: 14),
                ElevatedButton(
                  onPressed: isLoading ? null : _submit,
                  child: isLoading
                      ? const SizedBox.square(
                          dimension: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : Text(_label('تسجيل الدخول', 'Sign in')),
                ),
                const SizedBox(height: 16),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(_label('ليس لديك حساب؟', 'No account yet?')),
                    TextButton(
                      onPressed: () => context.push('/register'),
                      child: Text(_label('إنشاء حساب', 'Create one')),
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
