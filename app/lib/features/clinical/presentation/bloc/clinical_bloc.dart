import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../../core/network/api_error_message.dart';
import '../../domain/repositories/clinical_repository.dart';
import '../../data/models/clinical_models.dart';

// Events
abstract class ClinicalEvent {}

class ClinicalFetchPatients extends ClinicalEvent {}

class ClinicalFetchAppointments extends ClinicalEvent {}

class ClinicalFetchConsultations extends ClinicalEvent {}

class ClinicalScheduleAppointment extends ClinicalEvent {
  final Map<String, dynamic> data;
  ClinicalScheduleAppointment(this.data);
}

class ClinicalUpdateAppointmentStatus extends ClinicalEvent {
  final int id;
  final String status;
  ClinicalUpdateAppointmentStatus(this.id, this.status);
}

// States
abstract class ClinicalState {}

class ClinicalInitial extends ClinicalState {}

class ClinicalLoading extends ClinicalState {}

class ClinicalPatientsLoaded extends ClinicalState {
  final List<PatientModel> patients;
  ClinicalPatientsLoaded(this.patients);
}

class ClinicalAppointmentsLoaded extends ClinicalState {
  final List<AppointmentModel> appointments;
  ClinicalAppointmentsLoaded(this.appointments);
}

class ClinicalConsultationsLoaded extends ClinicalState {
  final List<Map<String, dynamic>> consultations;
  ClinicalConsultationsLoaded(this.consultations);
}

class ClinicalSuccess extends ClinicalState {
  final String message;
  ClinicalSuccess(this.message);
}

class ClinicalFailure extends ClinicalState {
  final String message;
  ClinicalFailure(this.message);
}

// Bloc
class ClinicalBloc extends Bloc<ClinicalEvent, ClinicalState> {
  final ClinicalRepository _repository;

  ClinicalBloc(this._repository) : super(ClinicalInitial()) {
    on<ClinicalFetchPatients>(_onFetchPatients);
    on<ClinicalFetchAppointments>(_onFetchAppointments);
    on<ClinicalFetchConsultations>(_onFetchConsultations);
    on<ClinicalScheduleAppointment>(_onScheduleAppointment);
    on<ClinicalUpdateAppointmentStatus>(_onUpdateAppointmentStatus);
  }

  Future<void> _onFetchPatients(
    ClinicalFetchPatients event,
    Emitter<ClinicalState> emit,
  ) async {
    emit(ClinicalLoading());
    try {
      final patients = await _repository.getPatients();
      emit(ClinicalPatientsLoaded(patients));
    } catch (e) {
      emit(ClinicalFailure(_failureMessage(e)));
    }
  }

  Future<void> _onFetchAppointments(
    ClinicalFetchAppointments event,
    Emitter<ClinicalState> emit,
  ) async {
    emit(ClinicalLoading());
    try {
      final appointments = await _repository.getAppointments();
      emit(ClinicalAppointmentsLoaded(appointments));
    } catch (e) {
      emit(ClinicalFailure(_failureMessage(e)));
    }
  }

  Future<void> _onFetchConsultations(
    ClinicalFetchConsultations event,
    Emitter<ClinicalState> emit,
  ) async {
    emit(ClinicalLoading());
    try {
      final consultations = await _repository.getConsultations();
      emit(ClinicalConsultationsLoaded(consultations));
    } catch (e) {
      emit(ClinicalFailure(_failureMessage(e)));
    }
  }

  Future<void> _onScheduleAppointment(
    ClinicalScheduleAppointment event,
    Emitter<ClinicalState> emit,
  ) async {
    emit(ClinicalLoading());
    try {
      await _repository.scheduleAppointment(event.data);
      emit(ClinicalSuccess('Appointment scheduled successfully'));
      add(ClinicalFetchAppointments()); // Refresh list
    } catch (e) {
      emit(ClinicalFailure(_failureMessage(e)));
    }
  }

  Future<void> _onUpdateAppointmentStatus(
    ClinicalUpdateAppointmentStatus event,
    Emitter<ClinicalState> emit,
  ) async {
    emit(ClinicalLoading());
    try {
      await _repository.updateAppointmentStatus(event.id, event.status);
      emit(ClinicalSuccess('Appointment status updated'));
      add(ClinicalFetchAppointments()); // Refresh list
    } catch (e) {
      emit(ClinicalFailure(_failureMessage(e)));
    }
  }

  String _failureMessage(Object error) {
    return _friendlyMessage(ApiErrorMessage.from(error));
  }

  String _friendlyMessage(String message) {
    final normalized = message.toLowerCase();

    if (normalized.contains('active appointment')) {
      return 'لديك موعد فعال بالفعل. يمكنك حجز موعد جديد بعد انتهاء الموعد الحالي أو إلغائه.';
    }

    if (normalized.contains('no longer available')) {
      return 'الوقت المحدد لم يعد متاحاً. اختاري وقتاً آخر.';
    }

    if (normalized.contains('after or equal to today')) {
      return 'لا يمكن حجز موعد بتاريخ قديم.';
    }

    return message.replaceFirst('Exception: ', '');
  }
}
