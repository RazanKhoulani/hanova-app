class OrderModel {
  final int id;
  final String orderNumber;
  final double totalAmount;
  final double? subtotalUsd;
  final double? totalAmountUsd;
  final double deliveryFee;
  final double? deliveryFeeUsd;
  final double discountAmount;
  final double? discountAmountUsd;
  final String? deliveryMethod;
  final String? paymentStatus;
  final String? paymentReceiptStatus;
  final String? receiptRejectionReason;
  final String status;
  final String statusLabel;
  final DateTime createdAt;
  final List<OrderItemModel> items;

  OrderModel({
    required this.id,
    required this.orderNumber,
    required this.totalAmount,
    this.subtotalUsd,
    this.totalAmountUsd,
    this.deliveryFee = 0,
    this.deliveryFeeUsd,
    this.discountAmount = 0,
    this.discountAmountUsd,
    this.deliveryMethod,
    this.paymentStatus,
    this.paymentReceiptStatus,
    this.receiptRejectionReason,
    required this.status,
    this.statusLabel = '',
    required this.createdAt,
    required this.items,
  });

  factory OrderModel.fromJson(Map<String, dynamic> json) {
    int parseInt(dynamic value) {
      return value is num ? value.toInt() : int.tryParse('$value') ?? 0;
    }

    double parseDouble(dynamic value) {
      return value is num ? value.toDouble() : double.tryParse('$value') ?? 0;
    }

    return OrderModel(
      id: parseInt(json['id']),
      orderNumber: json['order_number']?.toString() ?? 'ORD-${json['id']}',
      totalAmount: parseDouble(json['total_amount']),
      subtotalUsd: json['subtotal_usd'] == null
          ? null
          : parseDouble(json['subtotal_usd']),
      totalAmountUsd: json['total_amount_usd'] == null
          ? null
          : parseDouble(json['total_amount_usd']),
      deliveryFee: parseDouble(json['delivery_fee']),
      deliveryFeeUsd: json['delivery_fee_usd'] == null
          ? null
          : parseDouble(json['delivery_fee_usd']),
      discountAmount: parseDouble(json['discount_amount']),
      discountAmountUsd: json['discount_amount_usd'] == null
          ? null
          : parseDouble(json['discount_amount_usd']),
      deliveryMethod: json['delivery_method']?.toString(),
      paymentStatus: json['payment_status']?.toString(),
      paymentReceiptStatus: json['payment_receipt_status']?.toString(),
      receiptRejectionReason: json['receipt_rejection_reason']?.toString(),
      status: json['status']?.toString() ?? 'pending',
      statusLabel: json['status_label']?.toString() ?? '',
      createdAt:
          DateTime.tryParse(json['created_at']?.toString() ?? '') ??
          DateTime.now(),
      items: (json['items'] as List? ?? [])
          .map((item) => OrderItemModel.fromJson(item))
          .toList(),
    );
  }
}

class DeliveryAreaModel {
  final int id;
  final String name;
  final double fee;
  final double? feeUsd;

  DeliveryAreaModel({
    required this.id,
    required this.name,
    required this.fee,
    this.feeUsd,
  });

  factory DeliveryAreaModel.fromJson(Map<String, dynamic> json) {
    return DeliveryAreaModel(
      id: json['id'] is num
          ? (json['id'] as num).toInt()
          : int.parse('${json['id']}'),
      name: json['name']?.toString() ?? '',
      fee: (json['fee'] as num? ?? 0).toDouble(),
      feeUsd: json['fee_usd'] == null
          ? null
          : (json['fee_usd'] is num
                ? (json['fee_usd'] as num).toDouble()
                : double.tryParse(json['fee_usd'].toString())),
    );
  }
}

class OrderItemModel {
  final int productId;
  final String productName;
  final int quantity;
  final double price;
  final double? priceUsd;

  OrderItemModel({
    required this.productId,
    required this.productName,
    required this.quantity,
    required this.price,
    this.priceUsd,
  });

  factory OrderItemModel.fromJson(Map<String, dynamic> json) {
    String resolveProductName(dynamic value) {
      if (value is Map<String, dynamic>) {
        if (value['name'] is String) return value['name'] as String;
        if (value['name'] is Map<String, dynamic>) {
          final localized = value['name'] as Map<String, dynamic>;
          return (localized['ar'] ?? localized['en'] ?? 'Unknown Product')
              .toString();
        }
        if (value['name_translations'] is Map<String, dynamic>) {
          final localized = value['name_translations'] as Map<String, dynamic>;
          return (localized['ar'] ?? localized['en'] ?? 'Unknown Product')
              .toString();
        }
      }
      return value?.toString() ?? 'Unknown Product';
    }

    return OrderItemModel(
      productId:
          int.tryParse(
            '${json['product_id'] ?? (json['product']?['id'] ?? 0)}',
          ) ??
          0,
      productName:
          json['product_name']?.toString() ??
          resolveProductName(json['product']),
      quantity: int.tryParse('${json['quantity']}') ?? 0,
      price: json['price'] is num
          ? (json['price'] as num).toDouble()
          : double.tryParse('${json['price']}') ?? 0,
      priceUsd: json['price_usd'] == null
          ? null
          : (json['price_usd'] is num
                ? (json['price_usd'] as num).toDouble()
                : double.tryParse('${json['price_usd']}')),
    );
  }
}
