import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../features/auth/presentation/pages/splash_screen.dart';
import '../../features/auth/presentation/pages/login_screen.dart';
import '../../features/auth/presentation/pages/otp_screen.dart';
import '../../features/auth/presentation/pages/register_screen.dart';
import '../../features/auth/presentation/pages/main_screen.dart';
import '../../features/store/presentation/pages/product_details_screen.dart';
import '../../features/store/presentation/pages/cart_screen.dart';
import '../../features/store/presentation/pages/checkout_screen.dart';
import '../../features/store/presentation/pages/order_confirmation_screen.dart';
import '../../features/store/presentation/pages/my_orders_screen.dart';
import '../../features/clinical/presentation/pages/clinic_screen.dart';
import '../../features/clinical/presentation/pages/appointment_screen.dart';
import '../../features/communication/presentation/pages/bot_screen.dart';
import '../../features/communication/presentation/pages/chat_screen.dart';
import '../../features/notifications/presentation/pages/notifications_screen.dart';
import '../../features/support/presentation/pages/help_center_screen.dart';
import '../../features/support/presentation/pages/security_screen.dart';

final GlobalKey<NavigatorState> _rootNavigatorKey = GlobalKey<NavigatorState>();

class AppRouter {
  static final router = GoRouter(
    navigatorKey: _rootNavigatorKey,
    initialLocation: '/splash',
    routes: [
      GoRoute(
        path: '/splash',
        builder: (context, state) => const SplashScreen(),
      ),
      GoRoute(path: '/login', builder: (context, state) => const LoginScreen()),
      GoRoute(path: '/otp', builder: (context, state) => const OtpScreen()),
      GoRoute(
        path: '/register',
        builder: (context, state) => const RegisterScreen(),
      ),
      GoRoute(
        path: '/home',
        builder: (context, state) {
          final tabStr = state.uri.queryParameters['tab'];
          final initialTab = tabStr != null ? int.tryParse(tabStr) ?? 0 : 0;
          return MainScreen(initialTab: initialTab);
        },
      ),
      GoRoute(
        path: '/product-details/:id',
        builder: (context, state) =>
            ProductDetailsScreen(productId: state.pathParameters['id']!),
      ),
      GoRoute(path: '/cart', builder: (context, state) => const CartScreen()),
      GoRoute(
        path: '/checkout',
        builder: (context, state) => const CheckoutScreen(),
      ),
      GoRoute(
        path: '/order-confirmation',
        builder: (context, state) => const OrderConfirmationScreen(),
      ),
      GoRoute(
        path: '/appointment',
        builder: (context, state) => AppointmentScreen(
          initialSessionType: state.uri.queryParameters['type'],
          initialAppointmentType: state.uri.queryParameters['appointment_type'],
          openedFromBot: state.uri.queryParameters['source'] == 'bot',
          appointmentId: int.tryParse(
            state.uri.queryParameters['appointment_id'] ?? '',
          ),
          initialDate: DateTime.tryParse(
            state.uri.queryParameters['date'] ?? '',
          ),
          initialTime: state.uri.queryParameters['time'],
        ),
      ),
      GoRoute(
        path: '/bot',
        builder: (context, state) => BotScreen(
          productName: state.uri.queryParameters['product_name'],
          productDescription: state.uri.queryParameters['product_description'],
        ),
      ),
      GoRoute(
        path: '/orders',
        builder: (context, state) => const MyOrdersScreen(),
      ),
      GoRoute(
        path: '/clinic',
        builder: (context, state) => const ClinicScreen(),
      ),
      GoRoute(
        path: '/chat',
        builder: (context, state) => ChatScreen(
          consultationId: int.tryParse(
            state.uri.queryParameters['consultation_id'] ?? '',
          ),
        ),
      ),
      GoRoute(
        path: '/notifications',
        builder: (context, state) => const NotificationsScreen(),
      ),
      GoRoute(
        path: '/security',
        builder: (context, state) => const SecurityScreen(),
      ),
      GoRoute(
        path: '/help',
        builder: (context, state) => const HelpCenterScreen(),
      ),
    ],
  );
}
