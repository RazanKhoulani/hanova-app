import '../../data/models/message_model.dart';

abstract class CommunicationRepository {
  Future<int> getConversationId({int? consultationId});
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
