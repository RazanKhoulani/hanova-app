import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import 'package:go_router/go_router.dart';
import 'package:app/injection_container.dart';
import '../../../../core/localization/app_localizations.dart';
import '../../../../core/settings/app_settings_cubit.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../../core/utils/currency_formatter.dart';
import '../../../../core/widgets/hanova_ui.dart';
import '../../../auth/presentation/bloc/auth_bloc.dart';
import '../../../auth/presentation/bloc/auth_state.dart';
import '../cubit/orders_cubit.dart';
import '../../data/models/order_model.dart';

class MyOrdersScreen extends StatefulWidget {
  final bool showAppBar;
  final bool autoFetch;

  const MyOrdersScreen({
    super.key,
    this.showAppBar = true,
    this.autoFetch = true,
  });

  @override
  State<MyOrdersScreen> createState() => _MyOrdersScreenState();
}

class _MyOrdersScreenState extends State<MyOrdersScreen> {
  late final OrdersCubit _ordersCubit;
  bool _requestedOrders = false;

  @override
  void initState() {
    super.initState();
    _ordersCubit = sl<OrdersCubit>();
    WidgetsBinding.instance.addPostFrameCallback(
      (_) => _fetchOrdersIfAllowed(),
    );
  }

  @override
  void didUpdateWidget(covariant MyOrdersScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (!widget.autoFetch) {
      _requestedOrders = false;
      return;
    }

    if (widget.autoFetch && !oldWidget.autoFetch) {
      WidgetsBinding.instance.addPostFrameCallback(
        (_) => _fetchOrdersIfAllowed(force: true),
      );
    }
  }

  @override
  void dispose() {
    _ordersCubit.close();
    super.dispose();
  }

  void _fetchOrdersIfAllowed({bool force = false}) {
    if (!mounted || !widget.autoFetch) return;
    if (!force && _requestedOrders) return;
    if (context.read<AuthBloc>().state is AuthAuthenticated) {
      _requestedOrders = true;
      _ordersCubit.loadOrders();
    }
  }

  @override
  Widget build(BuildContext context) {
    return BlocProvider.value(
      value: _ordersCubit,
      child: BlocListener<AuthBloc, AuthState>(
        listener: (context, authState) {
          if (authState is AuthAuthenticated && widget.autoFetch) {
            WidgetsBinding.instance.addPostFrameCallback(
              (_) => _fetchOrdersIfAllowed(force: true),
            );
            return;
          }

          if (authState is! AuthAuthenticated) {
            _requestedOrders = false;
          }
        },
        child: Scaffold(
          backgroundColor: AppColors.background,
          appBar: widget.showAppBar
              ? AppBar(title: Text(context.tr('my_orders')))
              : null,
          body: BlocBuilder<AuthBloc, AuthState>(
            builder: (context, authState) {
              if (authState is AuthLoading || authState is AuthInitial) {
                return const HanovaLoadingView();
              }

              if (authState is! AuthAuthenticated) {
                return _buildAuthRequired(context);
              }

              return BlocBuilder<OrdersCubit, OrdersState>(
                builder: (context, state) {
                  final isDeliveryUser = authState.user.role == 'delivery';

                  if (state.isLoading && state.orders.isEmpty) {
                    return const HanovaLoadingView();
                  }

                  if (state.errorMessage != null && state.orders.isEmpty) {
                    return _buildFailureState(context, state.errorMessage!);
                  }

                  if (state.orders.isEmpty) {
                    return _buildEmptyState(context);
                  }

                  return ListView.builder(
                    padding: const EdgeInsets.all(20),
                    itemCount: state.orders.length,
                    itemBuilder: (context, index) {
                      final order = state.orders[index];
                      return _buildOrderCard(
                        order,
                        isDeliveryUser: isDeliveryUser,
                        isUpdating: state.isLoading,
                      );
                    },
                  );
                },
              );
            },
          ),
        ),
      ),
    );
  }

  Widget _buildAuthRequired(BuildContext context) {
    return HanovaStateView(
      icon: Icons.lock_outline_rounded,
      title: context.tr('login_required'),
      message: context.tr('login_required_order'),
      actionLabel: context.tr('login'),
      onAction: () => context.push('/login'),
    );
  }

  Widget _buildEmptyState(BuildContext context) {
    return HanovaStateView(
      icon: Icons.receipt_long_outlined,
      title: context.tr('no_orders'),
      message: context.tr('no_orders_note'),
      actionLabel: context.tr('browse_products'),
      onAction: () => context.go('/home?tab=0'),
    );
  }

