class ApiConstants {
  static const String baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'https://hanova-api-production.up.railway.app/api',
  );
  static const String pusherKey = String.fromEnvironment(
    'PUSHER_APP_KEY',
    defaultValue: '756b61b31f898dafe7e8',
  );
  static const String pusherCluster = String.fromEnvironment(
    'PUSHER_APP_CLUSTER',
    defaultValue: 'ap2',
  );

  static String get backendUrl {
    final apiIndex = baseUrl.indexOf('/api');
    return apiIndex >= 0 ? baseUrl.substring(0, apiIndex) : baseUrl;
  }

  // Auth Endpoints
  static const String register = '/auth/register';
  static const String verifyRegistrationOtp = '/auth/verify-registration-otp';
  static const String resendRegistrationOtp = '/auth/resend-registration-otp';
  static const String login = '/auth/login';
  static const String logout = '/auth/logout';
  static const String profile = '/auth/profile';
  static const String updateProfile = '/auth/profile';
  static const String forgotPassword = '/auth/forgot-password';

  // Products Endpoints
  static const String home = '/home';
  static const String products = '/products';
  static const String productDetails = '/products/'; // Append ID
  static const String categories = '/categories';
  static const String activeOffer = '/offers/active';

  // Cart Endpoints
  static const String cart = '/cart';

  // Order Endpoints
  static const String orders = '/orders';
  static const String confirmOrder = '/orders/'; // /orders/{id}/confirm
  static String markOrderDelivered(int id) => '/orders/$id/delivered';
  static const String deliveryAreas = '/delivery-areas';

  // Clinic & Medical Endpoints
  static const String patients = '/patients';
  static const String patientProgressPhotos = '/patient-progress-photos';
  static const String appointments = '/appointments';
  static const String appointmentAvailableSlots =
      '/appointments/available-slots';
  static const String consultations = '/consultations';

  // Interactive Endpoints
  static const String chatConversations = '/chat/conversations';
  static const String broadcastingAuth = '/broadcasting/auth';
  static const String chatMessages =
      '/chat/conversations/'; // /chat/conversations/{id}/messages
  static const String notifications = '/notifications';
  static const String deviceTokens = '/device-tokens';
  static const String botBootstrap = '/bot/bootstrap';
  static const String botAsk = '/bot/ask';
  static const String botConversation = '/bot/conversation';
  static const String faqs = '/faqs';
}
