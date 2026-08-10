import '../../../../core/constants/api_constants.dart';
import '../../../../core/network/dio_client.dart';
import '../models/notification_model.dart';

abstract class NotificationRemoteDataSource {
  Future<List<NotificationModel>> getNotifications();
  Future<void> markAsRead(int id);
}

class NotificationRemoteDataSourceImpl implements NotificationRemoteDataSource {
  final DioClient _dioClient;

  NotificationRemoteDataSourceImpl(this._dioClient);

  @override
  Future<List<NotificationModel>> getNotifications() async {
    final response = await _dioClient.get(ApiConstants.notifications);
    final envelope = response.data['data'] ?? response.data;
    final items = envelope is Map<String, dynamic>
        ? (envelope['data'] as List? ?? <dynamic>[])
        : (envelope as List? ?? <dynamic>[]);

    return items
        .whereType<Map<String, dynamic>>()
        .map(NotificationModel.fromJson)
        .toList();
  }

  @override
  Future<void> markAsRead(int id) async {
    await _dioClient.put('${ApiConstants.notifications}/$id/read');
  }
}
