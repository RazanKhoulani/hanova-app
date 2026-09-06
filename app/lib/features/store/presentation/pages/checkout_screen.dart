import 'package:app/injection_container.dart';
import 'dart:math';

import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:file_picker/file_picker.dart';
import 'package:dio/dio.dart';

import '../../../../core/localization/app_localizations.dart';
import '../../../../core/network/dio_client.dart';
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
  late final String _idempotencyKey = _newIdempotencyKey();
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
  late final Future<List<Map<String, dynamic>>> _qadmousLocationsFuture;

  String _selectedPayment = 'cash_on_delivery';
  String _deliveryMethod = 'clinic_pickup';
  int? _deliveryAreaId;
  int? _qadmousLocationId;
  double _deliveryFee = 0;
  double? _deliveryFeeUsd = 0;
  double? _shippingLatitude;
  double? _shippingLongitude;
  String? _paymentReceiptPath;
  bool _searchingAddress = false;

  Future<void> _searchAddress() async {
    final query = _addressController.text.trim();
    if (query.isEmpty) return;
    setState(() => _searchingAddress = true);
    try {
      final response = await Dio().get(
        'https://nominatim.openstreetmap.org/search',
        queryParameters: {
          'q': '$query, Syria',
          'format': 'jsonv2',
          'limit': 5,
          'accept-language': Localizations.localeOf(context).languageCode,
        },
        options: Options(headers: {'User-Agent': 'HanovaMobile/1.0'}),
      );
      final results = response.data is List ? response.data as List : const [];
      if (!mounted) return;
      final selected = await showModalBottomSheet<Map>(
        context: context,
        builder: (context) => SafeArea(
          child: ListView(
            shrinkWrap: true,
            children: [
              ListTile(
                title: Text(
                  context.tr('select_address'),
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
              ),
              ...results.map(
                (item) => ListTile(
                  leading: const Icon(
                    Icons.location_on_outlined,
                    color: AppColors.primary,
                  ),
                  title: Text(item['display_name']?.toString() ?? ''),
                  onTap: () => Navigator.pop(context, item),
                ),
              ),
            ],
          ),
        ),
      );
      if (selected != null) {
        setState(() {
          _addressController.text = selected['display_name'].toString();
          _shippingLatitude = double.tryParse(selected['lat'].toString());
          _shippingLongitude = double.tryParse(selected['lon'].toString());
        });
      }
    } catch (_) {
      if (mounted) _showCheckoutMessage(context.tr('address_search_failed'));
    } finally {
      if (mounted) setState(() => _searchingAddress = false);
    }
  }

  Future<void> _pickPaymentReceipt() async {
    final result = await FilePicker.platform.pickFiles(type: FileType.image);
    if (result?.files.single.path != null && mounted) {
      setState(() => _paymentReceiptPath = result!.files.single.path);
    }
  }

  @override
  void initState() {
    super.initState();
    _deliveryAreasFuture = sl<StoreRepository>().getDeliveryAreas();
    _qadmousLocationsFuture = _loadQadmousLocations();
  }

  Future<List<Map<String, dynamic>>> _loadQadmousLocations() async {
    final response = await sl<DioClient>().get('/qadmous-locations');
    final list = response.data['data'] as List? ?? [];
    return list.map((item) => Map<String, dynamic>.from(item as Map)).toList();
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
        _showCheckoutMessage(context.tr('choose_area'));
        return;
      }

      if (_addressController.text.trim().isEmpty) {
        _showCheckoutMessage(context.tr('enter_address'));
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
      _showCheckoutMessage(context.tr('qadmous_required'));
      return;
    }
    if (_selectedPayment == 'online' && _paymentReceiptPath == null) {
      _showCheckoutMessage(context.tr('payment_receipt_required'));
      return;
    }

    final orderData = {
      'payment_method': _selectedPayment,
      'delivery_method': _deliveryMethod,
      'idempotency_key': _idempotencyKey,
      if (_selectedPayment == 'online')
        'payment_receipt_path': _paymentReceiptPath,
      if (_deliveryMethod == 'home_delivery') ...{
        'delivery_area_id': _deliveryAreaId,
        'shipping_address': _addressController.text.trim(),
        if (_shippingLatitude != null) 'shipping_latitude': _shippingLatitude,
        if (_shippingLongitude != null)
          'shipping_longitude': _shippingLongitude,
      } else if (_deliveryMethod == 'qadmous') ...{
        'qadmous_location_id': _qadmousLocationId,
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

  String _newIdempotencyKey() {
    final random = Random.secure();
    final entropy = List.generate(
      20,
      (_) => random.nextInt(256).toRadixString(16).padLeft(2, '0'),
    ).join();
    return '${DateTime.now().microsecondsSinceEpoch}-$entropy';
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
                  _buildSectionHeader(context.tr('delivery_method')),
                  const SizedBox(height: 16),
                  _buildDeliveryOptions(),
                  const SizedBox(height: 24),
                  _buildDeliveryDetails(),
                  const SizedBox(height: 32),
                  _buildSectionHeader(context.tr('payment_method')),
                  const SizedBox(height: 16),
                  _buildPaymentOptions(),
                  if (_selectedPayment == 'online') ...[
                    const SizedBox(height: 4),
                    _buildPaymentReceiptPicker(),
                  ],
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
        'label': context.tr('clinic_pickup'),
        'value': 'clinic_pickup',
        'icon': Icons.local_hospital_rounded,
        'subtitle': context.tr('free_pickup'),
      },
      {
        'label': context.tr('pharmacy_pickup'),
        'value': 'pharmacy_pickup',
        'icon': Icons.local_pharmacy_rounded,
        'subtitle': context.tr('free_pickup'),
      },
      {
        'label': context.tr('home_delivery'),
        'value': 'home_delivery',
        'icon': Icons.delivery_dining_rounded,
        'subtitle': context.tr('area_fee'),
      },
      {
        'label': context.tr('qadmous_shipping'),
        'value': 'qadmous',
        'icon': Icons.local_shipping_rounded,
        'subtitle': context.tr('qadmous_note'),
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
                    _deliveryFeeUsd = 0;
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
          FutureBuilder<List<Map<String, dynamic>>>(
            future: _qadmousLocationsFuture,
            builder: (context, snapshot) {
              final locations = snapshot.data ?? const <Map<String, dynamic>>[];
              if (snapshot.connectionState == ConnectionState.waiting) {
                return const Center(child: CircularProgressIndicator());
              }
              if (locations.isEmpty) {
                return _buildInfoCard(
                  icon: Icons.info_outline,
                  text: context.tr('no_qadmous_branches'),
                );
              }
              return DropdownButtonFormField<int>(
                decoration: decoration(context.tr('qadmous_location')),
                items: locations
                    .map(
                      (location) => DropdownMenuItem<int>(
                        value: location['id'] as int,
                        child: Text(
                          '${location['governorate']} - ${location['branch']}',
                        ),
                      ),
                    )
                    .toList(),
                onChanged: (id) {
                  final selected = locations.firstWhere(
                    (item) => item['id'] == id,
                  );
                  _qadmousGovernorateController.text = selected['governorate']
                      .toString();
                  _qadmousBranchController.text = selected['branch'].toString();
                  setState(() => _qadmousLocationId = id);
                },
              );
            },
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _recipientNameController,
            decoration: decoration(context.tr('recipient_name')),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _recipientPhoneController,
            keyboardType: TextInputType.phone,
            decoration: decoration(context.tr('recipient_phone')),
          ),
          _buildInfoCard(
            icon: Icons.info_outline,
            text: context.tr('qadmous_fee_note'),
          ),
        ],
      );
    }
    if (_deliveryMethod != 'home_delivery') {
      return _buildInfoCard(
        icon: Icons.storefront_rounded,
        text: _deliveryMethod == 'clinic_pickup'
            ? context.tr('clinic_pickup_note')
            : context.tr('pharmacy_pickup_note'),
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
                text: context.tr('no_areas'),
              );
            }

            return DropdownButtonFormField<int>(
              initialValue: _deliveryAreaId,
              decoration: InputDecoration(
                labelText: context.tr('delivery_area'),
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
                        '${area.name} - ${CurrencyFormatter.dual(area.fee, area.feeUsd, languageCode: Localizations.localeOf(context).languageCode)}',
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
                  _deliveryFeeUsd = selectedArea?.feeUsd;
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
        const SizedBox(height: 10),
        SizedBox(
          width: double.infinity,
          child: OutlinedButton.icon(
            onPressed: _searchingAddress ? null : _searchAddress,
            icon: _searchingAddress
                ? const SizedBox(
                    width: 18,
                    height: 18,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Icon(Icons.map_outlined),
            label: Text(
              _shippingLatitude == null
                  ? context.tr('locate_address')
                  : context.tr('change_location'),
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
    final options = <Map<String, dynamic>>[
      {
        'label': context.tr('online_payment'),
        'value': 'online',
        'icon': Icons.credit_card_rounded,
        'enabled': true,
        'subtitle': context.tr('online_payment_note'),
      },
      {
        'label': context.tr('cash_on_delivery'),
        'value': 'cash_on_delivery',
        'icon': Icons.payments_rounded,
        'enabled': true,
        'subtitle': context.tr('pay_after_confirm'),
      },
    ];
    if (_deliveryMethod == 'qadmous') {
      options.removeWhere((option) => option['value'] == 'cash_on_delivery');
      if (_selectedPayment == 'cash_on_delivery') _selectedPayment = 'online';
    }

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
              context.tr(
                _selectedPayment == 'online'
                    ? 'advance_payment_info'
                    : 'payment_info',
              ),
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

  Widget _buildPaymentReceiptPicker() {
    return HanovaSurface(
      padding: const EdgeInsets.all(14),
      borderColor: _paymentReceiptPath == null
          ? AppColors.primary
          : AppColors.success,
      child: Row(
        children: [
          Icon(
            _paymentReceiptPath == null
                ? Icons.receipt_long_outlined
                : Icons.check_circle_rounded,
            color: _paymentReceiptPath == null
                ? AppColors.primary
                : AppColors.success,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              _paymentReceiptPath == null
                  ? context.tr('payment_receipt_required')
                  : context.tr('payment_receipt_selected'),
            ),
          ),
          TextButton(
            onPressed: _pickPaymentReceipt,
            child: Text(context.tr('upload_payment_receipt')),
          ),
        ],
      ),
    );
  }

  Widget _buildOrderSummary(CartState cartState) {
    final total = cartState.totalAmount + _deliveryFee;
    final totalUsd = cartState.totalUsd == null || _deliveryFeeUsd == null
        ? null
        : cartState.totalUsd! + _deliveryFeeUsd!;
    final languageCode = Localizations.localeOf(context).languageCode;

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
                  languageCode: languageCode,
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
                CurrencyFormatter.dual(
                  _deliveryFee,
                  _deliveryFeeUsd,
                  languageCode: languageCode,
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
                CurrencyFormatter.dual(
                  total,
                  totalUsd,
                  languageCode: languageCode,
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
}
