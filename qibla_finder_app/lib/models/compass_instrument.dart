import 'package:flutter/material.dart';

/// Selectable compass artwork with separate dial + live needle layers.
class CompassInstrument {
  const CompassInstrument({
    required this.id,
    required this.name,
    required this.dialAsset,
    this.roseOnly = false,
    required this.centerCoverColor,
    required this.needleColor,
    required this.needleTailColor,
    required this.needleAlignedColor,
    required this.cardinalColor,
    required this.cardinalNorthColor,
    required this.hubFill,
    required this.hubBorder,
    required this.hubText,
  });

  final String id;
  final String name;
  final String dialAsset;
  final bool roseOnly;
  final Color centerCoverColor;
  final Color needleColor;
  final Color needleTailColor;
  final Color needleAlignedColor;
  final Color cardinalColor;
  final Color cardinalNorthColor;
  final Color hubFill;
  final Color hubBorder;
  final Color hubText;

  static const all = [
    CompassInstrument(
      id: 'rose',
      name: 'Gold rose',
      dialAsset: 'assets/compasses/compass_01_rose.png',
      roseOnly: true,
      centerCoverColor: Color(0xFF000000),
      needleColor: Color(0xFFC3B081),
      needleTailColor: Color(0xFF6B6355),
      needleAlignedColor: Color(0xFFE8D9A8),
      cardinalColor: Color(0xFFC3B081),
      cardinalNorthColor: Color(0xFFE8D9A8),
      hubFill: Color(0xE6000000),
      hubBorder: Color(0xFFC3B081),
      hubText: Color(0xFFF4F1EC),
    ),
    CompassInstrument(
      id: 'architectural',
      name: 'Architectural',
      dialAsset: 'assets/compasses/compass_02_architectural.png',
      centerCoverColor: Color(0xFF1A3D32),
      needleColor: Color(0xFFD4BC8B),
      needleTailColor: Color(0xFF2D5A48),
      needleAlignedColor: Color(0xFFF0E0B8),
      cardinalColor: Color(0xFFD4BC8B),
      cardinalNorthColor: Color(0xFFF0E0B8),
      hubFill: Color(0xE61A3D32),
      hubBorder: Color(0xFFD4BC8B),
      hubText: Color(0xFFF4F1EC),
    ),
    CompassInstrument(
      id: 'precision',
      name: 'Precision',
      dialAsset: 'assets/compasses/compass_03_precision.png',
      centerCoverColor: Color(0xFFF5F0E6),
      needleColor: Color(0xFF6B4E2E),
      needleTailColor: Color(0xFFB8B0A6),
      needleAlignedColor: Color(0xFF8B6914),
      cardinalColor: Color(0xFF6B4E2E),
      cardinalNorthColor: Color(0xFF8B6914),
      hubFill: Color(0xE6F5F0E6),
      hubBorder: Color(0xFF6B4E2E),
      hubText: Color(0xFF2A2218),
    ),
    CompassInstrument(
      id: 'celestial',
      name: 'Celestial',
      dialAsset: 'assets/compasses/compass_04_celestial.png',
      centerCoverColor: Color(0xFF0A1628),
      needleColor: Color(0xFF7EC8E3),
      needleTailColor: Color(0xFF1E3A5F),
      needleAlignedColor: Color(0xFFB8F0FF),
      cardinalColor: Color(0xFF9AB4C8),
      cardinalNorthColor: Color(0xFFB8F0FF),
      hubFill: Color(0xE60A1628),
      hubBorder: Color(0xFF7EC8E3),
      hubText: Color(0xFFEEF6FF),
    ),
    CompassInstrument(
      id: 'emerald',
      name: 'Emerald',
      dialAsset: 'assets/compasses/compass_05_emerald.png',
      centerCoverColor: Color(0xFF1B4D3A),
      needleColor: Color(0xFFD4AF6A),
      needleTailColor: Color(0xFF2A6B52),
      needleAlignedColor: Color(0xFFF0D890),
      cardinalColor: Color(0xFFD4AF6A),
      cardinalNorthColor: Color(0xFFF0D890),
      hubFill: Color(0xE61B4D3A),
      hubBorder: Color(0xFFD4AF6A),
      hubText: Color(0xFFF4F1EC),
    ),
    CompassInstrument(
      id: 'ivory',
      name: 'Ivory',
      dialAsset: 'assets/compasses/compass_06_ivory.png',
      centerCoverColor: Color(0xFFF8F4EC),
      needleColor: Color(0xFF6B4E2E),
      needleTailColor: Color(0xFFC8B8A0),
      needleAlignedColor: Color(0xFF8B6914),
      cardinalColor: Color(0xFF6B4E2E),
      cardinalNorthColor: Color(0xFF8B6914),
      hubFill: Color(0xE6F8F4EC),
      hubBorder: Color(0xFF6B4E2E),
      hubText: Color(0xFF2A2218),
    ),
    CompassInstrument(
      id: 'midnight',
      name: 'Midnight',
      dialAsset: 'assets/compasses/compass_07_midnight.png',
      centerCoverColor: Color(0xFF0C1A2E),
      needleColor: Color(0xFF5EC4E8),
      needleTailColor: Color(0xFF1A3050),
      needleAlignedColor: Color(0xFF9AE8FF),
      cardinalColor: Color(0xFF8AAEC8),
      cardinalNorthColor: Color(0xFF9AE8FF),
      hubFill: Color(0xE60C1A2E),
      hubBorder: Color(0xFF5EC4E8),
      hubText: Color(0xFFE8F4FF),
    ),
    CompassInstrument(
      id: 'jade',
      name: 'Jade',
      dialAsset: 'assets/compasses/compass_08_jade.png',
      centerCoverColor: Color(0xFFE8F5F0),
      needleColor: Color(0xFF1B8C86),
      needleTailColor: Color(0xFF7FD4C8),
      needleAlignedColor: Color(0xFF0D6B62),
      cardinalColor: Color(0xFF1B8C86),
      cardinalNorthColor: Color(0xFF0D6B62),
      hubFill: Color(0xE6E8F5F0),
      hubBorder: Color(0xFF1B8C86),
      hubText: Color(0xFF0D3B2E),
    ),
  ];

  static int get count => all.length;

  static CompassInstrument at(int index) => all[index.clamp(0, all.length - 1)];
}
