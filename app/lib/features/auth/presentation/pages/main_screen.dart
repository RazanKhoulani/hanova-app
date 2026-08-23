import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/localization/app_localizations.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../store/presentation/pages/home_dashboard.dart';
import '../../../store/presentation/pages/my_orders_screen.dart';
import '../../../clinical/presentation/pages/clinic_screen.dart';
import '../../../communication/presentation/pages/chat_screen.dart';
import '../bloc/auth_state.dart';
import '../bloc/auth_bloc.dart';
import 'profile_screen.dart';

class MainScreen extends StatefulWidget {
  final int initialTab;
  const MainScreen({super.key, this.initialTab = 0});

  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  late int _selectedIndex;
  late final Set<int> _builtTabs;
  late final Set<int> _builtDeliveryTabs;

  @override
  void initState() {
    super.initState();
    _selectedIndex = widget.initialTab;
    _builtTabs = {_selectedIndex};
    _builtDeliveryTabs = {0};
  }

  List<Widget> _pages(int activeIndex) => [
    const HomeDashboard(),
    const ClinicScreen(),
    const ChatScreen(),
    MyOrdersScreen(showAppBar: false, autoFetch: activeIndex == 3),
    const ProfileScreen(),
  ];

  bool _requiresAuth(int index) => index == 1 || index == 2 || index == 3;

