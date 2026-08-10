import 'package:flutter_bloc/flutter_bloc.dart';
import '../../data/models/product_model.dart';

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

  int get itemCount => items.length;
}

class CartItem {
  final ProductModel product;
  final int quantity;
  CartItem({required this.product, required this.quantity});
}

// Bloc
class CartBloc extends Bloc<CartEvent, CartState> {
  CartBloc() : super(CartState()) {
    on<CartItemAdded>(_onItemAdded);
    on<CartItemRemoved>(_onItemRemoved);
    on<CartItemUpdated>(_onItemUpdated);
    on<CartCleared>(_onCleared);
  }

  void _onItemAdded(CartItemAdded event, Emitter<CartState> emit) {
    final updatedItems = Map<int, CartItem>.from(state.items);
    if (updatedItems.containsKey(event.product.id)) {
      updatedItems.update(
        event.product.id,
        (existing) => CartItem(
          product: existing.product,
          quantity: existing.quantity + event.quantity,
        ),
      );
    } else {
      updatedItems.putIfAbsent(
        event.product.id,
        () => CartItem(product: event.product, quantity: event.quantity),
      );
    }
    emit(CartState(items: updatedItems));
  }

  void _onItemRemoved(CartItemRemoved event, Emitter<CartState> emit) {
    final updatedItems = Map<int, CartItem>.from(state.items);
    updatedItems.remove(event.productId);
    emit(CartState(items: updatedItems));
  }

  void _onItemUpdated(CartItemUpdated event, Emitter<CartState> emit) {
    final updatedItems = Map<int, CartItem>.from(state.items);
    if (updatedItems.containsKey(event.productId)) {
      updatedItems.update(
        event.productId,
        (existing) => CartItem(product: existing.product, quantity: event.quantity),
      );
    }
    emit(CartState(items: updatedItems));
  }

  void _onCleared(CartCleared event, Emitter<CartState> emit) {
    emit(CartState(items: {}));
  }
}
