import 'package:flutter/material.dart';

class AppColors {
  static const Color primary = Color(0xFFCA6D84);
  static const Color primaryLight = Color(0xFFFBEAF0);
  static const Color primaryDark = Color(0xFFA24A63);
  static const Color primarySoft = Color(0xFFFFF2F5);
  static const Color primaryMist = Color(0xFFF7DDE5);

  // Secondary/Accent colors
  static const Color secondary = Color(0xFF8E7A70);
  static const Color accent = Color(0xFFD28A5E);

  // Status colors
  static const Color success = Color(0xFF28A745);
  static const Color danger = Color(0xFFC44747);
  static const Color warning = Color(0xFFFFC107);
  static const Color info = Color(0xFF17A2B8);

  static const Color background = Color(0xFFFFFBF8);
  static const Color surface = Colors.white;
  static const Color surfaceMuted = Color(0xFFF8F2EF);
  static const Color cardShadow = Color(0x0D5F3A43);

  // Text colors
  static const Color textPrimary = Color(0xFF2B2320);
  static const Color textSecondary = Color(0xFF7B6860);
  static const Color textLight = Color(0xFFADB5BD);

  // Divider colors
  static const Color divider = Color(0xFFEBDDD7);

  static const LinearGradient brandGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [Color(0xFFD77C93), primaryDark],
  );
}
