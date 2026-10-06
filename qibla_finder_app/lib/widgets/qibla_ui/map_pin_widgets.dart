import 'dart:math' as math;

import 'package:flutter/material.dart';

import 'kaaba_icon.dart';

/// Reference-style Kaaba callout (circular frame).
class KaabaMapPin extends StatelessWidget {
  const KaabaMapPin({super.key, this.compact = false});

  final bool compact;

  @override
  Widget build(BuildContext context) {
    final outer = compact ? 44.0 : 52.0;
    return Container(
      width: outer,
      height: outer,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: const Color(0xFF1A2332),
        border: Border.all(color: Colors.white.withValues(alpha: 0.85), width: 2),
        boxShadow: const [
          BoxShadow(color: Colors.black54, blurRadius: 8, offset: Offset(0, 3)),
        ],
      ),
      child: Center(
        child: KaabaIcon(size: compact ? 28 : 34, shadow: false),
      ),
    );
  }
}

/// Blue location dot with heading / Qibla arrow (reference map UI).
class UserMapPin extends StatelessWidget {
  const UserMapPin({
    super.key,
    required this.arrowDegrees,
    this.compact = false,
  });

  /// Arrow rotation on the map (degrees clockwise from north).
  final double arrowDegrees;
  final bool compact;

  static const _blue = Color(0xFF4285F4);

  @override
  Widget build(BuildContext context) {
    final size = compact ? 28.0 : 38.0;
    return SizedBox(
      width: size + 14,
      height: size + 14,
      child: Transform.rotate(
        angle: arrowDegrees * math.pi / 180,
        child: CustomPaint(
          size: Size(size + 14, size + 14),
          painter: _UserArrowPinPainter(diameter: size, compact: compact),
        ),
      ),
    );
  }
}

class _UserArrowPinPainter extends CustomPainter {
  _UserArrowPinPainter({required this.diameter, required this.compact});

  final double diameter;
  final bool compact;

  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height / 2 + (compact ? 2 : 3));

    final arrow = Path()
      ..moveTo(center.dx, center.dy - diameter * 0.62)
      ..lineTo(center.dx - diameter * 0.22, center.dy - diameter * 0.05)
      ..lineTo(center.dx, center.dy - diameter * 0.18)
      ..lineTo(center.dx + diameter * 0.22, center.dy - diameter * 0.05)
      ..close();
    canvas.drawPath(arrow, Paint()..color = Colors.white);

    canvas.drawCircle(
      center,
      diameter / 2,
      Paint()
        ..color = UserMapPin._blue
        ..style = PaintingStyle.fill,
    );
    canvas.drawCircle(
      center,
      diameter / 2,
      Paint()
        ..color = Colors.white
        ..style = PaintingStyle.stroke
        ..strokeWidth = compact ? 2 : 2.5,
    );
  }

  @override
  bool shouldRepaint(covariant _UserArrowPinPainter oldDelegate) =>
      oldDelegate.diameter != diameter || oldDelegate.compact != compact;
}
