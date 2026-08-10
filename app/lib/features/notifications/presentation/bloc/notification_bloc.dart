import 'package:flutter_bloc/flutter_bloc.dart';
import '../../data/models/notification_model.dart';
import '../../data/sources/notification_remote_data_source.dart';

abstract class NotificationEvent {}

class NotificationFetchRequested extends NotificationEvent {}

class NotificationMarkReadRequested extends NotificationEvent {
  final int id;

  NotificationMarkReadRequested(this.id);
}

abstract class NotificationState {}

class NotificationInitial extends NotificationState {}

class NotificationLoading extends NotificationState {}

class NotificationLoaded extends NotificationState {
  final List<NotificationModel> notifications;

  NotificationLoaded(this.notifications);

  int get unreadCount => notifications.where((item) => !item.isRead).length;
}

class NotificationFailure extends NotificationState {
  final String message;

  NotificationFailure(this.message);
}

class NotificationBloc extends Bloc<NotificationEvent, NotificationState> {
  final NotificationRemoteDataSource _remoteDataSource;

  NotificationBloc(this._remoteDataSource) : super(NotificationInitial()) {
    on<NotificationFetchRequested>(_onFetchRequested);
    on<NotificationMarkReadRequested>(_onMarkReadRequested);
  }

  Future<void> _onFetchRequested(
    NotificationFetchRequested event,
    Emitter<NotificationState> emit,
  ) async {
    emit(NotificationLoading());
    try {
      final notifications = await _remoteDataSource.getNotifications();
      emit(NotificationLoaded(notifications));
    } catch (e) {
      emit(NotificationFailure(e.toString()));
    }
  }

  Future<void> _onMarkReadRequested(
    NotificationMarkReadRequested event,
    Emitter<NotificationState> emit,
  ) async {
    final currentState = state;
    try {
      await _remoteDataSource.markAsRead(event.id);
      if (currentState is NotificationLoaded) {
        final updated = currentState.notifications.map((item) {
          if (item.id != event.id) return item;
          return NotificationModel(
            id: item.id,
            title: item.title,
            body: item.body,
            type: item.type,
            data: item.data,
            isRead: true,
            createdAt: item.createdAt,
          );
        }).toList();
        emit(NotificationLoaded(updated));
      } else {
        add(NotificationFetchRequested());
      }
    } catch (e) {
      emit(NotificationFailure(e.toString()));
    }
  }
}
