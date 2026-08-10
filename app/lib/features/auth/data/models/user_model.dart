class UserModel {
  final int id;
  final String name;
  final String phone;
  final String? email;
  final String? avatar;
  final String? role;

  UserModel({
    required this.id,
    required this.name,
    required this.phone,
    this.email,
    this.avatar,
    this.role,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    String? resolvedRole;
    if (json['role'] != null) {
      resolvedRole = json['role'].toString();
    } else if (json['roles'] is List && (json['roles'] as List).isNotEmpty) {
      resolvedRole = (json['roles'] as List).first.toString();
    }

    return UserModel(
      id: json['id'],
      name: json['name'],
      phone: json['phone'] ?? '',
      email: json['email'],
      avatar: json['avatar'],
      role: resolvedRole,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'phone': phone,
      'email': email,
      'avatar': avatar,
      'role': role,
    };
  }
}
