import '../../../../core/constants/api_constants.dart';

class BotOption {
  final String type;
  final int? id;
  final int? topicId;
  final String label;

  const BotOption({
    required this.type,
    required this.label,
    this.id,
    this.topicId,
  });

  factory BotOption.fromJson(dynamic value) {
    if (value is Map) {
      final json = Map<String, dynamic>.from(value);
      return BotOption(
        type: json['type']?.toString() ?? 'legacy',
        id: _integer(json['id']),
        topicId: _integer(json['topic_id']),
        label: json['label']?.toString() ?? '',
      );
    }

    return BotOption(type: 'legacy', label: value.toString());
  }

  static int? _integer(dynamic value) {
    if (value is num) return value.toInt();
    return value == null ? null : int.tryParse(value.toString());
  }
}

class MessageModel {
  final int? id;
  final String text;
  final bool isMe;
  final DateTime timestamp;
  final List<BotOption>? options;
  final String? attachmentUrl;
  final String? attachmentType;

  MessageModel({
    this.id,
    required this.text,
    required this.isMe,
    required this.timestamp,
    this.options,
    this.attachmentUrl,
    this.attachmentType,
  });

  factory MessageModel.fromJson(Map<String, dynamic> json) {
    final rawAttachment = (json['file_url'] ?? json['attachment'])?.toString();
    final attachment = rawAttachment == null || rawAttachment.isEmpty
        ? null
        : rawAttachment.startsWith('http')
        ? rawAttachment
        : '${ApiConstants.backendUrl}${rawAttachment.startsWith('/') ? '' : '/'}$rawAttachment';
    return MessageModel(
      id: BotOption._integer(json['id']),
      text: (json['text'] ?? json['message'] ?? json['body'] ?? '').toString(),
      isMe: json['is_me'] == true || json['is_me']?.toString() == '1',
      timestamp:
          DateTime.tryParse(json['created_at']?.toString() ?? '') ??
          DateTime.now(),
      options: _optionsFrom(json),
      attachmentUrl: attachment,
      attachmentType: json['type']?.toString(),
    );
  }

  factory MessageModel.fromBotPayload(Map<String, dynamic> payload) {
    return MessageModel(
      id: BotOption._integer(payload['message_id']),
      text: payload['answer']?.toString() ?? '',
      isMe: false,
      timestamp: DateTime.now(),
      options: _optionsFrom(payload),
    );
  }

  static List<BotOption> _optionsFrom(Map<String, dynamic> payload) {
    final structured = payload['option_items'];
    final raw = structured is List && structured.isNotEmpty
        ? structured
        : payload['options'];

    if (raw is! List) return <BotOption>[];

    return raw
        .map(BotOption.fromJson)
        .where((option) => option.label.trim().isNotEmpty)
        .toList();
  }

  Map<String, dynamic> toJson() => {'text': text, 'is_me': isMe};
}
