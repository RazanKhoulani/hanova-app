class ProductReviewModel {
  final int id;
  final int rating;
  final String? comment;
  final DateTime? createdAt;

  const ProductReviewModel({
    required this.id,
    required this.rating,
    this.comment,
    this.createdAt,
  });

  factory ProductReviewModel.fromJson(Map<String, dynamic> json) {
    return ProductReviewModel(
      id: json['id'] is num
          ? (json['id'] as num).toInt()
          : int.tryParse('${json['id']}') ?? 0,
      rating: json['rating'] is num
          ? (json['rating'] as num).toInt()
          : int.tryParse('${json['rating']}') ?? 0,
      comment: json['comment']?.toString(),
      createdAt: DateTime.tryParse('${json['created_at'] ?? ''}'),
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'rating': rating,
    'comment': comment,
    'created_at': createdAt?.toIso8601String(),
  };
}

class ProductReviewSubmission {
  final ProductReviewModel review;
  final String? rewardCouponCode;
  final String? rewardCouponDiscountType;
  final double? rewardCouponDiscountValue;

  const ProductReviewSubmission({
    required this.review,
    this.rewardCouponCode,
    this.rewardCouponDiscountType,
    this.rewardCouponDiscountValue,
  });

  factory ProductReviewSubmission.fromJson(Map<String, dynamic> json) {
    final review = json['review'] is Map
        ? Map<String, dynamic>.from(json['review'] as Map)
        : const <String, dynamic>{};
    final coupon = json['reward_coupon'] is Map
        ? Map<String, dynamic>.from(json['reward_coupon'] as Map)
        : null;

    return ProductReviewSubmission(
      review: ProductReviewModel.fromJson(review),
      rewardCouponCode: coupon?['code']?.toString(),
      rewardCouponDiscountType: coupon?['discount_type']?.toString(),
      rewardCouponDiscountValue: coupon?['discount_value'] is num
          ? (coupon!['discount_value'] as num).toDouble()
          : double.tryParse('${coupon?['discount_value'] ?? ''}'),
    );
  }
}
