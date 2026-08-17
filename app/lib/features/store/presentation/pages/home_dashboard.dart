import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../../../core/localization/app_localizations.dart';
import '../../../../core/settings/app_settings_cubit.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../../core/widgets/hanova_auth_shell.dart';
import '../../../auth/presentation/bloc/auth_bloc.dart';
import '../../../auth/presentation/bloc/auth_state.dart';
import '../../../notifications/presentation/widgets/notification_bell.dart';
import '../../../../injection_container.dart';
import '../bloc/store_bloc.dart';
import '../bloc/cart_bloc.dart';
import '../../domain/repositories/store_repository.dart';
import '../../data/models/product_model.dart';
import '../../data/models/home_data_model.dart';

class HomeDashboard extends StatefulWidget {
  const HomeDashboard({super.key});

  @override
  State<HomeDashboard> createState() => _HomeDashboardState();
}

class _HomeDashboardState extends State<HomeDashboard> {
  String? _selectedConcernSlug;
  final TextEditingController _searchController = TextEditingController();
  late Future<HomeDataModel> _homeFuture;
  Timer? _searchDebounce;
  String? _searchQuery;

  @override
  void initState() {
    super.initState();
    _homeFuture = _requestHomeData();
  }

  @override
  void dispose() {
    _searchDebounce?.cancel();
    _searchController.dispose();
    super.dispose();
  }

  void _fetchProducts({bool force = false}) {
    context.read<StoreBloc>().add(
      StoreFetchProducts(
        concern: _selectedConcernSlug,
        query: _searchQuery,
        force: force,
      ),
    );
  }

  void _onSearchChanged(String value) {
    _searchDebounce?.cancel();
    setState(() {});
    _searchDebounce = Timer(const Duration(milliseconds: 350), () {
      if (!mounted) return;
      final query = value.trim();
      _searchQuery = query.isEmpty ? null : query;
      _fetchProducts();
    });
  }

  void _clearFilters() {
    _searchDebounce?.cancel();
    setState(() {
      _selectedConcernSlug = null;
      _searchQuery = null;
      _searchController.clear();
    });
    _fetchProducts();
  }

  void _reloadHome() {
    setState(() {
      _homeFuture = _requestHomeData();
    });
  }

  Future<HomeDataModel> _requestHomeData() async {
    try {
      final data = await sl<StoreRepository>().getHomeData();
      if (mounted && _selectedConcernSlug == null && _searchQuery == null) {
        context.read<StoreBloc>().add(StoreSeedProducts(data.products));
      }
      return data;
    } catch (_) {
      if (mounted) {
        _fetchProducts(force: true);
      }
      rethrow;
    }
  }

