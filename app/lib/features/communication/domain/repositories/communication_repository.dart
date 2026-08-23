import '../../data/models/message_model.dart';

abstract class CommunicationRepository {
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
