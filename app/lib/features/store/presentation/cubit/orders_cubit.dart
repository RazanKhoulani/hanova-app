import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../../core/network/api_error_message.dart';

import '../../data/models/order_model.dart';
import '../../domain/repositories/store_repository.dart';

class OrdersState {
  final bool isLoading;
  final List<OrderModel> orders;
  final String? errorMessage;

  const OrdersState({
    required this.isLoading,
    required this.orders,
    required this.errorMessage,
  });

  factory OrdersState.initial() {
    return const OrdersState(
      isLoading: false,
      orders: <OrderModel>[],
      errorMessage: null,
    );
  }

  OrdersState copyWith({
    bool? isLoading,
    List<OrderModel>? orders,
    String? errorMessage,
    bool clearError = false,
  }) {
    return OrdersState(
      isLoading: isLoading ?? this.isLoading,
      orders: orders ?? this.orders,
      errorMessage: clearError ? null : errorMessage ?? this.errorMessage,
    );
  }
}

class OrdersCubit extends Cubit<OrdersState> {
  final StoreRepository _repository;
  bool _refreshQueued = false;

  OrdersCubit(this._repository) : super(OrdersState.initial());

  Future<void> loadOrders() async {
    if (state.isLoading) {
      _refreshQueued = true;
      return;
    }

    do {
      _refreshQueued = false;
      emit(state.copyWith(isLoading: true, clearError: true));

      try {
        final orders = await _repository.getOrders();
        if (isClosed) return;
        emit(OrdersState(isLoading: false, orders: orders, errorMessage: null));
      } catch (e) {
        if (isClosed) return;
        emit(
          state.copyWith(
            isLoading: false,
            errorMessage: ApiErrorMessage.from(e),
          ),
        );
      }
    } while (_refreshQueued && !isClosed);
  }

  Future<void> markDelivered(int orderId) async {
    if (state.isLoading) return;

    emit(state.copyWith(isLoading: true, clearError: true));

    try {
      await _repository.markOrderDelivered(orderId);
      final orders = await _repository.getOrders();
      if (isClosed) return;
      emit(OrdersState(isLoading: false, orders: orders, errorMessage: null));
    } catch (e) {
      if (isClosed) return;
      emit(
        state.copyWith(isLoading: false, errorMessage: ApiErrorMessage.from(e)),
      );
    }

    if (_refreshQueued && !isClosed) {
      await loadOrders();
    }
  }
}
