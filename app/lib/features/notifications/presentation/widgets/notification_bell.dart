import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/localization/app_localizations.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../auth/presentation/bloc/auth_bloc.dart';
import '../../../auth/presentation/bloc/auth_state.dart';
import '../bloc/notification_bloc.dart';

class NotificationBell extends StatefulWidget {
  const NotificationBell({super.key});

  @override
  State<NotificationBell> createState() => _NotificationBellState();
}

class _NotificationBellState extends State<NotificationBell> {
  bool _requestedNotifications = false;

  void _fetchNotificationsOnce() {
    if (_requestedNotifications) {
      return;
    }

    _requestedNotifications = true;
    context.read<NotificationBloc>().add(NotificationFetchRequested());
  }

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<AuthBloc, AuthState>(
      builder: (context, authState) {
        final isAuthenticated = authState is AuthAuthenticated;

        if (!isAuthenticated) {
          _requestedNotifications = false;
        } else if (context.read<NotificationBloc>().state
            is NotificationInitial) {
          WidgetsBinding.instance.addPostFrameCallback((_) {
            if (context.mounted) {
              _fetchNotificationsOnce();
            }
          });
        }

        return BlocBuilder<NotificationBloc, NotificationState>(
          builder: (context, notificationState) {
            final unreadCount = notificationState is NotificationLoaded
                ? notificationState.unreadCount
                : 0;

            return IconButton(
              tooltip: context.tr('notifications'),
              onPressed: () {
                if (!isAuthenticated) {
                  context.push('/login');
                  return;
                }
                context.push('/notifications');
              },
              icon: Stack(
                clipBehavior: Clip.none,
                children: [
                  const Icon(
                    Icons.notifications_none_rounded,
                    color: AppColors.primary,
                  ),
                  if (unreadCount > 0)
                    Positioned(
                      right: -5,
                      top: -5,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 5,
                          vertical: 1,
                        ),
                        decoration: BoxDecoration(
                          color: AppColors.danger,
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: Colors.white, width: 1.4),
                        ),
                        constraints: const BoxConstraints(
                          minWidth: 17,
                          minHeight: 17,
                        ),
                        child: Text(
                          unreadCount > 9 ? '9+' : unreadCount.toString(),
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            );
          },
        );
      },
    );
  }
}