  Widget _buildFailureState(BuildContext context, String message) {
    return HanovaStateView(
      icon: Icons.error_outline_rounded,
      iconColor: AppColors.danger,
      title: context.tr('something_went_wrong'),
      message: message,
      actionLabel: context.tr('try_again'),
      onAction: () => _fetchOrdersIfAllowed(force: true),
    );
  }

  Widget _buildOrderCard(
    OrderModel order, {
    required bool isDeliveryUser,
    required bool isUpdating,
  }) {
    final statusLabel = _orderStatusLabel(order);

    return HanovaSurface(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                _orderTitle(order.orderNumber),
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 16,
                ),
              ),
              _buildStatusBadge(order.status, statusLabel),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            _formatDate(order.createdAt),
            style: const TextStyle(color: AppColors.textLight, fontSize: 12),
          ),
          if (![
            'cancelled',
            'canceled',
          ].contains(order.status.toLowerCase())) ...[
            const SizedBox(height: 14),
            _buildOrderProgress(order.status),
          ],
          const Divider(height: 24),
          ...order.items.map(
            (item) => Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('${item.quantity}x ${item.productName}'),
                  Text(
                    CurrencyFormatter.dual(
                      item.price * item.quantity,
                      item.priceUsd == null
                          ? null
                          : item.priceUsd! * item.quantity,
                    ),
                  ),
                ],
              ),
            ),
          ),
          const Divider(height: 24),
          if (order.deliveryFee > 0 || order.discountAmount > 0) ...[
            if (order.discountAmount > 0)
              _buildAmountLine(
                _isArabic ? 'الحسم' : 'Discount',
                '-${CurrencyFormatter.display(order.discountAmount, context.watch<AppSettingsCubit>().state)}',
                valueColor: AppColors.success,
              ),
            if (order.deliveryFee > 0)
              _buildAmountLine(
                context.tr('delivery_fee'),
                CurrencyFormatter.display(
                  order.deliveryFee,
                  context.watch<AppSettingsCubit>().state,
                ),
              ),
            const Divider(height: 24),
          ],
          if (order.deliveryMethod != null || order.paymentStatus != null) ...[
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                if (order.deliveryMethod != null)
                  _InfoChip(label: _deliveryMethodLabel(order.deliveryMethod!)),
                if (order.paymentStatus != null)
                  _InfoChip(label: _paymentStatusLabel(order.paymentStatus!)),
              ],
            ),
            const Divider(height: 24),
          ],
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                context.tr('total_amount'),
                style: const TextStyle(fontWeight: FontWeight.bold),
              ),
              Text(
                CurrencyFormatter.display(
                  order.totalAmount,
                  context.watch<AppSettingsCubit>().state,
                ),
                style: const TextStyle(
                  color: AppColors.primary,
                  fontWeight: FontWeight.bold,
                  fontSize: 18,
                ),
              ),
            ],
          ),
          if (isDeliveryUser && order.status.toLowerCase() != 'delivered') ...[
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: isUpdating
                    ? null
                    : () => _ordersCubit.markDelivered(order.id),
                icon: const Icon(Icons.check_circle_outline_rounded),
                label: Text(_deliveryActionLabel),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildAmountLine(String label, String value, {Color? valueColor}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(color: AppColors.textSecondary)),
          Text(
            value,
            style: TextStyle(
              color: valueColor ?? AppColors.textPrimary,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildOrderProgress(String status) {
    const stages = ['pending', 'accepted', 'ready', 'delivered'];
    final normalized = status.toLowerCase();
    final aliases = {
      'processing': 'accepted',
      'paid': 'accepted',
      'shipped': 'ready',
      'completed': 'delivered',
    };
    final current = stages.indexOf(aliases[normalized] ?? normalized);

    return Row(
      children: List.generate(stages.length * 2 - 1, (index) {
        if (index.isOdd) {
          final completed = index ~/ 2 < current;
          return Expanded(
            child: Container(
              height: 2,
              color: completed ? AppColors.primary : AppColors.divider,
            ),
          );
        }

        final stageIndex = index ~/ 2;
        final completed = stageIndex <= current;
        return Tooltip(
          message: _progressStageLabel(stages[stageIndex]),
          child: Container(
            width: 12,
            height: 12,
            decoration: BoxDecoration(
              color: completed ? AppColors.primary : AppColors.surfaceMuted,
              shape: BoxShape.circle,
              border: Border.all(
                color: completed ? AppColors.primary : AppColors.divider,
                width: 2,
              ),
            ),
          ),
        );
      }),
    );
  }

  String _progressStageLabel(String stage) {
    if (!_isArabic) {
      return switch (stage) {
        'pending' => 'Received',
        'accepted' => 'Accepted',
        'ready' => 'Ready',
        _ => 'Delivered',
      };
    }
    return switch (stage) {
      'pending' => 'تم استلام الطلب',
      'accepted' => 'تم قبول الطلب',
      'ready' => 'الطلب جاهز',
      _ => 'تم التسليم',
    };
  }

  Widget _buildStatusBadge(String status, String label) {
    Color color;
    switch (status.toLowerCase()) {
      case 'completed':
      case 'accepted':
      case 'ready':
      case 'delivered':
        color = AppColors.success;
        break;
      case 'pending':
      case 'processing':
        color = Colors.orange;
        break;
      case 'cancelled':
      case 'canceled':
        color = AppColors.danger;
        break;
      default:
        color = AppColors.primary;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: color,
          fontSize: 12,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }

  String _orderTitle(String orderNumber) {
    if (!_isArabic) return 'Order #$orderNumber';
    return '\u0637\u0644\u0628 #$orderNumber';
  }

  String _formatDate(DateTime value) {
    if (!_isArabic) {
      return DateFormat('MMM dd, yyyy').format(value);
    }

    return '${value.day} ${_arabicMonths[value.month - 1]} ${value.year}';
  }

  String _orderStatusLabel(OrderModel order) {
    final locale = _isArabic ? 'ar' : 'en';
    final normalized = order.status.toLowerCase().trim();
    final translated = _statusTranslations[locale]?[normalized];
    if (translated != null) return translated;

    final apiLabel = order.statusLabel.trim();
    return apiLabel.isNotEmpty ? apiLabel : order.status;
  }

  bool get _isArabic => Localizations.localeOf(context).languageCode == 'ar';

  String get _deliveryActionLabel {
    return _isArabic ? 'تم التسليم' : 'Mark Delivered';
  }

  String _deliveryMethodLabel(String value) {
    final normalized = value.toLowerCase();
    if (!_isArabic) {
      return normalized.replaceAll('_', ' ');
    }

    return switch (normalized) {
      'clinic_pickup' => 'استلام من العيادة',
      'pharmacy_pickup' => 'استلام من الصيدلية',
      'home_delivery' => 'توصيل للبيت',
      _ => value,
    };
  }

  String _paymentStatusLabel(String value) {
    final normalized = value.toLowerCase();
    if (!_isArabic) {
      return normalized.replaceAll('_', ' ');
    }

    return switch (normalized) {
      'paid' => 'مدفوع',
      'unpaid' => 'غير مدفوع',
      'pending' => 'قيد التحقق',
      _ => value,
    };
  }

  static const _arabicMonths = [
    '\u064a\u0646\u0627\u064a\u0631',
    '\u0641\u0628\u0631\u0627\u064a\u0631',
    '\u0645\u0627\u0631\u0633',
    '\u0623\u0628\u0631\u064a\u0644',
    '\u0645\u0627\u064a\u0648',
    '\u064a\u0648\u0646\u064a\u0648',
    '\u064a\u0648\u0644\u064a\u0648',
    '\u0623\u063a\u0633\u0637\u0633',
    '\u0633\u0628\u062a\u0645\u0628\u0631',
    '\u0623\u0643\u062a\u0648\u0628\u0631',
    '\u0646\u0648\u0641\u0645\u0628\u0631',
    '\u062f\u064a\u0633\u0645\u0628\u0631',
  ];

  static const _statusTranslations = {
    'en': {
      'pending': 'Pending',
      'accepted': 'Accepted',
      'confirmed': 'Confirmed',
      'processing': 'Processing',
      'ready': 'Ready',
      'completed': 'Completed',
      'delivered': 'Delivered',
      'cancelled': 'Cancelled',
      'canceled': 'Cancelled',
    },
    'ar': {
      'pending':
          '\u0642\u064a\u062f \u0627\u0644\u0627\u0646\u062a\u0638\u0627\u0631',
      'accepted': '\u0645\u0642\u0628\u0648\u0644',
      'confirmed': '\u0645\u0624\u0643\u062f',
      'processing':
          '\u0642\u064a\u062f \u0627\u0644\u062a\u062c\u0647\u064a\u0632',
      'ready': '\u062c\u0627\u0647\u0632',
      'completed': '\u0645\u0643\u062a\u0645\u0644',
      'delivered': '\u062a\u0645 \u0627\u0644\u062a\u0633\u0644\u064a\u0645',
      'cancelled': '\u0645\u0644\u063a\u064a',
      'canceled': '\u0645\u0644\u063a\u064a',
    },
  };
}

class _InfoChip extends StatelessWidget {
  final String label;

  const _InfoChip({required this.label});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: AppColors.primaryLight,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        label,
        style: const TextStyle(
          color: AppColors.primary,
          fontSize: 11,
          fontWeight: FontWeight.w700,
        ),
      ),
    );
  }
}
