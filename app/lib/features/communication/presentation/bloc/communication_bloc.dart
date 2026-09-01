import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../../core/network/api_error_message.dart';
import '../../domain/repositories/communication_repository.dart';
import '../../data/models/message_model.dart';

// Events
abstract class CommunicationEvent {}

class CommunicationFetchChatMessages extends CommunicationEvent {
  final bool showLoading;
  final int? consultationId;

  CommunicationFetchChatMessages({
    this.showLoading = true,
    this.consultationId,
  });
}

class CommunicationRealtimeChatMessageReceived extends CommunicationEvent {
  final MessageModel message;
  final int? consultationId;

  CommunicationRealtimeChatMessageReceived(
    this.message, {
    this.consultationId,
  });
}

class CommunicationInitializeBot extends CommunicationEvent {
  final bool loadHistory;
  final String? productName;
  final String? productDescription;

  CommunicationInitializeBot({
    required this.loadHistory,
    this.productName,
    this.productDescription,
  });
}

class CommunicationSendChatMessage extends CommunicationEvent {
  final String text;
  final int? consultationId;
  CommunicationSendChatMessage(this.text, {this.consultationId});
}

class CommunicationSendChatAttachment extends CommunicationEvent {
  final String filePath;
  final String? message;
  final int? consultationId;
  CommunicationSendChatAttachment(
    this.filePath, {
    this.message,
    this.consultationId,
  });
}

class CommunicationSendBotMessage extends CommunicationEvent {
  final String text;
  final BotOption? option;
  final String? productName;
  final String? productDescription;

  CommunicationSendBotMessage(
    this.text, {
    this.option,
    this.productName,
    this.productDescription,
  });
}

// States
abstract class CommunicationState {}

class CommunicationInitial extends CommunicationState {}

class CommunicationLoading extends CommunicationState {}

class CommunicationChatLoaded extends CommunicationState {
  final List<MessageModel> messages;
  CommunicationChatLoaded(this.messages);
}

class CommunicationBotLoaded extends CommunicationState {
  final List<MessageModel> messages;
  CommunicationBotLoaded(this.messages);
}

class CommunicationFailure extends CommunicationState {
  final String message;
  CommunicationFailure(this.message);
}

// Bloc
class CommunicationBloc extends Bloc<CommunicationEvent, CommunicationState> {
  final CommunicationRepository _repository;
  final List<MessageModel> _chatMessages = [];
  final List<MessageModel> _botMessages = [];
  int? _activeChatConsultationId;

  CommunicationBloc(this._repository) : super(CommunicationInitial()) {
    on<CommunicationFetchChatMessages>(_onFetchChatMessages);
    on<CommunicationRealtimeChatMessageReceived>(
      _onRealtimeChatMessageReceived,
    );
    on<CommunicationInitializeBot>(_onInitializeBot);
    on<CommunicationSendChatMessage>(_onSendChatMessage);
    on<CommunicationSendChatAttachment>(_onSendChatAttachment);
    on<CommunicationSendBotMessage>(_onSendBotMessage);
  }

  Future<void> _onFetchChatMessages(
    CommunicationFetchChatMessages event,
    Emitter<CommunicationState> emit,
  ) async {
    if (_activeChatConsultationId != event.consultationId) {
      _activeChatConsultationId = event.consultationId;
      _chatMessages.clear();
    }
    if (event.showLoading && _chatMessages.isEmpty) {
      emit(CommunicationLoading());
    }
    try {
      final messages = await _repository.getChatMessages(
        consultationId: event.consultationId,
      );
      _chatMessages.clear();
      _chatMessages.addAll(messages);
      emit(CommunicationChatLoaded(List.from(_chatMessages)));
    } catch (e) {
      emit(CommunicationFailure(ApiErrorMessage.from(e)));
    }
  }

  void _onRealtimeChatMessageReceived(
    CommunicationRealtimeChatMessageReceived event,
    Emitter<CommunicationState> emit,
  ) {
    if (_activeChatConsultationId != event.consultationId) return;

    final messageId = event.message.id;
    if (messageId != null &&
        _chatMessages.any((message) => message.id == messageId)) {
      return;
    }

    if (event.message.isMe) {
      final pendingIndex = _chatMessages.indexWhere(
        (message) =>
            message.id == null &&
            message.isMe &&
            message.text == event.message.text &&
            message.timestamp
                    .difference(event.message.timestamp)
                    .inSeconds
                    .abs() <=
                30,
      );
      if (pendingIndex >= 0) {
        _chatMessages[pendingIndex] = event.message;
        emit(CommunicationChatLoaded(List.from(_chatMessages)));
        return;
      }
    }

    _chatMessages.add(event.message);
    emit(CommunicationChatLoaded(List.from(_chatMessages)));
  }

