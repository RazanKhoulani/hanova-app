import 'package:firebase_core/firebase_core.dart' show FirebaseOptions;
import 'package:flutter/foundation.dart'
    show TargetPlatform, defaultTargetPlatform, kIsWeb;

class DefaultFirebaseOptions {
  static FirebaseOptions get currentPlatform {
    if (kIsWeb) return web;

    return switch (defaultTargetPlatform) {
      TargetPlatform.android => android,
      TargetPlatform.iOS => ios,
      TargetPlatform.macOS => ios,
      _ => throw UnsupportedError(
        'Firebase is not configured for this platform.',
      ),
    };
  }

  static const FirebaseOptions web = FirebaseOptions(
    apiKey: 'AIzaSyCx5k2Ex9fvtW4S6AAFSAvk-gw-D5xO3lk',
    appId: '1:1009458279788:web:b106e0341471b9dc68af49',
    messagingSenderId: '1009458279788',
    projectId: 'hanva-app',
    authDomain: 'hanva-app.firebaseapp.com',
    storageBucket: 'hanva-app.firebasestorage.app',
    measurementId: 'G-V1X0DSM95L',
  );

  static const FirebaseOptions android = FirebaseOptions(
    apiKey: 'AIzaSyDtUEuC5khPzbPllcj1etxRWT3QfVViiXc',
    appId: '1:1009458279788:android:024287b1b6ffef8168af49',
    messagingSenderId: '1009458279788',
    projectId: 'hanva-app',
    storageBucket: 'hanva-app.firebasestorage.app',
  );

  static const FirebaseOptions ios = FirebaseOptions(
    apiKey: 'AIzaSyCU7SD0E3xeLjHdDDK_RsAEi6FFR8nO3mQ',
    appId: '1:1009458279788:ios:0139ac37ca078cb168af49',
    messagingSenderId: '1009458279788',
    projectId: 'hanva-app',
    storageBucket: 'hanva-app.firebasestorage.app',
    iosBundleId: 'hanova-app.com',
  );
}
