import 'package:app/injection_container.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:table_calendar/table_calendar.dart';

import '../../../../core/theme/app_colors.dart';
import '../../../auth/presentation/bloc/auth_bloc.dart';
import '../../../auth/presentation/bloc/auth_state.dart';
import '../bloc/clinical_bloc.dart';
import '../cubit/appointment_availability_cubit.dart';

class AppointmentScreen extends StatefulWidget {
  const AppointmentScreen({super.key});

  @override
  State<AppointmentScreen> createState() => _AppointmentScreenState();
}

class _AppointmentScreenState extends State<AppointmentScreen> {
  late final AppointmentAvailabilityCubit _availabilityCubit;
  CalendarFormat _calendarFormat = CalendarFormat.month;
  DateTime _focusedDay = DateTime.now();
  DateTime? _selectedDay;
  String _sessionType = 'Clinic';
  String _appointmentType = 'Treatment';
  String? _selectedTime;

  @override
  void initState() {
    super.initState();
    _selectedDay = DateTime.now();
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
          backgroundColor: Colors.white,
          appBar: AppBar(title: const Text('Book Appointment')),
          body: SingleChildScrollView(
            child: Column(
              children: [
                _buildCalendar(),
                Padding(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Select Session Type',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 16),
                      _buildSessionTypeSelector(),
                      const SizedBox(height: 32),
                      const Text(
                        'Appointment Type',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 16),
                      _buildAppointmentTypeSelector(),
                      const SizedBox(height: 32),
                      const Text(
                        'Available Time',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
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
                                : () {
                                    if (!isAuthenticated) {
                                      _showAuthRequired();
                                      return;
                                    }

                                    if (_selectedDay == null ||
                                        _selectedTime == null) {
                                      ScaffoldMessenger.of(
                                        context,
                                      ).showSnackBar(
                                        const SnackBar(
                                          content: Text(
                                            'Please select date and time',
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
                                      if (availabilityState.doctorId != null)
                                        'doctor_id': availabilityState.doctorId,
                                    };

                                    context.read<ClinicalBloc>().add(
                                      ClinicalScheduleAppointment(payload),
                                    );
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
                                : const Text('Confirm Booking'),
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
    return Container(
      margin: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: const [
          BoxShadow(color: AppColors.cardShadow, blurRadius: 20),
        ],
      ),
      child: TableCalendar(
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
        Expanded(child: _buildTypeCard('Clinic', Icons.store_rounded)),
        const SizedBox(width: 16),
        Expanded(child: _buildTypeCard('Online', Icons.videocam_rounded)),
      ],
    );
  }

  Widget _buildAppointmentTypeSelector() {
    return Row(
      children: [
        Expanded(
          child: _buildAppointmentTypeCard(
            'Treatment',
            Icons.healing_rounded,
            '30 min',
          ),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: _buildAppointmentTypeCard(
            'Session',
            Icons.spa_rounded,
            '60 min',
          ),
        ),
      ],
    );
  }

  Widget _buildAppointmentTypeCard(
    String type,
    IconData icon,
    String duration,
  ) {
    final isSelected = _appointmentType == type;
    return GestureDetector(
      onTap: () {
        setState(() => _appointmentType = type);
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
              type,
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

  Widget _buildTypeCard(String type, IconData icon) {
    final isSelected = _sessionType == type;
    return GestureDetector(
      onTap: () {
        setState(() => _sessionType = type);
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
              type,
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
          return const Center(
            child: Padding(
              padding: EdgeInsets.symmetric(vertical: 16),
              child: SizedBox(
                height: 24,
                width: 24,
                child: CircularProgressIndicator(strokeWidth: 2),
              ),
            ),
          );
        }

        if (availabilityState.errorMessage != null) {
          return _buildAvailabilityMessage(availabilityState.errorMessage!);
        }

        if (availabilityState.slots.isEmpty) {
          return _buildAvailabilityMessage('No available times for this day.');
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
            const Text(
              'Login Required',
              style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 8),
            const Text(
              'Please login to book a consultation appointment.',
              style: TextStyle(color: AppColors.textSecondary),
            ),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: () {
                Navigator.pop(context);
                context.push('/login');
              },
              child: const Text('Login'),
            ),
          ],
        ),
      ),
    );
  }
}
