import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/constants/api_constants.dart';
import '../../../../core/localization/app_localizations.dart';
import '../../../../core/network/api_error_message.dart';
import '../../../../core/network/dio_client.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../../injection_container.dart';

class HelpCenterScreen extends StatefulWidget {
  const HelpCenterScreen({super.key});

  @override
  State<HelpCenterScreen> createState() => _HelpCenterScreenState();
}

class _HelpCenterScreenState extends State<HelpCenterScreen> {
  late Future<List<_FaqItem>> _faqs;

  @override
  void initState() {
    super.initState();
    _faqs = _loadFaqs();
  }

  Future<List<_FaqItem>> _loadFaqs() async {
    final response = await sl<DioClient>().get(ApiConstants.faqs);
    final envelope = response.data;
    final raw = envelope is Map<String, dynamic> ? envelope['data'] : envelope;
    final list = raw is List ? raw : const <dynamic>[];

    return list
        .whereType<Map>()
        .map((item) => _FaqItem.fromJson(Map<String, dynamic>.from(item)))
        .where((item) => item.question.isNotEmpty && item.answer.isNotEmpty)
        .toList(growable: false);
  }

  Future<void> _refresh() async {
    final request = _loadFaqs();
    setState(() => _faqs = request);
    await request;
  }

  void _retry() {
    setState(() => _faqs = _loadFaqs());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(context.tr('help_center'))),
      body: RefreshIndicator(
        onRefresh: _refresh,
        color: AppColors.primary,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(20),
          children: [
            Container(
              padding: const EdgeInsets.all(22),
              decoration: BoxDecoration(
                gradient: AppColors.brandGradient,
                borderRadius: BorderRadius.circular(28),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(
                    Icons.support_agent_rounded,
                    color: Colors.white,
                    size: 34,
                  ),
                  const SizedBox(height: 14),
                  Text(
                    context.tr('how_can_we_help'),
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 21,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  const SizedBox(height: 7),
                  Text(
                    context.tr('help_intro'),
                    style: TextStyle(
                      color: Colors.white.withValues(alpha: 0.8),
                      height: 1.45,
                    ),
                  ),
                  const SizedBox(height: 18),
                  const Row(
                    children: [
                      Icon(
                        Icons.phone_in_talk_outlined,
                        color: Colors.white,
                        size: 20,
                      ),
                      SizedBox(width: 8),
                      Directionality(
                        textDirection: TextDirection.ltr,
                        child: SelectableText(
                          ApiConstants.supportPhone,
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  Row(
                    children: [
                      Expanded(
                        child: _HelpAction(
                          icon: Icons.chat_bubble_outline_rounded,
                          label: context.tr('live_chat'),
                          onTap: () => context.push('/chat'),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: _HelpAction(
                          icon: Icons.smart_toy_outlined,
                          label: context.tr('bot'),
                          onTap: () => context.push('/bot'),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 26),
            Text(
              context.tr('frequent_questions'),
              style: const TextStyle(fontSize: 19, fontWeight: FontWeight.w800),
            ),
            const SizedBox(height: 12),
            FutureBuilder<List<_FaqItem>>(
              future: _faqs,
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) {
                  return const Padding(
                    padding: EdgeInsets.symmetric(vertical: 40),
                    child: Center(child: CircularProgressIndicator()),
                  );
                }

                if (snapshot.hasError) {
                  return _HelpError(
                    message: ApiErrorMessage.from(snapshot.error!),
                    onRetry: _retry,
                  );
                }

                final faqs = snapshot.data ?? const <_FaqItem>[];
                if (faqs.isEmpty) {
                  return _HelpError(
                    message: context.tr('no_faqs'),
                    onRetry: _retry,
                  );
                }

                return Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    border: Border.all(color: AppColors.divider),
                    borderRadius: BorderRadius.circular(22),
                  ),
                  child: Column(
                    children: List.generate(faqs.length, (index) {
                      final faq = faqs[index];
                      return Column(
                        children: [
                          ExpansionTile(
                            tilePadding: const EdgeInsets.symmetric(
                              horizontal: 18,
                              vertical: 4,
                            ),
                            childrenPadding: const EdgeInsets.fromLTRB(
                              18,
                              0,
                              18,
                              18,
                            ),
                            iconColor: AppColors.primary,
                            collapsedIconColor: AppColors.textSecondary,
                            title: Text(
                              faq.question,
                              style: const TextStyle(
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                            children: [
                              Align(
                                alignment: AlignmentDirectional.centerStart,
                                child: Text(
                                  faq.answer,
                                  style: const TextStyle(
                                    color: AppColors.textSecondary,
                                    height: 1.55,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          if (index != faqs.length - 1)
                            const Divider(height: 1, indent: 18, endIndent: 18),
                        ],
                      );
                    }),
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }
}

class _HelpAction extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  const _HelpAction({
    required this.icon,
    required this.label,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white.withValues(alpha: 0.15),
      borderRadius: BorderRadius.circular(16),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, color: Colors.white, size: 20),
              const SizedBox(width: 7),
              Flexible(
                child: Text(
                  label,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _HelpError extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;

  const _HelpError({required this.message, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border.all(color: AppColors.divider),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Column(
        children: [
          const Icon(Icons.cloud_off_outlined, color: AppColors.textLight),
          const SizedBox(height: 8),
          Text(message, textAlign: TextAlign.center),
          const SizedBox(height: 10),
          TextButton(onPressed: onRetry, child: Text(context.tr('try_again'))),
        ],
      ),
    );
  }
}

class _FaqItem {
  final String question;
  final String answer;

  const _FaqItem({required this.question, required this.answer});

  factory _FaqItem.fromJson(Map<String, dynamic> json) {
    return _FaqItem(
      question: json['question_text']?.toString() ?? '',
      answer: json['answer_text']?.toString() ?? '',
    );
  }
}
