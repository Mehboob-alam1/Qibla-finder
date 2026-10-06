import 'package:flutter/material.dart';

import '../theme/app_theme.dart';

class CompassPalette {
  const CompassPalette({
    required this.name,
    required this.gold,
    required this.goldSoft,
    required this.roseInner,
    required this.roseOuter,
    required this.needle,
    required this.needleAligned,
    required this.glow,
  });

  final String name;
  final Color gold;
  final Color goldSoft;
  final Color roseInner;
  final Color roseOuter;
  final Color needle;
  final Color needleAligned;
  final Color glow;

  static const presets = [
    CompassPalette(
      name: 'Classic',
      gold: AppColors.gold,
      goldSoft: AppColors.goldSoft,
      roseInner: Color(0xFF245C45),
      roseOuter: Color(0xFF06140F),
      needle: AppColors.gold,
      needleAligned: AppColors.goldSoft,
      glow: AppColors.forest,
    ),
    CompassPalette(
      name: 'Ocean',
      gold: Color(0xFF4FC3F7),
      goldSoft: Color(0xFFB3E5FC),
      roseInner: Color(0xFF1565C0),
      roseOuter: Color(0xFF0A1929),
      needle: Color(0xFF29B6F6),
      needleAligned: Color(0xFF81D4FA),
      glow: Color(0xFF0277BD),
    ),
    CompassPalette(
      name: 'Ruby',
      gold: Color(0xFFE57373),
      goldSoft: Color(0xFFFFCDD2),
      roseInner: Color(0xFF8E2430),
      roseOuter: Color(0xFF1A0508),
      needle: Color(0xFFEF5350),
      needleAligned: Color(0xFFFF8A80),
      glow: Color(0xFFB71C1C),
    ),
    CompassPalette(
      name: 'Amethyst',
      gold: Color(0xFFCE93D8),
      goldSoft: Color(0xFFE1BEE7),
      roseInner: Color(0xFF6A1B9A),
      roseOuter: Color(0xFF12041A),
      needle: Color(0xFFAB47BC),
      needleAligned: Color(0xFFEA80FC),
      glow: Color(0xFF4A148C),
    ),
    CompassPalette(
      name: 'Sunset',
      gold: Color(0xFFFFB74D),
      goldSoft: Color(0xFFFFE0B2),
      roseInner: Color(0xFFBF360C),
      roseOuter: Color(0xFF1A0A04),
      needle: Color(0xFFFF9800),
      needleAligned: Color(0xFFFFCC80),
      glow: Color(0xFFE65100),
    ),
    CompassPalette(
      name: 'Emerald',
      gold: Color(0xFF69F0AE),
      goldSoft: Color(0xFFB9F6CA),
      roseInner: Color(0xFF1B5E20),
      roseOuter: Color(0xFF041208),
      needle: Color(0xFF00E676),
      needleAligned: Color(0xFF69F0AE),
      glow: Color(0xFF2E7D32),
    ),
  ];
}
