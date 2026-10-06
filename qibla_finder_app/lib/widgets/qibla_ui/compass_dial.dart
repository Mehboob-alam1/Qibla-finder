import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../../theme/app_theme.dart';

class QiblaCompassDial extends StatelessWidget {
  const QiblaCompassDial({
    super.key,
    required this.needleDegrees,
    required this.roseRotationDegrees,
    this.size = 270,
    this.accent = AppColors.gold,
  });

  final double needleDegrees;
  final double roseRotationDegrees;
  final double size;
  final Color accent;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: size,
      height: size,
      child: Stack(
        alignment: Alignment.center,
        children: [
          AnimatedRotation(
            turns: roseRotationDegrees / 360,
            duration: const Duration(milliseconds: 160),
            curve: Curves.easeOut,
            child: CustomPaint(
              size: Size(size, size),
              painter: QiblaRosePainter(),
              child: const Stack(
                children: [
                  Positioned(top: 14, left: 0, right: 0, child: Center(child: _Cardinal('N'))),
                  Positioned(bottom: 14, left: 0, right: 0, child: Center(child: _Cardinal('S'))),
                  Positioned(right: 14, top: 0, bottom: 0, child: Center(child: _Cardinal('E'))),
                  Positioned(left: 14, top: 0, bottom: 0, child: Center(child: _Cardinal('W'))),
                ],
              ),
            ),
          ),
          AnimatedRotation(
            turns: needleDegrees / 360,
            duration: const Duration(milliseconds: 160),
            curve: Curves.easeOut,
            child: CustomPaint(
              size: Size(size, size),
              painter: QiblaNeedlePainter(accent: accent),
            ),
          ),
          Align(
            alignment: Alignment.topCenter,
            child: Padding(
              padding: const EdgeInsets.only(top: 2),
              child: Icon(Icons.arrow_drop_down, color: accent, size: 32),
            ),
          ),
        ],
      ),
    );
  }
}

class _Cardinal extends StatelessWidget {
  const _Cardinal(this.letter);
  final String letter;

  @override
  Widget build(BuildContext context) {
    return Text(
      letter,
      style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: AppColors.sub),
    );
  }
}

class QiblaRosePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final center = size.center(Offset.zero);
    final radius = size.width / 2;

    canvas.drawCircle(center, radius, Paint()..color = const Color(0xFF121A23));
    final ringPaint = Paint()
      ..color = const Color(0xFF24313F)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1;
    canvas.drawCircle(center, radius, ringPaint);
    canvas.drawCircle(center, radius - 14, ringPaint);

    for (var i = 0; i < 36; i++) {
      final angle = (i * 10) * math.pi / 180;
      final major = i % 9 == 0;
      final tickPaint = Paint()
        ..color = major ? const Color(0xFF4A5866) : const Color(0xFF2C3947)
        ..strokeWidth = 2;
      final outer = center + Offset(math.sin(angle), -math.cos(angle)) * (radius - 6);
      final inner = center + Offset(math.sin(angle), -math.cos(angle)) * (radius - 15);
      canvas.drawLine(inner, outer, tickPaint);
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class QiblaNeedlePainter extends CustomPainter {
  QiblaNeedlePainter({required this.accent});

  final Color accent;

  @override
  void paint(Canvas canvas, Size size) {
    final center = size.center(Offset.zero);
    final radius = size.width / 2;
    final tip = center + Offset(0, -(radius - 30));
    final needlePaint = Paint()
      ..shader = LinearGradient(colors: [accent, accent.withValues(alpha: 0)])
          .createShader(Rect.fromPoints(center, tip))
      ..strokeWidth = 5
      ..strokeCap = StrokeCap.round;
    canvas.drawLine(center, tip, needlePaint);
    canvas.drawCircle(tip, 6.5, Paint()..color = accent);
  }

  @override
  bool shouldRepaint(covariant QiblaNeedlePainter oldDelegate) => oldDelegate.accent != accent;
}
