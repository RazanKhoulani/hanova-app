class PatientModel {
  final int id;
  final String name;
  final String? email;
  final String? phone;
  final String? gender;
  final DateTime? dateOfBirth;

  PatientModel({
    required this.id,
    required this.name,
    this.email,
    this.phone,
    this.gender,
    this.dateOfBirth,
  });

  factory PatientModel.fromJson(Map<String, dynamic> json) {
    return PatientModel(
      id: json['id'],
      name: json['name'],
      email: json['email'],
      phone: json['phone'],
      gender: json['gender'],
      dateOfBirth: json['dob'] != null ? DateTime.parse(json['dob']) : null,
    );
  }
}

class AppointmentModel {
  final int id;
  final int patientId;
  final String patientName;
  final DateTime appointmentDate;
  final String appointmentType;
  final int durationMinutes;
  final String status;
  final String statusLabel;
  final String? notes;

  AppointmentModel({
    required this.id,
    required this.patientId,
    required this.patientName,
    required this.appointmentDate,
    this.appointmentType = 'treatment',
    this.durationMinutes = 30,
    required this.status,
    this.statusLabel = '',
    this.notes,
  });

  factory AppointmentModel.fromJson(Map<String, dynamic> json) {
    final String? appointmentDateString = json['appointment_date']?.toString();
    DateTime parsedDate;
    if (appointmentDateString != null && appointmentDateString.isNotEmpty) {
      parsedDate = DateTime.tryParse(appointmentDateString) ?? DateTime.now();
    } else {
      final date = json['date']?.toString() ?? '';
      final time = json['time']?.toString() ?? '00:00';
      parsedDate = DateTime.tryParse('$date $time') ?? DateTime.now();
    }

    return AppointmentModel(
      id: json['id'],
      patientId: json['patient_id'] ?? json['patient']?['id'] ?? 0,
      patientName:
          json['patient_name'] ?? json['patient']?['name'] ?? 'Unknown Patient',
      appointmentDate: parsedDate,
      appointmentType: json['appointment_type']?.toString() ?? 'treatment',
      durationMinutes:
          int.tryParse(json['duration_minutes']?.toString() ?? '') ?? 30,
      status: json['status']?.toString() ?? 'pending',
      statusLabel: json['status_label']?.toString() ?? '',
      notes: json['notes'],
    );
  }
}

class AvailableTimeSlotsModel {
  final int doctorId;
  final String doctorName;
  final String date;
  final String type;
  final String appointmentType;
  final int durationMinutes;
  final bool isClosed;
  final String? unavailableReason;
  final String? message;
  final List<String> slots;

  AvailableTimeSlotsModel({
    required this.doctorId,
    required this.doctorName,
    required this.date,
    required this.type,
    required this.appointmentType,
    required this.durationMinutes,
    required this.isClosed,
    this.unavailableReason,
    this.message,
    required this.slots,
  });

  factory AvailableTimeSlotsModel.fromJson(Map<String, dynamic> json) {
    final rawSlots = json['slots'];
    final slots = rawSlots is List
        ? rawSlots.map((slot) => slot.toString()).toList()
        : <String>[];

    return AvailableTimeSlotsModel(
      doctorId: int.tryParse(json['doctor_id']?.toString() ?? '') ?? 0,
      doctorName: json['doctor_name']?.toString() ?? '',
      date: json['date']?.toString() ?? '',
      type: json['type']?.toString() ?? 'clinic',
      appointmentType: json['appointment_type']?.toString() ?? 'treatment',
      durationMinutes:
          int.tryParse(json['duration_minutes']?.toString() ?? '') ?? 30,
      isClosed:
          json['is_closed'] == true || json['is_closed']?.toString() == '1',
      unavailableReason: json['unavailable_reason']?.toString(),
      message: json['message']?.toString(),
      slots: slots,
    );
  }
}
