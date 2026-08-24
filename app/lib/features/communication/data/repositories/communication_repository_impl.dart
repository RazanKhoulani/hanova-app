import '../../domain/repositories/communication_repository.dart';
import '../models/message_model.dart';
import '../sources/communication_remote_data_source.dart';

class CommunicationRepositoryImpl implements CommunicationRepository {
  final CommunicationRemoteDataSource _remoteDataSource;

  CommunicationRepositoryImpl(this._remoteDataSource);

  @override
  Future<int> getConversationId({int? consultationId}) async {
    return await _remoteDataSource.getConversationId(
      consultationId: consultationId,
    );
  }

  @override
  Future<List<MessageModel>> getChatMessages({int? consultationId}) async {
    return await _remoteDataSource.getChatMessages(
      consultationId: consultationId,
    );
  }

  @override
  Future<void> sendChatMessage(String text, {int? consultationId}) async {
    await _remoteDataSource.sendChatMessage(
      text,
      consultationId: consultationId,
    );
  }

  @override
  Future<void> sendChatAttachment(
    String filePath, {
    String? message,
    int? consultationId,
  }) async {
    await _remoteDataSource.sendChatAttachment(
      filePath,
      message: message,
      consultationId: consultationId,
    );
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
