import 'package:app/injection_container.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:table_calendar/table_calendar.dart';

import '../../../../core/localization/app_localizations.dart';
import '../../../../core/theme/app_colors.dart';
import '../../../../core/widgets/hanova_ui.dart';
import '../../../auth/presentation/bloc/auth_bloc.dart';
import '../../../auth/presentation/bloc/auth_state.dart';
import '../../domain/repositories/clinical_repository.dart';
import '../bloc/clinical_bloc.dart';
import '../cubit/appointment_availability_cubit.dart';

class AppointmentScreen extends StatefulWidget {
  final String? initialSessionType;
  final String? initialAppointmentType;
  final String? initialSpecialty;
  final bool openedFromBot;
  final int? appointmentId;
  final DateTime? initialDate;
  final String? initialTime;

  const AppointmentScreen({
    super.key,
    this.initialSessionType,
    this.initialAppointmentType,
    this.initialSpecialty,
    this.openedFromBot = false,
    this.appointmentId,
    this.initialDate,
    this.initialTime,
  });

  @override
  State<AppointmentScreen> createState() => _AppointmentScreenState();
}

class _AppointmentScreenState extends State<AppointmentScreen> {
  late final AppointmentAvailabilityCubit _availabilityCubit;
  CalendarFormat _calendarFormat = CalendarFormat.month;
  DateTime _focusedDay = DateTime.now();
  DateTime? _selectedDay;
  late String _sessionType;
  late String _appointmentType;
  late String _specialty;
  String? _selectedTime;

  @override
  void initState() {
    super.initState();
    _sessionType = _normalizedSessionType(widget.initialSessionType);
    _appointmentType = _normalizedAppointmentType(
      widget.initialAppointmentType,
    );
    _specialty = _normalizedSpecialty(widget.initialSpecialty);
    if (_specialty == 'Nutrition') {
      _sessionType = 'Online';
      _appointmentType = 'Consultation';
    }
    _selectedDay = widget.initialDate ?? DateTime.now();
    _focusedDay = _selectedDay!;
    _selectedTime = widget.initialTime;
    _availabilityCubit = sl<AppointmentAvailabilityCubit>();
    _availabilityCubit.loadAvailableSlots(
      date: _selectedDay!,
      sessionType: _sessionType,
      appointmentType: _appointmentType,
    );
  }

  @override
  void dispose() {
    _availabilityCubit.close();
    super.dispose();
  }

  void _loadAvailableSlots() {
    if (!mounted || _selectedDay == null) {
      return;
    }

    setState(() {
      _selectedTime = null;
    });

    _availabilityCubit.loadAvailableSlots(
      date: _selectedDay!,
      sessionType: _sessionType,
      appointmentType: _appointmentType,
      doctorId: _availabilityCubit.state.doctorId,
    );
  }

