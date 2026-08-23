import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/localization/app_localizations.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../auth/presentation/bloc/auth_bloc.dart';
import '../../../auth/presentation/bloc/auth_state.dart';
import '../../data/models/message_model.dart';
import '../bloc/communication_bloc.dart';

class BotScreen extends StatefulWidget {
  final String? productName;
  final String? productDescription;

  const BotScreen({super.key, this.productName, this.productDescription});

  @override
  State<BotScreen> createState() => _BotScreenState();
}

class _BotScreenState extends State<BotScreen> {
  final TextEditingController _controller = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) {
        return;
      }

      final authState = context.read<AuthBloc>().state;
      context.read<CommunicationBloc>().add(
        CommunicationInitializeBot(
          loadHistory: authState is AuthAuthenticated,
          productName: widget.productName,
          productDescription: widget.productDescription,
        ),
      );
    });
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _sendMessage([BotOption? option]) {
    final msgText = option?.label ?? _controller.text.trim();
    if (msgText.isEmpty) {
      return;
    }

    if (option?.type == 'book_consultation' || _isBookConsultation(msgText)) {
      final authState = context.read<AuthBloc>().state;
      if (authState is AuthAuthenticated) {
        context.go('/home?tab=1');
        return;
      }

      context.push('/login');
      return;
    }

    context.read<CommunicationBloc>().add(
      CommunicationSendBotMessage(
        msgText,
        option: option,
        productName: widget.productName,
        productDescription: widget.productDescription,
      ),
    );

    if (option == null) {
      _controller.clear();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(title: Text(context.tr('bot_title')), centerTitle: true),
      body: Column(
        children: [
          _buildHeader(),
          Expanded(
            child: BlocBuilder<CommunicationBloc, CommunicationState>(
              builder: (context, state) {
                if (state is CommunicationLoading ||
                    state is CommunicationInitial) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (state is CommunicationFailure) {
                  return _buildFailure(state.message);
                }

                var messages = <MessageModel>[];
                if (state is CommunicationBotLoaded) {
                  messages = state.messages;
                }

                return ListView(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 10),
                  children: [
                    ...messages.map(
                      (msg) => msg.isMe
                          ? _buildUserMessage(msg.text)
                          : _buildBotMessage(msg),
                    ),
                  ],
                );
              },
            ),
          ),
          _buildInputBar(),
        ],
      ),
    );
  }

  bool _isBookConsultation(String text) {
    final normalized = text.trim().toLowerCase();

    return text == context.readTr('book_consultation') ||
        normalized == 'book consultation' ||
        text.trim() == 'احجزي استشارة';
  }

  Widget _buildHeader() {
    final productName = widget.productName;
    final headerText = productName == null || productName.trim().isEmpty
        ? _defaultHeader()
        : _productHeader(productName);

    return Container(
      margin: const EdgeInsets.fromLTRB(16, 10, 16, 0),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFFFCEAF0), Color(0xFFFFF5ED)],
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        children: [
          const CircleAvatar(
            backgroundColor: Colors.white,
            child: Icon(Icons.smart_toy_rounded, color: AppColors.primary),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              headerText,
              style: const TextStyle(
                color: AppColors.textPrimary,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _quickChip(BotOption option) {
    final isNavigation = option.type == 'topics';
    final isBooking = option.type == 'book_consultation';

    return ActionChip(
      avatar: isNavigation
          ? const Icon(Icons.arrow_back_rounded, size: 18)
          : isBooking
          ? const Icon(Icons.calendar_month_rounded, size: 18)
          : null,
      label: Text(option.label),
      onPressed: () => _sendMessage(option),
      side: BorderSide(
        color: isNavigation ? AppColors.textSecondary : AppColors.primary,
      ),
      backgroundColor: isBooking ? AppColors.primaryLight : Colors.white,
      labelStyle: TextStyle(
        color: isNavigation ? AppColors.textSecondary : AppColors.primary,
      ),
    );
  }

  Widget _buildFailure(String message) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(
              Icons.error_outline_rounded,
              color: AppColors.danger,
              size: 42,
            ),
            const SizedBox(height: 12),
            Text(message, textAlign: TextAlign.center),
            const SizedBox(height: 12),
            TextButton(
              onPressed: _initializeBot,
              child: Text(context.tr('try_again')),
            ),
          ],
        ),
      ),
    );
  }

  void _initializeBot() {
    final authState = context.read<AuthBloc>().state;
    context.read<CommunicationBloc>().add(
      CommunicationInitializeBot(
        loadHistory: authState is AuthAuthenticated,
        productName: widget.productName,
        productDescription: widget.productDescription,
      ),
    );
  }

  Widget _buildBotMessage(MessageModel msg) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const CircleAvatar(
            radius: 16,
            backgroundColor: AppColors.primaryLight,
            child: Icon(
              Icons.smart_toy_rounded,
              color: AppColors.primary,
              size: 18,
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: const BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.only(
                      topRight: Radius.circular(16),
                      bottomLeft: Radius.circular(16),
                      bottomRight: Radius.circular(16),
                    ),
                  ),
                  child: Text(msg.text, style: const TextStyle(height: 1.5)),
                ),
                if (msg.options != null && msg.options!.isNotEmpty) ...[
                  const SizedBox(height: 10),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: msg.options!.map(_quickChip).toList(),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildUserMessage(String text) {
    return Align(
      alignment: Alignment.centerRight,
      child: Container(
        margin: const EdgeInsets.only(bottom: 12, left: 40),
        padding: const EdgeInsets.all(14),
        decoration: const BoxDecoration(
          color: AppColors.primary,
          borderRadius: BorderRadius.only(
            topLeft: Radius.circular(16),
            topRight: Radius.circular(16),
            bottomLeft: Radius.circular(16),
          ),
        ),
        child: Text(
          text,
          style: const TextStyle(color: Colors.white, height: 1.5),
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
          Expanded(
            child: TextField(
              controller: _controller,
              onSubmitted: (_) => _sendMessage(),
              decoration: InputDecoration(
                hintText: context.tr('ask_bot_hint'),
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

  String _productHeader(String productName) {
    final isArabic = Localizations.localeOf(context).languageCode == 'ar';
    if (isArabic) {
      return 'اسألي البوت عن $productName أو عن مناسبته لحالتك.';
    }

    return 'Ask the bot about $productName or whether it fits your concern.';
  }

  String _defaultHeader() {
    final isArabic = Localizations.localeOf(context).languageCode == 'ar';
    if (isArabic) {
      return 'استخدمي البوت التفاعلي لفهم مشاكل البشرة والشعر بسرعة.';
    }

    return 'Use the interactive bot to understand skin and hair concerns quickly.';
  }
}
