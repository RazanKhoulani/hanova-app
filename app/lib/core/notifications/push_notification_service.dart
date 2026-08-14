import 'dart:convert';

import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

import '../../firebase_options.dart';
import '../constants/api_constants.dart';
import '../network/dio_client.dart';
import '../router/app_router.dart';

@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp(options: DefaultFirebaseOptions.currentPlatform);
}

class PushNotificationService {
  static const _channel = AndroidNotificationChannel(
    'hanova_notifications',
    'Hanova notifications',
    description: 'Orders, appointments, offers, and chat messages.',
    importance: Importance.high,
  );

  final DioClient _dioClient;
  final FirebaseMessaging _messaging;
  final FlutterLocalNotificationsPlugin _localNotifications;
  Map<String, dynamic>? _initialMessageData;
  bool _initialized = false;

  PushNotificationService(
    this._dioClient, {
    FirebaseMessaging? messaging,
    FlutterLocalNotificationsPlugin? localNotifications,
  }) : _messaging = messaging ?? FirebaseMessaging.instance,
       _localNotifications =
           localNotifications ?? FlutterLocalNotificationsPlugin();

  Future<void> initialize() async {
    if (_initialized || !_isMobile) return;
    _initialized = true;

    FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);

    const initializationSettings = InitializationSettings(
      android: AndroidInitializationSettings('@mipmap/ic_launcher'),
      iOS: DarwinInitializationSettings(
        requestAlertPermission: false,
        requestBadgePermission: false,
        requestSoundPermission: false,
      ),
    );

    await _localNotifications.initialize(
      settings: initializationSettings,
      onDidReceiveNotificationResponse: (response) {
        final payload = response.payload;
        if (payload == null || payload.isEmpty) return;

        try {
          final data = jsonDecode(payload);
          if (data is Map<String, dynamic>) _openFromData(data);
        } on FormatException {
          _openFromData(const {});
        }
      },
    );

    if (defaultTargetPlatform == TargetPlatform.android) {
      final android = _localNotifications
          .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin
          >();
      await android?.createNotificationChannel(_channel);
      await android?.requestNotificationsPermission();
    }

    await _messaging.requestPermission(alert: true, badge: true, sound: true);
    await _messaging.setForegroundNotificationPresentationOptions(
      alert: false,
      badge: false,
      sound: false,
    );

    FirebaseMessaging.onMessage.listen(_showForegroundNotification);
    FirebaseMessaging.onMessageOpenedApp.listen(
      (message) => _openFromData(message.data),
    );
    _messaging.onTokenRefresh.listen(_registerToken);

    _initialMessageData = (await _messaging.getInitialMessage())?.data;
  }

  Future<void> syncToken() async {
    if (!_isMobile) return;

    try {
      final token = await _messaging.getToken();
      if (token != null && token.isNotEmpty) {
        await _registerToken(token);
      }
    } catch (error) {
      if (kDebugMode) {
        debugPrint('Unable to register the notification token: $error');
      }
    }
  }

  Future<void> removeCurrentToken() async {
    if (!_isMobile) return;

    try {
      final token = await _messaging.getToken();
      if (token != null && token.isNotEmpty) {
        await _dioClient.delete(
          ApiConstants.deviceTokens,
          data: {'token': token},
        );
      }
    } catch (error) {
      if (kDebugMode) {
        debugPrint('Unable to unregister the notification token: $error');
      }
    } finally {
      try {
        await _messaging.deleteToken();
      } catch (error) {
        if (kDebugMode) {
          debugPrint('Unable to delete the local notification token: $error');
        }
      }
    }
  }

  void handleInitialMessage() {
    final data = _initialMessageData;
    _initialMessageData = null;
    if (data != null) _openFromData(data);
  }

  Future<void> _registerToken(String token) async {
    try {
      await _dioClient.post(
        ApiConstants.deviceTokens,
        data: {
          'token': token,
          'platform': defaultTargetPlatform == TargetPlatform.iOS
              ? 'ios'
              : 'android',
        },
      );
    } catch (error) {
      if (kDebugMode) {
        debugPrint('Unable to sync the notification token: $error');
      }
    }
  }

  Future<void> _showForegroundNotification(RemoteMessage message) async {
    final notification = message.notification;
    final title = notification?.title ?? message.data['title'];
    final body = notification?.body ?? message.data['body'];
    if (title == null && body == null) return;

    await _localNotifications.show(
      id:
          message.messageId?.hashCode ??
          DateTime.now().millisecondsSinceEpoch.remainder(2147483647),
      title: title?.toString(),
      body: body?.toString(),
      notificationDetails: const NotificationDetails(
        android: AndroidNotificationDetails(
          'hanova_notifications',
          'Hanova notifications',
          channelDescription:
              'Orders, appointments, offers, and chat messages.',
          importance: Importance.high,
          priority: Priority.high,
          icon: '@mipmap/ic_launcher',
        ),
        iOS: DarwinNotificationDetails(),
      ),
      payload: jsonEncode(message.data),
    );
  }

  void _openFromData(Map<String, dynamic> data) {
    final type = data['type']?.toString();

    if (type == 'chat_message' || data['conversation_id'] != null) {
      AppRouter.router.go('/chat');
    } else if (type?.startsWith('order_') == true || data['order_id'] != null) {
      AppRouter.router.go('/orders');
    } else {
      AppRouter.router.go('/notifications');
    }
  }

  bool get _isMobile =>
      !kIsWeb &&
      (defaultTargetPlatform == TargetPlatform.android ||
          defaultTargetPlatform == TargetPlatform.iOS);
}
