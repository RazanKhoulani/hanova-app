import 'package:app/injection_container.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/localization/app_localizations.dart';
import '../../../../core/settings/app_settings_cubit.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../../core/utils/currency_formatter.dart';
import '../../../../core/widgets/hanova_ui.dart';
import '../../../auth/presentation/bloc/auth_bloc.dart';
import '../../../auth/presentation/bloc/auth_state.dart';
import '../../data/models/order_model.dart';
import '../../domain/repositories/store_repository.dart';
import '../bloc/cart_bloc.dart';
import '../bloc/store_bloc.dart';

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  final TextEditingController _addressController = TextEditingController();
  final TextEditingController _qadmousGovernorateController =
      TextEditingController();
  final TextEditingController _qadmousBranchController =
      TextEditingController();
  final TextEditingController _recipientNameController =
      TextEditingController();
  final TextEditingController _recipientPhoneController =
      TextEditingController();
  late final Future<List<DeliveryAreaModel>> _deliveryAreasFuture;

  String _selectedPayment = 'cash_on_delivery';
  String _deliveryMethod = 'clinic_pickup';
  int? _deliveryAreaId;
  double _deliveryFee = 0;

  @override
  void initState() {
    super.initState();
    _deliveryAreasFuture = sl<StoreRepository>().getDeliveryAreas();
  }

  @override
  void dispose() {
    _addressController.dispose();
    _qadmousGovernorateController.dispose();
    _qadmousBranchController.dispose();
    _recipientNameController.dispose();
    _recipientPhoneController.dispose();
    super.dispose();
  }

  void _placeOrder(CartState cartState) {
    if (context.read<AuthBloc>().state is! AuthAuthenticated) {
      _showAuthRequiredSheet();
      return;
    }

    if (cartState.items.isEmpty) {
      return;
    }

    if (_deliveryMethod == 'home_delivery') {
      if (_deliveryAreaId == null) {
        _showCheckoutMessage(_checkoutLabel('choose_area'));
        return;
      }

      if (_addressController.text.trim().isEmpty) {
        _showCheckoutMessage(_checkoutLabel('enter_address'));
        return;
      }
    }
    if (_deliveryMethod == 'qadmous' &&
        [
          _qadmousGovernorateController,
          _qadmousBranchController,
          _recipientNameController,
          _recipientPhoneController,
        ].any((controller) => controller.text.trim().isEmpty)) {
      _showCheckoutMessage(_checkoutLabel('qadmous_required'));
      return;
    }

    final orderData = {
      'payment_method': _selectedPayment,
      'delivery_method': _deliveryMethod,
      if (_deliveryMethod == 'home_delivery') ...{
        'delivery_area_id': _deliveryAreaId,
        'shipping_address': _addressController.text.trim(),
      } else if (_deliveryMethod == 'qadmous') ...{
        'qadmous_governorate': _qadmousGovernorateController.text.trim(),
        'qadmous_branch': _qadmousBranchController.text.trim(),
        'recipient_name': _recipientNameController.text.trim(),
        'recipient_phone': _recipientPhoneController.text.trim(),
        'shipping_address':
            '${_qadmousGovernorateController.text.trim()} - ${_qadmousBranchController.text.trim()}',
      } else ...{
        'pickup_location': _deliveryMethod == 'clinic_pickup'
            ? 'clinic'
            : 'pharmacy',
        'shipping_address': _deliveryMethod == 'clinic_pickup'
            ? 'Clinic pickup'
            : 'Pharmacy pickup',
      },
      'items': cartState.items.values
          .map(
            (item) => {
              'product_id': item.product.id,
              'quantity': item.quantity,
              'price': item.product.price,
            },
          )
          .toList(),
    };

    context.read<StoreBloc>().add(StoreCheckout(orderData));
  }

  void _showCheckoutMessage(String message) {
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(message)));
  }

  void _showAuthRequiredSheet() {
    showModalBottomSheet<void>(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) => Padding(
        padding: const EdgeInsets.fromLTRB(20, 20, 20, 30),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              context.tr('login_required'),
              style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 8),
            Text(
              context.tr('login_required_order'),
              style: const TextStyle(color: AppColors.textSecondary),
            ),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: () {
                Navigator.pop(context);
                context.push('/login');
              },
              child: Text(context.tr('login')),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return BlocListener<StoreBloc, StoreState>(
      listener: (context, state) {
        if (state is StoreCheckoutSuccess) {
          context.read<CartBloc>().add(CartCleared());
          context.pushReplacement('/order-confirmation');
        } else if (state is StoreFailure) {
          _showCheckoutMessage(state.message);
        }
      },
      child: Scaffold(
        backgroundColor: AppColors.background,
        appBar: AppBar(title: Text(context.tr('checkout'))),
        body: BlocBuilder<CartBloc, CartState>(
          builder: (context, cartState) {
            if (cartState.items.isEmpty) {
              return HanovaStateView(
                icon: Icons.shopping_bag_outlined,
                title: context.tr('cart_empty'),
                message: context.tr('cart_empty_note'),
                actionLabel: context.tr('start_shopping'),
                onAction: () => context.go('/home?tab=0'),
              );
            }
            return SingleChildScrollView(
              padding: const EdgeInsets.all(24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildSectionHeader(_checkoutLabel('delivery_method')),
                  const SizedBox(height: 16),
                  _buildDeliveryOptions(),
                  const SizedBox(height: 24),
                  _buildDeliveryDetails(),
                  const SizedBox(height: 32),
                  _buildSectionHeader(context.tr('payment_method')),
                  const SizedBox(height: 16),
                  _buildPaymentOptions(),
                  const SizedBox(height: 12),
                  _buildPaymentInfo(),
                  const SizedBox(height: 32),
                  _buildSectionHeader(context.tr('order_summary')),
                  const SizedBox(height: 16),
                  _buildOrderSummary(cartState),
                  const SizedBox(height: 40),
                  BlocBuilder<StoreBloc, StoreState>(
                    builder: (context, storeState) {
                      return ElevatedButton(
                        onPressed: storeState is StoreLoading
                            ? null
                            : () => _placeOrder(cartState),
                        child: storeState is StoreLoading
                            ? const SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(
                                  color: Colors.white,
                                  strokeWidth: 2,
                                ),
                              )
                            : Text(context.tr('place_order')),
                      );
                    },
                  ),
                ],
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return HanovaSectionHeader(title: title);
  }

  Widget _buildDeliveryOptions() {
    final options = [
      {
        'label': _checkoutLabel('clinic_pickup'),
        'value': 'clinic_pickup',
        'icon': Icons.local_hospital_rounded,
        'subtitle': _checkoutLabel('free_pickup'),
      },
      {
        'label': _checkoutLabel('pharmacy_pickup'),
        'value': 'pharmacy_pickup',
        'icon': Icons.local_pharmacy_rounded,
        'subtitle': _checkoutLabel('free_pickup'),
      },
      {
        'label': _checkoutLabel('home_delivery'),
        'value': 'home_delivery',
        'icon': Icons.delivery_dining_rounded,
        'subtitle': _checkoutLabel('area_fee'),
      },
      {
        'label': _checkoutLabel('qadmous_shipping'),
        'value': 'qadmous',
        'icon': Icons.local_shipping_rounded,
        'subtitle': _checkoutLabel('qadmous_note'),
      },
    ];

    return Column(
      children: options.map((opt) {
        final value = opt['value'] as String;
        final isSelected = _deliveryMethod == value;

        return HanovaSurface(
          margin: const EdgeInsets.only(bottom: 12),
          padding: EdgeInsets.zero,
          borderColor: isSelected ? AppColors.primary : AppColors.divider,
          child: Material(
            color: Colors.transparent,
            borderRadius: BorderRadius.circular(HanovaRadii.card),
            child: ListTile(
              leading: Icon(
                opt['icon'] as IconData,
                color: isSelected ? AppColors.primary : AppColors.textSecondary,
              ),
              title: Text(
                opt['label'] as String,
                style: TextStyle(
                  fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
                ),
              ),
              subtitle: Text(
                opt['subtitle'] as String,
                style: const TextStyle(
                  color: AppColors.textLight,
                  fontSize: 12,
                ),
              ),
              trailing: Icon(
                isSelected
                    ? Icons.radio_button_checked_rounded
                    : Icons.radio_button_off_rounded,
                color: isSelected ? AppColors.primary : AppColors.textLight,
              ),
              onTap: () {
                setState(() {
                  _deliveryMethod = value;
                  if (value != 'home_delivery') {
                    _deliveryAreaId = null;
                    _deliveryFee = 0;
                  }
                });
              },
            ),
          ),
        );
      }).toList(),
    );
  }

  Widget _buildDeliveryDetails() {
    if (_deliveryMethod == 'qadmous') {
      InputDecoration decoration(String label) => InputDecoration(
        labelText: label,
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
      );
      return Column(
        children: [
          TextField(
            controller: _qadmousGovernorateController,
            decoration: decoration(_checkoutLabel('governorate')),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _qadmousBranchController,
            decoration: decoration(_checkoutLabel('qadmous_branch')),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _recipientNameController,
            decoration: decoration(_checkoutLabel('recipient_name')),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _recipientPhoneController,
            keyboardType: TextInputType.phone,
            decoration: decoration(_checkoutLabel('recipient_phone')),
          ),
          const SizedBox(height: 10),
          _buildInfoCard(
            icon: Icons.info_outline,
            text: _checkoutLabel('qadmous_fee_note'),
          ),
        ],
      );
    }
    if (_deliveryMethod != 'home_delivery') {
      return _buildInfoCard(
        icon: Icons.storefront_rounded,
        text: _deliveryMethod == 'clinic_pickup'
            ? _checkoutLabel('clinic_pickup_note')
            : _checkoutLabel('pharmacy_pickup_note'),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        FutureBuilder<List<DeliveryAreaModel>>(
          future: _deliveryAreasFuture,
          builder: (context, snapshot) {
            final areas = snapshot.data ?? <DeliveryAreaModel>[];

            if (snapshot.connectionState == ConnectionState.waiting) {
              return const Center(
                child: Padding(
                  padding: EdgeInsets.all(16),
                  child: CircularProgressIndicator(),
                ),
              );
            }

            if (areas.isEmpty) {
              return _buildInfoCard(
                icon: Icons.info_outline_rounded,
                text: _checkoutLabel('no_areas'),
              );
            }

            return DropdownButtonFormField<int>(
              initialValue: _deliveryAreaId,
              decoration: InputDecoration(
                labelText: _checkoutLabel('delivery_area'),
                filled: true,
                fillColor: Colors.white,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: const BorderSide(color: AppColors.divider),
                ),
              ),
              items: areas
                  .map(
                    (area) => DropdownMenuItem<int>(
                      value: area.id,
                      child: Text(
                        '${area.name} - ${CurrencyFormatter.display(area.fee, context.watch<AppSettingsCubit>().state)}',
                      ),
                    ),
                  )
                  .toList(),
              onChanged: (value) {
                DeliveryAreaModel? selectedArea;
                for (final area in areas) {
                  if (area.id == value) {
                    selectedArea = area;
                    break;
                  }
                }

                setState(() {
                  _deliveryAreaId = value;
                  _deliveryFee = selectedArea?.fee ?? 0;
                });
              },
            );
          },
        ),
        const SizedBox(height: 12),
        TextField(
          controller: _addressController,
          minLines: 2,
          maxLines: 3,
          decoration: InputDecoration(
            labelText: context.tr('shipping_address'),
            filled: true,
            fillColor: Colors.white,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: AppColors.divider),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildInfoCard({required IconData icon, required String text}) {
    return HanovaSurface(
      padding: const EdgeInsets.all(14),
      child: Row(
        children: [
          Icon(icon, color: AppColors.primary),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              text,
              style: const TextStyle(
                color: AppColors.textPrimary,
                height: 1.35,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentOptions() {
    final options = [
      {
        'label': context.tr('cash_on_delivery'),
        'value': 'cash_on_delivery',
        'icon': Icons.payments_rounded,
        'enabled': true,
        'subtitle': context.tr('pay_after_confirm'),
      },
    ];

    return Column(
      children: options.map((opt) {
        final value = opt['value'] as String;
        final enabled = opt['enabled'] as bool;
        final isSelected = _selectedPayment == value;

        return Opacity(
          opacity: enabled ? 1 : 0.55,
          child: HanovaSurface(
            margin: const EdgeInsets.only(bottom: 12),
            padding: EdgeInsets.zero,
            borderColor: isSelected ? AppColors.primary : AppColors.divider,
            child: Material(
              color: Colors.transparent,
              borderRadius: BorderRadius.circular(HanovaRadii.card),
              child: ListTile(
                leading: Icon(
                  opt['icon'] as IconData,
                  color: isSelected
                      ? AppColors.primary
                      : AppColors.textSecondary,
                ),
                title: Text(
                  opt['label'] as String,
                  style: TextStyle(
                    fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
                  ),
                ),
                subtitle: Text(
                  opt['subtitle'] as String,
                  style: const TextStyle(
                    color: AppColors.textLight,
                    fontSize: 12,
                  ),
                ),
                trailing: enabled
                    ? Icon(
                        isSelected
                            ? Icons.radio_button_checked_rounded
                            : Icons.radio_button_off_rounded,
                        color: isSelected
                            ? AppColors.primary
                            : AppColors.textLight,
                      )
                    : const Icon(
                        Icons.lock_outline_rounded,
                        color: AppColors.textLight,
                      ),
                onTap: enabled
                    ? () => setState(() => _selectedPayment = value)
                    : null,
              ),
            ),
          ),
        );
      }).toList(),
    );
  }

  Widget _buildPaymentInfo() {
    return Container(
      width: double.infinity,
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
              context.tr('payment_info'),
              style: const TextStyle(
                color: AppColors.textPrimary,
                height: 1.35,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildOrderSummary(CartState cartState) {
    final total = cartState.totalAmount + _deliveryFee;

    return HanovaSurface(
      padding: const EdgeInsets.all(20),
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                '${context.tr('items')} (${cartState.items.length})',
                style: const TextStyle(color: AppColors.textSecondary),
              ),
              Text(
                CurrencyFormatter.dual(
                  cartState.totalAmount,
                  cartState.totalUsd,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                context.tr('delivery'),
                style: const TextStyle(color: AppColors.textSecondary),
              ),
              Text(
                CurrencyFormatter.display(
                  _deliveryFee,
                  context.watch<AppSettingsCubit>().state,
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
                  fontWeight: FontWeight.bold,
                  fontSize: 16,
                ),
              ),
              Text(
                CurrencyFormatter.display(
                  total,
                  context.watch<AppSettingsCubit>().state,
                ),
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 18,
                  color: AppColors.primary,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  String _checkoutLabel(String key) {
    final isArabic = Localizations.localeOf(context).languageCode == 'ar';
    final labels = isArabic ? _arabicLabels : _englishLabels;
    return labels[key] ?? key;
  }

  static const _englishLabels = {
    'delivery_method': 'Delivery Method',
    'clinic_pickup': 'Clinic Pickup',
    'pharmacy_pickup': 'Pharmacy Pickup',
    'home_delivery': 'Home Delivery',
    'free_pickup': 'No delivery fee.',
    'area_fee': 'Fee depends on the delivery area.',
    'clinic_pickup_note':
        'You can pick up your order from the clinic when it is ready.',
    'pharmacy_pickup_note':
        'You can pick up your order from the pharmacy when it is ready.',
    'delivery_area': 'Delivery Area',
    'no_areas': 'No delivery areas are available yet.',
    'choose_area': 'Please choose a delivery area.',
    'enter_address': 'Please enter your delivery address.',
    'qadmous_shipping': 'Qadmous Shipping',
    'qadmous_note': 'Ship to the Qadmous branch you choose.',
    'governorate': 'Governorate',
    'qadmous_branch': 'Qadmous branch',
    'recipient_name': 'Recipient name',
    'recipient_phone': 'Recipient phone',
    'qadmous_required': 'Please complete all Qadmous shipping details.',
    'qadmous_fee_note':
        'The clinic will confirm the Qadmous shipping fee and tracking number. No payment receipt is needed for cash on delivery.',
  };

  static const _arabicLabels = {
    'delivery_method': 'طريقة الاستلام',
    'clinic_pickup': 'استلام من العيادة',
    'pharmacy_pickup': 'استلام من الصيدلية',
    'home_delivery': 'توصيل للمنزل',
    'free_pickup': 'بدون رسوم توصيل.',
    'area_fee': 'الرسوم حسب منطقة التوصيل.',
    'clinic_pickup_note': 'يمكنك استلام الطلب من العيادة عندما يصبح جاهزاً.',
    'pharmacy_pickup_note': 'يمكنك استلام الطلب من الصيدلية عندما يصبح جاهزاً.',
    'delivery_area': 'منطقة التوصيل',
    'no_areas': 'لا توجد مناطق توصيل متاحة حالياً.',
    'choose_area': 'يرجى اختيار منطقة التوصيل.',
    'enter_address': 'يرجى كتابة عنوان التوصيل.',
    'qadmous_shipping': 'شحن قدموس',
    'qadmous_note': 'شحن الطلب إلى فرع قدموس الذي تختارينه.',
    'governorate': 'المحافظة',
    'qadmous_branch': 'فرع قدموس',
    'recipient_name': 'اسم المستلم',
    'recipient_phone': 'رقم هاتف المستلم',
    'qadmous_required': 'يرجى تعبئة جميع معلومات شحن قدموس.',
    'qadmous_fee_note':
        'تؤكد العيادة أجور الشحن ورقم التتبع بعد تجهيز الطلب. لا حاجة لرفع إشعار دفع مع الدفع عند الاستلام.',
  };
}
