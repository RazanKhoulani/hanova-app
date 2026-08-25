import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/localization/app_localizations.dart';
import '../../../../core/settings/app_settings_cubit.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../notifications/presentation/widgets/notification_bell.dart';
import '../../data/models/user_model.dart';
import '../bloc/auth_bloc.dart';
import '../bloc/auth_event.dart';
import '../bloc/auth_state.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<AuthBloc, AuthState>(
      builder: (context, state) {
        final isAuthenticated = state is AuthAuthenticated;
        final UserModel? user = isAuthenticated ? state.user : null;
        final languageLabel = context
            .watch<AppSettingsCubit>()
            .state
            .languageLabel;
        final currencyLabel = context
            .watch<AppSettingsCubit>()
            .state
            .currencyLabel;

        return Scaffold(
          backgroundColor: AppColors.background,
          appBar: AppBar(
            title: Text(context.tr('my_profile')),
            actions: const [NotificationBell()],
          ),
          body: SingleChildScrollView(
            padding: const EdgeInsets.all(20),
            child: Column(
              children: [
                _buildProfileCard(context, user),
                const SizedBox(height: 24),
                if (!isAuthenticated) ...[
                  _buildGuestActions(context),
                  const SizedBox(height: 24),
                ],
                _buildMenuSection([
                  _buildMenuItem(
                    Icons.person_outline_rounded,
                    context.tr('edit_profile'),
                    () {
                      if (user != null) {
                        _showEditProfileDialog(context, user);
                      }
                    },
                  ),
                  _buildMenuItem(
                    Icons.shopping_bag_outlined,
                    context.tr('order_history'),
                    () {
                      if (isAuthenticated) {
                        context.push('/orders');
                      } else {
                        context.push('/login');
                      }
                    },
                  ),
                ]),
                const SizedBox(height: 24),
                _buildMenuSection([
                  _buildMenuItem(
                    Icons.language_rounded,
                    context.tr('language'),
                    () => _showLanguageSheet(context),
                    trailingText: languageLabel,
                  ),
                  _buildMenuItem(
                    Icons.currency_exchange_rounded,
                    context.tr('display_currency'),
                    () => _showCurrencySheet(context),
                    trailingText: currencyLabel,
                  ),
                  _buildMenuItem(
                    Icons.security_rounded,
                    context.tr('security'),
                    () => context.push('/security'),
                  ),
                ]),
                const SizedBox(height: 24),
                _buildMenuSection([
                  _buildMenuItem(
                    Icons.help_outline_rounded,
                    context.tr('help_center'),
                    () => context.push('/help'),
                  ),
                  if (isAuthenticated)
                    _buildMenuItem(
                      Icons.logout_rounded,
                      context.tr('logout'),
                      () {
                        context.read<AuthBloc>().add(AuthLogoutRequested());
                        context.go('/home');
                      },
                      isDestructive: true,
                    ),
                ]),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildProfileCard(BuildContext context, UserModel? user) {
    final isGuest = user == null;

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(22, 28, 22, 24),
      decoration: BoxDecoration(
        gradient: AppColors.brandGradient,
        borderRadius: BorderRadius.circular(30),
        boxShadow: const [
          BoxShadow(
            color: Color(0x24A24A63),
            blurRadius: 28,
            offset: Offset(0, 14),
          ),
        ],
      ),
      child: Column(
        children: [
          Container(
            width: 86,
            height: 86,
            decoration: BoxDecoration(
              color: Colors.white,
              shape: BoxShape.circle,
              border: Border.all(
                color: Colors.white.withValues(alpha: 0.5),
                width: 4,
              ),
            ),
            child: const Icon(
              Icons.person_rounded,
              size: 44,
              color: AppColors.primary,
            ),
          ),
          const SizedBox(height: 14),
          Text(
            isGuest ? context.tr('guest_user') : user.name,
            textAlign: TextAlign.center,
            style: const TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.w800,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            isGuest ? context.tr('login_to_unlock') : user.phone,
            textAlign: TextAlign.center,
            style: TextStyle(
              color: Colors.white.withValues(alpha: 0.75),
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildGuestActions(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: AppColors.divider, width: 0.6),
        boxShadow: const [
          BoxShadow(color: AppColors.cardShadow, blurRadius: 10),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            context.tr('guest_browsing'),
            style: const TextStyle(fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 8),
          Text(
            context.tr('guest_browsing_note'),
            style: const TextStyle(color: AppColors.textSecondary),
          ),
          const SizedBox(height: 14),
          ElevatedButton(
            onPressed: () => context.push('/login'),
            child: Text(context.tr('login')),
          ),
          const SizedBox(height: 8),
          OutlinedButton(
            onPressed: () => context.push('/register'),
            style: OutlinedButton.styleFrom(
              minimumSize: const Size(double.infinity, 50),
              side: const BorderSide(color: AppColors.primary),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
              ),
            ),
            child: Text(
              context.tr('create_account'),
              style: const TextStyle(color: AppColors.primary),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMenuSection(List<Widget> children) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: AppColors.divider, width: 0.6),
        boxShadow: const [
          BoxShadow(color: AppColors.cardShadow, blurRadius: 10),
        ],
      ),
      child: Column(children: children),
    );
  }

  Widget _buildMenuItem(
    IconData icon,
    String title,
    VoidCallback onTap, {
    bool isDestructive = false,
    String? trailingText,
  }) {
    return ListTile(
      leading: Icon(
        icon,
        color: isDestructive ? AppColors.danger : AppColors.primary,
      ),
      title: Text(
        title,
        style: TextStyle(
          fontWeight: FontWeight.w500,
          color: isDestructive ? AppColors.danger : AppColors.textPrimary,
        ),
      ),
      trailing: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (trailingText != null)
            Text(
              trailingText,
              style: const TextStyle(
                color: AppColors.textSecondary,
                fontSize: 13,
              ),
            ),
          const Icon(Icons.chevron_right_rounded, size: 20),
        ],
      ),
      onTap: onTap,
    );
  }

  void _showLanguageSheet(BuildContext context) {
    final settingsCubit = context.read<AppSettingsCubit>();
    final currentCode = settingsCubit.state.locale.languageCode;

    showModalBottomSheet<void>(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (sheetContext) {
        return Padding(
          padding: const EdgeInsets.fromLTRB(20, 18, 20, 30),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                context.tr('language'),
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 14),
              _buildLanguageOption(
                context: sheetContext,
                label: context.tr('arabic'),
                code: 'ar',
                isSelected: currentCode == 'ar',
                settingsCubit: settingsCubit,
              ),
              _buildLanguageOption(
                context: sheetContext,
                label: context.tr('english'),
                code: 'en',
                isSelected: currentCode == 'en',
                settingsCubit: settingsCubit,
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildLanguageOption({
    required BuildContext context,
    required String label,
    required String code,
    required bool isSelected,
    required AppSettingsCubit settingsCubit,
  }) {
    return ListTile(
      contentPadding: EdgeInsets.zero,
      leading: Icon(
        isSelected
            ? Icons.radio_button_checked_rounded
            : Icons.radio_button_off_rounded,
        color: isSelected ? AppColors.primary : AppColors.textLight,
      ),
      title: Text(label, style: const TextStyle(fontWeight: FontWeight.w600)),
      onTap: () {
        settingsCubit.setLanguage(code);
        Navigator.pop(context);
      },
    );
  }

  void _showCurrencySheet(BuildContext context) {
    final settingsCubit = context.read<AppSettingsCubit>();
    final currentCurrency = settingsCubit.state.currency;

    showModalBottomSheet<void>(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (sheetContext) {
        return Padding(
          padding: const EdgeInsets.fromLTRB(20, 18, 20, 30),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                context.tr('display_currency'),
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                context.tr('currency_rate_note'),
                style: const TextStyle(color: AppColors.textSecondary),
              ),
              const SizedBox(height: 12),
              _buildCurrencyOption(
                context: sheetContext,
                label: context.tr('currency_syp_old'),
                currency: DisplayCurrency.sypOld,
                isSelected: currentCurrency == DisplayCurrency.sypOld,
                settingsCubit: settingsCubit,
              ),
              _buildCurrencyOption(
                context: sheetContext,
                label: context.tr('currency_syp_new'),
                currency: DisplayCurrency.sypNew,
                isSelected: currentCurrency == DisplayCurrency.sypNew,
                settingsCubit: settingsCubit,
              ),
              _buildCurrencyOption(
                context: sheetContext,
                label: context.tr('currency_usd'),
                currency: DisplayCurrency.usd,
                isSelected: currentCurrency == DisplayCurrency.usd,
                settingsCubit: settingsCubit,
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildCurrencyOption({
    required BuildContext context,
    required String label,
    required DisplayCurrency currency,
    required bool isSelected,
    required AppSettingsCubit settingsCubit,
  }) {
    return ListTile(
      contentPadding: EdgeInsets.zero,
      leading: Icon(
        isSelected
            ? Icons.radio_button_checked_rounded
            : Icons.radio_button_off_rounded,
        color: isSelected ? AppColors.primary : AppColors.textLight,
      ),
      title: Text(label, style: const TextStyle(fontWeight: FontWeight.w600)),
      onTap: () {
        settingsCubit.setCurrency(currency);
        Navigator.pop(context);
      },
    );
  }

  void _showEditProfileDialog(BuildContext context, UserModel user) {
    final nameController = TextEditingController(text: user.name);
    final emailController = TextEditingController(text: user.email);

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Edit Profile'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: nameController,
              decoration: const InputDecoration(labelText: 'Name'),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: emailController,
              decoration: const InputDecoration(labelText: 'Email'),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              context.read<AuthBloc>().add(
                AuthUpdateProfileRequested(
                  nameController.text,
                  emailController.text,
                ),
              );
              Navigator.pop(context);
            },
            child: const Text('Save'),
          ),
        ],
      ),
    );
  }
}
