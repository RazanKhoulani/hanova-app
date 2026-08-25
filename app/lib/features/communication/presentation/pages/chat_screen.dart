import 'dart:async';

import 'package:flutter/material.dart';
import 'package:file_picker/file_picker.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';
import '../../../../core/constants/api_constants.dart';
import '../../../../core/localization/app_localizations.dart';
import '../../../../core/network/dio_client.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../../injection_container.dart';
import '../../../auth/presentation/bloc/auth_bloc.dart';
import '../../../auth/presentation/bloc/auth_state.dart';
import '../../domain/repositories/communication_repository.dart';
import '../bloc/communication_bloc.dart';
import '../../data/models/message_model.dart';

class ChatScreen extends StatefulWidget {
  final int? consultationId;

  const ChatScreen({super.key, this.consultationId});

  @override
  State<ChatScreen> createState() => _ChatScreenState();
}

class _ChatScreenState extends State<ChatScreen> {
  final TextEditingController _controller = TextEditingController();
  final PusherChannelsFlutter _pusher = PusherChannelsFlutter.getInstance();
  String? _pusherChannelName;
  bool _realtimeStarted = false;
  Timer? _messageRefreshTimer;

  @override
  void initState() {
    super.initState();
    if (context.read<AuthBloc>().state is AuthAuthenticated) {
      context.read<CommunicationBloc>().add(
        CommunicationFetchChatMessages(consultationId: widget.consultationId),
      );
      _initRealtime();
      _startMessageRefreshFallback();
    }
  }

  void _startMessageRefreshFallback() {
    // Pusher is immediate when connected; polling keeps an open chat current
    // if a mobile network temporarily drops the private-channel subscription.
    _messageRefreshTimer ??= Timer.periodic(const Duration(seconds: 8), (_) {
      if (!mounted) return;
      context.read<CommunicationBloc>().add(
        CommunicationFetchChatMessages(
          showLoading: false,
          consultationId: widget.consultationId,
        ),
      );
    });
  }

  Future<void> _initRealtime() async {
    if (_realtimeStarted || ApiConstants.pusherKey.isEmpty) return;
    _realtimeStarted = true;

    try {
      final conversationId = await sl<CommunicationRepository>()
          .getConversationId(consultationId: widget.consultationId);
      final channelName = 'private-conversation.$conversationId';
      _pusherChannelName = channelName;

      await _pusher.init(
        apiKey: ApiConstants.pusherKey,
        cluster: ApiConstants.pusherCluster,
        useTLS: true,
        onAuthorizer: _authorizePusher,
        onEvent: _handleRealtimeEvent,
      );
      await _pusher.subscribe(channelName: channelName);
      await _pusher.connect();
    } catch (error) {
      _realtimeStarted = false;
      if (kDebugMode) debugPrint('Pusher connection failed: $error');
    }
  }

  Future<dynamic> _authorizePusher(
    String channelName,
    String socketId,
    dynamic options,
  ) async {
    final response = await sl<DioClient>().post(
      ApiConstants.broadcastingAuth,
      data: {'socket_id': socketId, 'channel_name': channelName},
    );

    return response.data;
  }

  void _handleRealtimeEvent(PusherEvent event) {
    if (!mounted || event.channelName != _pusherChannelName) return;

    final eventName = event.eventName.replaceFirst('.', '');
    if (eventName == 'message.sent') {
      context.read<CommunicationBloc>().add(
        CommunicationFetchChatMessages(
          showLoading: false,
          consultationId: widget.consultationId,
        ),
      );
    }
  }

  void _sendMessage() {
    if (_controller.text.trim().isNotEmpty) {
      context.read<CommunicationBloc>().add(
        CommunicationSendChatMessage(
          _controller.text.trim(),
          consultationId: widget.consultationId,
        ),
      );
      _controller.clear();
    }
  }

