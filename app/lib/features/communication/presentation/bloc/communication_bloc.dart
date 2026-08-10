import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../../core/network/api_error_message.dart';
import '../../domain/repositories/communication_repository.dart';
import '../../data/models/message_model.dart';

// Events
abstract class CommunicationEvent {}

class CommunicationFetchChatMessages extends CommunicationEvent {
  final bool showLoading;

  CommunicationFetchChatMessages({this.showLoading = true});
}

class CommunicationFetchBotConversation extends CommunicationEvent {
  final String? productName;
  final String? productDescription;

  CommunicationFetchBotConversation({
    this.productName,
    this.productDescription,
  });
}

class CommunicationClearBotConversation extends CommunicationEvent {}

class CommunicationSendChatMessage extends CommunicationEvent {
  final String text;
  CommunicationSendChatMessage(this.text);
}

class CommunicationSendBotMessage extends CommunicationEvent {
  final String text;
  final String? productName;
  final String? productDescription;

  CommunicationSendBotMessage(
    this.text, {
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

  CommunicationBloc(this._repository) : super(CommunicationInitial()) {
    on<CommunicationFetchChatMessages>(_onFetchChatMessages);
    on<CommunicationFetchBotConversation>(_onFetchBotConversation);
    on<CommunicationClearBotConversation>(_onClearBotConversation);
    on<CommunicationSendChatMessage>(_onSendChatMessage);
    on<CommunicationSendBotMessage>(_onSendBotMessage);
  }

  Future<void> _onFetchChatMessages(
    CommunicationFetchChatMessages event,
    Emitter<CommunicationState> emit,
  ) async {
    if (event.showLoading && _chatMessages.isEmpty) {
      emit(CommunicationLoading());
    }
    try {
      final messages = await _repository.getChatMessages();
      _chatMessages.clear();
      _chatMessages.addAll(messages);
      emit(CommunicationChatLoaded(List.from(_chatMessages)));
    } catch (e) {
      emit(CommunicationFailure(ApiErrorMessage.from(e)));
    }
  }

  Future<void> _onFetchBotConversation(
    CommunicationFetchBotConversation event,
    Emitter<CommunicationState> emit,
  ) async {
    try {
      final messages = await _repository.getBotMessages(
        productName: event.productName,
        productDescription: event.productDescription,
      );
      _botMessages.clear();
      _botMessages.addAll(messages);
      emit(CommunicationBotLoaded(List.from(_botMessages)));
    } catch (_) {
      emit(CommunicationBotLoaded(List.from(_botMessages)));
    }
  }

  void _onClearBotConversation(
    CommunicationClearBotConversation event,
    Emitter<CommunicationState> emit,
  ) {
    _botMessages.clear();
    emit(CommunicationBotLoaded(List.from(_botMessages)));
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
      await _repository.sendChatMessage(event.text);
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