  Future<void> _onInitializeBot(
    CommunicationInitializeBot event,
    Emitter<CommunicationState> emit,
  ) async {
    emit(CommunicationLoading());
    _botMessages.clear();

    try {
      if (event.loadHistory) {
        final messages = await _repository.getBotMessages(
          productName: event.productName,
          productDescription: event.productDescription,
        );
        _botMessages.addAll(messages);
        _hidePreviousBotOptions();
      }

      final bootstrapMessage = await _repository.getBotBootstrap(
        productName: event.productName,
        productDescription: event.productDescription,
      );
      _botMessages.add(bootstrapMessage);
      emit(CommunicationBotLoaded(List.from(_botMessages)));
    } catch (e) {
      emit(CommunicationFailure(ApiErrorMessage.from(e)));
    }
  }

  Future<void> _onSendChatMessage(
    CommunicationSendChatMessage event,
    Emitter<CommunicationState> emit,
  ) async {
    final newMessage = MessageModel(
      text: event.text,
      isMe: true,
      timestamp: DateTime.now(),
    );
    _chatMessages.add(newMessage);
    emit(CommunicationChatLoaded(List.from(_chatMessages)));

    try {
      await _repository.sendChatMessage(
        event.text,
        consultationId: event.consultationId,
      );
    } catch (e) {
      _chatMessages.remove(newMessage);
      _chatMessages.add(
        MessageModel(
          text: ApiErrorMessage.from(e),
          isMe: false,
          timestamp: DateTime.now(),
        ),
      );
      emit(CommunicationChatLoaded(List.from(_chatMessages)));
    }
  }

  Future<void> _onSendChatAttachment(
    CommunicationSendChatAttachment event,
    Emitter<CommunicationState> emit,
  ) async {
    try {
      final message = await _repository.sendChatAttachment(
        event.filePath,
        message: event.message,
        consultationId: event.consultationId,
      );
      // The API response and the realtime broadcast can both arrive for the
      // same upload. Keep one bubble only.
      final alreadyPresent = message.id != null &&
          _chatMessages.any((item) => item.id == message.id);
      if (!alreadyPresent) _chatMessages.add(message);
      emit(CommunicationChatLoaded(List.from(_chatMessages)));
    } catch (e) {
      emit(CommunicationFailure(ApiErrorMessage.from(e)));
    }
  }

  Future<void> _onSendBotMessage(
    CommunicationSendBotMessage event,
    Emitter<CommunicationState> emit,
  ) async {
    _hidePreviousBotOptions();

    final userMsg = MessageModel(
      text: event.text,
      isMe: true,
      timestamp: DateTime.now(),
    );
    _botMessages.add(userMsg);
    emit(CommunicationBotLoaded(List.from(_botMessages)));

    try {
      final askedQuestions = _botMessages
          .where((message) => message.isMe)
          .map((message) => message.text)
          .toList();

      final botMsg = await _repository.sendBotMessage(
        event.text,
        option: event.option,
        productName: event.productName,
        productDescription: event.productDescription,
        askedQuestions: askedQuestions,
      );
      _botMessages.add(botMsg);
      emit(CommunicationBotLoaded(List.from(_botMessages)));
    } catch (e) {
      _botMessages.add(
        MessageModel(
          text: ApiErrorMessage.from(e),
          isMe: false,
          timestamp: DateTime.now(),
        ),
      );
      emit(CommunicationBotLoaded(List.from(_botMessages)));
    }
  }

  void _hidePreviousBotOptions() {
    for (var i = 0; i < _botMessages.length; i++) {
      final message = _botMessages[i];
      if (message.isMe || message.options == null || message.options!.isEmpty) {
        continue;
      }

      _botMessages[i] = MessageModel(
        id: message.id,
        text: message.text,
        isMe: message.isMe,
        timestamp: message.timestamp,
        options: const [],
      );
    }
  }
}
