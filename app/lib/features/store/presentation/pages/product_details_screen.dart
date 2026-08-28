import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../../../core/localization/app_localizations.dart';
import '../../../../core/network/api_error_message.dart';
import '../../../../core/settings/app_settings_cubit.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../../core/utils/currency_formatter.dart';
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
                padding: const EdgeInsets.fromLTRB(22, 24, 22, 28),
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
                              CurrencyFormatter.display(
                                product.price,
                                context.watch<AppSettingsCubit>().state,
                              ),
                              style: const TextStyle(
                                color: AppColors.primary,
                                fontWeight: FontWeight.bold,
                                fontSize: 18,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 14),
                      _buildRatingCard(product),
                      const SizedBox(height: 26),
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(22),
                          border: Border.all(color: AppColors.divider),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              _detailLabel('description'),
                              style: const TextStyle(
                                fontSize: 18,
                                fontWeight: FontWeight.bold,
                                color: AppColors.textPrimary,
                              ),
                            ),
                            ...[
                                  ('usage', product.usage),
                                  ('suitable_for', product.suitableFor),
                                  (
                                    'active_ingredients',
                                    product.activeIngredients,
                                  ),
                                  ('warnings', product.warnings),
                                ]
                                .where(
                                  (item) => item.$2?.trim().isNotEmpty == true,
                                )
                                .expand(
                                  (item) => [
                                    const SizedBox(height: 16),
                                    Text(
                                      _detailLabel(item.$1),
                                      style: const TextStyle(
                                        fontWeight: FontWeight.bold,
                                        color: AppColors.textPrimary,
                                      ),
                                    ),
                                    const SizedBox(height: 5),
                                    Text(
                                      item.$2!,
                                      style: const TextStyle(
                                        color: AppColors.textSecondary,
                                        height: 1.5,
                                      ),
                                    ),
                                  ],
                                ),
                            const SizedBox(height: 10),
                            Text(
                              product.description ??
                                  _detailLabel('no_description'),
                              style: const TextStyle(
                                fontSize: 15,
                                color: AppColors.textSecondary,
                                height: 1.6,
                              ),
                            ),
                          ],
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
                                    if (product.botDescription.isNotEmpty)
                                      'product_description':
                                          product.botDescription,
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
      expandedHeight: 350,
      pinned: true,
      backgroundColor: AppColors.background,
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
              colors: [AppColors.primaryMist, AppColors.background],
            ),
          ),
          child: Center(
            child: Container(
              margin: const EdgeInsets.only(top: 64),
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(30),
                border: Border.all(color: Colors.white, width: 5),
                boxShadow: const [
                  BoxShadow(color: AppColors.cardShadow, blurRadius: 14),
                ],
              ),
              child: SizedBox(
                width: 210,
                height: 210,
                child: product.image != null
                    ? CachedNetworkImage(
                        imageUrl: product.image!,
                        fit: BoxFit.contain,
                        memCacheWidth: 640,
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
    final canAdd = !product.tracksInventory || product.isInStock;
    final canIncrease = !product.tracksInventory || _quantity < product.stock;

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
                  onPressed: canIncrease
                      ? () => setState(() => _quantity++)
                      : null,
                ),
              ],
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: ElevatedButton(
              onPressed: canAdd
                  ? () {
                      context.read<CartBloc>().add(
                        CartItemAdded(product, _quantity),
                      );
                      ScaffoldMessenger.of(context).hideCurrentSnackBar();
                      context.push('/cart');
                    }
                  : null,
              child: Text(
                _detailLabel(canAdd ? 'add_to_cart' : 'out_of_stock'),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRatingCard(ProductModel product) {
    final hasRating = product.ratingCount > 0;
    final userReview = product.currentUserReview;

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.divider),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            _detailLabel('product_rating'),
            style: const TextStyle(
              fontWeight: FontWeight.bold,
              color: AppColors.textPrimary,
            ),
          ),
          if (product.tracksInventory) ...[
            const SizedBox(height: 8),
            Text(
              _detailLabel(
                product.isInStock
                    ? (product.isLowStock ? 'stock_low' : 'stock_available')
                    : 'out_of_stock',
              ).replaceFirst(':count', '${product.stock}'),
              style: TextStyle(
                color: product.isInStock
                    ? AppColors.primary
                    : AppColors.textLight,
                fontWeight: FontWeight.w600,
                fontSize: 12,
              ),
            ),
          ],
          const SizedBox(height: 10),
          Row(
            children: [
              Text(
                hasRating ? product.ratingAverage.toStringAsFixed(1) : '-',
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 20,
                  color: AppColors.textPrimary,
                ),
              ),
              const SizedBox(width: 8),
              ...List.generate(
                5,
                (index) => Icon(
                  index < product.ratingAverage.round()
                      ? Icons.star_rounded
                      : Icons.star_border_rounded,
                  size: 20,
                  color: const Color(0xFFE9B949),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  hasRating
                      ? _detailLabel(
                          'ratings_count',
                        ).replaceFirst(':count', '${product.ratingCount}')
                      : _detailLabel('no_ratings'),
                  style: const TextStyle(
                    color: AppColors.textSecondary,
                    fontSize: 12,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          if (product.canReview)
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: () => _showReviewSheet(product),
                icon: Icon(
                  userReview == null
                      ? Icons.rate_review_outlined
                      : Icons.edit_outlined,
                  size: 18,
                ),
                label: Text(
                  userReview == null
                      ? _detailLabel('rate_product')
                      : _detailLabel('update_rating'),
                ),
                style: OutlinedButton.styleFrom(
                  minimumSize: const Size.fromHeight(44),
                  side: const BorderSide(color: AppColors.primary),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
            )
          else
            Text(
              _detailLabel('review_after_delivery'),
              style: const TextStyle(
                color: AppColors.textSecondary,
                fontSize: 12,
              ),
            ),
        ],
      ),
    );
  }

  Future<void> _showReviewSheet(ProductModel product) async {
    final commentController = TextEditingController(
      text: product.currentUserReview?.comment ?? '',
    );
    final draft = await showModalBottomSheet<_ReviewDraft>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (sheetContext) {
        var rating = product.currentUserReview?.rating ?? 0;

        return StatefulBuilder(
          builder: (context, setSheetState) => SafeArea(
            child: Padding(
              padding: EdgeInsets.fromLTRB(
                20,
                0,
                20,
                MediaQuery.viewInsetsOf(context).bottom + 20,
              ),
              child: Material(
                color: Colors.white,
                borderRadius: BorderRadius.circular(24),
                child: Padding(
                  padding: const EdgeInsets.all(22),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _detailLabel('rate_product'),
                        style: const TextStyle(
                          fontSize: 19,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        _detailLabel('review_reward_note'),
                        style: const TextStyle(
                          color: AppColors.textSecondary,
                          height: 1.4,
                        ),
                      ),
                      const SizedBox(height: 18),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: List.generate(
                          5,
                          (index) => IconButton(
                            onPressed: () =>
                                setSheetState(() => rating = index + 1),
                            icon: Icon(
                              index < rating
                                  ? Icons.star_rounded
                                  : Icons.star_border_rounded,
                              color: const Color(0xFFE9B949),
                              size: 34,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),
                      TextField(
                        controller: commentController,
                        maxLines: 3,
                        maxLength: 500,
                        textInputAction: TextInputAction.done,
                        decoration: InputDecoration(
                          labelText: _detailLabel('review_comment'),
                          hintText: _detailLabel('review_comment_hint'),
                          alignLabelWithHint: true,
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(14),
                          ),
                        ),
                      ),
                      const SizedBox(height: 8),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: rating == 0
                              ? null
                              : () => Navigator.of(sheetContext).pop(
                                  _ReviewDraft(
                                    rating: rating,
                                    comment: commentController.text,
                                  ),
                                ),
                          child: Text(_detailLabel('submit_rating')),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        );
      },
    );
    commentController.dispose();

    if (draft == null) return;

    try {
      final submission = await sl<StoreRepository>().submitProductReview(
        product.id,
        rating: draft.rating,
        comment: draft.comment,
      );
      if (!mounted) return;

      setState(() => _productFuture = _fetchProduct());
      final hasReward = submission.rewardCouponCode?.isNotEmpty == true;
      final rewardValue = submission.rewardCouponDiscountValue;
      final rewardText = hasReward && rewardValue != null
          ? '\n${_detailLabel('reward_granted')}: ${rewardValue.toStringAsFixed(rewardValue % 1 == 0 ? 0 : 1)}% (${submission.rewardCouponCode})'
          : '';
      ScaffoldMessenger.of(context)
        ..hideCurrentSnackBar()
        ..showSnackBar(
          SnackBar(
            content: Text('${_detailLabel('rating_saved')}$rewardText'),
            duration: const Duration(seconds: 5),
          ),
        );
    } catch (error) {
      if (!mounted) return;
      ScaffoldMessenger.of(context)
        ..hideCurrentSnackBar()
        ..showSnackBar(SnackBar(content: Text(ApiErrorMessage.from(error))));
    }
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
    'usage': 'How to use',
    'suitable_for': 'Suitable for',
    'active_ingredients': 'Active ingredients',
    'warnings': 'Warnings',
    'add_to_cart': 'Add to Cart',
    'stock_available': 'Available: :count',
    'stock_low': 'Low stock: :count left',
    'out_of_stock': 'Out of stock',
    'product_rating': 'Product rating',
    'ratings_count': ':count ratings',
    'no_ratings': 'No ratings yet',
    'rate_product': 'Rate this product',
    'update_rating': 'Update your rating',
    'review_after_delivery':
        'You can rate this product after your order is delivered.',
    'review_reward_note':
        'Your first eligible review earns a discount for a future order.',
    'review_comment': 'Your comment (optional)',
    'review_comment_hint': 'Tell us about your experience with the product.',
    'submit_rating': 'Submit rating',
    'rating_saved': 'Your rating was saved.',
    'reward_granted': 'Your next-order discount',
  };

  static const _arabicDetailLabels = {
    'premium_product':
        '\u0645\u0646\u062a\u062c \u0639\u0646\u0627\u064a\u0629 \u0645\u062e\u062a\u0627\u0631',
    'description': '\u0627\u0644\u0648\u0635\u0641',
    'no_description':
        '\u0644\u0627 \u064a\u0648\u062c\u062f \u0648\u0635\u0641 \u0645\u062a\u0627\u062d.',
    'ask_bot': '\u0627\u0633\u0623\u0644\u064a \u0627\u0644\u0628\u0648\u062a',
    'usage': 'طريقة الاستخدام',
    'suitable_for': 'لمن يناسب',
    'active_ingredients': 'المكونات الفعالة',
    'warnings': 'التحذيرات',
    'add_to_cart':
        '\u0625\u0636\u0627\u0641\u0629 \u0625\u0644\u0649 \u0627\u0644\u0633\u0644\u0629',
    'stock_available': '\u0645\u062a\u0648\u0641\u0631: :count',
    'stock_low':
        '\u0627\u0644\u0645\u062a\u0628\u0642\u064a \u0642\u0644\u064a\u0644: :count',
    'out_of_stock':
        '\u0646\u0641\u062f \u0627\u0644\u0645\u062e\u0632\u0648\u0646',
    'product_rating':
        '\u062a\u0642\u064a\u064a\u0645 \u0627\u0644\u0645\u0646\u062a\u062c',
    'ratings_count': ':count \u062a\u0642\u064a\u064a\u0645',
    'no_ratings':
        '\u0644\u0627 \u062a\u0648\u062c\u062f \u062a\u0642\u064a\u064a\u0645\u0627\u062a \u0628\u0639\u062f',
    'rate_product':
        '\u0642\u064a\u0651\u0645\u064a \u0627\u0644\u0645\u0646\u062a\u062c',
    'update_rating':
        '\u062a\u0639\u062f\u064a\u0644 \u062a\u0642\u064a\u064a\u0645\u0643',
    'review_after_delivery':
        '\u064a\u0645\u0643\u0646\u0643 \u062a\u0642\u064a\u064a\u0645 \u0627\u0644\u0645\u0646\u062a\u062c \u0628\u0639\u062f \u062a\u0633\u0644\u064a\u0645 \u0637\u0644\u0628\u0643.',
    'review_reward_note':
        '\u0633\u062a\u062d\u0635\u0644\u064a\u0646 \u0639\u0644\u0649 \u062e\u0635\u0645 \u0644\u0637\u0644\u0628 \u0642\u0627\u062f\u0645 \u0628\u0639\u062f \u0623\u0648\u0644 \u062a\u0642\u064a\u064a\u0645 \u0645\u0624\u0647\u0644.',
    'review_comment':
        '\u062a\u0639\u0644\u064a\u0642\u0643 (\u0627\u062e\u062a\u064a\u0627\u0631\u064a)',
    'review_comment_hint':
        '\u0623\u062e\u0628\u0631\u064a\u0646\u0627 \u0639\u0646 \u062a\u062c\u0631\u0628\u062a\u0643 \u0645\u0639 \u0627\u0644\u0645\u0646\u062a\u062c.',
    'submit_rating':
        '\u0625\u0631\u0633\u0627\u0644 \u0627\u0644\u062a\u0642\u064a\u064a\u0645',
    'rating_saved':
        '\u062a\u0645 \u062d\u0641\u0638 \u062a\u0642\u064a\u064a\u0645\u0643.',
    'reward_granted':
        '\u062e\u0635\u0645\u0643 \u0644\u0644\u0637\u0644\u0628 \u0627\u0644\u0642\u0627\u062f\u0645',
  };
}

class _ReviewDraft {
  final int rating;
  final String comment;

  const _ReviewDraft({required this.rating, required this.comment});
}
