import 'dart:async';
import 'dart:math' as math;

import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart' show ScrollDirection;
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
  final PageController _bannerController = PageController(
    viewportFraction: 0.95,
  );
  final ScrollController _categoryController = ScrollController();
  final GlobalKey _productsSectionKey = GlobalKey();
  late Future<HomeDataModel> _homeFuture;
  Timer? _bannerTimer;
  String? _searchQuery;
  bool _hasSearchText = false;
  bool _categoryHintScheduled = false;
  bool _categoryHintCancelled = false;
  int _bannerIndex = 0;

  @override
  void initState() {
    super.initState();
    _homeFuture = _requestHomeData();
    _bannerTimer = Timer.periodic(const Duration(seconds: 6), (_) {
      if (!mounted || !_bannerController.hasClients) return;
      final nextPage = (_bannerIndex + 1) % 2;
      _bannerController.animateToPage(
        nextPage,
        duration: const Duration(milliseconds: 420),
        curve: Curves.easeOutCubic,
      );
    });
  }

  @override
  void dispose() {
    _bannerTimer?.cancel();
    _bannerController.dispose();
    _categoryController.dispose();
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
    final query = value.trim();
    setState(() {
      _hasSearchText = value.isNotEmpty;
      _searchQuery = query.isEmpty ? null : query;
    });
    _fetchProducts();
  }

  void _clearFilters() {
    setState(() {
      _selectedConcernSlug = null;
      _searchQuery = null;
      _hasSearchText = false;
      _searchController.clear();
    });
    _fetchProducts();
  }

  void _reloadHome() {
    setState(() {
      _homeFuture = _requestHomeData(force: true);
    });
  }

  void _scrollToProducts() {
    if (_selectedConcernSlug != null || _searchQuery != null) {
      _clearFilters();
    }

    WidgetsBinding.instance.addPostFrameCallback((_) {
      final productsContext = _productsSectionKey.currentContext;
      if (productsContext == null) return;
      Scrollable.ensureVisible(
        productsContext,
        duration: const Duration(milliseconds: 480),
        curve: Curves.easeOutCubic,
        alignment: 0.08,
      );
    });
  }

  void _scheduleCategoryScrollHint(int categoryCount) {
    if (_categoryHintScheduled || categoryCount < 5) return;
    _categoryHintScheduled = true;

    WidgetsBinding.instance.addPostFrameCallback((_) async {
      await Future<void>.delayed(const Duration(milliseconds: 650));
      if (!mounted ||
          _categoryHintCancelled ||
          !_categoryController.hasClients) {
        return;
      }

      final maxExtent = _categoryController.position.maxScrollExtent;
      if (maxExtent <= 0) return;
      final hintOffset = math.min(54.0, maxExtent);
      await _categoryController.animateTo(
        hintOffset,
        duration: const Duration(milliseconds: 520),
        curve: Curves.easeOutCubic,
      );
      await Future<void>.delayed(const Duration(milliseconds: 260));
      if (!mounted || _categoryHintCancelled) return;
      await _categoryController.animateTo(
        0,
        duration: const Duration(milliseconds: 620),
        curve: Curves.easeInOutCubic,
      );
    });
  }

  Future<void> _refreshHome() async {
    final request = _requestHomeData(force: true);
    setState(() {
      _homeFuture = request;
    });

    try {
      await request;
    } catch (_) {
      // The page already renders a retry state with the server error.
    }
  }

  Future<HomeDataModel> _requestHomeData({bool force = false}) async {
    try {
      final data = await sl<StoreRepository>().getHomeData(force: force);
      if (mounted) {
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
          _homeFuture = _requestHomeData(force: true);
        });
      },
      child: Scaffold(
        backgroundColor: AppColors.background,
        body: RefreshIndicator(
          onRefresh: _refreshHome,
          color: AppColors.primary,
          child: CustomScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            slivers: [
              _buildAppBar(context),
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildBanner(),
                      const SizedBox(height: 14),
                      _buildSectionHeader(
                        context.tr('categories'),
                        _clearFilters,
                      ),
                      const SizedBox(height: 8),
                      _buildCategoriesSection(),
                      const SizedBox(height: 18),
                      _buildSectionHeader(
                        context.tr('top_products'),
                        _clearFilters,
                      ),
                      const SizedBox(height: 16),
                      Container(
                        key: _productsSectionKey,
                        child: _buildProductsSection(),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
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
                  suffixIcon: !_hasSearchText
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
            final slides = [
              _BannerSlideData(
                badge: offer?.discountLabel ?? 'HANOVA',
                title: offer?.title ?? context.tr('shop_banner_title'),
                description: offer?.description?.isNotEmpty == true
                    ? offer!.description!
                    : context.tr('shop_banner_copy'),
                actionLabel: context.tr('order_now'),
                icon: offer == null
                    ? Icons.shopping_bag_rounded
                    : Icons.local_offer_rounded,
                colors: const [AppColors.primary, Color(0xFFE08FA5)],
                onAction: _scrollToProducts,
              ),
              _BannerSlideData(
                title: context.tr('skin_consultation'),
                description: context.tr('clinic_banner_copy'),
                actionLabel: isAuthenticated
                    ? context.tr('book_now')
                    : context.tr('login_to_book'),
                icon: Icons.calendar_month_rounded,
                colors: const [Color(0xFFA24A63), Color(0xFFC76C83)],
                onAction: () =>
                    context.push(isAuthenticated ? '/appointment' : '/login'),
              ),
            ];

            return Column(
              children: [
                SizedBox(
                  height: 188,
                  child: PageView.builder(
                    controller: _bannerController,
                    itemCount: slides.length,
                    onPageChanged: (index) {
                      if (mounted) setState(() => _bannerIndex = index);
                    },
                    itemBuilder: (context, index) {
                      return Padding(
                        padding: const EdgeInsetsDirectional.only(end: 10),
                        child: _HomeBannerCard(data: slides[index]),
                      );
                    },
                  ),
                ),
                const SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: List.generate(slides.length, (index) {
                    final selected = index == _bannerIndex;
                    return AnimatedContainer(
                      duration: const Duration(milliseconds: 220),
                      width: selected ? 22 : 7,
                      height: 7,
                      margin: const EdgeInsets.symmetric(horizontal: 3),
                      decoration: BoxDecoration(
                        color: selected
                            ? AppColors.primary
                            : AppColors.primaryMist,
                        borderRadius: BorderRadius.circular(99),
                      ),
                    );
                  }),
                ),
              ],
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
          style: TextButton.styleFrom(
            minimumSize: const Size(0, 32),
            padding: const EdgeInsets.symmetric(horizontal: 4),
            tapTargetSize: MaterialTapTargetSize.shrinkWrap,
          ),
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

        _scheduleCategoryScrollHint(categories.length);

        return SizedBox(
          height: 92,
          child: Stack(
            children: [
              NotificationListener<UserScrollNotification>(
                onNotification: (notification) {
                  if (notification.direction != ScrollDirection.idle) {
                    _categoryHintCancelled = true;
                  }
                  return false;
                },
                child: ListView.separated(
                  controller: _categoryController,
                  scrollDirection: Axis.horizontal,
                  physics: const BouncingScrollPhysics(),
                  padding: const EdgeInsetsDirectional.only(end: 26),
                  itemCount: categories.length,
                  separatorBuilder: (_, _) => const SizedBox(width: 6),
                  itemBuilder: (context, index) {
                    final category = categories[index];
                    final filterValue = category.slug ?? category.name;
                    final isSelected = _selectedConcernSlug == filterValue;
                    return GestureDetector(
                      key: ValueKey(category.id),
                      onTap: () {
                        setState(() {
                          _selectedConcernSlug = isSelected
                              ? null
                              : filterValue;
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
              ),
              if (categories.length >= 5)
                PositionedDirectional(
                  end: 0,
                  top: 10,
                  child: IgnorePointer(
                    child: Container(
                      width: 30,
                      height: 52,
                      alignment: AlignmentDirectional.centerEnd,
                      decoration: const BoxDecoration(
                        gradient: LinearGradient(
                          begin: AlignmentDirectional.centerStart,
                          end: AlignmentDirectional.centerEnd,
                          colors: [Colors.transparent, AppColors.background],
                        ),
                      ),
                      child: Icon(
                        Directionality.of(context) == TextDirection.rtl
                            ? Icons.chevron_left_rounded
                            : Icons.chevron_right_rounded,
                        size: 22,
                        color: AppColors.primary,
                      ),
                    ),
                  ),
                ),
            ],
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
                          final messenger = ScaffoldMessenger.of(context);
                          messenger.hideCurrentSnackBar();
                          messenger.showSnackBar(
                            SnackBar(
                              duration: const Duration(seconds: 2),
                              content: Text(context.readTr('added_to_cart')),
                              action: SnackBarAction(
                                label: context.readTr('view_cart'),
                                textColor: AppColors.primaryLight,
                                onPressed: () {
                                  messenger.hideCurrentSnackBar();
                                  context.push('/cart');
                                },
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

class _BannerSlideData {
  final String? badge;
  final String title;
  final String description;
  final String actionLabel;
  final IconData icon;
  final List<Color> colors;
  final VoidCallback onAction;

  const _BannerSlideData({
    this.badge,
    required this.title,
    required this.description,
    required this.actionLabel,
    required this.icon,
    required this.colors,
    required this.onAction,
  });
}

class _HomeBannerCard extends StatelessWidget {
  final _BannerSlideData data;

  const _HomeBannerCard({required this.data});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: data.colors,
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(28),
        boxShadow: const [
          BoxShadow(
            color: Color(0x1FA24A63),
            blurRadius: 18,
            offset: Offset(0, 9),
          ),
        ],
      ),
      child: Stack(
        children: [
          PositionedDirectional(
            end: -18,
            bottom: -18,
            child: Icon(
              data.icon,
              size: 108,
              color: Colors.white.withValues(alpha: 0.14),
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(19),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (data.badge != null)
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
                      data.badge!,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ),
                const Spacer(),
                Text(
                  data.title,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 21,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 5),
                Text(
                  data.description,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.76),
                    fontSize: 12,
                    height: 1.45,
                  ),
                ),
                const SizedBox(height: 13),
                SizedBox(
                  height: 36,
                  child: ElevatedButton.icon(
                    onPressed: data.onAction,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.white,
                      foregroundColor: AppColors.primaryDark,
                      minimumSize: const Size(112, 36),
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      elevation: 0,
                    ),
                    icon: Icon(data.icon, size: 17),
                    label: Text(
                      data.actionLabel,
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
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
      width: 74,
      child: Column(
        children: [
          AnimatedScale(
            scale: isSelected ? 1.06 : 1,
            duration: const Duration(milliseconds: 240),
            curve: Curves.easeOutBack,
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 240),
              curve: Curves.easeOutCubic,
              width: 52,
              height: 52,
              decoration: BoxDecoration(
                color: isSelected ? AppColors.primary : Colors.white,
                shape: BoxShape.circle,
                border: Border.all(
                  color: isSelected ? AppColors.primary : AppColors.divider,
                ),
                boxShadow: isSelected
                    ? const [
                        BoxShadow(
                          color: Color(0x2EC76C83),
                          blurRadius: 12,
                          offset: Offset(0, 5),
                        ),
                      ]
                    : null,
              ),
              child: Icon(
                icon,
                size: 22,
                color: isSelected ? Colors.white : AppColors.primary,
              ),
            ),
          ),
          const SizedBox(height: 6),
          AnimatedDefaultTextStyle(
            duration: const Duration(milliseconds: 180),
            style: TextStyle(
              height: 1.1,
              fontSize: 10.5,
              fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
              color: isSelected ? AppColors.primaryDark : AppColors.textPrimary,
            ),
            child: Text(
              label,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              textAlign: TextAlign.center,
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
