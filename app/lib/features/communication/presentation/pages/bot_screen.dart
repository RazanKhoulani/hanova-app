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
      final communicationBloc = context.read<CommunicationBloc>();

      if (authState is AuthAuthenticated) {
        communicationBloc.add(
          CommunicationFetchBotConversation(
            productName: widget.productName,
            productDescription: widget.productDescription,
          ),
        );
      } else {
        communicationBloc.add(CommunicationClearBotConversation());
      }
    });
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _sendMessage([String? text]) {
    final msgText = text ?? _controller.text.trim();
    if (msgText.isEmpty) {
      return;
    }

    if (_isBookConsultation(msgText)) {
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
        productName: widget.productName,
        productDescription: widget.productDescription,
      ),
    );

    if (text == null) {
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
                var messages = <MessageModel>[];
                if (state is CommunicationBotLoaded) {
                  messages = state.messages;
                }

                return ListView(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 10),
                  children: [
                    if (messages.isEmpty) _buildBotMessage(_initialMessage()),
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

  MessageModel _initialMessage() {
    return MessageModel(
      text: widget.productName == null
          ? context.tr('bot_welcome')
          : _productWelcome(widget.productName!),
      isMe: false,
      timestamp: DateTime.now(),
      options: _topicOptions(),
    );
  }

  bool _isBookConsultation(String text) {
    final normalized = text.trim().toLowerCase();

    return text == context.readTr('book_consultation') ||
        normalized == 'book consultation' ||
        text.trim() == 'احجزي استشارة';
  }

  List<String> _topicOptions() {
    final isArabic = Localizations.localeOf(context).languageCode == 'ar';

    if (isArabic) {
      return const [
        'حب الشباب',
        'التصبغات',
        'تصبغات الجسم',
        'الهالات السوداء',
        'توسع المسامات',
        'مشاكل الشعر',
        'اضطراب الهرمونات',
        'ترطيب',
        'تنظيف البشرة',
        'واقي الشمس',
        'علامات التمدد',
        'السيلوليت',
        'غير متأكدة',
        'احجزي استشارة',
      ];
    }

    return const [
      'Acne',
      'Pigmentation',
      'Body pigmentation',
      'Dark circles',
      'Large pores',
      'Hair issues',
      'Hormonal imbalance',
      'Hydration',
      'Cleansing',
      'Sun protection',
      'Stretch marks',
      'Cellulite',
      'Not sure',
      'Book Consultation',
    ];
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

  Widget _quickChip(String label) {
    return ActionChip(
      label: Text(label),
      onPressed: () => _sendMessage(label),
      side: const BorderSide(color: AppColors.primary),
      backgroundColor: Colors.white,
      labelStyle: const TextStyle(color: AppColors.primary),
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
                    children: msg.options!
                        .map((option) => _quickChip(option))
                        .toList(),
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

  String _productWelcome(String productName) {
    final isArabic = Localizations.localeOf(context).languageCode == 'ar';
    if (isArabic) {
      return 'أنا جاهز أجاوبك عن $productName. اكتبي مشكلتك أو اسأليني عن طريقة الاستخدام.';
    }

    return 'I can help with $productName. Tell me your concern or ask how to use it.';
  }
}
