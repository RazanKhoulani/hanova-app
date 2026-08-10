import 'offer_model.dart';
import 'product_model.dart';

class HomeDataModel {
  final List<ProductModel> products;
  final List<CategoryModel> categories;
  final OfferModel? activeOffer;

  const HomeDataModel({
    required this.products,
    required this.categories,
    required this.activeOffer,
  });

  factory HomeDataModel.fromJson(Map<String, dynamic> json) {
    final products = json['products'] is List
        ? json['products'] as List
        : const <dynamic>[];
    final categories = json['categories'] is List
        ? json['categories'] as List
        : const <dynamic>[];
    final offer = json['active_offer'];

    return HomeDataModel(
      products: products
          .whereType<Map>()
          .map((item) => ProductModel.fromJson(Map<String, dynamic>.from(item)))
          .toList(),
      categories: categories
          .whereType<Map>()
          .map(
            (item) => CategoryModel.fromJson(Map<String, dynamic>.from(item)),
          )
          .toList(),
      activeOffer: offer is Map
          ? OfferModel.fromJson(Map<String, dynamic>.from(offer))
          : null,
    );
  }
}
