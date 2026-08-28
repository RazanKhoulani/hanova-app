import '../../../../core/constants/api_constants.dart';
import 'product_review_model.dart';

class ProductModel {
  final int id;
  final String name;
  final String? description;
  final double price;
  final String? image;
  final String? category;
  final String? brand;
  final String catalogType;
  final List<int> bundleProductIds;
  final String? unit;
  final int stock;
  final bool tracksInventory;
  final bool isInStock;
  final bool isLowStock;
  final List<String> concernSlugs;
  final String searchText;
  final String? usage;
  final String? suitableFor;
  final String? activeIngredients;
  final String? warnings;
  final double ratingAverage;
  final int ratingCount;
  final bool canReview;
  final ProductReviewModel? currentUserReview;

  ProductModel({
    required this.id,
    required this.name,
    this.description,
    required this.price,
    this.image,
    this.category,
    this.brand,
    this.catalogType = 'product',
    this.bundleProductIds = const [],
    this.unit,
    required this.stock,
    this.tracksInventory = true,
    this.isInStock = true,
    this.isLowStock = false,
    this.concernSlugs = const [],
    this.searchText = '',
    this.usage,
    this.suitableFor,
    this.activeIngredients,
    this.warnings,
    this.ratingAverage = 0,
    this.ratingCount = 0,
    this.canReview = false,
    this.currentUserReview,
  });

  String get botDescription => [
    description,
    usage,
    suitableFor,
    activeIngredients,
    warnings,
  ].whereType<String>().where((value) => value.trim().isNotEmpty).join('\n\n');

