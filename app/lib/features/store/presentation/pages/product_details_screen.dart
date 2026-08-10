import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../../../core/localization/app_localizations.dart';
import '../../../../core/network/api_error_message.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../../injection_container.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../bloc/cart_bloc.dart';
import '../../data/models/product_model.dart';
import '../../domain/repositories/store_repository.dart';

class ProductDetailsScreen extends StatefulWidget {
  final String productId;
  const ProductDetailsScreen({super.key, required this.productId});

  @override
  State<ProductDetailsScreen> createState() => _ProductDetailsScreenState();
}

class _ProductDetailsScreenState extends State<ProductDetailsScreen> {
  int _quantity = 1;
  late Future<ProductModel> _productFuture;

  @override
  void initState() {
    super.initState();
    _productFuture = _fetchProduct();
  }

  Future<ProductModel> _fetchProduct() {
    return sl<StoreRepository>().getProductDetails(int.parse(widget.productId));
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<ProductModel>(
      future: _productFuture,
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return const Scaffold(
            body: Center(child: CircularProgressIndicator()),
          );
        }

        if (snapshot.hasError || !snapshot.hasData) {
          return Scaffold(
            appBar: AppBar(),
            body: Center(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(
                      Icons.cloud_off_rounded,
                      size: 56,
                      color: AppColors.textLight,
                    ),
                    const SizedBox(height: 12),
                    Text(
                      snapshot.error == null
                          ? _detailLabel('no_description')
                          : ApiErrorMessage.from(snapshot.error!),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 16),
                    OutlinedButton(
                      onPressed: () {
                        setState(() => _productFuture = _fetchProduct());
                      },
                      child: Text(context.tr('try_again')),
                    ),
                  ],
                ),
              ),
            ),
          );
        }

        final product = snapshot.data!;
        return Scaffold(
          backgroundColor: AppColors.background,
          body: CustomScrollView(
            slivers: [
              _buildAppBar(product),
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  product.name,
                                  style: const TextStyle(
                                    fontSize: 24,
                                    fontWeight: FontWeight.bold,
                                    color: AppColors.textPrimary,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  product.unit?.isNotEmpty == true
                                      ? product.unit!
                                      : _detailLabel('premium_product'),
                                  style: const TextStyle(
                                    fontSize: 14,
                                    color: AppColors.textSecondary,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 12,
                              vertical: 6,
                            ),
                            decoration: BoxDecoration(
                              color: AppColors.primaryLight,
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Text(
                              '\$${product.price.toStringAsFixed(2)}',
                              style: const TextStyle(
                                color: AppColors.primary,
                                fontWeight: FontWeight.bold,
                                fontSize: 18,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 32),
                      Text(
                        _detailLabel('description'),
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      const SizedBox(height: 12),
                      Text(
                        product.description ?? _detailLabel('no_description'),
                        style: const TextStyle(
                          fontSize: 15,
                          color: AppColors.textSecondary,
                          height: 1.6,
                        ),
                      ),
                      const SizedBox(height: 24),
                      Row(
                        children: [
                          Expanded(
                            child: OutlinedButton.icon(
                              onPressed: () {
                                final uri = Uri(
                                  path: '/bot',
                                  queryParameters: {
                                    'product_name': product.name,
                                    if (product.description != null &&
                                        product.description!.isNotEmpty)
                                      'product_description':
                                          product.description!,
                                  },
                                );
                                context.push(uri.toString());
                              },
                              icon: const Icon(Icons.smart_toy_outlined),
                              label: Text(_detailLabel('ask_bot')),
                              style: OutlinedButton.styleFrom(
                                minimumSize: const Size(double.infinity, 52),
                                side: const BorderSide(
                                  color: AppColors.primary,
                                ),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(14),
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 40),
                    ],
                  ),
                ),
              ),
            ],
          ),
          bottomNavigationBar: _buildBottomBar(product),
        );
      },
    );
  }

  Widget _buildAppBar(ProductModel product) {
    return SliverAppBar(
      expandedHeight: 320,
      pinned: true,
      backgroundColor: Colors.white,
      leading: IconButton(
        icon: const Icon(Icons.arrow_back_ios_new_rounded, size: 20),
        onPressed: () => context.pop(),
      ),
      flexibleSpace: FlexibleSpaceBar(
        background: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topCenter,
              end: Alignment.bottomCenter,
              colors: [Color(0xFFFDEFF3), AppColors.background],
            ),
          ),
          child: Center(
            child: Container(
              margin: const EdgeInsets.only(top: 60),
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(24),
                boxShadow: const [
                  BoxShadow(color: AppColors.cardShadow, blurRadius: 14),
                ],
              ),
              child: SizedBox(
                width: 190,
                height: 190,
                child: product.image != null
                    ? CachedNetworkImage(
                        imageUrl: product.image!,
                        fit: BoxFit.contain,
                        fadeInDuration: Duration.zero,
                        placeholder: (context, url) => const Center(
                          child: CircularProgressIndicator(strokeWidth: 2),
                        ),
                        errorWidget: (context, url, error) => const Icon(
                          Icons.image_outlined,
                          size: 100,
                          color: AppColors.textLight,
                        ),
                      )
                    : const Icon(
                        Icons.image_outlined,
                        size: 100,
                        color: AppColors.textLight,
                      ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildBottomBar(ProductModel product) {
    return Container(
      padding: const EdgeInsets.fromLTRB(24, 16, 24, 32),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, -5),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            decoration: BoxDecoration(
              color: AppColors.background,
              borderRadius: BorderRadius.circular(16),
            ),
            child: Row(
              children: [
                IconButton(
                  icon: const Icon(Icons.remove, size: 20),
                  onPressed: () {
                    if (_quantity > 1) {
                      setState(() => _quantity--);
                    }
                  },
                ),
                Text(
                  '$_quantity',
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.add, size: 20),
                  onPressed: () => setState(() => _quantity++),
                ),
              ],
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: ElevatedButton(
              onPressed: () {
                context.read<CartBloc>().add(CartItemAdded(product, _quantity));
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text(context.readTr('added_to_cart'))),
                );
                context.push('/cart');
              },
              child: Text(_detailLabel('add_to_cart')),
            ),
          ),
        ],
      ),
    );
  }

  String _detailLabel(String key) {
    final isArabic = Localizations.localeOf(context).languageCode == 'ar';
    final labels = isArabic ? _arabicDetailLabels : _englishDetailLabels;
    return labels[key] ?? key;
  }

  static const _englishDetailLabels = {
    'premium_product': 'Premium care product',
    'description': 'Description',
    'no_description': 'No description available.',
    'ask_bot': 'Ask Bot',
    'add_to_cart': 'Add to Cart',
  };

  static const _arabicDetailLabels = {
    'premium_product':
        '\u0645\u0646\u062a\u062c \u0639\u0646\u0627\u064a\u0629 \u0645\u062e\u062a\u0627\u0631',
    'description': '\u0627\u0644\u0648\u0635\u0641',
    'no_description':
        '\u0644\u0627 \u064a\u0648\u062c\u062f \u0648\u0635\u0641 \u0645\u062a\u0627\u062d.',
    'ask_bot': '\u0627\u0633\u0623\u0644\u064a \u0627\u0644\u0628\u0648\u062a',
    'add_to_cart':
        '\u0625\u0636\u0627\u0641\u0629 \u0625\u0644\u0649 \u0627\u0644\u0633\u0644\u0629',
  };
}
