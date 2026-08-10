import '../../data/models/product_model.dart';
import '../../data/models/order_model.dart';
import '../../data/models/offer_model.dart';
import '../../data/models/home_data_model.dart';

abstract class StoreRepository {
  Future<HomeDataModel> getHomeData();
  Future<List<ProductModel>> getProducts({
    String? category,
    String? concern,
    String? query,
  });
  Future<ProductModel> getProductDetails(int id);
  Future<List<CategoryModel>> getCategories();
  Future<OfferModel?> getActiveOffer();
  Future<void> addToCart(int productId, int quantity);
  Future<void> checkout(Map<String, dynamic> orderData);
  Future<List<OrderModel>> getOrders();
  Future<List<DeliveryAreaModel>> getDeliveryAreas();
  Future<void> markOrderDelivered(int orderId);
}
