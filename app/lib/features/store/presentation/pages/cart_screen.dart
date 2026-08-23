import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../../../core/localization/app_localizations.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../auth/presentation/bloc/auth_bloc.dart';
import '../../../auth/presentation/bloc/auth_state.dart';
import '../bloc/cart_bloc.dart';

class CartScreen extends StatelessWidget {
  const CartScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<CartBloc, CartState>(
      builder: (context, state) {
        final cartItems = state.items.values.toList();
        final isAuthenticated =
            context.read<AuthBloc>().state is AuthAuthenticated;

        return Scaffold(
          backgroundColor: AppColors.background,
          appBar: AppBar(
            title: Text(context.tr('shopping_cart')),
            actions: [
              if (cartItems.isNotEmpty)
                TextButton(
                  onPressed: () => context.read<CartBloc>().add(CartCleared()),
                  child: Text(
                    context.tr('clear'),
                    style: const TextStyle(color: AppColors.danger),
                  ),
                ),
            ],
          ),
          body: cartItems.isEmpty
              ? _buildEmptyState(context)
              : Column(
                  children: [
                    if (!isAuthenticated) _buildGuestNotice(context),
                    Expanded(
                      child: ListView.builder(
                        padding: const EdgeInsets.all(20),
                        itemCount: cartItems.length,
                        itemBuilder: (context, index) {
                          return _buildCartItem(context, cartItems[index]);
                        },
                      ),
                    ),
                    _buildSummary(context, state),
                  ],
                ),
        );
      },
    );
  }

  Widget _buildCartItem(BuildContext context, CartItem item) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: AppColors.divider, width: 0.6),
        boxShadow: const [
          BoxShadow(color: AppColors.cardShadow, blurRadius: 10),
        ],
      ),
      child: Row(
        children: [
          Container(
            width: 80,
            height: 80,
            decoration: BoxDecoration(
              color: AppColors.background,
              borderRadius: BorderRadius.circular(16),
            ),
            child: item.product.image != null
                ? ClipRRect(
                    borderRadius: BorderRadius.circular(16),
                    child: CachedNetworkImage(
                      imageUrl: item.product.image!,
                      fit: BoxFit.contain,
                      memCacheWidth: 240,
                      fadeInDuration: Duration.zero,
                      placeholder: (context, url) => const Center(
                        child: CircularProgressIndicator(strokeWidth: 2),
                      ),
                      errorWidget: (context, url, error) => const Icon(
                        Icons.image_outlined,
                        color: AppColors.textLight,
                      ),
                    ),
                  )
                : const Icon(Icons.image_outlined, color: AppColors.textLight),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item.product.name,
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 16,
                  ),
                ),
                Text(
                  item.product.unit ?? '',
                  style: const TextStyle(
                    color: AppColors.textLight,
                    fontSize: 12,
                  ),
                ),
                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      '\$${item.product.price.toStringAsFixed(2)}',
                      style: const TextStyle(
                        color: AppColors.primary,
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                      ),
                    ),
                    Container(
                      decoration: BoxDecoration(
                        color: AppColors.background,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Row(
                        children: [
                          IconButton(
                            icon: const Icon(Icons.remove, size: 16),
                            onPressed: () {
                              if (item.quantity > 1) {
                                context.read<CartBloc>().add(
                                  CartItemUpdated(
                                    item.product.id,
                                    item.quantity - 1,
                                  ),
                                );
                              } else {
                                context.read<CartBloc>().add(
                                  CartItemRemoved(item.product.id),
                                );
                              }
                            },
                          ),
                          Text(
                            '${item.quantity}',
                            style: const TextStyle(fontWeight: FontWeight.bold),
                          ),
                          IconButton(
                            icon: const Icon(Icons.add, size: 16),
                            onPressed: () {
                              context.read<CartBloc>().add(
                                CartItemUpdated(
                                  item.product.id,
                                  item.quantity + 1,
                                ),
                              );
                            },
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSummary(BuildContext context, CartState state) {
    return Container(
      padding: const EdgeInsets.fromLTRB(24, 24, 24, 32),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(30)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, -5),
          ),
        ],
      ),
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                context.tr('subtotal'),
                style: const TextStyle(color: AppColors.textSecondary),
              ),
              Text(
                '\$${state.totalAmount.toStringAsFixed(2)}',
                style: const TextStyle(fontWeight: FontWeight.bold),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                context.tr('delivery_fee'),
                style: const TextStyle(color: AppColors.textSecondary),
              ),
              Text(
                _cartLabel(context, 'calculated_checkout'),
                style: const TextStyle(
                  color: AppColors.textSecondary,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ),
          const Divider(height: 32),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                context.tr('total'),
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                ),
              ),
              Text(
                '\$${state.totalAmount.toStringAsFixed(2)}',
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  color: AppColors.primary,
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: () => context.push('/checkout'),
            child: Text(context.tr('proceed_checkout')),
          ),
        ],
      ),
    );
  }

  Widget _buildGuestNotice(BuildContext context) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.fromLTRB(20, 20, 20, 0),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFFFFF4EC),
        borderRadius: BorderRadius.circular(14),
      ),
      child: Row(
        children: [
          const Icon(Icons.info_outline_rounded, color: AppColors.accent),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              context.tr('guest_checkout_notice'),
              style: const TextStyle(color: AppColors.textPrimary),
            ),
          ),
          TextButton(
            onPressed: () => context.push('/login'),
            child: Text(context.tr('login')),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 90,
              height: 90,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(24),
                boxShadow: const [
                  BoxShadow(color: AppColors.cardShadow, blurRadius: 10),
                ],
              ),
              child: const Icon(
                Icons.shopping_bag_outlined,
                color: AppColors.textSecondary,
                size: 44,
              ),
            ),
            const SizedBox(height: 18),
            Text(
              context.tr('cart_empty'),
              style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 8),
            Text(
              context.tr('cart_empty_note'),
              style: const TextStyle(color: AppColors.textSecondary),
            ),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: () => context.go('/home?tab=0'),
              child: Text(context.tr('start_shopping')),
            ),
          ],
        ),
      ),
    );
  }

  String _cartLabel(BuildContext context, String key) {
    final isArabic = Localizations.localeOf(context).languageCode == 'ar';
    final labels = isArabic ? _arabicLabels : _englishLabels;
    return labels[key] ?? key;
  }

  static const _englishLabels = {
    'calculated_checkout': 'Based on pickup method and area',
  };

  static const _arabicLabels = {
    'calculated_checkout': 'حسب طريقة الاستلام والمنطقة',
  };
}
