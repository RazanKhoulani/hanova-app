class NotificationModel {
  final int id;
  final String title;
  final String body;
  final String type;
  final Map<String, dynamic>? data;
  final bool isRead;
  final DateTime createdAt;

  const NotificationModel({
    required this.id,
    required this.title,
    required this.body,
    required this.type,
    required this.isRead,
    required this.createdAt,
    this.data,
  });

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    final rawData = json['data'];

    return NotificationModel(
      id: json['id'] is num
          ? (json['id'] as num).toInt()
          : int.parse(json['id'].toString()),
      title: json['title']?.toString() ?? '',
      body: json['body']?.toString() ?? json['message']?.toString() ?? '',
      type: json['type']?.toString() ?? 'general',
      data: rawData is Map<String, dynamic> ? rawData : null,
      isRead: json['is_read'] == true || json['is_read'] == 1,
      createdAt:
          DateTime.tryParse(json['created_at']?.toString() ?? '') ??
          DateTime.now(),
    );
  }
}
