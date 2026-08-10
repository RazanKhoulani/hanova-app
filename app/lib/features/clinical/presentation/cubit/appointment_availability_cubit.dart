import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../../core/network/api_error_message.dart';

import '../../domain/repositories/clinical_repository.dart';
import 'package:intl/intl.dart';

class AppointmentAvailabilityState {
  final bool isLoading;
  final List<String> slots;
  final int? doctorId;
  final String? doctorName;
  final String? errorMessage;
  final String? selectedDate;
  final String? sessionType;
  final String? appointmentType;
  final int? durationMinutes;

  const AppointmentAvailabilityState({
    required this.isLoading,
    required this.slots,
    required this.doctorId,
    required this.doctorName,
    required this.errorMessage,
    required this.selectedDate,
    required this.sessionType,
    required this.appointmentType,
    required this.durationMinutes,
  });

  factory AppointmentAvailabilityState.initial() {
    return const AppointmentAvailabilityState(
      isLoading: false,
      slots: <String>[],
      doctorId: null,
      doctorName: null,
      errorMessage: null,
      selectedDate: null,
      sessionType: null,
      appointmentType: null,
      durationMinutes: null,
    );
  }

  AppointmentAvailabilityState copyWith({
    bool? isLoading,
    List<String>? slots,
    int? doctorId,
    String? doctorName,
    String? errorMessage,
    String? selectedDate,
    String? sessionType,
    String? appointmentType,
    int? durationMinutes,
  }) {
    return AppointmentAvailabilityState(
      isLoading: isLoading ?? this.isLoading,
      slots: slots ?? this.slots,
      doctorId: doctorId ?? this.doctorId,
      doctorName: doctorName ?? this.doctorName,
      errorMessage: errorMessage,
      selectedDate: selectedDate ?? this.selectedDate,
      sessionType: sessionType ?? this.sessionType,
      appointmentType: appointmentType ?? this.appointmentType,
      durationMinutes: durationMinutes ?? this.durationMinutes,
    );
  }
}

class AppointmentAvailabilityCubit extends Cubit<AppointmentAvailabilityState> {
  final ClinicalRepository _repository;

  AppointmentAvailabilityCubit(this._repository)
    : super(AppointmentAvailabilityState.initial());

  Future<void> loadAvailableSlots({
    required DateTime date,
    required String sessionType,
    required String appointmentType,
    int? doctorId,
  }) async {
    if (isClosed) {
      return;
    }

    emit(
      state.copyWith(
        isLoading: true,
        slots: const <String>[],
        errorMessage: null,
        selectedDate: DateFormat('yyyy-MM-dd').format(date),
        sessionType: sessionType.toLowerCase(),
        appointmentType: appointmentType.toLowerCase(),
        doctorId: doctorId,
      ),
    );

    try {
      final result = await _repository.getAvailableTimeSlots(
        date: DateFormat('yyyy-MM-dd').format(date),
        type: sessionType.toLowerCase(),
        appointmentType: appointmentType.toLowerCase(),
        doctorId: doctorId,
      );

      if (isClosed) {
        return;
      }

      final unavailableMessage =
          result.isClosed &&
              result.message != null &&
              result.message!.isNotEmpty
          ? result.message
          : null;

      emit(
        state.copyWith(
          isLoading: false,
          slots: result.slots,
          doctorId: result.doctorId == 0 ? doctorId : result.doctorId,
          doctorName: result.doctorName.isEmpty
              ? state.doctorName
              : result.doctorName,
          errorMessage: unavailableMessage,
          selectedDate: result.date,
          sessionType: result.type,
          appointmentType: result.appointmentType,
          durationMinutes: result.durationMinutes,
        ),
      );
    } catch (e) {
      if (isClosed) {
        return;
      }

      emit(
        state.copyWith(
          isLoading: false,
          slots: const <String>[],
          errorMessage: ApiErrorMessage.from(e),
        ),
      );
    }
  }
}
