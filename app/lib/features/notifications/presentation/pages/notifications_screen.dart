import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../../../../core/localization/app_localizations.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../../core/widgets/hanova_ui.dart';
import '../../../auth/presentation/bloc/auth_bloc.dart';
import '../../../auth/presentation/bloc/auth_state.dart';
import '../../data/models/notification_model.dart';
import '../bloc/notification_bloc.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _fetchIfAllowed());
  }

  void _fetchIfAllowed() {
    if (!mounted) return;
    if (context.read<AuthBloc>().state is AuthAuthenticated) {
      context.read<NotificationBloc>().add(NotificationFetchRequested());
    }
  }

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<AuthBloc, AuthState>(
      builder: (context, authState) {
        final isAuthenticated = authState is AuthAuthenticated;

        return Scaffold(
          backgroundColor: AppColors.background,
          appBar: AppBar(title: Text(context.tr('notifications'))),
          body: isAuthenticated
              ? _buildNotifications()
              : _buildAuthRequired(context),
        );
      },
    );
  }

  Widget _buildNotifications() {
    return BlocBuilder<NotificationBloc, NotificationState>(
      builder: (context, state) {
        if (state is NotificationLoading) {
          return const HanovaLoadingView();
        }

        if (state is NotificationFailure) {
          return _buildFailure(state.message);
        }

        if (state is NotificationLoaded) {
          if (state.notifications.isEmpty) {
            return _buildEmptyState();
          }

          return RefreshIndicator(
            onRefresh: () async => context.read<NotificationBloc>().add(
              NotificationFetchRequested(),
            ),
            child: ListView.separated(
              padding: const EdgeInsets.all(20),
              itemCount: state.notifications.length,
              separatorBuilder: (context, index) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                final notification = state.notifications[index];
                return _NotificationCard(
                  notification: notification,
                  onTap: () {
                    if (!notification.isRead) {
                      context.read<NotificationBloc>().add(
                        NotificationMarkReadRequested(notification.id),
                      );
                    }

                    _openNotification(notification);
                  },
                );
              },
            ),
          );
        }

        return const SizedBox.shrink();
      },
    );
  }

  void _openNotification(NotificationModel notification) {
    final type = notification.type.toLowerCase();
    final data = notification.data ?? const <String, dynamic>{};

    if (type == 'chat_message' || data['conversation_id'] != null) {
      final consultationId = data['consultation_id'];
      context.push(
        consultationId == null ? '/chat' : '/chat?consultation_id=$consultationId',
      );
      return;
    }
    if (type.contains('appointment') || data['appointment_id'] != null) {
      context.push('/clinic');
      return;
    }
    if (type.startsWith('order') ||
        type == 'new_order' ||
        data['order_id'] != null) {
      context.push('/orders');
      return;
    }
    context.push('/home');
  }

  Widget _buildAuthRequired(BuildContext context) {
    return HanovaStateView(
      icon: Icons.lock_outline_rounded,
      title: context.tr('login_required'),
      message: context.tr('notifications_login_note'),
      actionLabel: context.tr('login'),
      onAction: () => context.push('/login'),
    );
  }

  Widget _buildFailure(String message) {
    return HanovaStateView(
      icon: Icons.cloud_off_rounded,
      title: context.tr('something_went_wrong'),
      message: message,
      actionLabel: context.tr('try_again'),
      onAction: _fetchIfAllowed,
      iconColor: AppColors.danger,
    );
  }

  Widget _buildEmptyState() {
    return HanovaStateView(
      icon: Icons.notifications_none_rounded,
      title: context.tr('no_notifications'),
      message: context.tr('no_notifications_note'),
    );
  }
}

class _NotificationCard extends StatelessWidget {
  final NotificationModel notification;
  final VoidCallback onTap;

