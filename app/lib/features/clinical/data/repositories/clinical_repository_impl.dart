import '../../domain/repositories/clinical_repository.dart';
import '../models/clinical_models.dart';
import '../sources/clinical_remote_data_source.dart';

class ClinicalRepositoryImpl implements ClinicalRepository {
  final ClinicalRemoteDataSource _remoteDataSource;

  ClinicalRepositoryImpl(this._remoteDataSource);

  @override
  Future<List<PatientModel>> getPatients() async {
    return await _remoteDataSource.getPatients();
  }

  @override
  Future<List<AppointmentModel>> getAppointments() async {
    return await _remoteDataSource.getAppointments();
  }

  @override
  Future<List<Map<String, dynamic>>> getConsultations() async {
    return await _remoteDataSource.getConsultations();
  }

  @override
  Future<void> uploadProgressPhotos({
    required String beforeImagePath,
    required String afterImagePath,
    required bool consentForDiscount,
  }) async {
    await _remoteDataSource.uploadProgressPhotos(
      beforeImagePath: beforeImagePath,
      afterImagePath: afterImagePath,
      consentForDiscount: consentForDiscount,
    );
  }

  @override
  Future<AvailableTimeSlotsModel> getAvailableTimeSlots({
    required String date,
    required String type,
    required String appointmentType,
    int? doctorId,
  }) async {
    return await _remoteDataSource.getAvailableTimeSlots(
      date: date,
      type: type,
      appointmentType: appointmentType,
      doctorId: doctorId,
    );
  }

  @override
  Future<void> scheduleAppointment(Map<String, dynamic> data) async {
    await _remoteDataSource.scheduleAppointment(data);
  }

  @override
  Future<void> updateAppointmentStatus(int id, String status) async {
    await _remoteDataSource.updateAppointmentStatus(id, status);
  }

  @override
  Future<void> rescheduleAppointment(int id, Map<String, dynamic> data) async {
    await _remoteDataSource.rescheduleAppointment(id, data);
  }
}
