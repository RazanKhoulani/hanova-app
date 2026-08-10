import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../../../../core/localization/app_localizations.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../store/presentation/pages/home_dashboard.dart';
import '../../../store/presentation/pages/my_orders_screen.dart';
import '../../../store/presentation/bloc/store_bloc.dart';
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
          bottomNavigationBar: Container(
            decoration: BoxDecoration(
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.05),
                  blurRadius: 10,
                  offset: const Offset(0, -5),
                ),
              ],
            ),
            child: BottomNavigationBar(
              currentIndex: visibleIndex,
              onTap: (index) {
                if (!isAuthenticated && _requiresAuth(index)) {
                  _askForAuth();
                  return;
                }
                if (index == 0) {
                  context.read<StoreBloc>().add(StoreFetchProducts());
                }
                setState(() {
                  _selectedIndex = index;
                  _builtTabs.add(index);
                });
              },
              type: BottomNavigationBarType.fixed,
              backgroundColor: Colors.white,
              selectedItemColor: AppColors.primary,
              unselectedItemColor: AppColors.textLight,
              selectedLabelStyle: const TextStyle(
                fontWeight: FontWeight.bold,
                fontSize: 12,
              ),
              unselectedLabelStyle: const TextStyle(
                fontWeight: FontWeight.w500,
                fontSize: 12,
              ),
              elevation: 0,
              items: [
                BottomNavigationBarItem(
                  icon: const Icon(Icons.home_outlined),
                  activeIcon: const Icon(Icons.home_rounded),
                  label: context.tr('home'),
                ),
                BottomNavigationBarItem(
                  icon: Icon(
                    Icons.local_hospital_outlined,
                    color: !isAuthenticated ? AppColors.textLight : null,
                  ),
                  activeIcon: const Icon(Icons.local_hospital_rounded),
                  label: context.tr('clinic'),
                ),
                BottomNavigationBarItem(
                  icon: Icon(
                    Icons.chat_bubble_outline_rounded,
                    color: !isAuthenticated ? AppColors.textLight : null,
                  ),
                  activeIcon: const Icon(Icons.chat_bubble_rounded),
                  label: context.tr('chat'),
                ),
                BottomNavigationBarItem(
                  icon: Icon(
                    Icons.receipt_long_outlined,
                    color: !isAuthenticated ? AppColors.textLight : null,
                  ),
                  activeIcon: const Icon(Icons.receipt_long_rounded),
                  label: context.tr('orders'),
                ),
                BottomNavigationBarItem(
                  icon: const Icon(Icons.person_outline_rounded),
                  activeIcon: const Icon(Icons.person_rounded),
                  label: context.tr('profile'),
                ),
              ],
            ),
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
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: visibleIndex,
        type: BottomNavigationBarType.fixed,
        backgroundColor: Colors.white,
        selectedItemColor: AppColors.primary,
        unselectedItemColor: AppColors.textLight,
        elevation: 0,
        onTap: (index) {
          setState(() {
            _selectedIndex = index == 0 ? 3 : 4;
            _builtDeliveryTabs.add(index);
          });
        },
        items: [
          BottomNavigationBarItem(
            icon: const Icon(Icons.receipt_long_outlined),
            activeIcon: const Icon(Icons.receipt_long_rounded),
            label: context.tr('orders'),
          ),
          BottomNavigationBarItem(
            icon: const Icon(Icons.person_outline_rounded),
            activeIcon: const Icon(Icons.person_rounded),
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
