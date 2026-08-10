import '../../domain/repositories/store_repository.dart';
import '../models/product_model.dart';
import '../models/order_model.dart';
import '../models/offer_model.dart';
import '../models/home_data_model.dart';
import '../sources/store_remote_data_source.dart';

class StoreRepositoryImpl implements StoreRepository {
  final StoreRemoteDataSource _remoteDataSource;

  StoreRepositoryImpl(this._remoteDataSource);

  @override
  Future<HomeDataModel> getHomeData() {
    return _remoteDataSource.getHomeData();
  }

  @override
  Future<List<ProductModel>> getProducts({
    String? category,
    String? concern,
    String? query,
  }) async {
    return await _remoteDataSource.getProducts(
      category: category,
      concern: concern,
      query: query,
    );
  }

  @override
  Future<ProductModel> getProductDetails(int id) async {
    return await _remoteDataSource.getProductDetails(id);
  }

  @override
  Future<List<CategoryModel>> getCategories() async {
    return await _remoteDataSource.getCategories();
  }

  @override
  Future<OfferModel?> getActiveOffer() async {
    return await _remoteDataSource.getActiveOffer();
  }

  @override
  Future<void> addToCart(int productId, int quantity) async {
    await _remoteDataSource.addToCart(productId, quantity);
  }

  @override
  Future<void> checkout(Map<String, dynamic> orderData) async {
    await _remoteDataSource.checkout(orderData);
  }

  @override
  Future<List<OrderModel>> getOrders() async {
    return await _remoteDataSource.getOrders();
  }

  @override
  Future<List<DeliveryAreaModel>> getDeliveryAreas() async {
    return await _remoteDataSource.getDeliveryAreas();
  }

  @override
  Future<void> markOrderDelivered(int orderId) async {
    await _remoteDataSource.markOrderDelivered(orderId);
  }
}
