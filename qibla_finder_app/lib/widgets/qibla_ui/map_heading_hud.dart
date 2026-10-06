import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../../services/qibla_math.dart';
import '../../theme/app_theme.dart';

/// Fixed center reticle + Qibla bearing arc (heading-up map mode).
class MapHeadingHud extends StatelessWidget {
  const MapHeadingHud({
    super.key,
    required this.heading,
    required this.qiblaBearing,
    required this.aligned,
  });

  final double? heading;
  final double qiblaBearing;
  final bool aligned;

  @override
  Widget build(BuildContext context) {
    final h = heading;
    final delta = h == null ? null : QiblaMath.shortestDelta(h, qiblaBearing);

    return IgnorePointer(
      child: Stack(
        alignment: Alignment.center,
        children: [
          CustomPaint(
            size: const Size(220, 220),
            painter: _QiblaArcPainter(
              qiblaBearing: qiblaBearing,
              heading: h ?? 0,
              aligned: aligned,
            ),
          ),
          Icon(
            Icons.navigation_rounded,
            size: 36,
            color: aligned ? AppColors.gold : Colors.white.withValues(alpha: 0.92),
            shadows: const [Shadow(blurRadius: 12, color: Colors.black54)],
          ),
          if (delta != null && !aligned)
            Positioned(
              bottom: 0,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: Colors.black.withValues(alpha: 0.65),
                  borderRadius: BorderRadius.circular(999),
                ),
                child: Text(
                  delta > 0
                      ? 'Turn right ${delta.abs().toStringAsFixed(0)}°'
                      : 'Turn left ${delta.abs().toStringAsFixed(0)}°',
                  style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class _QiblaArcPainter extends CustomPainter {
  _QiblaArcPainter({
    required this.qiblaBearing,
    required this.heading,
    required this.aligned,
  });

  final double qiblaBearing;
  final double heading;
  final bool aligned;

  @override
  void paint(Canvas canvas, Size size) {
    final center = size.center(Offset.zero);
    final r = size.width / 2 - 8;
    final relative = QiblaMath.normalize(qiblaBearing - heading) * math.pi / 180;
    final paint = Paint()
      ..color = (aligned ? AppColors.gold : AppColors.teal).withValues(alpha: 0.85)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 3
      ..strokeCap = StrokeCap.round;

    canvas.drawArc(
      Rect.fromCircle(center: center, radius: r),
      -math.pi / 2 + relative - 0.08,
      0.16,
      false,
      paint,
    );
    final tip = center + Offset(math.sin(-math.pi / 2 + relative) * r, -math.cos(-math.pi / 2 + relative) * r);
    canvas.drawCircle(tip, 5, Paint()..color = aligned ? AppColors.gold : AppColors.teal);
  }

  @override
  bool shouldRepaint(covariant _QiblaArcPainter oldDelegate) =>
      oldDelegate.qiblaBearing != qiblaBearing ||
      oldDelegate.heading != heading ||
      oldDelegate.aligned != aligned;
}
