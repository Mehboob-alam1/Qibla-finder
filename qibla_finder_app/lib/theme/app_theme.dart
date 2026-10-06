import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class AppColors {
  static const ink = Color(0xFF07140F);
  static const forest = Color(0xFF0D3B2E);
  static const moss = Color(0xFF1A6B4A);
  static const gold = Color(0xFFD4A64B);
  static const goldSoft = Color(0xFFE8D48B);
  static const cream = Color(0xFFF4F1EC);
  static const sand = Color(0xFFE8E2D6);
  static const card = Color(0xFFFFFFFF);
  static const night = Color(0xFF08140F);

  // Qibla UI kit (dark screens)
  static const bg = Color(0xFF0A0E13);
  static const bg2 = Color(0xFF111820);
  static const uiCard = Color(0xFF161F29);
  static const line = Color(0xFF233040);
  static const teal = Color(0xFF1B8C86);
  static const text = Color(0xFFEEF2F6);
  static const sub = Color(0xFF8FA0B2);
  static const danger = Color(0xFFE2634E);
  static const navInactive = Color(0xFF5C6B7A);
}

class AppTextStyles {
  static const heading = TextStyle(
    fontWeight: FontWeight.w800,
    color: AppColors.text,
    letterSpacing: -0.2,
  );
  static const label = TextStyle(
    fontWeight: FontWeight.w600,
    color: AppColors.sub,
    fontSize: 11,
    letterSpacing: 0.4,
  );
  static const body = TextStyle(color: AppColors.text, fontSize: 13.5);
}

class AppTheme {
  static ThemeData light() {
    final base = ThemeData(
      useMaterial3: true,
      brightness: Brightness.light,
      colorScheme: ColorScheme.fromSeed(
        seedColor: AppColors.forest,
        primary: AppColors.forest,
        secondary: AppColors.gold,
        surface: AppColors.cream,
      ),
    );
    return base.copyWith(
      scaffoldBackgroundColor: AppColors.cream,
      textTheme: GoogleFonts.plusJakartaSansTextTheme(base.textTheme).apply(
        bodyColor: AppColors.ink,
        displayColor: AppColors.ink,
      ),
      appBarTheme: const AppBarTheme(
        backgroundColor: AppColors.cream,
        foregroundColor: AppColors.ink,
        elevation: 0,
        scrolledUnderElevation: 0.5,
        centerTitle: true,
        titleTextStyle: TextStyle(
          fontSize: 18,
          fontWeight: FontWeight.w700,
          color: AppColors.ink,
        ),
      ),
      cardTheme: CardThemeData(
        color: AppColors.card,
        elevation: 0,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      ),
    );
  }

  static ThemeData dark() {
    final base = ThemeData(
      useMaterial3: true,
      brightness: Brightness.dark,
      scaffoldBackgroundColor: AppColors.bg,
      primaryColor: AppColors.gold,
      colorScheme: const ColorScheme.dark(
        primary: AppColors.gold,
        secondary: AppColors.teal,
        surface: AppColors.uiCard,
      ),
    );
    return base.copyWith(
      scaffoldBackgroundColor: AppColors.bg,
      textTheme: GoogleFonts.plusJakartaSansTextTheme(base.textTheme).apply(
        bodyColor: AppColors.text,
        displayColor: AppColors.text,
      ),
      appBarTheme: const AppBarTheme(
        backgroundColor: AppColors.bg,
        foregroundColor: AppColors.text,
        elevation: 0,
        centerTitle: true,
        titleTextStyle: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: AppColors.text),
      ),
      cardTheme: CardThemeData(
        color: AppColors.uiCard,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: AppColors.line),
        ),
      ),
      switchTheme: SwitchThemeData(
        thumbColor: WidgetStateProperty.resolveWith((states) => Colors.white),
        trackColor: WidgetStateProperty.resolveWith(
          (states) => states.contains(WidgetState.selected) ? AppColors.teal : AppColors.line,
        ),
      ),
    );
  }
}
