import 'package:flutter/material.dart';

import '../services/qibla_math.dart';
import '../theme/app_theme.dart';
import 'qibla_ui/kaaba_icon.dart';

/// Horizontal degree strip centered on [centerHeading], with Kaaba markers at Qibla.
class DegreeRulerBar extends StatelessWidget {
  const DegreeRulerBar({
    super.key,
    required this.centerHeading,
    required this.qiblaBearing,
    this.pxPerDegree = 2.0,
    this.height = 56,
  });

  final double centerHeading;
  final double qiblaBearing;
  final double pxPerDegree;
  final double height;

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final width = constraints.maxWidth.isFinite ? constraints.maxWidth : 300.0;
        final centerX = width / 2;
        final heading = QiblaMath.normalize(centerHeading);
        final kaabaPositions = <double>[];

        for (final offset in [-360.0, 0.0, 360.0]) {
          final bearing = qiblaBearing + offset;
          final delta = QiblaMath.shortestDelta(heading, bearing);
          final x = centerX + delta * pxPerDegree;
          if (x >= -24 && x <= width + 24) {
            kaabaPositions.add(x);
          }
        }

        return SizedBox(
          width: width,
          height: height,
          child: Stack(
            clipBehavior: Clip.none,
            children: [
              CustomPaint(
                size: Size(width, height),
                painter: _RulerViewportPainter(
                  centerHeading: centerHeading,
                  pxPerDegree: pxPerDegree,
                ),
              ),
              for (final x in kaabaPositions)
                Positioned(
                  left: x - 16,
                  top: height / 2 - 18,
                  child: const KaabaIcon(size: 32),
                ),
            ],
          ),
        );
      },
    );
  }
}

class _RulerViewportPainter extends CustomPainter {
  _RulerViewportPainter({
    required this.centerHeading,
    required this.pxPerDegree,
  });

  final double centerHeading;
  final double pxPerDegree;

  @override
  void paint(Canvas canvas, Size size) {
    final centerX = size.width / 2;
    final heading = QiblaMath.normalize(centerHeading);

    _drawBackground(canvas, size);
    _drawTicks(canvas, size, centerX, heading);
    _drawCenterReticle(canvas, size, centerX);
  }

  void _drawBackground(Canvas canvas, Size size) {
    final rect = Offset.zero & size;
    canvas.drawRRect(
      RRect.fromRectAndRadius(rect, const Radius.circular(10)),
      Paint()..color = Colors.black.withValues(alpha: 0.35),
    );
  }

  void _drawTicks(Canvas canvas, Size size, double centerX, double heading) {
    final tickPaint = Paint()
      ..color = Colors.white70
      ..strokeWidth = 1.2;

    final halfSpan = centerX / pxPerDegree + 10;
    final startDeg = (heading - halfSpan).floor();
    final endDeg = (heading + halfSpan).ceil();

    for (var d = startDeg; d <= endDeg; d += 5) {
      final deg = QiblaMath.normalize(d.toDouble());
      final delta = QiblaMath.shortestDelta(heading, deg);
      final x = centerX + delta * pxPerDegree;
      if (x < -24 || x > size.width + 24) {
        continue;
      }

      final major = deg % 30 == 0;
      final h = major ? 22.0 : 12.0;
      final baseY = size.height - 8;
      canvas.drawLine(Offset(x, baseY - h), Offset(x, baseY), tickPaint);

      if (major) {
        final tp = TextPainter(
          text: TextSpan(
            text: '${deg.round()}°',
            style: const TextStyle(
              color: Colors.white,
              fontSize: 10,
              fontWeight: FontWeight.w600,
            ),
          ),
          textDirection: TextDirection.ltr,
        )..layout();
        tp.paint(canvas, Offset(x - tp.width / 2, 6));
      }
    }
  }

  void _drawCenterReticle(Canvas canvas, Size size, double centerX) {
    canvas.drawLine(
      Offset(centerX, 0),
      Offset(centerX, size.height),
      Paint()
        ..color = AppColors.gold
        ..strokeWidth = 2,
    );
    canvas.drawCircle(
      Offset(centerX, size.height - 4),
      3,
      Paint()..color = AppColors.gold,
    );
  }

  @override
  bool shouldRepaint(covariant _RulerViewportPainter oldDelegate) =>
      oldDelegate.centerHeading != centerHeading || oldDelegate.pxPerDegree != pxPerDegree;
}
