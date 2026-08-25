import '../../../../core/network/dio_client.dart';
import '../../../../core/constants/api_constants.dart';
import '../models/product_model.dart';
import '../models/order_model.dart';
import '../models/offer_model.dart';
import '../models/home_data_model.dart';

abstract class StoreRemoteDataSource {
  Future<HomeDataModel> getHomeData();
  Future<List<ProductModel>> getProducts({
    String? category,
    String? concern,
    String? catalogType,
    String? query,
  });
  Future<ProductModel> getProductDetails(int id);
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

class StoreRemoteDataSourceImpl implements StoreRemoteDataSource {
  final DioClient _dioClient;

  StoreRemoteDataSourceImpl(this._dioClient);

  @override
  Future<HomeDataModel> getHomeData() async {
    final response = await _dioClient.get(ApiConstants.home);
    final payload = response.data['data'] ?? response.data;
    return HomeDataModel.fromJson(Map<String, dynamic>.from(payload as Map));
  }

  @override
  Future<List<ProductModel>> getProducts({
    String? category,
    String? concern,
    String? catalogType,
    String? query,
  }) async {
    final response = await _dioClient.get(
      ApiConstants.products,
      queryParameters: {
        if (category != null) 'category': category,
        if (concern != null) 'concern': concern,
        if (catalogType != null) 'catalog_type': catalogType,
        if (query != null) 'query': query,
      },
    );
    final payload = response.data['data'] ?? response.data;
    final items = payload is List ? payload : <dynamic>[];
    return items.map((json) => ProductModel.fromJson(json)).toList();
  }

  @override
  Future<ProductModel> getProductDetails(int id) async {
    final response = await _dioClient.get('${ApiConstants.productDetails}$id');
    final payload = response.data['data'] ?? response.data;
    return ProductModel.fromJson(payload);
  }

  @override
  Future<List<CategoryModel>> getCategories() async {
    final response = await _dioClient.get(ApiConstants.categories);
    final items = response.data['data'] as List? ?? [];
    return items.map((json) => CategoryModel.fromJson(json)).toList();
  }

  @override
  Future<OfferModel?> getActiveOffer() async {
    try {
      final response = await _dioClient.get(ApiConstants.activeOffer);
      final payload = response.data['data'];
      if (payload is! Map<String, dynamic>) return null;
      return OfferModel.fromJson(payload);
    } catch (_) {
      return null;
    }
  }

  @override
  Future<void> addToCart(int productId, int quantity) async {
    await _dioClient.post(
      ApiConstants.cart,
      data: {'product_id': productId, 'quantity': quantity},
    );
  }

  @override
  Future<List<RemoteCartItem>> getCart() async {
    final response = await _dioClient.get(ApiConstants.cart);
    final payload = response.data['data'] ?? response.data;
    return (payload as List? ?? []).map((item) => RemoteCartItem.fromJson(Map<String, dynamic>.from(item as Map))).toList();
  }

  @override
  Future<void> updateCartItem(int itemId, int quantity) async {
    await _dioClient.put('${ApiConstants.cart}/$itemId', data: {'quantity': quantity});
  }

  @override
  Future<void> removeCartItem(int itemId) async {
    await _dioClient.delete('${ApiConstants.cart}/$itemId');
  }

  @override
  Future<void> checkout(Map<String, dynamic> orderData) async {
    String normalizePaymentMethod(String value) {
      switch (value.toLowerCase()) {
        case 'credit card':
          return 'credit_card';
        case 'cash on delivery':
          return 'cash_on_delivery';
        case 'apple pay':
          return 'apple_pay';
        default:
          return value.toLowerCase().replaceAll(' ', '_');
      }
    }

    final payload = {
      if (orderData['shipping_address'] != null)
        'shipping_address': orderData['shipping_address'],
      'payment_method': normalizePaymentMethod(
        orderData['payment_method']?.toString() ?? 'cash_on_delivery',
      ),
      if (orderData['delivery_method'] != null)
        'delivery_method': orderData['delivery_method'],
      if (orderData['pickup_location'] != null)
        'pickup_location': orderData['pickup_location'],
      if (orderData['delivery_area_id'] != null)
        'delivery_area_id': orderData['delivery_area_id'],
      if (orderData['qadmous_governorate'] != null) 'qadmous_governorate': orderData['qadmous_governorate'],
      if (orderData['qadmous_branch'] != null) 'qadmous_branch': orderData['qadmous_branch'],
      if (orderData['recipient_name'] != null) 'recipient_name': orderData['recipient_name'],
      if (orderData['recipient_phone'] != null) 'recipient_phone': orderData['recipient_phone'],
      'items': orderData['items'] ?? [],
    };

    await _dioClient.post(ApiConstants.orders, data: payload);
  }

  @override
  Future<List<OrderModel>> getOrders() async {
    final response = await _dioClient.get(ApiConstants.orders);
    final payload = response.data['data'] ?? response.data;
    final items = payload is List ? payload : <dynamic>[];
    return items.map((json) => OrderModel.fromJson(json)).toList();
  }

  @override
  Future<List<DeliveryAreaModel>> getDeliveryAreas() async {
    final response = await _dioClient.get(ApiConstants.deliveryAreas);
    final payload = response.data['data'] ?? response.data;
    final items = payload is List ? payload : <dynamic>[];
    return items.map((json) => DeliveryAreaModel.fromJson(json)).toList();
  }

  @override
  Future<void> markOrderDelivered(int orderId) async {
    await _dioClient.post(ApiConstants.markOrderDelivered(orderId));
  }
}
