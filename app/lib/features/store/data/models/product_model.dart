import '../../../../core/constants/api_constants.dart';

class ProductModel {
  final int id;
  final String name;
  final String? description;
  final double price;
  final String? image;
  final String? category;
  final String? unit;
  final int stock;

  ProductModel({
    required this.id,
    required this.name,
    this.description,
    required this.price,
    this.image,
    this.category,
    this.unit,
    required this.stock,
  });

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

    return ProductModel(
      id: parseInt(json['id']),
      name: resolveLocalized(json['name']).isNotEmpty
          ? resolveLocalized(json['name'])
          : resolveLocalized(json['name_translations']),
      description: resolveLocalized(json['description']).isNotEmpty
          ? resolveLocalized(json['description'])
          : resolveLocalized(json['description_translations']),
      price: parseDouble(json['price']),
      image: resolveImage(json['image'] ?? json['image_url']),
      category: json['category'],
      unit: json['unit'],
      stock: parseInt(json['stock']),
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
      'unit': unit,
      'stock': stock,
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
