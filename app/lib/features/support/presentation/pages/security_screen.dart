import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/localization/app_localizations.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../auth/presentation/bloc/auth_bloc.dart';
import '../../../auth/presentation/bloc/auth_event.dart';
import '../../../auth/presentation/bloc/auth_state.dart';

class SecurityScreen extends StatelessWidget {
  const SecurityScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<AuthBloc, AuthState>(
      builder: (context, state) {
        final user = state is AuthAuthenticated ? state.user : null;
        final authenticated = user != null;
        final phone = user?.phone;

        return Scaffold(
          appBar: AppBar(title: Text(context.tr('security'))),
          body: ListView(
            padding: const EdgeInsets.all(20),
            children: [
              _SecurityHero(authenticated: authenticated),
              const SizedBox(height: 20),
              _SecurityItem(
                icon: Icons.verified_user_outlined,
                title: context.tr('verified_phone'),
                description: authenticated
                    ? '${context.tr('verified_phone_note')}\n$phone'
                    : context.tr('login_to_manage_security'),
                isVerified: authenticated,
              ),
              const SizedBox(height: 12),
              _SecurityItem(
                icon: Icons.phonelink_lock_outlined,
                title: context.tr('secure_session'),
                description: context.tr('secure_session_note'),
                isVerified: true,
              ),
              const SizedBox(height: 12),
              _SecurityItem(
                icon: Icons.password_rounded,
                title: context.tr('otp_protection'),
                description: context.tr('otp_protection_note'),
                isVerified: true,
              ),
              const SizedBox(height: 24),
              if (authenticated)
                OutlinedButton.icon(
                  onPressed: () {
                    context.read<AuthBloc>().add(AuthLogoutRequested());
                    context.go('/home');
                  },
                  style: OutlinedButton.styleFrom(
                    minimumSize: const Size(double.infinity, 52),
                    foregroundColor: AppColors.danger,
                    side: const BorderSide(color: AppColors.danger),
                  ),
                  icon: const Icon(Icons.logout_rounded),
                  label: Text(context.tr('secure_logout')),
                )
              else
                ElevatedButton(
                  onPressed: () => context.push('/login'),
                  child: Text(context.tr('login')),
                ),
            ],
          ),
        );
      },
    );
  }
}

class _SecurityHero extends StatelessWidget {
  final bool authenticated;

  const _SecurityHero({required this.authenticated});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        gradient: AppColors.brandGradient,
        borderRadius: BorderRadius.circular(28),
      ),
      child: Row(
        children: [
          Container(
            width: 58,
            height: 58,
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.16),
              borderRadius: BorderRadius.circular(18),
            ),
            child: const Icon(
              Icons.shield_outlined,
              color: Colors.white,
              size: 31,
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  authenticated
                      ? context.tr('account_protected')
                      : context.tr('protect_your_account'),
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 19,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 5),
                Text(
                  context.tr('security_overview_note'),
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.78),
                    height: 1.45,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _SecurityItem extends StatelessWidget {
  final IconData icon;
  final String title;
  final String description;
  final bool isVerified;

  const _SecurityItem({
    required this.icon,
    required this.title,
    required this.description,
    required this.isVerified,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border.all(color: AppColors.divider),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 46,
            height: 46,
            decoration: BoxDecoration(
              color: AppColors.primarySoft,
              borderRadius: BorderRadius.circular(15),
            ),
            child: Icon(icon, color: AppColors.primary),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 5),
                Text(
                  description,
                  style: const TextStyle(
                    color: AppColors.textSecondary,
                    height: 1.45,
                  ),
                ),
              ],
            ),
          ),
          Icon(
            isVerified
                ? Icons.check_circle_rounded
                : Icons.info_outline_rounded,
            color: isVerified ? AppColors.success : AppColors.textLight,
            size: 21,
          ),
        ],
      ),
    );
  }
}
