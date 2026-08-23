import '../../../../core/network/dio_client.dart';
import '../../../../core/network/api_interceptor.dart';
import '../../../../core/constants/api_constants.dart';
import '../models/message_model.dart';

abstract class CommunicationRemoteDataSource {
  Future<int> getConversationId();
  Future<List<MessageModel>> getChatMessages();
  Future<void> sendChatMessage(String text);
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
  int? _chatConversationId;
  Future<int>? _chatConversationRequest;
  int? _botConversationId;
  late int _sessionVersion;

  CommunicationRemoteDataSourceImpl(this._dioClient, this._apiInterceptor) {
    _sessionVersion = _apiInterceptor.sessionVersion;
  }

  Future<int> _ensureConversationId() async {
    _syncSessionCache();
    if (_chatConversationId != null) return _chatConversationId!;
    if (_chatConversationRequest != null) return _chatConversationRequest!;

    final request = _loadConversationId();
    _chatConversationRequest = request;

    try {
      final id = await request;
      _sessionVersion = _apiInterceptor.sessionVersion;
      _chatConversationId = id;
      return id;
    } finally {
      _chatConversationRequest = null;
    }
  }

  Future<int> _loadConversationId() async {
    final response = await _dioClient.get(ApiConstants.chatConversations);
    final conversationsEnvelope = response.data['data'];

    final List<dynamic> conversations =
        conversationsEnvelope is Map<String, dynamic>
        ? (conversationsEnvelope['data'] as List? ?? [])
        : (conversationsEnvelope as List? ?? []);

    if (conversations.isNotEmpty) {
      final id = conversations.first['id'];
      return id is num ? id.toInt() : int.parse(id.toString());
    }

    final createResponse = await _dioClient.post(
      ApiConstants.chatConversations,
      data: {},
    );
    final id = createResponse.data['data']['id'];
    return id is num ? id.toInt() : int.parse(id.toString());
  }

  void _syncSessionCache() {
    if (_sessionVersion == _apiInterceptor.sessionVersion) return;

    _sessionVersion = _apiInterceptor.sessionVersion;
    _chatConversationId = null;
    _chatConversationRequest = null;
    _botConversationId = null;
  }

  @override
  Future<int> getConversationId() async {
    return _ensureConversationId();
  }

  @override
  Future<List<MessageModel>> getChatMessages() async {
    final conversationId = await _ensureConversationId();
    final response = await _dioClient.get(
      '${ApiConstants.chatMessages}$conversationId/messages',
    );
    final payload = response.data['data'] ?? response.data;
    final items = payload is List ? payload : <dynamic>[];
    return items.map((json) => MessageModel.fromJson(json)).toList();
  }

  @override
  Future<void> sendChatMessage(String text) async {
    final conversationId = await _ensureConversationId();
    await _dioClient.post(
      '${ApiConstants.chatMessages}$conversationId/messages',
      data: {'message': text},
    );
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