  Future<void> _pickAttachment() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: const ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
    );
    final path = result?.files.single.path;
    if (path == null || !mounted) return;
    context.read<CommunicationBloc>().add(
      CommunicationSendChatAttachment(
        path,
        message: _controller.text.trim().isEmpty
            ? null
            : _controller.text.trim(),
        consultationId: widget.consultationId,
      ),
    );
    _controller.clear();
  }

  @override
  void dispose() {
    final channelName = _pusherChannelName;
    if (channelName != null) {
      _pusher.unsubscribe(channelName: channelName);
    }
    _messageRefreshTimer?.cancel();
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return BlocConsumer<AuthBloc, AuthState>(
      listenWhen: (previous, current) =>
          previous is! AuthAuthenticated && current is AuthAuthenticated,
      listener: (context, state) {
        context.read<CommunicationBloc>().add(
          CommunicationFetchChatMessages(consultationId: widget.consultationId),
        );
        _initRealtime();
        _startMessageRefreshFallback();
      },
      builder: (context, authState) {
        if (authState is AuthLoading || authState is AuthInitial) {
          return const Scaffold(
            body: Center(child: CircularProgressIndicator()),
          );
        }

        if (authState is! AuthAuthenticated) {
          return Scaffold(
            backgroundColor: AppColors.background,
            appBar: AppBar(title: Text(context.tr('clinic_support'))),
            body: Center(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(
                      Icons.lock_outline_rounded,
                      size: 64,
                      color: AppColors.textLight,
                    ),
                    const SizedBox(height: 16),
                    Text(
                      context.tr('login_required'),
                      style: const TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      context.tr('chat_login_note'),
                      textAlign: TextAlign.center,
                      style: const TextStyle(color: AppColors.textSecondary),
                    ),
                    const SizedBox(height: 24),
                    ElevatedButton(
                      onPressed: () => context.push('/login'),
                      child: Text(context.tr('login')),
                    ),
                  ],
                ),
              ),
            ),
          );
        }

        return Scaffold(
          backgroundColor: AppColors.background,
          appBar: AppBar(
            title: Column(
              children: [
                Text(context.tr('clinic_support')),
                Text(
                  context.tr('live_chat_subtitle'),
                  style: const TextStyle(
                    fontSize: 12,
                    color: AppColors.success,
                    fontWeight: FontWeight.normal,
                  ),
                ),
              ],
            ),
          ),
          body: Column(
            children: [
              Expanded(
                child: BlocBuilder<CommunicationBloc, CommunicationState>(
                  builder: (context, state) {
                    if (state is CommunicationLoading) {
                      return const Center(child: CircularProgressIndicator());
                    }

                    if (state is CommunicationChatLoaded) {
                      return _buildMessages(state.messages);
                    }

                    if (state is CommunicationFailure) {
                      return Center(
                        child: Padding(
                          padding: const EdgeInsets.all(24),
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(
                                Icons.cloud_off_rounded,
                                size: 52,
                                color: AppColors.textLight,
                              ),
                              const SizedBox(height: 12),
                              Text(
                                state.message,
                                textAlign: TextAlign.center,
                                style: const TextStyle(
                                  color: AppColors.textSecondary,
                                ),
                              ),
                              const SizedBox(height: 16),
                              OutlinedButton(
                                onPressed: () =>
                                    context.read<CommunicationBloc>().add(
                                      CommunicationFetchChatMessages(
                                        consultationId: widget.consultationId,
                                      ),
                                    ),
                                child: Text(context.tr('try_again')),
                              ),
                            ],
                          ),
                        ),
                      );
                    }

                    return _buildMessages(const <MessageModel>[]);
                  },
                ),
              ),
              _buildInputBar(),
            ],
          ),
        );
      },
    );
  }

  Widget _buildMessages(List<MessageModel> messages) {
    if (messages.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 80,
                height: 80,
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: const [
                    BoxShadow(color: AppColors.cardShadow, blurRadius: 10),
                  ],
                ),
                child: const Icon(
                  Icons.chat_bubble_outline_rounded,
                  color: AppColors.primary,
                  size: 36,
                ),
              ),
              const SizedBox(height: 14),
              Text(
                context.tr('start_chat'),
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 6),
              Text(
                context.tr('start_chat_note'),
                textAlign: TextAlign.center,
                style: const TextStyle(color: AppColors.textSecondary),
              ),
            ],
          ),
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 10),
      itemCount: messages.length,
      itemBuilder: (context, index) {
        final msg = messages[index];
        final time = _formatMessageTime(msg.timestamp);
        return _buildMessage(msg, time);
      },
    );
  }

  String _formatMessageTime(DateTime value) {
    if (Localizations.localeOf(context).languageCode != 'ar') {
      return DateFormat('hh:mm a').format(value);
    }

    final hour = value.hour % 12 == 0 ? 12 : value.hour % 12;
    final minute = value.minute.toString().padLeft(2, '0');
    final period = value.hour < 12 ? '\u0635' : '\u0645';
    return '${hour.toString().padLeft(2, '0')}:$minute $period';
  }

  Widget _buildMessage(MessageModel message, String time) {
    final text = message.text;
    final isMe = message.isMe;
    final attachment = message.attachmentUrl;
    final isImage = attachment != null && RegExp(r'\.(jpe?g|png|webp)(\?.*)?$', caseSensitive: false).hasMatch(attachment);
    return Align(
      alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.only(bottom: 12),
        padding: const EdgeInsets.fromLTRB(14, 12, 14, 10),
        constraints: const BoxConstraints(maxWidth: 290),
        decoration: BoxDecoration(
          color: isMe ? AppColors.primary : Colors.white,
          borderRadius: BorderRadius.only(
            topLeft: const Radius.circular(16),
            topRight: const Radius.circular(16),
            bottomLeft: Radius.circular(isMe ? 16 : 4),
            bottomRight: Radius.circular(isMe ? 4 : 16),
          ),
          boxShadow: isMe
              ? null
              : const [BoxShadow(color: AppColors.cardShadow, blurRadius: 8)],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (attachment != null) ...[
              if (isImage)
                ClipRRect(
                  borderRadius: BorderRadius.circular(10),
                  child: Image.network(attachment, width: 240, height: 180, fit: BoxFit.cover, errorBuilder: (_, error, stackTrace) => const Icon(Icons.broken_image_outlined)),
                )
              else
                Row(mainAxisSize: MainAxisSize.min, children: [
                  Icon(Icons.insert_drive_file_rounded, color: isMe ? Colors.white : AppColors.primary),
                  const SizedBox(width: 8),
                  Flexible(child: Text(context.tr('medical_attachment'), style: TextStyle(color: isMe ? Colors.white : AppColors.textPrimary))),
                ]),
              if (text.isNotEmpty) const SizedBox(height: 8),
            ],
            if (text.isNotEmpty)
              Text(
                text,
                style: TextStyle(
                  color: isMe ? Colors.white : AppColors.textPrimary,
                  height: 1.45,
                ),
              ),
            const SizedBox(height: 4),
            Text(
              time,
              style: TextStyle(
                color: isMe ? Colors.white70 : AppColors.textLight,
                fontSize: 10,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInputBar() {
    return Container(
      padding: const EdgeInsets.fromLTRB(14, 10, 14, 26),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Row(
        children: [
          IconButton(
            tooltip: context.tr('attach_medical_file'),
            onPressed: _pickAttachment,
            icon: const Icon(
              Icons.attach_file_rounded,
              color: AppColors.primary,
            ),
          ),
          Expanded(
            child: TextField(
              controller: _controller,
              onSubmitted: (_) => _sendMessage(),
              decoration: InputDecoration(
                hintText: context.tr('write_message'),
                filled: true,
                fillColor: AppColors.background,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(24),
                  borderSide: BorderSide.none,
                ),
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 18,
                  vertical: 10,
                ),
              ),
            ),
          ),
          const SizedBox(width: 10),
          CircleAvatar(
            backgroundColor: AppColors.primary,
            child: IconButton(
              icon: const Icon(
                Icons.send_rounded,
                color: Colors.white,
                size: 20,
              ),
              onPressed: _sendMessage,
            ),
          ),
        ],
      ),
    );
  }
}