  const _NotificationCard({required this.notification, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final icon = _iconForType(notification.type);
    final accentColor = _colorForType(notification.type);
    final title = _localizedTitle(context);
    final body = _localizedBody(context);
    final time = _formatTime(context, notification.createdAt);

    return Material(
      color: AppColors.surface,
      borderRadius: BorderRadius.circular(18),
      child: InkWell(
        borderRadius: BorderRadius.circular(18),
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(18),
            border: Border.all(
              color: notification.isRead
                  ? Colors.transparent
                  : AppColors.primary.withValues(alpha: 0.28),
            ),
            boxShadow: const [BoxShadow(
              color: AppColors.cardShadow,
              blurRadius: 18,
              offset: Offset(0, 6),
            )],
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: accentColor.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(icon, color: accentColor),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Expanded(
                          child: Text(
                            title,
                            style: const TextStyle(
                              color: AppColors.textPrimary,
                              fontWeight: FontWeight.w700,
                              fontSize: 15,
                            ),
                          ),
                        ),
                        if (!notification.isRead)
                          Container(
                            width: 8,
                            height: 8,
                            margin: const EdgeInsets.only(left: 8, top: 6),
                            decoration: const BoxDecoration(
                              color: AppColors.primary,
                              shape: BoxShape.circle,
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    Text(
                      body,
                      style: const TextStyle(
                        color: AppColors.textSecondary,
                        height: 1.38,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      time,
                      style: const TextStyle(
                        color: AppColors.textLight,
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  IconData _iconForType(String type) {
    switch (type) {
      case 'order_accepted':
        return Icons.check_circle_outline_rounded;
      case 'order_ready':
        return Icons.inventory_2_outlined;
      case 'order_status':
      case 'order_created':
        return Icons.receipt_long_outlined;
      case 'offer':
        return Icons.local_offer_outlined;
      case 'clinic':
        return Icons.local_hospital_outlined;
      default:
        return Icons.notifications_none_rounded;
    }
  }

  Color _colorForType(String type) {
    switch (type) {
      case 'order_accepted':
      case 'order_ready':
        return AppColors.success;
      case 'offer':
        return AppColors.accent;
      case 'clinic':
        return AppColors.primary;
      default:
        return AppColors.primary;
    }
  }

  String _localizedTitle(BuildContext context) {
    return _localizedNotificationText(
      context,
      field: 'title',
      value: notification.title,
      fallbacks: _arabicTitleFallbacks,
    );
  }

  String _localizedBody(BuildContext context) {
    return _localizedNotificationText(
      context,
      field: 'body',
      value: notification.body,
      fallbacks: _arabicBodyFallbacks,
    );
  }

  String _localizedNotificationText(
    BuildContext context, {
    required String field,
    required String value,
    required Map<String, String> fallbacks,
  }) {
    final languageCode = Localizations.localeOf(context).languageCode;
    final translated = _translationFromData(languageCode, field);
    if (translated.isNotEmpty) return translated;

    final sourceValue = value.trim();
    final brandedValue = _legacyBrandingFallbacks[sourceValue] ?? value;
    if (languageCode == 'ar') {
      return fallbacks[brandedValue.trim()] ?? brandedValue;
    }

    return brandedValue;
  }

  String _translationFromData(String languageCode, String field) {
    final translations = notification.data?['translations'];
    if (translations is! Map) return '';

    final localized = translations[languageCode];
    if (localized is! Map) return '';

    return localized[field]?.toString() ?? '';
  }

  String _formatTime(BuildContext context, DateTime value) {
    if (Localizations.localeOf(context).languageCode != 'ar') {
      return DateFormat('MMM d, hh:mm a').format(value);
    }

    final hour = value.hour % 12 == 0 ? 12 : value.hour % 12;
    final minute = value.minute.toString().padLeft(2, '0');
    final period = value.hour < 12 ? '\u0635' : '\u0645';
    return '${value.day} ${_arabicMonths[value.month - 1]}\u060c '
        '${hour.toString().padLeft(2, '0')}:$minute $period';
  }

  static const _arabicMonths = [
    '\u064a\u0646\u0627\u064a\u0631',
    '\u0641\u0628\u0631\u0627\u064a\u0631',
    '\u0645\u0627\u0631\u0633',
    '\u0623\u0628\u0631\u064a\u0644',
    '\u0645\u0627\u064a\u0648',
    '\u064a\u0648\u0646\u064a\u0648',
    '\u064a\u0648\u0644\u064a\u0648',
    '\u0623\u063a\u0633\u0637\u0633',
    '\u0633\u0628\u062a\u0645\u0628\u0631',
    '\u0623\u0643\u062a\u0648\u0628\u0631',
    '\u0646\u0648\u0641\u0645\u0628\u0631',
    '\u062f\u064a\u0633\u0645\u0628\u0631',
  ];

  static const _arabicTitleFallbacks = {
    'Appointment Confirmed':
        '\u062a\u0645 \u062a\u0623\u0643\u064a\u062f \u0627\u0644\u0645\u0648\u0639\u062f',
    'Welcome to Hanova':
        '\u0645\u0631\u062d\u0628\u0627\u064b \u0628\u0643 \u0641\u064a Hanova',
    'Order Confirmation':
        '\u062a\u0623\u0643\u064a\u062f \u0627\u0644\u0637\u0644\u0628',
    'Order received':
        '\u062a\u0645 \u0627\u0633\u062a\u0644\u0627\u0645 \u0627\u0644\u0637\u0644\u0628',
    'Order accepted':
        '\u062a\u0645 \u0642\u0628\u0648\u0644 \u0627\u0644\u0637\u0644\u0628',
    'Order is ready': '\u0627\u0644\u0637\u0644\u0628 \u062c\u0627\u0647\u0632',
    'Order update':
        '\u062a\u062d\u062f\u064a\u062b \u0627\u0644\u0637\u0644\u0628',
    'New offer': '\u0639\u0631\u0636 \u062c\u062f\u064a\u062f',
  };

  static const _arabicBodyFallbacks = {
    'Your appointment for tomorrow is confirmed.':
        '\u062a\u0645 \u062a\u0623\u0643\u064a\u062f \u0645\u0648\u0639\u062f\u0643 \u0644\u064a\u0648\u0645 \u063a\u062f.',
    'Thank you for choosing Hanova for your beauty care.':
        '\u0634\u0643\u0631\u0627\u064b \u0644\u0627\u062e\u062a\u064a\u0627\u0631\u0643 Hanova \u0644\u0644\u0639\u0646\u0627\u064a\u0629 \u0628\u062c\u0645\u0627\u0644\u0643.',
    'Your order was sent to the clinic and is waiting for confirmation.':
        '\u062a\u0645 \u0625\u0631\u0633\u0627\u0644 \u0637\u0644\u0628\u0643 \u0644\u0644\u0639\u064a\u0627\u062f\u0629 \u0648\u0647\u0648 \u0628\u0627\u0646\u062a\u0638\u0627\u0631 \u0627\u0644\u062a\u0623\u0643\u064a\u062f.',
    'Your order was accepted by the clinic.':
        '\u062a\u0645 \u0642\u0628\u0648\u0644 \u0637\u0644\u0628\u0643 \u0645\u0646 \u0627\u0644\u0639\u064a\u0627\u062f\u0629.',
    'Your order is ready.':
        '\u0637\u0644\u0628\u0643 \u062c\u0627\u0647\u0632.',
  };

  static const _legacyBrandingFallbacks = {
    'Welcome to MedicalStore': 'Welcome to Hanova',
    'Thank you for choosing us for your medical needs.':
        'Thank you for choosing Hanova for your beauty care.',
  };
}
