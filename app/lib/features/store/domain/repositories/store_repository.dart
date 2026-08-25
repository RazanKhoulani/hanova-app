import '../../data/models/product_model.dart';
import '../../data/models/order_model.dart';
import '../../data/models/offer_model.dart';
import '../../data/models/home_data_model.dart';
import '../../data/models/product_review_model.dart';

abstract class StoreRepository {
  Future<HomeDataModel> getHomeData({bool force = false});
  Future<List<ProductModel>> getProducts({
    String? category,
    String? concern,
    String? catalogType,
    String? query,
  });
  Future<ProductModel> getProductDetails(int id);
  Future<ProductReviewSubmission> submitProductReview(
    int productId, {
    required int rating,
    String? comment,
  });
  Future<List<CategoryModel>> getCategories();
  Future<OfferModel?> getActiveOffer();
  Future<void> addToCart(int productId, int quantity);
  Future<List<RemoteCartItem>> getCart();
  Future<void> updateCartItem(int itemId, int quantity);
  Future<void> removeCartItem(int itemId);
  Future<void> checkout(Map<String, dynamic> orderData);
  Future<List<OrderModel>> getOrders();
  Future<List<DeliveryAreaModel>> getDeliveryAreas();
  Future<void> markOrderDelivered(int orderId);
}
