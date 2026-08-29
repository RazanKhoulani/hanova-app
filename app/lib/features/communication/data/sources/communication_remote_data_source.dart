import '../../../../core/network/dio_client.dart';
import 'package:dio/dio.dart';
import '../../../../core/network/api_interceptor.dart';
import '../../../../core/constants/api_constants.dart';
import '../models/message_model.dart';

abstract class CommunicationRemoteDataSource {
  Future<int> getConversationId({int? consultationId});
  Future<String?> getConversationPhone({int? consultationId});
  Future<List<MessageModel>> getChatMessages({int? consultationId});
  Future<void> sendChatMessage(String text, {int? consultationId});
  Future<MessageModel> sendChatAttachment(
    String filePath, {
    String? message,
    int? consultationId,
  });
  Future<List<MessageModel>> getBotMessages({
    String? productName,
    String? productDescription,
  });
  Future<MessageModel> getBotBootstrap({
    String? productName,
    String? productDescription,
  });
  Future<MessageModel> sendBotMessage(
    String text, {
    BotOption? option,
    String? productName,
    String? productDescription,
    List<String> askedQuestions = const [],
  });
}

class CommunicationRemoteDataSourceImpl
    implements CommunicationRemoteDataSource {
  final DioClient _dioClient;
  final ApiInterceptor _apiInterceptor;
  final Map<String, int> _chatConversationIds = {};
  final Map<String, String?> _chatConversationPhones = {};
  final Map<String, Future<int>> _chatConversationRequests = {};
  int? _botConversationId;
  late int _sessionVersion;

  CommunicationRemoteDataSourceImpl(this._dioClient, this._apiInterceptor) {
    _sessionVersion = _apiInterceptor.sessionVersion;
  }

  Future<int> _ensureConversationId({int? consultationId}) async {
    _syncSessionCache();
    final key = consultationId?.toString() ?? 'general';
    if (_chatConversationIds[key] != null) return _chatConversationIds[key]!;
    if (_chatConversationRequests[key] != null) {
      return _chatConversationRequests[key]!;
    }

    final request = _loadConversationId(consultationId: consultationId);
    _chatConversationRequests[key] = request;

    try {
      final id = await request;
      _sessionVersion = _apiInterceptor.sessionVersion;
      _chatConversationIds[key] = id;
      return id;
    } finally {
      _chatConversationRequests.remove(key);
    }
  }

  Future<int> _loadConversationId({int? consultationId}) async {
    final key = consultationId?.toString() ?? 'general';
    final response = await _dioClient.get(ApiConstants.chatConversations);
    final conversationsEnvelope = response.data['data'];

    final List<dynamic> conversations =
        conversationsEnvelope is Map<String, dynamic>
        ? (conversationsEnvelope['data'] as List? ?? [])
        : (conversationsEnvelope as List? ?? []);

    final matching = conversations.where((item) {
      if (item is! Map) return false;
      final rawConsultationId = item['consultation_id'];
      if (consultationId == null) return rawConsultationId == null;
      return rawConsultationId?.toString() == consultationId.toString();
    }).toList();

    if (matching.isNotEmpty) {
      final conversation = Map<String, dynamic>.from(matching.first);
      _chatConversationPhones[key] = _contactPhone(conversation);
      final id = conversation['id'];
      return id is num ? id.toInt() : int.parse(id.toString());
    }

    _chatConversationPhones[key] = null;
    final createResponse = await _dioClient.post(
      ApiConstants.chatConversations,
      data: {if (consultationId != null) 'consultation_id': consultationId},
    );
    final id = createResponse.data['data']['id'];
    return id is num ? id.toInt() : int.parse(id.toString());
  }

  void _syncSessionCache() {
    if (_sessionVersion == _apiInterceptor.sessionVersion) return;

    _sessionVersion = _apiInterceptor.sessionVersion;
    _chatConversationIds.clear();
    _chatConversationPhones.clear();
    _chatConversationRequests.clear();
    _botConversationId = null;
  }

  @override
  Future<int> getConversationId({int? consultationId}) async {
    return _ensureConversationId(consultationId: consultationId);
  }

  @override
  Future<String?> getConversationPhone({int? consultationId}) async {
    await _ensureConversationId(consultationId: consultationId);
    final key = consultationId?.toString() ?? 'general';
    return _chatConversationPhones[key];
  }

  String? _contactPhone(Map<String, dynamic> conversation) {
    final user = conversation['user'];
    if (user is Map && user['phone'] != null) {
      return user['phone'].toString();
    }

    for (final key in const ['contact_phone', 'patient_phone', 'user_phone']) {
      final value = conversation[key];
      if (value != null && value.toString().trim().isNotEmpty) {
        return value.toString();
      }
    }

    return null;
  }

  @override
  Future<List<MessageModel>> getChatMessages({int? consultationId}) async {
    final conversationId = await _ensureConversationId(
      consultationId: consultationId,
    );
    final response = await _dioClient.get(
      '${ApiConstants.chatMessages}$conversationId/messages',
    );
    final payload = response.data['data'] ?? response.data;
    final items = payload is List ? payload : <dynamic>[];
    return items.map((json) => MessageModel.fromJson(json)).toList();
  }

  @override
  Future<void> sendChatMessage(String text, {int? consultationId}) async {
    final conversationId = await _ensureConversationId(
      consultationId: consultationId,
    );
    await _dioClient.post(
      '${ApiConstants.chatMessages}$conversationId/messages',
      data: {'message': text},
    );
  }

  @override
  Future<void> sendChatAttachment(
    String filePath, {
    String? message,
    int? consultationId,
  }) async {
    final conversationId = await _ensureConversationId(
      consultationId: consultationId,
    );
    final lower = filePath.toLowerCase();
    final type = RegExp(r'\.(jpg|jpeg|png)$').hasMatch(lower)
        ? 'image'
        : 'file';
    final data = FormData.fromMap({
      'file': await MultipartFile.fromFile(filePath),
      'type': type,
      if (message != null && message.trim().isNotEmpty)
        'message': message.trim(),
    });
    final response = await _dioClient.post(
      '${ApiConstants.chatMessages}$conversationId/messages',
      data: data,
    );

    final payload = response.data is Map
        ? (response.data['data'] ?? response.data)
        : <String, dynamic>{};
    if (payload is! Map) {
      throw StateError('The attachment response did not contain a message.');
    }

    return MessageModel.fromJson(Map<String, dynamic>.from(payload));
  }

  @override
  Future<List<MessageModel>> getBotMessages({
    String? productName,
    String? productDescription,
  }) async {
    _syncSessionCache();
    final response = await _dioClient.get(
      ApiConstants.botConversation,
      queryParameters: {
        if (productName != null && productName.trim().isNotEmpty)
          'product_name': productName,
        if (productDescription != null && productDescription.trim().isNotEmpty)
          'product_description': productDescription,
      },
    );

    final payload = response.data['data'] ?? response.data;
    final conversationId = payload['id'];
    if (conversationId is num) {
      _botConversationId = conversationId.toInt();
    } else if (conversationId != null) {
      _botConversationId = int.tryParse(conversationId.toString());
    }

    final items = payload['messages'] is List
        ? payload['messages'] as List
        : <dynamic>[];
    return items.map((json) => MessageModel.fromJson(json)).toList();
  }

  @override
  Future<MessageModel> getBotBootstrap({
    String? productName,
    String? productDescription,
  }) async {
    final response = await _dioClient.get(
      ApiConstants.botBootstrap,
      queryParameters: {
        if (productName != null && productName.trim().isNotEmpty)
          'product_name': productName,
        if (productDescription != null && productDescription.trim().isNotEmpty)
          'product_description': productDescription,
      },
    );

    final payload = response.data['data'];
    if (response.data['success'] != true || payload is! Map<String, dynamic>) {
      throw const FormatException('Invalid bot bootstrap response.');
    }

    return MessageModel.fromBotPayload(payload);
  }

  @override
  Future<MessageModel> sendBotMessage(
    String text, {
    BotOption? option,
    String? productName,
    String? productDescription,
    List<String> askedQuestions = const [],
  }) async {
    _syncSessionCache();
    final context = <String, dynamic>{
      if (_botConversationId != null) 'conversation_id': _botConversationId,
      if (productName != null && productName.trim().isNotEmpty)
        'product_name': productName,
      if (productDescription != null && productDescription.trim().isNotEmpty)
        'product_description': productDescription,
      if (askedQuestions.isNotEmpty) 'asked_questions': askedQuestions,
    };

    final response = await _dioClient.post(
      ApiConstants.botAsk,
      data: {
        'query': text,
        if (option != null && option.type != 'legacy')
          'option_type': option.type,
        if (option?.id != null) 'option_id': option!.id,
        if (context.isNotEmpty) 'context': context,
      },
    );

    if (response.data['success'] == true &&
        response.data['data'] is Map<String, dynamic>) {
      final conversationId = response.data['data']['conversation_id'];
      if (conversationId is num) {
        _botConversationId = conversationId.toInt();
      } else if (conversationId != null) {
        _botConversationId = int.tryParse(conversationId.toString());
      }

      return MessageModel.fromBotPayload(response.data['data']);
    }

    return MessageModel(
      text: response.data['message']?.toString() ?? 'No answer found.',
      isMe: false,
      timestamp: DateTime.now(),
    );
  }
}
