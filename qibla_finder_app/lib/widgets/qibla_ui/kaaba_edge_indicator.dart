import 'dart:math' as math;
import 'dart:ui' as ui;

import 'package:flutter/material.dart';

import '../../services/qibla_math.dart';
import '../../theme/app_theme.dart';
import 'kaaba_icon.dart';

/// Kaaba badge pinned to the screen edge when Mecca is off the visible map.
class KaabaEdgeIndicator extends StatelessWidget {
  const KaabaEdgeIndicator({
    super.key,
    required this.qiblaBearing,
    required this.heading,
    required this.distanceKm,
  });

  final double qiblaBearing;
  final double? heading;
  final double distanceKm;

  @override
  Widget build(BuildContext context) {
    final h = heading;
    if (h == null) {
      return const SizedBox.shrink();
    }

    final deltaDeg = QiblaMath.shortestDelta(h, qiblaBearing);
    final angle = deltaDeg * math.pi / 180;

    return LayoutBuilder(
      builder: (context, constraints) {
        final size = Size(constraints.maxWidth, constraints.maxHeight);
        const inset = 52.0;
        final center = Offset(size.width / 2, size.height / 2);
        final edge = _rayToRectEdge(center, angle, size, inset);

        return IgnorePointer(
          child: CustomPaint(
            size: size,
            painter: _QiblaEdgeLinePainter(center: center, edge: edge),
            child: Stack(
              clipBehavior: Clip.none,
              children: [
                Positioned(
                  left: edge.dx - 28,
                  top: edge.dy - 28,
                  child: _KaabaEdgeBadge(distanceKm: distanceKm),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  /// [angle] radians: 0 = up, clockwise positive (screen space).
  static Offset _rayToRectEdge(Offset center, double angle, Size size, double inset) {
    final dx = math.sin(angle);
    final dy = -math.cos(angle);
    final maxW = size.width / 2 - inset;
    final maxH = size.height / 2 - inset;

    var t = double.infinity;
    if (dx.abs() > 1e-6) {
      t = math.min(t, (dx > 0 ? maxW : -maxW) / dx);
    }
    if (dy.abs() > 1e-6) {
      t = math.min(t, (dy > 0 ? maxH : -maxH) / dy);
    }
    if (!t.isFinite || t <= 0) {
      t = math.min(maxW, maxH);
    }
    return center + Offset(dx * t, dy * t);
  }
}

class _QiblaEdgeLinePainter extends CustomPainter {
  _QiblaEdgeLinePainter({required this.center, required this.edge});

  final Offset center;
  final Offset edge;

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..shader = ui.Gradient.linear(
        center,
        edge,
        [AppColors.gold.withValues(alpha: 0.08), AppColors.gold.withValues(alpha: 0.65)],
      )
      ..strokeWidth = 3
      ..strokeCap = StrokeCap.round;
    canvas.drawLine(center, edge, paint);
  }

  @override
  bool shouldRepaint(covariant _QiblaEdgeLinePainter oldDelegate) =>
      oldDelegate.center != center || oldDelegate.edge != edge;
}

class _KaabaEdgeBadge extends StatelessWidget {
  const _KaabaEdgeBadge({required this.distanceKm});

  final double distanceKm;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 56,
      height: 56,
      decoration: BoxDecoration(
        color: const Color(0xFF0D131A).withValues(alpha: 0.92),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.gold, width: 1.5),
        boxShadow: [
          BoxShadow(color: AppColors.gold.withValues(alpha: 0.35), blurRadius: 14),
          BoxShadow(color: Colors.black.withValues(alpha: 0.45), blurRadius: 8),
        ],
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const KaabaIcon(size: 26, shadow: false),
          const SizedBox(height: 2),
          Text(
            '${distanceKm.round()} km',
            style: const TextStyle(fontSize: 8, fontWeight: FontWeight.w700, color: AppColors.goldSoft),
          ),
        ],
      ),
    );
  }
}
