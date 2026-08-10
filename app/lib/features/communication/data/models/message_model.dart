class MessageModel {
  final int? id;
  final String text;
  final bool isMe;
  final DateTime timestamp;
  final List<String>? options;

  MessageModel({
    this.id,
    required this.text,
    required this.isMe,
    required this.timestamp,
    this.options,
  });

  factory MessageModel.fromJson(Map<String, dynamic> json) {
    final optionsRaw = json['options'];
    final List<String>? options = optionsRaw is List
        ? List<String>.from(optionsRaw.map((e) => e.toString()))
        : null;

    return MessageModel(
      id: json['id'] is num
          ? (json['id'] as num).toInt()
          : int.tryParse('${json['id']}'),
      text: (json['text'] ?? json['message'] ?? json['body'] ?? '').toString(),
      isMe: json['is_me'] == true || json['is_me']?.toString() == '1',
      timestamp:
          DateTime.tryParse(json['created_at']?.toString() ?? '') ??
          DateTime.now(),
      options: options,
    );
  }

  Map<String, dynamic> toJson() => {'text': text, 'is_me': isMe};
}
