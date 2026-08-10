import '../../data/models/message_model.dart';

abstract class CommunicationRepository {
  Future<int> getConversationId();
  Future<List<MessageModel>> getChatMessages();
  Future<void> sendChatMessage(String text);
  Future<List<MessageModel>> getBotMessages({
    String? productName,
    String? productDescription,
  });
  Future<MessageModel> sendBotMessage(
    String text, {
    String? productName,
    String? productDescription,
    List<String> askedQuestions = const [],
  });
}
