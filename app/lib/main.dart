import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:firebase_core/firebase_core.dart';
import 'core/localization/app_localizations.dart';
import 'core/theme/app_theme.dart';
import 'core/router/app_router.dart';
import 'core/settings/app_settings_cubit.dart';
import 'features/auth/presentation/bloc/auth_bloc.dart';
import 'features/auth/presentation/bloc/auth_event.dart';
import 'features/auth/presentation/bloc/auth_state.dart';
import 'features/store/presentation/bloc/store_bloc.dart';
import 'features/store/presentation/bloc/cart_bloc.dart';
import 'features/clinical/presentation/bloc/clinical_bloc.dart';
import 'features/communication/presentation/bloc/communication_bloc.dart';
import 'features/notifications/presentation/bloc/notification_bloc.dart';
import 'injection_container.dart' as di;
import 'firebase_options.dart';
import 'core/notifications/push_notification_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp(options: DefaultFirebaseOptions.currentPlatform);
  await di.init();
  await di.sl<PushNotificationService>().initialize();
  runApp(const MyApp());
  WidgetsBinding.instance.addPostFrameCallback((_) {
    di.sl<PushNotificationService>().handleInitialMessage();
  });
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiBlocProvider(
      providers: [
        BlocProvider(
          create: (context) => di.sl<AuthBloc>()..add(AuthCheckStatus()),
        ),
        BlocProvider(create: (context) => di.sl<StoreBloc>()),
        BlocProvider(create: (context) => di.sl<CartBloc>()),
        BlocProvider(create: (context) => di.sl<ClinicalBloc>()),
        BlocProvider(create: (context) => di.sl<CommunicationBloc>()),
        BlocProvider(create: (context) => di.sl<NotificationBloc>()),
        BlocProvider(create: (context) => di.sl<AppSettingsCubit>()..load()),
      ],
      child: const _AppView(),
    );
  }
}

class _AppView extends StatefulWidget {
  const _AppView();

  @override
  State<_AppView> createState() => _AppViewState();
}

class _AppViewState extends State<_AppView> with WidgetsBindingObserver {
  StreamSubscription<Map<String, dynamic>>? _notificationSubscription;
  Timer? _notificationRefreshDebounce;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _notificationSubscription = di.sl<PushNotificationService>().events.listen(
      (_) => _refreshNotifications(),
    );
  }

  void _refreshNotifications() {
    if (!mounted || context.read<AuthBloc>().state is! AuthAuthenticated) {
      return;
    }
    _notificationRefreshDebounce?.cancel();
    _notificationRefreshDebounce = Timer(const Duration(milliseconds: 350), () {
      if (mounted) {
        context.read<NotificationBloc>().add(NotificationFetchRequested());
      }
    });
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) _refreshNotifications();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _notificationRefreshDebounce?.cancel();
    _notificationSubscription?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<AppSettingsCubit, AppSettingsState>(
      builder: (context, settingsState) {
        return MaterialApp.router(
          title: context.tr('app_name'),
          debugShowCheckedModeBanner: false,
          theme: AppTheme.light,
          routerConfig: AppRouter.router,
          locale: settingsState.locale,
          localizationsDelegates: const [
            GlobalMaterialLocalizations.delegate,
            GlobalWidgetsLocalizations.delegate,
            GlobalCupertinoLocalizations.delegate,
          ],
          supportedLocales: const [Locale('en', ''), Locale('ar', '')],
        );
      },
    );
  }
}
