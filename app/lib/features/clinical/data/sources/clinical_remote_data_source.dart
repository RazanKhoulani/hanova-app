import 'package:dio/dio.dart';

import '../../../../core/network/dio_client.dart';
import '../../../../core/constants/api_constants.dart';
import '../models/clinical_models.dart';

abstract class ClinicalRemoteDataSource {
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

class ClinicalRemoteDataSourceImpl implements ClinicalRemoteDataSource {
  final DioClient _dioClient;

  ClinicalRemoteDataSourceImpl(this._dioClient);

  @override
  Future<List<PatientModel>> getPatients() async {
    final response = await _dioClient.get(ApiConstants.patients);
    final payload = response.data['data'] ?? response.data;
    final items = payload is List ? payload : <dynamic>[];
    return items.map((json) => PatientModel.fromJson(json)).toList();
  }

  @override
  Future<List<AppointmentModel>> getAppointments() async {
    final response = await _dioClient.get(ApiConstants.appointments);
    final payload = response.data['data'] ?? response.data;
    final items = payload is List ? payload : <dynamic>[];
    return items.map((json) => AppointmentModel.fromJson(json)).toList();
  }

  @override
  Future<List<Map<String, dynamic>>> getConsultations() async {
    final response = await _dioClient.get(ApiConstants.consultations);
    final payload = response.data['data'] ?? response.data;
    final items = payload is List ? payload : <dynamic>[];
    return List<Map<String, dynamic>>.from(items);
  }

  @override
  Future<void> uploadProgressPhotos({
    required String beforeImagePath,
    required String afterImagePath,
    required bool consentForDiscount,
  }) async {
    final formData = FormData.fromMap({
      'before_image': await MultipartFile.fromFile(beforeImagePath),
      'after_image': await MultipartFile.fromFile(afterImagePath),
      'consent_for_discount': consentForDiscount ? 1 : 0,
    });

    await _dioClient.post(ApiConstants.patientProgressPhotos, data: formData);
  }

  @override
  Future<AvailableTimeSlotsModel> getAvailableTimeSlots({
    required String date,
    required String type,
    required String appointmentType,
    int? doctorId,
  }) async {
    final queryParameters = <String, dynamic>{
      'date': date,
      'type': type,
      'appointment_type': appointmentType,
      if (doctorId != null) 'doctor_id': doctorId,
    };

    final response = await _dioClient.get(
      ApiConstants.appointmentAvailableSlots,
      queryParameters: queryParameters,
    );
    final payload = response.data['data'] ?? response.data;
    final map = payload is Map<String, dynamic>
        ? payload
        : Map<String, dynamic>.from(payload as Map);
    return AvailableTimeSlotsModel.fromJson(map);
  }

  @override
  Future<void> scheduleAppointment(Map<String, dynamic> data) async {
    await _dioClient.post(ApiConstants.appointments, data: data);
  }

  @override
  Future<void> updateAppointmentStatus(int id, String status) async {
    await _dioClient.put(
      '${ApiConstants.appointments}/$id',
      data: {'status': status},
    );
  }

  @override
  Future<void> rescheduleAppointment(int id, Map<String, dynamic> data) async {
    await _dioClient.put('${ApiConstants.appointments}/$id', data: data);
  }
}
