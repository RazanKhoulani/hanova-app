import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:country_code_picker/country_code_picker.dart';

import '../../../../core/theme/app_colors.dart';
import '../../../../core/utils/syrian_phone_number.dart';
import '../../../../core/widgets/hanova_auth_shell.dart';
import '../bloc/auth_bloc.dart';
import '../bloc/auth_event.dart';
import '../bloc/auth_state.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPhoneController = TextEditingController();
  bool _obscurePassword = true;
  String _callingCode = '+963';

  bool get _isArabic => Localizations.localeOf(context).languageCode == 'ar';
  String _label(String ar, String en) => _isArabic ? ar : en;

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    _confirmPhoneController.dispose();
    super.dispose();
  }

  void _submit() {
    final name = _nameController.text.trim();
    final phone = SyrianPhoneNumber.tryInternational(
      _phoneController.text,
      callingCode: _callingCode,
    );
    final phoneConfirmation = SyrianPhoneNumber.tryInternational(
      _confirmPhoneController.text,
      callingCode: _callingCode,
    );
    final password = _passwordController.text;
    final email = _emailController.text.trim();

    if (name.isEmpty ||
        phone == null ||
        password.isEmpty ||
        phoneConfirmation == null) {
      _showMessage(
        _label(
          'أكملي جميع الحقول وأدخلي رقم موبايل صحيحاً',
          'Complete all fields with a valid phone number',
        ),
      );
      return;
    }
    if (password.length < 6) {
      _showMessage(
        _label(
          'كلمة المرور يجب أن تكون 6 محارف على الأقل',
          'Password must be at least 6 characters',
        ),
      );
      return;
    }
    if (email.isNotEmpty &&
        !RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$').hasMatch(email)) {
      _showMessage(
        _label('أدخلي بريداً إلكترونياً صحيحاً', 'Enter a valid email address'),
      );
      return;
    }
    if (phone != phoneConfirmation) {
      _showMessage(
        _label('تأكيد رقم الموبايل غير مطابق', 'Phone numbers do not match'),
      );
      return;
    }

    context.read<AuthBloc>().add(
      AuthRegisterRequested(
        name,
        email.isEmpty ? null : email,
        phone,
        password,
        phoneConfirmation,
      ),
    );
  }

  void _showMessage(String message) {
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(message)));
  }

  @override
  Widget build(BuildContext context) {
    return BlocConsumer<AuthBloc, AuthState>(
      listener: (context, state) {
        if (state is AuthOtpRequired) {
          context.push(
            '/otp',
            extra: {
              'phone': state.phone,
              'otp_simulated': state.otpSimulated,
              'delivery_status': state.deliveryStatus,
            },
          );
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
          title: _label('إنشاء حساب', 'Create account'),
          subtitle: _label(
            'خطوة واحدة تفصلك عن رعاية مصممة لك',
            'One step away from care designed around you',
          ),
          child: AutofillGroup(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                HanovaFieldLabel(_label('الاسم الكامل', 'Full name')),
                TextField(
                  controller: _nameController,
                  textInputAction: TextInputAction.next,
                  autofillHints: const [AutofillHints.name],
                  decoration: InputDecoration(
                    hintText: _label('الاسم', 'Your name'),
                    prefixIcon: const Icon(Icons.person_outline_rounded),
                  ),
                ),
                const SizedBox(height: 16),
                HanovaFieldLabel(_label('البريد الإلكتروني (اختياري)', 'Email (optional)')),
                TextField(
                  controller: _emailController,
                  keyboardType: TextInputType.emailAddress,
                  textInputAction: TextInputAction.next,
                  autofillHints: const [AutofillHints.email],
                  decoration: InputDecoration(
                    hintText: _label('name@example.com', 'name@example.com'),
                    prefixIcon: const Icon(Icons.email_outlined),
                  ),
                ),
                const SizedBox(height: 16),
                HanovaFieldLabel(_label('رقم الموبايل', 'Phone number')),
                Directionality(
                  textDirection: TextDirection.ltr,
                  child: TextField(
                    controller: _phoneController,
                    keyboardType: TextInputType.phone,
                    textInputAction: TextInputAction.next,
                    textDirection: TextDirection.ltr,
                    textAlign: TextAlign.left,
                    autofillHints: const [AutofillHints.telephoneNumber],
                    inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                    decoration: InputDecoration(
                      hintText: _callingCode == '+963'
                          ? '0945345844 / 945345844'
                          : _label('رقم الموبايل', 'Phone number'),
                      prefixIcon: CountryCodePicker(
                        initialSelection: 'SY',
                        favorite: const ['SY', 'AE', 'SA', 'LB'],
                        showCountryOnly: false,
                        showOnlyCountryWhenClosed: false,
                        headerText: _label('اختاري الدولة', 'Select country'),
                        searchDecoration: InputDecoration(
                          hintText: _label('ابحثي عن دولة', 'Search country'),
                        ),
                        onChanged: (country) => setState(
                          () => _callingCode = country.dialCode ?? '+963',
                        ),
                      ),
                      prefixIconConstraints: const BoxConstraints(
                        minWidth: 112,
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                HanovaFieldLabel(_label('تأكيد رقم الموبايل', 'Confirm phone number')),
                Directionality(
                  textDirection: TextDirection.ltr,
                  child: TextField(
                    controller: _confirmPhoneController,
                    keyboardType: TextInputType.phone,
                    textInputAction: TextInputAction.next,
                    textDirection: TextDirection.ltr,
                    textAlign: TextAlign.left,
                    inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                    decoration: InputDecoration(
                      hintText: _label(
                        'أعيدي كتابة الرقم',
                        'Repeat phone number',
                      ),
                      prefixText: '$_callingCode  ',
                      prefixIcon: const Icon(Icons.phone_android_rounded),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                HanovaFieldLabel(_label('كلمة المرور', 'Password')),
                TextField(
                  controller: _passwordController,
                  obscureText: _obscurePassword,
                  textInputAction: TextInputAction.done,
                  autofillHints: const [AutofillHints.newPassword],
                  decoration: InputDecoration(
                    hintText: _label(
                      '6 محارف على الأقل',
                      'At least 6 characters',
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
                const SizedBox(height: 24),
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
                      : Text(
                          _label(
                            'إنشاء الحساب وإرسال الرمز',
                            'Register & send OTP',
                          ),
                        ),
                ),
                const SizedBox(height: 14),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(_label('لديك حساب؟', 'Already registered?')),
                    TextButton(
                      onPressed: () => context.pop(),
                      child: Text(_label('تسجيل الدخول', 'Sign in')),
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