  @override
  Widget build(BuildContext context) {
    return BlocProvider.value(
      value: _availabilityCubit,
      child: BlocListener<ClinicalBloc, ClinicalState>(
        listener: (context, state) {
          if (state is ClinicalSuccess) {
            ScaffoldMessenger.of(
              context,
            ).showSnackBar(SnackBar(content: Text(state.message)));
            context.pop();
          } else if (state is ClinicalFailure) {
            ScaffoldMessenger.of(
              context,
            ).showSnackBar(SnackBar(content: Text(state.message)));
            final normalizedMessage = state.message.toLowerCase();
            if (normalizedMessage.contains('no longer available') ||
                state.message.contains('لم يعد متاح')) {
              _loadAvailableSlots();
            }
          }
        },
        child: Scaffold(
          backgroundColor: AppColors.background,
          appBar: AppBar(title: Text(context.tr('book_appointment'))),
          body: SingleChildScrollView(
            child: Column(
              children: [
                _buildCalendar(),
                Padding(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      HanovaSectionHeader(
                        title: context.tr('select_session_type'),
                      ),
                      const SizedBox(height: 16),
                      _buildSessionTypeSelector(),
                      const SizedBox(height: 32),
                      HanovaSectionHeader(
                        title: context.tr('appointment_type'),
                      ),
                      const SizedBox(height: 16),
                      _buildAppointmentTypeSelector(),
                      if (_appointmentType == 'Consultation') ...[
                        const SizedBox(height: 24),
                        HanovaSectionHeader(
                          title: context.tr('consultation_subject'),
                        ),
                        const SizedBox(height: 12),
                        _buildSpecialtySelector(),
                      ],
                      const SizedBox(height: 32),
                      HanovaSectionHeader(title: context.tr('available_time')),
                      const SizedBox(height: 16),
                      _buildTimeSlots(),
                      const SizedBox(height: 48),
                      BlocBuilder<ClinicalBloc, ClinicalState>(
                        builder: (context, state) {
                          final isAuthenticated =
                              context.read<AuthBloc>().state
                                  is AuthAuthenticated;

                          return ElevatedButton(
                            onPressed: state is ClinicalLoading
                                ? null
                                : () async {
                                    if (!isAuthenticated) {
                                      _showAuthRequired();
                                      return;
                                    }

                                    if (_selectedDay == null ||
                                        _selectedTime == null) {
                                      ScaffoldMessenger.of(
                                        context,
                                      ).showSnackBar(
                                        SnackBar(
                                          content: Text(
                                            context.tr('select_date_time'),
                                          ),
                                        ),
                                      );
                                      return;
                                    }

                                    final parsedTime = DateFormat(
                                      'hh:mm a',
                                    ).parse(_selectedTime!);
                                    final apiTime = DateFormat(
                                      'HH:mm',
                                    ).format(parsedTime);
                                    final apiDate = DateFormat(
                                      'yyyy-MM-dd',
                                    ).format(_selectedDay!);
                                    final availabilityState = context
                                        .read<AppointmentAvailabilityCubit>()
                                        .state;

                                    final payload = <String, dynamic>{
                                      'date': apiDate,
                                      'time': apiTime,
                                      'type': _sessionType.toLowerCase(),
                                      'appointment_type': _appointmentType
                                          .toLowerCase(),
                                      'specialty': _specialty.toLowerCase(),
                                      if (availabilityState.doctorId != null)
                                        'doctor_id': availabilityState.doctorId,
                                    };

                                    if (widget.appointmentId != null) {
                                      await sl<ClinicalRepository>()
                                          .rescheduleAppointment(
                                            widget.appointmentId!,
                                            payload,
                                          );
                                      if (!context.mounted) return;
                                      context.pop(true);
                                    } else {
                                      context.read<ClinicalBloc>().add(
                                        ClinicalScheduleAppointment(payload),
                                      );
                                    }
                                  },
                            child: state is ClinicalLoading
                                ? const SizedBox(
                                    height: 20,
                                    width: 20,
                                    child: CircularProgressIndicator(
                                      color: Colors.white,
                                      strokeWidth: 2,
                                    ),
                                  )
                                : Text(context.tr('confirm_booking')),
                          );
                        },
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildCalendar() {
    return HanovaSurface(
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: TableCalendar(
        locale: Localizations.localeOf(context).languageCode,
        firstDay: DateTime.now(),
        lastDay: DateTime.now().add(const Duration(days: 30)),
        focusedDay: _focusedDay,
        calendarFormat: _calendarFormat,
        selectedDayPredicate: (day) => isSameDay(_selectedDay, day),
        onDaySelected: (selectedDay, focusedDay) {
          setState(() {
            _selectedDay = selectedDay;
            _focusedDay = focusedDay;
            _selectedTime = null;
          });
          _loadAvailableSlots();
        },
        onFormatChanged: (format) => setState(() => _calendarFormat = format),
        headerStyle: const HeaderStyle(
          formatButtonVisible: false,
          titleCentered: true,
          titleTextStyle: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
        ),
        calendarStyle: const CalendarStyle(
          todayDecoration: BoxDecoration(
            color: AppColors.primaryLight,
            shape: BoxShape.circle,
          ),
          todayTextStyle: TextStyle(color: AppColors.primary),
          selectedDecoration: BoxDecoration(
            color: AppColors.primary,
            shape: BoxShape.circle,
          ),
        ),
      ),
    );
  }

  Widget _buildSessionTypeSelector() {
    return Row(
      children: [
        Expanded(
          child: _buildTypeCard(
            'Clinic',
            context.tr('clinic_session'),
            Icons.store_rounded,
          ),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: _buildTypeCard(
            'Online',
            context.tr('online_session'),
            Icons.videocam_rounded,
          ),
        ),
      ],
    );
  }

  Widget _buildAppointmentTypeSelector() {
    return Column(
      children: [
        Row(
          children: [
            Expanded(
              child: _buildAppointmentTypeCard(
                'Consultation',
                context.tr('consultation_type'),
                Icons.medical_information_outlined,
                context.tr('duration_15_min'),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildAppointmentTypeCard(
                'Treatment',
                context.tr('treatment_type'),
                Icons.healing_rounded,
                context.tr('duration_30_min'),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        SizedBox(
          width: double.infinity,
          child: _buildAppointmentTypeCard(
            'Session',
            context.tr('session_type'),
            Icons.spa_rounded,
            context.tr('duration_60_min'),
          ),
        ),
      ],
    );
  }

  Widget _buildSpecialtySelector() {
    const specialties = [
      ('Skin', 'skin_consultation_subject', Icons.face_retouching_natural),
      ('Hair', 'hair_consultation_subject', Icons.content_cut_rounded),
      (
        'Nutrition',
        'nutrition_consultation_subject',
        Icons.restaurant_menu_rounded,
      ),
    ];

    return Wrap(
      spacing: 10,
      runSpacing: 10,
      children: specialties.map((specialty) {
        final isSelected = _specialty == specialty.$1;
        return ChoiceChip(
          label: Text(context.tr(specialty.$2)),
          avatar: Icon(specialty.$3, size: 18),
          selected: isSelected,
          selectedColor: AppColors.primarySoft,
          onSelected: (_) {
            setState(() {
              _specialty = specialty.$1;
              if (_specialty == 'Nutrition') {
                _sessionType = 'Online';
                _appointmentType = 'Consultation';
              }
            });
            _loadAvailableSlots();
          },
        );
      }).toList(),
    );
  }

  Widget _buildAppointmentTypeCard(
    String type,
    String label,
    IconData icon,
    String duration,
  ) {
    final isSelected = _appointmentType == type;
    return GestureDetector(
      onTap: () {
        setState(() {
          _appointmentType = type;
          if (type != 'Consultation' && _specialty == 'Nutrition') {
            _specialty = 'Skin';
          }
        });
        _loadAvailableSlots();
      },
      child: Container(
        height: 100,
        decoration: BoxDecoration(
          color: isSelected ? AppColors.primary : Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: isSelected ? AppColors.primary : AppColors.divider,
          ),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: AppColors.primary.withValues(alpha: 0.3),
                    blurRadius: 10,
                  ),
                ]
              : [],
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              icon,
              color: isSelected ? Colors.white : AppColors.primary,
              size: 30,
            ),
            const SizedBox(height: 8),
            Text(
              label,
              style: TextStyle(
                color: isSelected ? Colors.white : AppColors.textPrimary,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              duration,
              style: TextStyle(
                color: isSelected ? Colors.white70 : AppColors.textSecondary,
                fontSize: 12,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTypeCard(String type, String label, IconData icon) {
    final isSelected = _sessionType == type;
    return GestureDetector(
      onTap: () {
        setState(() {
          _sessionType = type;
          if (type != 'Online' && _specialty == 'Nutrition') {
            _specialty = 'Skin';
          }
        });
        _loadAvailableSlots();
      },
      child: Container(
        height: 100,
        decoration: BoxDecoration(
          color: isSelected ? AppColors.primary : Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: isSelected ? AppColors.primary : AppColors.divider,
          ),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: AppColors.primary.withValues(alpha: 0.3),
                    blurRadius: 10,
                  ),
                ]
              : [],
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              icon,
              color: isSelected ? Colors.white : AppColors.primary,
              size: 30,
            ),
            const SizedBox(height: 8),
            Text(
              label,
              style: TextStyle(
                color: isSelected ? Colors.white : AppColors.textPrimary,
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTimeSlots() {
    return BlocBuilder<
      AppointmentAvailabilityCubit,
      AppointmentAvailabilityState
    >(
      builder: (context, availabilityState) {
        if (availabilityState.isLoading) {
          return const SizedBox(height: 72, child: HanovaLoadingView());
        }

        if (availabilityState.errorMessage != null) {
          return _buildAvailabilityMessage(availabilityState.errorMessage!);
        }

        if (availabilityState.slots.isEmpty) {
          return _buildAvailabilityMessage(context.tr('no_available_times'));
        }

        return Wrap(
          spacing: 12,
          runSpacing: 12,
          children: availabilityState.slots.map((slot) {
            final isSelected = _selectedTime == slot;
            return GestureDetector(
              onTap: () => setState(() => _selectedTime = slot),
              child: Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 10,
                ),
                decoration: BoxDecoration(
                  color: isSelected ? AppColors.primary : AppColors.background,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  slot,
                  style: TextStyle(
                    fontWeight: FontWeight.w600,
                    color: isSelected ? Colors.white : AppColors.textPrimary,
                  ),
                ),
              ),
            );
          }).toList(),
        );
      },
    );
  }

  Widget _buildAvailabilityMessage(String message) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      decoration: BoxDecoration(
        color: AppColors.background,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: [
          const Icon(Icons.event_busy_rounded, color: AppColors.primary),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              message,
              style: const TextStyle(
                color: AppColors.textSecondary,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _showAuthRequired() {
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
              context.tr('login_required_appointment'),
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

  String _normalizedSessionType(String? value) {
    return value?.toLowerCase() == 'online' ? 'Online' : 'Clinic';
  }

  String _normalizedAppointmentType(String? value) {
    return switch (value?.toLowerCase()) {
      'consultation' => 'Consultation',
      'session' => 'Session',
      _ => 'Treatment',
    };
  }

  String _normalizedSpecialty(String? value) {
    return switch (value?.toLowerCase()) {
      'hair' => 'Hair',
      'nutrition' => 'Nutrition',
      _ => 'Skin',
    };
  }
}
