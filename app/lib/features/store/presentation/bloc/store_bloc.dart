import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../../core/network/api_error_message.dart';
import '../../domain/repositories/store_repository.dart';
import '../../data/models/product_model.dart';
import '../../data/models/order_model.dart';

abstract class StoreEvent {}

class StoreFetchProducts extends StoreEvent {
  final String? category;
  final String? concern;
  final String? query;
  final bool force;
  StoreFetchProducts({
    this.category,
    this.concern,
    this.query,
    this.force = false,
  });
}

class StoreSeedProducts extends StoreEvent {
  final List<ProductModel> products;
  StoreSeedProducts(this.products);
}

class StoreFetchCategories extends StoreEvent {}

class StoreFetchOrders extends StoreEvent {}

class StoreCheckout extends StoreEvent {
  final Map<String, dynamic> orderData;
  StoreCheckout(this.orderData);
}

class StoreFetchProductDetails extends StoreEvent {
  final int id;
  StoreFetchProductDetails(this.id);
}

// States
abstract class StoreState {}

class StoreInitial extends StoreState {}

class StoreLoading extends StoreState {}

class StoreProductsLoaded extends StoreState {
  final List<ProductModel> products;
  StoreProductsLoaded(this.products);
}

class StoreCategoriesLoaded extends StoreState {
  final List<CategoryModel> categories;
  StoreCategoriesLoaded(this.categories);
}

class StoreProductDetailsLoaded extends StoreState {
  final ProductModel product;
  StoreProductDetailsLoaded(this.product);
}

class StoreOrdersLoaded extends StoreState {
  final List<OrderModel> orders;
  StoreOrdersLoaded(this.orders);
}

class StoreCheckoutSuccess extends StoreState {}

class StoreFailure extends StoreState {
  final String message;
  StoreFailure(this.message);
}

// Bloc
class StoreBloc extends Bloc<StoreEvent, StoreState> {
  final StoreRepository _repository;
  List<ProductModel> _cachedProducts = const [];
  String? _cachedCategory;
  String? _cachedConcern;
  String? _cachedQuery;

  StoreBloc(this._repository) : super(StoreInitial()) {
    on<StoreFetchProducts>(_onFetchProducts);
    on<StoreSeedProducts>(_onSeedProducts);
    on<StoreFetchCategories>(_onFetchCategories);
    on<StoreFetchProductDetails>(_onFetchProductDetails);
    on<StoreFetchOrders>(_onFetchOrders);
    on<StoreCheckout>(_onCheckout);
  }

  void _onSeedProducts(StoreSeedProducts event, Emitter<StoreState> emit) {
    _cachedProducts = event.products;
    _cachedCategory = null;
    _cachedConcern = null;
    _cachedQuery = null;
    emit(StoreProductsLoaded(event.products));
  }

  Future<void> _onFetchProducts(
    StoreFetchProducts event,
    Emitter<StoreState> emit,
  ) async {
    final sameFilter =
        _cachedCategory == event.category &&
        _cachedConcern == event.concern &&
        _cachedQuery == event.query;

    if (!event.force && sameFilter && _cachedProducts.isNotEmpty) {
      emit(StoreProductsLoaded(_cachedProducts));
      return;
    }

    emit(StoreLoading());
    try {
      final products = await _repository.getProducts(
        category: event.category,
        concern: event.concern,
        query: event.query,
      );
      _cachedProducts = products;
      _cachedCategory = event.category;
      _cachedConcern = event.concern;
      _cachedQuery = event.query;
      emit(StoreProductsLoaded(products));
    } catch (e) {
      emit(StoreFailure(ApiErrorMessage.from(e)));
    }
  }

  Future<void> _onFetchCategories(
    StoreFetchCategories event,
    Emitter<StoreState> emit,
  ) async {
    emit(StoreLoading());
    try {
      final categories = await _repository.getCategories();
      emit(StoreCategoriesLoaded(categories));
    } catch (e) {
      emit(StoreFailure(ApiErrorMessage.from(e)));
    }
  }

  Future<void> _onFetchProductDetails(
    StoreFetchProductDetails event,
    Emitter<StoreState> emit,
  ) async {
    emit(StoreLoading());
    try {
      final product = await _repository.getProductDetails(event.id);
      emit(StoreProductDetailsLoaded(product));
    } catch (e) {
      emit(StoreFailure(ApiErrorMessage.from(e)));
    }
  }

  Future<void> _onFetchOrders(
    StoreFetchOrders event,
    Emitter<StoreState> emit,
  ) async {
    emit(StoreLoading());
    try {
      final orders = await _repository.getOrders();
      emit(StoreOrdersLoaded(orders));
    } catch (e) {
      emit(StoreFailure(ApiErrorMessage.from(e)));
    }
  }

  Future<void> _onCheckout(
    StoreCheckout event,
    Emitter<StoreState> emit,
  ) async {
    emit(StoreLoading());
    try {
      await _repository.checkout(event.orderData);
      emit(StoreCheckoutSuccess());
    } catch (e) {
      emit(StoreFailure(ApiErrorMessage.from(e)));
    }
  }
}
