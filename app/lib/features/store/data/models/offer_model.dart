import '../../../../core/constants/api_constants.dart';

class OfferModel {
  final int id;
  final String title;
  final String? description;
  final String discountType;
  final double discountValue;
  final String targetSegment;
  final String? image;

  OfferModel({
    required this.id,
    required this.title,
    this.description,
    required this.discountType,
    required this.discountValue,
    required this.targetSegment,
    this.image,
  });

  factory OfferModel.fromJson(Map<String, dynamic> json) {
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
        return value;
      }

      final base = ApiConstants.baseUrl;
      final apiIndex = base.indexOf('/api');
      final host = apiIndex >= 0 ? base.substring(0, apiIndex) : base;
      return value.startsWith('/') ? '$host$value' : '$host/$value';
    }

    return OfferModel(
      id: json['id'],
      title: resolveLocalized(json['title']).isNotEmpty
          ? resolveLocalized(json['title'])
          : resolveLocalized(json['title_translations']),
      description: resolveLocalized(json['description']).isNotEmpty
          ? resolveLocalized(json['description'])
          : resolveLocalized(json['description_translations']),
      discountType: json['discount_type']?.toString() ?? 'percentage',
      discountValue: (json['discount_value'] as num?)?.toDouble() ?? 0,
      targetSegment: json['target_segment']?.toString() ?? 'all',
      image: resolveImage(json['image']),
    );
  }

  String get discountLabel {
    if (discountType == 'percentage') {
      return '${discountValue.toStringAsFixed(0)}%';
    }

    return '${discountValue.toStringAsFixed(0)} ل.س';
  }
}