  factory ProductModel.fromJson(Map<String, dynamic> json) {
    String resolveLocalized(dynamic value) {
      if (value is Map<String, dynamic>) {
        return (value['ar'] ?? value['en'] ?? '').toString();
      }
      return (value ?? '').toString();
    }

    String? resolveImage(dynamic raw) {
      if (raw == null) return null;
      final value = raw.toString();
      if (value.isEmpty) return null;
      if (value.startsWith('http://') || value.startsWith('https://')) {
        final imageUri = Uri.tryParse(value);
        final backendUri = Uri.tryParse(ApiConstants.backendUrl);
        if (imageUri != null &&
            backendUri != null &&
            backendUri.scheme == 'https' &&
            imageUri.scheme == 'http' &&
            imageUri.host == backendUri.host) {
          return imageUri.replace(scheme: 'https').toString();
        }
        return value;
      }

      final base = ApiConstants.baseUrl;
      final apiIndex = base.indexOf('/api');
      final host = apiIndex >= 0 ? base.substring(0, apiIndex) : base;
      if (value.startsWith('/')) {
        return '$host$value';
      }
      return '$host/$value';
    }

    int parseInt(dynamic value, {int fallback = 0}) {
      return value is num ? value.toInt() : int.tryParse('$value') ?? fallback;
    }

    double parseDouble(dynamic value) {
      return value is num ? value.toDouble() : double.tryParse('$value') ?? 0;
    }

    String flattenLocalized(dynamic value) {
      if (value is Map) {
        return value.values.map((item) => item?.toString() ?? '').join(' ');
      }
      return value?.toString() ?? '';
    }

    final concerns = json['concerns'] is List
        ? (json['concerns'] as List).whereType<Map>().toList()
        : const <Map>[];
    final name = resolveLocalized(json['name']).isNotEmpty
        ? resolveLocalized(json['name'])
        : resolveLocalized(json['name_translations']);
    final description = resolveLocalized(json['description']).isNotEmpty
        ? resolveLocalized(json['description'])
        : resolveLocalized(json['description_translations']);
    final concernSlugs = concerns
        .map((concern) => concern['slug']?.toString() ?? '')
        .where((slug) => slug.isNotEmpty)
        .toList(growable: false);
    final currentUserReview = json['current_user_review'] is Map
        ? ProductReviewModel.fromJson(
            Map<String, dynamic>.from(json['current_user_review'] as Map),
          )
        : null;
    final searchText = [
      name,
      description,
      flattenLocalized(json['name_translations']),
      flattenLocalized(json['description_translations']),
      json['category']?.toString() ?? '',
      ...concerns.expand(
        (concern) => [
          concern['name']?.toString() ?? '',
          concern['slug']?.toString() ?? '',
        ],
      ),
    ].join(' ').toLowerCase();

    final stock = parseInt(json['stock']);
    final rawTracksInventory = json['tracks_inventory'];
    final tracksInventory = rawTracksInventory == null
        ? true
        : rawTracksInventory == true || rawTracksInventory.toString() == '1';
    final rawIsInStock = json['is_in_stock'];
    final isInStock = rawIsInStock == null
        ? !tracksInventory || stock > 0
        : rawIsInStock == true || rawIsInStock.toString() == '1';
    final rawIsLowStock = json['is_low_stock'];
    final isLowStock =
        rawIsLowStock == true || rawIsLowStock?.toString() == '1';

    return ProductModel(
      id: parseInt(json['id']),
      name: name,
      description: description,
      price: parseDouble(json['price']),
      image: resolveImage(json['image'] ?? json['image_url']),
      category: json['category'],
      brand: json['brand']?.toString(),
      catalogType: json['catalog_type']?.toString() ?? 'product',
      bundleProductIds: (json['bundle_product_ids'] as List? ?? const [])
          .map((id) => parseInt(id))
          .where((id) => id > 0)
          .toList(growable: false),
      unit: json['unit'],
      stock: stock,
      tracksInventory: tracksInventory,
      isInStock: isInStock,
      isLowStock: isLowStock,
      concernSlugs: concernSlugs,
      searchText: searchText,
      usage: json['usage']?.toString(),
      suitableFor: json['suitable_for']?.toString(),
      activeIngredients: json['active_ingredients']?.toString(),
      warnings: json['warnings']?.toString(),
      ratingAverage: parseDouble(json['rating_average']),
      ratingCount: parseInt(json['rating_count']),
      canReview:
          json['can_review'] == true || json['can_review']?.toString() == '1',
      currentUserReview: currentUserReview,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'description': description,
      'price': price,
      'image': image,
      'category': category,
      'brand': brand,
      'catalog_type': catalogType,
      'bundle_product_ids': bundleProductIds,
      'unit': unit,
      'stock': stock,
      'tracks_inventory': tracksInventory,
      'is_in_stock': isInStock,
      'is_low_stock': isLowStock,
      'usage': usage,
      'suitable_for': suitableFor,
      'active_ingredients': activeIngredients,
      'warnings': warnings,
      'rating_average': ratingAverage,
      'rating_count': ratingCount,
      'can_review': canReview,
      'current_user_review': currentUserReview?.toJson(),
    };
  }
}

class CategoryModel {
  final int id;
  final String name;
  final String? slug;
  final String? image;
  final String type;

  CategoryModel({
    required this.id,
    required this.name,
    this.slug,
    this.image,
    this.type = 'category',
  });

  factory CategoryModel.fromJson(Map<String, dynamic> json) {
    return CategoryModel(
      id: json['id'] is num
          ? (json['id'] as num).toInt()
          : int.tryParse('${json['id']}') ?? 0,
      name: json['name']?.toString() ?? '',
      slug: json['slug']?.toString(),
      image: json['image'],
      type: json['type']?.toString() ?? 'category',
    );
  }
}

class RemoteCartItem {
  final int id;
  final ProductModel product;
  final int quantity;
  const RemoteCartItem({
    required this.id,
    required this.product,
    required this.quantity,
  });
  factory RemoteCartItem.fromJson(Map<String, dynamic> json) => RemoteCartItem(
    id: json['id'] is num
        ? (json['id'] as num).toInt()
        : int.parse('${json['id']}'),
    product: ProductModel.fromJson(
      Map<String, dynamic>.from(json['product'] as Map),
    ),
    quantity: json['quantity'] is num
        ? (json['quantity'] as num).toInt()
        : int.parse('${json['quantity']}'),
  );
}
