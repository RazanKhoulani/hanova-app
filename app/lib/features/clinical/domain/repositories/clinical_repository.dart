import '../../data/models/clinical_models.dart';

abstract class ClinicalRepository {
  Future<List<PatientModel>> getPatients();
  Future<List<AppointmentModel>> getAppointments();
  Future<List<Map<String, dynamic>>> getConsultations();
  Future<void> uploadProgressPhotos({
    required String beforeImagePath,
    required String afterImagePath,
    required bool consentForDiscount,
  });
  Future<AvailableTimeSlotsModel> getAvailableTimeSlots({
    required String date,
    required String type,
    required String appointmentType,
    int? doctorId,
  });
  Future<void> scheduleAppointment(Map<String, dynamic> data);
  Future<void> updateAppointmentStatus(int id, String status);
  Future<void> rescheduleAppointment(int id, Map<String, dynamic> data);
}