  @override
  Widget build(BuildContext context) {
    return BlocListener<AppSettingsCubit, AppSettingsState>(
      listener: (context, state) {
        setState(() {
          _homeFuture = _requestHomeData();
        });
        if (_selectedConcernSlug != null || _searchQuery != null) {
          _fetchProducts(force: true);
        }
      },
      child: Scaffold(
        backgroundColor: AppColors.background,
        body: CustomScrollView(
          slivers: [
            _buildAppBar(context),
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildBanner(),
                    const SizedBox(height: 24),
                    _buildSectionHeader(
                      context.tr('categories'),
                      _clearFilters,
                    ),
                    const SizedBox(height: 16),
                    _buildCategoriesSection(),
                    const SizedBox(height: 24),
                    _buildSectionHeader(
                      context.tr('top_products'),
                      _clearFilters,
                    ),
                    const SizedBox(height: 16),
                    _buildProductsSection(),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAppBar(BuildContext context) {
    return SliverAppBar(
      floating: true,
      pinned: true,
      toolbarHeight: 126,
      titleSpacing: 0,
      backgroundColor: AppColors.background,
      surfaceTintColor: Colors.transparent,
      title: Padding(
        padding: const EdgeInsets.fromLTRB(20, 6, 20, 10),
        child: Column(
          children: [
            Row(
              children: [
                const HanovaBrandMark(size: 42),
                const SizedBox(width: 10),
                Expanded(
                  child: BlocBuilder<AuthBloc, AuthState>(
                    buildWhen: (previous, current) =>
                        previous.runtimeType != current.runtimeType,
                    builder: (context, authState) {
                      final name = authState is AuthAuthenticated
                          ? authState.user.name
                          : 'Hanova';
                      return Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            name,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                          Text(
                            context.tr('skin_consultation'),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              fontSize: 10,
                              color: AppColors.textSecondary,
                            ),
                          ),
                        ],
                      );
                    },
                  ),
                ),
                _HomeHeaderAction(
                  icon: Icons.smart_toy_outlined,
                  onTap: () => context.push('/bot'),
                ),
                const NotificationBell(),
                BlocBuilder<CartBloc, CartState>(
                  buildWhen: (previous, current) =>
                      previous.itemCount != current.itemCount,
                  builder: (context, cartState) {
                    return _HomeHeaderAction(
                      icon: Icons.shopping_bag_outlined,
                      badge: cartState.itemCount,
                      onTap: () => context.push('/cart'),
                    );
                  },
                ),
              ],
            ),
            const SizedBox(height: 10),
            SizedBox(
              height: 48,
              child: TextField(
                controller: _searchController,
                onChanged: _onSearchChanged,
                textInputAction: TextInputAction.search,
                decoration: InputDecoration(
                  filled: true,
                  fillColor: Colors.white,
                  hintText: context.tr('search_products'),
                  prefixIcon: const Icon(Icons.search_rounded),
                  suffixIcon: _searchController.text.isEmpty
                      ? null
                      : IconButton(
                          onPressed: _clearFilters,
                          icon: const Icon(Icons.close_rounded),
                        ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(18),
                    borderSide: const BorderSide(color: AppColors.divider),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(18),
                    borderSide: const BorderSide(color: AppColors.primary),
                  ),
                  contentPadding: EdgeInsets.zero,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBanner() {
    return BlocBuilder<AuthBloc, AuthState>(
      builder: (context, authState) {
        final isAuthenticated = authState is AuthAuthenticated;

        return FutureBuilder<HomeDataModel>(
          future: _homeFuture,
          builder: (context, snapshot) {
            final offer = snapshot.data?.activeOffer;

            return Container(
              constraints: const BoxConstraints(minHeight: 188),
              width: double.infinity,
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [AppColors.primary, Color(0xFFE08FA5)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(28),
              ),
              child: Stack(
                children: [
                  Positioned(
                    right: -20,
                    bottom: -20,
                    child: Icon(
                      offer == null
                          ? Icons.medical_services_rounded
                          : Icons.local_offer_rounded,
                      size: 120,
                      color: Colors.white.withValues(alpha: 0.15),
                    ),
                  ),
                  Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        if (offer != null)
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 10,
                              vertical: 5,
                            ),
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.18),
                              borderRadius: BorderRadius.circular(999),
                            ),
                            child: Text(
                              offer.discountLabel,
                              style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        if (offer != null) const SizedBox(height: 10),
                        Text(
                          offer?.title ?? context.tr('skin_consultation'),
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          offer?.description?.isNotEmpty == true
                              ? offer!.description!
                              : context.tr('home_banner_copy'),
                          style: const TextStyle(
                            color: Colors.white70,
                            fontSize: 13,
                          ),
                        ),
                        const SizedBox(height: 12),
                        ElevatedButton(
                          onPressed: () {
                            if (offer != null) {
                              setState(() {
                                _selectedConcernSlug = null;
                              });
                              context.read<StoreBloc>().add(
                                StoreFetchProducts(),
                              );
                              return;
                            }

                            if (isAuthenticated) {
                              context.push('/appointment');
                              return;
                            }
                            context.push('/login');
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.white,
                            foregroundColor: AppColors.primary,
                            minimumSize: const Size(100, 36),
                            padding: const EdgeInsets.symmetric(horizontal: 16),
                          ),
                          child: Text(
                            offer != null
                                ? context.tr('browse_products')
                                : isAuthenticated
                                ? context.tr('book_now')
                                : context.tr('login_to_book'),
                            style: const TextStyle(fontSize: 12),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildSectionHeader(String title, VoidCallback onSeeAll) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          title,
          style: const TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: AppColors.textPrimary,
          ),
        ),
        TextButton(
          onPressed: onSeeAll,
          child: Text(
            context.tr('see_all'),
            style: TextStyle(
              color: AppColors.primary,
              fontWeight: FontWeight.w600,
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildCategoriesSection() {
    return FutureBuilder<HomeDataModel>(
      future: _homeFuture,
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return const SizedBox(
            height: 42,
            child: Align(
              alignment: AlignmentDirectional.centerStart,
              child: CircularProgressIndicator(strokeWidth: 2),
            ),
          );
        }

        if (snapshot.hasError) {
          return Row(
            children: [
              const Icon(Icons.cloud_off_rounded, color: AppColors.textLight),
              const SizedBox(width: 8),
              Expanded(child: Text(context.tr('no_data_available'))),
              TextButton(
                onPressed: _reloadHome,
                child: Text(context.tr('try_again')),
              ),
            ],
          );
        }

        final categories = snapshot.data?.categories ?? <CategoryModel>[];
        if (categories.isEmpty) {
          return Text(
            context.tr('no_data_available'),
            style: const TextStyle(color: AppColors.textLight),
          );
        }

        return SizedBox(
          height: 104,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            itemCount: categories.length,
            separatorBuilder: (_, _) => const SizedBox(width: 10),
            itemBuilder: (context, index) {
              final category = categories[index];
              final filterValue = category.slug ?? category.name;
              final isSelected = _selectedConcernSlug == filterValue;
              return GestureDetector(
                onTap: () {
                  setState(() {
                    _selectedConcernSlug = isSelected ? null : filterValue;
                  });
                  context.read<StoreBloc>().add(
                    StoreFetchProducts(
                      concern: _selectedConcernSlug,
                      query: _searchQuery,
                    ),
                  );
                },
                child: _CategoryChip(
                  label: category.name,
                  isSelected: isSelected,
                  icon: _categoryIcon(filterValue),
                ),
              );
            },
          ),
        );
      },
    );
  }

  Widget _buildProductsSection() {
    return BlocBuilder<StoreBloc, StoreState>(
      builder: (context, state) {
        if (state is StoreLoading) {
          return const Center(child: CircularProgressIndicator());
        } else if (state is StoreProductsLoaded) {
          if (state.products.isEmpty) {
            return Padding(
              padding: const EdgeInsets.symmetric(vertical: 32),
              child: Center(child: Text(context.tr('no_data_available'))),
            );
          }

          return GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              childAspectRatio: 0.72,
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
            ),
            itemCount: state.products.length,
            itemBuilder: (context, index) {
              final product = state.products[index];
              return _buildProductCard(context, product);
            },
          );
        } else if (state is StoreFailure) {
          return Center(
            child: Padding(
              padding: const EdgeInsets.symmetric(vertical: 24),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(
                    Icons.cloud_off_rounded,
                    size: 48,
                    color: AppColors.textLight,
                  ),
                  const SizedBox(height: 10),
                  Text(state.message, textAlign: TextAlign.center),
                  const SizedBox(height: 12),
                  OutlinedButton(
                    onPressed: () => _fetchProducts(force: true),
                    child: Text(context.tr('try_again')),
                  ),
                ],
              ),
            ),
          );
        }
        return const SizedBox.shrink();
      },
    );
  }

  Widget _buildProductCard(BuildContext context, ProductModel product) {
    return GestureDetector(
      onTap: () => context.push('/product-details/${product.id}'),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(22),
          border: Border.all(color: AppColors.divider, width: 0.6),
          boxShadow: const [
            BoxShadow(color: AppColors.cardShadow, blurRadius: 10),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Container(
                width: double.infinity,
                decoration: const BoxDecoration(
                  color: AppColors.primarySoft,
                  borderRadius: BorderRadius.vertical(top: Radius.circular(22)),
                ),
                child: product.image != null
                    ? CachedNetworkImage(
                        imageUrl: product.image!,
                        fit: BoxFit.contain,
                        memCacheWidth: 360,
                        fadeInDuration: Duration.zero,
                        placeholder: (context, url) => const Center(
                          child: CircularProgressIndicator(strokeWidth: 2),
                        ),
                        errorWidget: (context, url, error) => const Icon(
                          Icons.image_outlined,
                          color: AppColors.textLight,
                          size: 40,
                        ),
                      )
                    : const Icon(
                        Icons.image_outlined,
                        color: AppColors.textLight,
                        size: 40,
                      ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    product.name,
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 14,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),
                  Text(
                    product.unit ?? '',
                    style: const TextStyle(
                      color: AppColors.textLight,
                      fontSize: 11,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        '\$${product.price.toStringAsFixed(2)}',
                        style: const TextStyle(
                          color: AppColors.primary,
                          fontWeight: FontWeight.bold,
                          fontSize: 16,
                        ),
                      ),
                      GestureDetector(
                        onTap: () {
                          context.read<CartBloc>().add(
                            CartItemAdded(product, 1),
                          );
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(context.readTr('added_to_cart')),
                              action: SnackBarAction(
                                label: context.readTr('view_cart'),
                                textColor: AppColors.primaryLight,
                                onPressed: () => context.push('/cart'),
                              ),
                            ),
                          );
                        },
                        child: Container(
                          padding: const EdgeInsets.all(6),
                          decoration: BoxDecoration(
                            color: AppColors.primary,
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(
                            Icons.add_rounded,
                            color: Colors.white,
                            size: 18,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  IconData _categoryIcon(String value) {
    final key = value.toLowerCase();
    if (key.contains('hair')) return Icons.auto_awesome_rounded;
    if (key.contains('sun')) return Icons.wb_sunny_outlined;
    if (key.contains('body') || key.contains('cellulite')) {
      return Icons.accessibility_new_rounded;
    }
    if (key.contains('clean')) return Icons.water_drop_outlined;
    if (key.contains('hormon')) return Icons.balance_rounded;
    if (key.contains('acne')) return Icons.healing_rounded;
    return Icons.spa_outlined;
  }
}

class _CategoryChip extends StatelessWidget {
  final String label;
  final bool isSelected;
  final IconData icon;

  const _CategoryChip({
    required this.label,
    required this.icon,
    this.isSelected = false,
  });

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 82,
      child: Column(
        children: [
          AnimatedContainer(
            duration: const Duration(milliseconds: 160),
            width: 58,
            height: 58,
            decoration: BoxDecoration(
              color: isSelected ? AppColors.primary : Colors.white,
              shape: BoxShape.circle,
              border: Border.all(
                color: isSelected ? AppColors.primary : AppColors.divider,
              ),
            ),
            child: Icon(
              icon,
              color: isSelected ? Colors.white : AppColors.primary,
            ),
          ),
          const SizedBox(height: 7),
          Text(
            label,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            textAlign: TextAlign.center,
            style: TextStyle(
              height: 1.1,
              fontSize: 11,
              fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
              color: AppColors.textPrimary,
            ),
          ),
        ],
      ),
    );
  }
}

class _HomeHeaderAction extends StatelessWidget {
  final IconData icon;
  final VoidCallback onTap;
  final int badge;

  const _HomeHeaderAction({
    required this.icon,
    required this.onTap,
    this.badge = 0,
  });

  @override
  Widget build(BuildContext context) {
    return Stack(
      clipBehavior: Clip.none,
      children: [
        IconButton.filledTonal(
          onPressed: onTap,
          style: IconButton.styleFrom(
            backgroundColor: AppColors.primaryLight,
            foregroundColor: AppColors.primary,
          ),
          icon: Icon(icon, size: 20),
        ),
        if (badge > 0)
          PositionedDirectional(
            top: 0,
            end: 0,
            child: Container(
              constraints: const BoxConstraints(minWidth: 17, minHeight: 17),
              padding: const EdgeInsets.symmetric(horizontal: 4),
              decoration: BoxDecoration(
                color: AppColors.danger,
                borderRadius: BorderRadius.circular(99),
                border: Border.all(color: Colors.white),
              ),
              child: Text(
                badge > 99 ? '99+' : '$badge',
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 9,
                  fontWeight: FontWeight.w800,
                ),
              ),
            ),
          ),
      ],
    );
  }
}
