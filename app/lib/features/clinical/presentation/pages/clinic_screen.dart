import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import '../../../../core/localization/app_localizations.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../../injection_container.dart';
import '../../../auth/presentation/bloc/auth_bloc.dart';
import '../../../auth/presentation/bloc/auth_state.dart';
import '../../data/models/clinical_models.dart';
import '../../domain/repositories/clinical_repository.dart';
import '../bloc/clinical_bloc.dart';

class ClinicScreen extends StatefulWidget {
  const ClinicScreen({super.key});

  @override
  State<ClinicScreen> createState() => _ClinicScreenState();
}

class _ClinicScreenState extends State<ClinicScreen> {
  bool _requestedAppointments = false;
  XFile? _beforeProgressImage;
  XFile? _afterProgressImage;
  bool _progressPhotoConsent = true;
  bool _uploadingProgressPhotos = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback(
      (_) => _fetchAppointmentsIfAllowed(),
    );
  }

  void _fetchAppointmentsIfAllowed({bool force = false}) {
    if (!mounted) return;
    if (!force && _requestedAppointments) return;
    if (context.read<AuthBloc>().state is AuthAuthenticated) {
      _requestedAppointments = true;
      context.read<ClinicalBloc>().add(ClinicalFetchAppointments());
    }
  }

  Future<void> _pickProgressImage({required bool before}) async {
    final picked = await ImagePicker().pickImage(
      source: ImageSource.gallery,
      imageQuality: 85,
    );

    if (picked == null || !mounted) return;

    setState(() {
      if (before) {
        _beforeProgressImage = picked;
      } else {
        _afterProgressImage = picked;
      }
    });
  }

  Future<void> _submitProgressPhotos() async {
    if (_beforeProgressImage == null || _afterProgressImage == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            _clinicText(
              'اختاري صورة قبل وصورة بعد أولاً',
              'Choose before and after photos first',
            ),
          ),
        ),
      );
      return;
    }

    setState(() => _uploadingProgressPhotos = true);

    try {
      await sl<ClinicalRepository>().uploadProgressPhotos(
        beforeImagePath: _beforeProgressImage!.path,
        afterImagePath: _afterProgressImage!.path,
        consentForDiscount: _progressPhotoConsent,
      );

      if (!mounted) return;
      setState(() {
        _beforeProgressImage = null;
        _afterProgressImage = null;
        _progressPhotoConsent = true;
      });

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            _clinicText(
              'تم إرسال الصور للمراجعة',
              'Photos submitted for review',
            ),
          ),
        ),
      );
    } catch (_) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            _clinicText(
              'تعذر إرسال الصور. حاولي مرة أخرى',
              'Could not upload photos. Please try again',
            ),
          ),
        ),
      );
    } finally {
      if (mounted) setState(() => _uploadingProgressPhotos = false);
    }
  }

  String _clinicText(String ar, String en) {
    return _isArabic(context) ? ar : en;
  }

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<AuthBloc, AuthState>(
      builder: (context, authState) {
        if (authState is AuthLoading || authState is AuthInitial) {
          return const Scaffold(
            body: Center(child: CircularProgressIndicator()),
          );
        }

        if (authState is! AuthAuthenticated) {
          return Scaffold(
            backgroundColor: AppColors.background,
            appBar: AppBar(title: Text(context.tr('my_clinic'))),
            body: _buildAuthRequired(context),
          );
        }

        WidgetsBinding.instance.addPostFrameCallback(
          (_) => _fetchAppointmentsIfAllowed(),
        );

        return Scaffold(
          backgroundColor: AppColors.background,
          appBar: AppBar(title: Text(context.tr('my_clinic'))),
          body: BlocBuilder<ClinicalBloc, ClinicalState>(
            builder: (context, state) {
              if (state is ClinicalLoading) {
                return _buildDashboard(context, isLoading: true);
              }

              if (state is ClinicalFailure) {
                return _buildDashboard(context, errorMessage: state.message);
              }

              if (state is ClinicalAppointmentsLoaded) {
                return _buildDashboard(
                  context,
                  appointments: state.appointments,
                );
              }

              return _buildDashboard(context);
            },
          ),
          floatingActionButton: FloatingActionButton.extended(
            onPressed: () => context.push('/appointment'),
            label: Text(context.tr('book_now')),
            icon: const Icon(Icons.add_rounded),
            backgroundColor: AppColors.primary,
            foregroundColor: Colors.white,
          ),
        );
      },
    );
  }

  Widget _buildDashboard(
    BuildContext context, {
    List<AppointmentModel> appointments = const [],
    bool isLoading = false,
    String? errorMessage,
  }) {
    return RefreshIndicator(
      onRefresh: () async => _fetchAppointmentsIfAllowed(force: true),
      child: ListView(
        padding: const EdgeInsets.fromLTRB(20, 18, 20, 110),
        children: [
          _buildClinicHeader(context),
          const SizedBox(height: 18),
          _buildQuickActions(context),
          const SizedBox(height: 26),
          _buildSectionHeader(context.tr('upcoming_appointments')),
          const SizedBox(height: 12),
          if (isLoading)
            const Center(
              child: Padding(
                padding: EdgeInsets.all(24),
                child: CircularProgressIndicator(),
              ),
            )
          else if (errorMessage != null)
            _buildErrorCard(errorMessage)
          else if (appointments.isEmpty)
            _buildEmptyAppointments(context)
          else
            ...appointments.map(
              (appointment) => _buildAppointmentCard(context, appointment),
            ),
          const SizedBox(height: 26),
          _buildSectionHeader(context.tr('medical_followup')),
          const SizedBox(height: 12),
          _buildFollowUpCard(),
        ],
      ),
    );
  }

  Widget _buildClinicHeader(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [AppColors.primary, Color(0xFFE08FA5)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            context.tr('clinic_care'),
            style: const TextStyle(
              color: Colors.white,
              fontSize: 22,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            context.tr('clinic_care_note'),
            style: const TextStyle(color: Colors.white70, height: 1.4),
          ),
          const SizedBox(height: 16),
          ElevatedButton.icon(
            onPressed: () => context.push('/appointment'),
            icon: const Icon(Icons.calendar_month_rounded, size: 18),
            label: Text(context.tr('book_now')),
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.white,
              foregroundColor: AppColors.primary,
              minimumSize: const Size(170, 42),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQuickActions(BuildContext context) {
    return GridView.count(
      crossAxisCount: 2,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      mainAxisSpacing: 12,
      crossAxisSpacing: 12,
      childAspectRatio: 2.35,
      children: [
        _ActionTile(
          icon: Icons.calendar_month_outlined,
          label: context.tr('appointment'),
          onTap: () => context.push('/appointment'),
        ),
        _ActionTile(
          icon: Icons.chat_bubble_outline_rounded,
          label: context.tr('live_chat'),
          onTap: () => context.push('/chat'),
        ),
        _ActionTile(
          icon: Icons.smart_toy_outlined,
          label: context.tr('bot'),
          onTap: () => context.push('/bot'),
        ),
        _ActionTile(
          icon: Icons.receipt_long_outlined,
          label: context.tr('orders'),
          onTap: () => context.push('/orders'),
        ),
      ],
    );
  }

  Widget _buildSectionHeader(String title) {
    return Text(
      title,
      style: const TextStyle(
        fontSize: 18,
        fontWeight: FontWeight.bold,
        color: AppColors.textPrimary,
      ),
    );
  }

  Widget _buildAppointmentCard(
    BuildContext context,
    AppointmentModel appointment,
  ) {
    final date = _formatDate(context, appointment.appointmentDate);
    final time = _formatTime(context, appointment.appointmentDate);
    final statusLabel = _statusLabel(context, appointment);

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        boxShadow: const [
          BoxShadow(color: AppColors.cardShadow, blurRadius: 10),
        ],
      ),
      child: Row(
        children: [
          Container(
            width: 52,
            height: 52,
            decoration: BoxDecoration(
              color: AppColors.primaryLight,
              borderRadius: BorderRadius.circular(14),
            ),
            child: const Icon(
              Icons.event_available_rounded,
              color: AppColors.primary,
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(date, style: const TextStyle(fontWeight: FontWeight.w700)),
                const SizedBox(height: 4),
                Text(
                  time,
                  style: const TextStyle(
                    color: AppColors.textSecondary,
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),
          _StatusPill(status: appointment.status, label: statusLabel),
        ],
      ),
    );
  }

  String _formatDate(BuildContext context, DateTime value) {
    if (!_isArabic(context)) {
      return DateFormat('MMM d, yyyy').format(value);
    }

    return '${value.day} ${_arabicMonths[value.month - 1]} ${value.year}';
  }

  String _formatTime(BuildContext context, DateTime value) {
    if (!_isArabic(context)) {
      return DateFormat('hh:mm a').format(value);
    }

    final hour = value.hour % 12 == 0 ? 12 : value.hour % 12;
    final minute = value.minute.toString().padLeft(2, '0');
    final period = value.hour < 12 ? '\u0635' : '\u0645';
    return '${hour.toString().padLeft(2, '0')}:$minute $period';
  }

  String _statusLabel(BuildContext context, AppointmentModel appointment) {
    final locale = _isArabic(context) ? 'ar' : 'en';
    final normalized = appointment.status.toLowerCase().trim();
    final translated = _statusTranslations[locale]?[normalized];
    if (translated != null) return translated;

    final apiLabel = appointment.statusLabel.trim();
    return apiLabel.isNotEmpty ? apiLabel : appointment.status;
  }

  bool _isArabic(BuildContext context) {
    return Localizations.localeOf(context).languageCode == 'ar';
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
      'confirmed': 'Confirmed',
      'completed': 'Completed',
      'cancelled': 'Cancelled',
      'canceled': 'Cancelled',
      'accepted': 'Accepted',
      'ready': 'Ready',
    },
    'ar': {
      'pending':
          '\u0642\u064a\u062f \u0627\u0644\u0627\u0646\u062a\u0638\u0627\u0631',
      'confirmed': '\u0645\u0624\u0643\u062f',
      'completed': '\u0645\u0643\u062a\u0645\u0644',
      'cancelled': '\u0645\u0644\u063a\u064a',
      'canceled': '\u0645\u0644\u063a\u064a',
      'accepted': '\u0645\u0642\u0628\u0648\u0644',
      'ready': '\u062c\u0627\u0647\u0632',
    },
  };

  Widget _buildEmptyAppointments(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        boxShadow: const [
          BoxShadow(color: AppColors.cardShadow, blurRadius: 10),
        ],
      ),
      child: Column(
        children: [
          const Icon(
            Icons.event_note_outlined,
            color: AppColors.primary,
            size: 42,
          ),
          const SizedBox(height: 10),
          Text(
            context.tr('no_appointments'),
            style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 6),
          Text(
            context.tr('no_appointments_note'),
            textAlign: TextAlign.center,
            style: const TextStyle(color: AppColors.textSecondary),
          ),
          const SizedBox(height: 14),
          OutlinedButton.icon(
            onPressed: () => context.push('/appointment'),
            icon: const Icon(Icons.add_rounded),
            label: Text(context.tr('book_now')),
          ),
        ],
      ),
    );
  }

  Widget _buildErrorCard(String message) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        boxShadow: const [
          BoxShadow(color: AppColors.cardShadow, blurRadius: 10),
        ],
      ),
      child: Column(
        children: [
          const Icon(
            Icons.error_outline_rounded,
            color: AppColors.danger,
            size: 38,
          ),
          const SizedBox(height: 10),
          Text(
            message,
            textAlign: TextAlign.center,
            style: const TextStyle(color: AppColors.textSecondary),
          ),
          const SizedBox(height: 12),
          OutlinedButton(
            onPressed: () => _fetchAppointmentsIfAllowed(force: true),
            child: Text(context.tr('try_again')),
          ),
        ],
      ),
    );
  }

  Widget _buildFollowUpCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        boxShadow: const [
          BoxShadow(color: AppColors.cardShadow, blurRadius: 10),
        ],
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(
            Icons.photo_camera_back_outlined,
            color: AppColors.primary,
            size: 34,
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  context.tr('followup_note'),
                  style: const TextStyle(
                    color: AppColors.textSecondary,
                    height: 1.4,
                  ),
                ),
                const SizedBox(height: 16),
                Wrap(
                  spacing: 10,
                  runSpacing: 10,
                  children: [
                    _ProgressPhotoButton(
                      label:
                          _beforeProgressImage?.name ??
                          _clinicText('اختيار صورة قبل', 'Choose before photo'),
                      icon: Icons.looks_one_outlined,
                      onTap: _uploadingProgressPhotos
                          ? null
                          : () => _pickProgressImage(before: true),
                    ),
                    _ProgressPhotoButton(
                      label:
                          _afterProgressImage?.name ??
                          _clinicText('اختيار صورة بعد', 'Choose after photo'),
                      icon: Icons.looks_two_outlined,
                      onTap: _uploadingProgressPhotos
                          ? null
                          : () => _pickProgressImage(before: false),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                CheckboxListTile(
                  contentPadding: EdgeInsets.zero,
                  value: _progressPhotoConsent,
                  onChanged: _uploadingProgressPhotos
                      ? null
                      : (value) {
                          setState(() {
                            _progressPhotoConsent = value ?? true;
                          });
                        },
                  controlAffinity: ListTileControlAffinity.leading,
                  title: Text(
                    _clinicText(
                      'أوافق على مراجعة الصور لإضافة حسم على الطلب القادم',
                      'I agree to review these photos for a next-order discount',
                    ),
                    style: const TextStyle(
                      color: AppColors.textSecondary,
                      fontSize: 12,
                    ),
                  ),
                ),
                const SizedBox(height: 8),
                ElevatedButton.icon(
                  onPressed: _uploadingProgressPhotos
                      ? null
                      : _submitProgressPhotos,
                  icon: _uploadingProgressPhotos
                      ? const SizedBox(
                          width: 16,
                          height: 16,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Icon(Icons.cloud_upload_outlined),
                  label: Text(
                    _clinicText('إرسال الصور للمراجعة', 'Submit photos'),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    foregroundColor: Colors.white,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAuthRequired(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(
              Icons.lock_outline_rounded,
              size: 64,
              color: AppColors.textLight,
            ),
            const SizedBox(height: 16),
            Text(
              context.tr('login_required'),
              style: const TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: AppColors.textPrimary,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              context.tr('clinic_login_note'),
              textAlign: TextAlign.center,
              style: const TextStyle(color: AppColors.textSecondary),
            ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: () => context.push('/login'),
              child: Text(context.tr('login')),
            ),
          ],
        ),
      ),
    );
  }
}

class _ProgressPhotoButton extends StatelessWidget {
  final String label;
  final IconData icon;
  final VoidCallback? onTap;

  const _ProgressPhotoButton({
    required this.label,
    required this.icon,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return OutlinedButton.icon(
      onPressed: onTap,
      icon: Icon(icon, size: 18),
      label: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 170),
        child: Text(label, maxLines: 1, overflow: TextOverflow.ellipsis),
      ),
    );
  }
}

class _ActionTile extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  const _ActionTile({
    required this.icon,
    required this.label,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(16),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 14),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            boxShadow: const [
              BoxShadow(color: AppColors.cardShadow, blurRadius: 10),
            ],
          ),
          child: Row(
            children: [
              Icon(icon, color: AppColors.primary, size: 24),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  label,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(fontWeight: FontWeight.w700),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _StatusPill extends StatelessWidget {
  final String status;
  final String label;

  const _StatusPill({required this.status, required this.label});

  @override
  Widget build(BuildContext context) {
    final normalized = status.toLowerCase();
    final color = normalized == 'confirmed' || normalized == 'completed'
        ? AppColors.success
        : normalized == 'cancelled'
        ? AppColors.danger
        : AppColors.primary;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: color,
          fontSize: 11,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }
}