  void _askForAuth() {
    showModalBottomSheet<void>(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return Padding(
          padding: const EdgeInsets.fromLTRB(22, 20, 22, 28),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                context.tr('login_required'),
                style: const TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 10),
              Text(
                '${context.tr('guest_browsing_note')} ${context.tr('notifications_login_note')}',
                style: const TextStyle(
                  color: AppColors.textSecondary,
                  height: 1.45,
                ),
              ),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: () {
                  Navigator.pop(context);
                  context.push('/login');
                },
                child: Text(context.tr('login')),
              ),
              const SizedBox(height: 8),
              OutlinedButton(
                onPressed: () {
                  Navigator.pop(context);
                  context.push('/register');
                },
                style: OutlinedButton.styleFrom(
                  minimumSize: const Size(double.infinity, 52),
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
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<AuthBloc, AuthState>(
      builder: (context, authState) {
        final isAuthenticated = authState is AuthAuthenticated;
        if (authState is AuthAuthenticated &&
            authState.user.role == 'delivery') {
          return _buildDeliveryShell();
        }

        final visibleIndex = !isAuthenticated && _requiresAuth(_selectedIndex)
            ? 0
            : _selectedIndex;

        return Scaffold(
          body: IndexedStack(
            index: visibleIndex,
            children: _lazyPages(
              pageCount: 5,
              activeIndex: visibleIndex,
              builtIndexes: _builtTabs,
              pageBuilder: (index) => _pages(visibleIndex)[index],
            ),
          ),
          bottomNavigationBar: _HanovaNavigationBar(
            currentIndex: visibleIndex,
            onTap: (index) {
              if (!isAuthenticated && _requiresAuth(index)) {
                _askForAuth();
                return;
              }
              setState(() {
                _selectedIndex = index;
                _builtTabs.add(index);
              });
            },
            items: [
              _HanovaNavItem(
                icon: Icons.home_outlined,
                activeIcon: Icons.home_rounded,
                label: context.tr('home'),
              ),
              _HanovaNavItem(
                icon: Icons.local_hospital_outlined,
                activeIcon: Icons.local_hospital_rounded,
                label: context.tr('clinic'),
                isLocked: !isAuthenticated,
              ),
              _HanovaNavItem(
                icon: Icons.chat_bubble_outline_rounded,
                activeIcon: Icons.chat_bubble_rounded,
                label: context.tr('chat'),
                isLocked: !isAuthenticated,
              ),
              _HanovaNavItem(
                icon: Icons.receipt_long_outlined,
                activeIcon: Icons.receipt_long_rounded,
                label: context.tr('orders'),
                isLocked: !isAuthenticated,
              ),
              _HanovaNavItem(
                icon: Icons.person_outline_rounded,
                activeIcon: Icons.person_rounded,
                label: context.tr('profile'),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildDeliveryShell() {
    final visibleIndex = _selectedIndex == 4 ? 1 : 0;

    return Scaffold(
      body: IndexedStack(
        index: visibleIndex,
        children: _lazyPages(
          pageCount: 2,
          activeIndex: visibleIndex,
          builtIndexes: _builtDeliveryTabs,
          pageBuilder: (index) => index == 0
              ? MyOrdersScreen(showAppBar: false, autoFetch: visibleIndex == 0)
              : const ProfileScreen(),
        ),
      ),
      bottomNavigationBar: _HanovaNavigationBar(
        currentIndex: visibleIndex,
        onTap: (index) {
          setState(() {
            _selectedIndex = index == 0 ? 3 : 4;
            _builtDeliveryTabs.add(index);
          });
        },
        items: [
          _HanovaNavItem(
            icon: Icons.receipt_long_outlined,
            activeIcon: Icons.receipt_long_rounded,
            label: context.tr('orders'),
          ),
          _HanovaNavItem(
            icon: Icons.person_outline_rounded,
            activeIcon: Icons.person_rounded,
            label: context.tr('profile'),
          ),
        ],
      ),
    );
  }

  List<Widget> _lazyPages({
    required int pageCount,
    required int activeIndex,
    required Set<int> builtIndexes,
    required Widget Function(int index) pageBuilder,
  }) {
    builtIndexes.add(activeIndex);

    return List<Widget>.generate(pageCount, (index) {
      if (!builtIndexes.contains(index)) {
        return const SizedBox.shrink();
      }

      return pageBuilder(index);
    });
  }
}

class _HanovaNavItem {
  final IconData icon;
  final IconData activeIcon;
  final String label;
  final bool isLocked;

  const _HanovaNavItem({
    required this.icon,
    required this.activeIcon,
    required this.label,
    this.isLocked = false,
  });
}

class _HanovaNavigationBar extends StatelessWidget {
  final int currentIndex;
  final ValueChanged<int> onTap;
  final List<_HanovaNavItem> items;

  const _HanovaNavigationBar({
    required this.currentIndex,
    required this.onTap,
    required this.items,
  });

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(top: BorderSide(color: AppColors.divider, width: 0.7)),
        boxShadow: [
          BoxShadow(
            color: AppColors.cardShadow,
            blurRadius: 24,
            offset: Offset(0, -8),
          ),
        ],
      ),
      child: SafeArea(
        top: false,
        child: SizedBox(
          height: 72,
          child: Row(
            children: List.generate(items.length, (index) {
              final item = items[index];
              final selected = index == currentIndex;
              return Expanded(
                child: InkWell(
                  onTap: () => onTap(index),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      AnimatedContainer(
                        duration: const Duration(milliseconds: 180),
                        width: selected ? 42 : 34,
                        height: 32,
                        decoration: BoxDecoration(
                          color: selected
                              ? AppColors.primaryLight
                              : Colors.transparent,
                          borderRadius: BorderRadius.circular(13),
                        ),
                        child: Stack(
                          alignment: Alignment.center,
                          children: [
                            Icon(
                              selected ? item.activeIcon : item.icon,
                              size: 22,
                              color: item.isLocked
                                  ? AppColors.textLight
                                  : selected
                                  ? AppColors.primary
                                  : AppColors.textSecondary,
                            ),
                            if (item.isLocked)
                              const PositionedDirectional(
                                top: 2,
                                end: 2,
                                child: Icon(
                                  Icons.lock_rounded,
                                  size: 9,
                                  color: AppColors.textLight,
                                ),
                              ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 3),
                      Text(
                        item.label,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: selected
                              ? FontWeight.w800
                              : FontWeight.w500,
                          color: selected
                              ? AppColors.primaryDark
                              : AppColors.textSecondary,
                        ),
                      ),
                    ],
                  ),
                ),
              );
            }),
          ),
        ),
      ),
    );
  }
}
