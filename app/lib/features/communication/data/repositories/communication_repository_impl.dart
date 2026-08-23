import '../../domain/repositories/communication_repository.dart';
import '../models/message_model.dart';
import '../sources/communication_remote_data_source.dart';

class CommunicationRepositoryImpl implements CommunicationRepository {
  final CommunicationRemoteDataSource _remoteDataSource;

  CommunicationRepositoryImpl(this._remoteDataSource);

  @override
  Future<int> getConversationId() async {
    return await _remoteDataSource.getConversationId();
  }

  @override
  Future<List<MessageModel>> getChatMessages() async {
    return await _remoteDataSource.getChatMessages();
  }

  @override
  Future<void> sendChatMessage(String text) async {
    await _remoteDataSource.sendChatMessage(text);
  }

  @override
  Future<List<MessageModel>> getBotMessages({
    String? productName,
    String? productDescription,
  }) async {
    return await _remoteDataSource.getBotMessages(
      productName: productName,
      productDescription: productDescription,
    );
  }

  @override
  Future<MessageModel> getBotBootstrap({
    String? productName,
    String? productDescription,
  }) async {
    return await _remoteDataSource.getBotBootstrap(
      productName: productName,
      productDescription: productDescription,
    );
  }

  @override
  Future<MessageModel> sendBotMessage(
    String text, {
    BotOption? option,
    String? productName,
    String? productDescription,
    List<String> askedQuestions = const [],
  }) async {
    return await _remoteDataSource.sendBotMessage(
      text,
      option: option,
      productName: productName,
      productDescription: productDescription,
      askedQuestions: askedQuestions,
    );
  }
}
