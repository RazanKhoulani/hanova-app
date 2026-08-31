import 'package:flutter_bloc/flutter_bloc.dart';
import '../../data/models/product_model.dart';
import '../../domain/repositories/store_repository.dart';

abstract class CartEvent {}

class CartItemAdded extends CartEvent {
  final ProductModel product;
  final int quantity;
  CartItemAdded(this.product, this.quantity);
}

class CartItemRemoved extends CartEvent {
  final int productId;
  CartItemRemoved(this.productId);
}

class CartItemUpdated extends CartEvent {
  final int productId;
  final int quantity;
  CartItemUpdated(this.productId, this.quantity);
}

class CartCleared extends CartEvent {}

class CartLoadRequested extends CartEvent {}

// State
class CartState {
  final Map<int, CartItem> items;
  CartState({this.items = const {}});

  double get totalAmount {
    var total = 0.0;
    items.forEach((key, item) {
      total += item.product.price * item.quantity;
    });
    return total;
  }

  double? get totalUsd {
    if (items.values.any((item) => item.product.priceUsd == null)) return null;
    return items.values.fold<double>(0, (total, item) => total + item.product.priceUsd! * item.quantity);
  }

  int get itemCount => items.length;
}

class CartItem {
  final ProductModel product;
  final int quantity;
  final int? serverId;
  CartItem({required this.product, required this.quantity, this.serverId});
}

// Bloc
class CartBloc extends Bloc<CartEvent, CartState> {
  final StoreRepository _repository;
  int _pendingMutations = 0;
  CartBloc(this._repository) : super(CartState()) {
    on<CartItemAdded>(_onItemAdded);
    on<CartItemRemoved>(_onItemRemoved);
    on<CartItemUpdated>(_onItemUpdated);
    on<CartCleared>(_onCleared);
    on<CartLoadRequested>(_onLoad);
  }

  Future<void> _onLoad(CartLoadRequested event, Emitter<CartState> emit) async {
    if (_pendingMutations > 0) return;
    try {
      final remote = await _repository.getCart();
      emit(
        CartState(
          items: {
            for (final item in remote)
              item.product.id: CartItem(
                product: item.product,
                quantity: item.quantity,
                serverId: item.id,
              ),
          },
        ),
      );
    } catch (_) {}
  }

  Future<void> _onItemAdded(
    CartItemAdded event,
    Emitter<CartState> emit,
  ) async {
    final previousState = state;
    final updatedItems = Map<int, CartItem>.from(state.items);
    final existing = updatedItems[event.product.id];
    final requestedQuantity = (existing?.quantity ?? 0) + event.quantity;
    if (event.product.tracksInventory &&
        requestedQuantity > event.product.stock) {
      return;
    }
    if (existing != null) {
      updatedItems.update(
        event.product.id,
        (existing) => CartItem(
          product: existing.product,
          quantity: existing.quantity + event.quantity,
          serverId: existing.serverId,
        ),
      );
    } else {
      updatedItems.putIfAbsent(
        event.product.id,
        () => CartItem(product: event.product, quantity: event.quantity),
      );
    }
    emit(CartState(items: updatedItems));
    _pendingMutations++;
    try {
      await _repository.addToCart(event.product.id, event.quantity);
    } catch (_) {
      emit(previousState);
    } finally {
      _pendingMutations--;
    }
  }

  Future<void> _onItemRemoved(
    CartItemRemoved event,
    Emitter<CartState> emit,
  ) async {
    final updatedItems = Map<int, CartItem>.from(state.items);
    final serverId = updatedItems[event.productId]?.serverId;
    updatedItems.remove(event.productId);
    emit(CartState(items: updatedItems));
    if (serverId != null) {
      try {
        await _repository.removeCartItem(serverId);
      } catch (_) {}
    }
  }

  Future<void> _onItemUpdated(
    CartItemUpdated event,
    Emitter<CartState> emit,
  ) async {
    final previousState = state;
    final updatedItems = Map<int, CartItem>.from(state.items);
    if (updatedItems.containsKey(event.productId)) {
      final existing = updatedItems[event.productId]!;
      final quantity = existing.product.tracksInventory
          ? event.quantity.clamp(1, existing.product.stock).toInt()
          : event.quantity;
      updatedItems.update(
        event.productId,
        (existing) => CartItem(
          product: existing.product,
          quantity: quantity,
          serverId: existing.serverId,
        ),
      );
    }
    emit(CartState(items: updatedItems));
    final serverId = updatedItems[event.productId]?.serverId;
    if (serverId != null) {
      try {
        await _repository.updateCartItem(
          serverId,
          updatedItems[event.productId]!.quantity,
        );
      } catch (_) {
        emit(previousState);
      }
    }
  }

  Future<void> _onCleared(CartCleared event, Emitter<CartState> emit) async {
    final ids = state.items.values
        .map((item) => item.serverId)
        .whereType<int>()
        .toList();
    emit(CartState(items: {}));
    for (final id in ids) {
      try {
        await _repository.removeCartItem(id);
      } catch (_) {}
    }
  }
}
