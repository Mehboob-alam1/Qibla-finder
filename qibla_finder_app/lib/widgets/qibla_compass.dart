import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../theme/app_theme.dart';

class QiblaCompass extends StatelessWidget {
  const QiblaCompass({
    super.key,
    required this.roseAngle,
    required this.needleAngle,
    required this.aligned,
    required this.locked,
    this.headingLabel,
    this.qiblaLabel,
    this.placeLabel,
  });

  final double roseAngle;
  final double needleAngle;
  final bool aligned;
  final bool locked;
  final String? headingLabel;
  final String? qiblaLabel;
  final String? placeLabel;

  @override
  Widget build(BuildContext context) {
    return AspectRatio(
      aspectRatio: 1,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 240),
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          boxShadow: [
            BoxShadow(
              color: (aligned ? AppColors.gold : AppColors.forest).withValues(alpha: 0.28),
              blurRadius: aligned ? 28 : 16,
              spreadRadius: aligned ? 2 : 0,
            ),
          ],
        ),
        child: Stack(
          alignment: Alignment.center,
          children: [
            CustomPaint(size: Size.infinite, painter: _BezelPainter()),
            AnimatedRotation(
              turns: roseAngle / 360,
              duration: const Duration(milliseconds: 160),
              curve: Curves.easeOut,
              child: CustomPaint(size: Size.infinite, painter: _RosePainter()),
            ),
            AnimatedRotation(
              turns: needleAngle / 360,
              duration: Duration(milliseconds: locked ? 240 : 160),
              curve: locked ? Curves.easeOutCubic : Curves.easeOut,
              child: CustomPaint(size: Size.infinite, painter: _NeedlePainter(aligned: aligned)),
            ),
            Container(
              width: 118,
              height: 118,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: AppColors.ink.withValues(alpha: 0.72),
                border: Border.all(color: AppColors.gold.withValues(alpha: 0.45)),
              ),
              padding: const EdgeInsets.all(10),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    placeLabel ?? '—',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    textAlign: TextAlign.center,
                    style: const TextStyle(color: AppColors.goldSoft, fontSize: 11),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    headingLabel ?? '—°',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 20,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  Text(
                    qiblaLabel ?? 'Qibla',
                    style: const TextStyle(color: AppColors.goldSoft, fontSize: 11),
                  ),
                ],
              ),
            ),
            const Align(
              alignment: Alignment.topCenter,
              child: Padding(
                padding: EdgeInsets.only(top: 6),
                child: Icon(Icons.arrow_drop_down, color: AppColors.gold, size: 36),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _BezelPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final center = size.center(Offset.zero);
    final radius = size.shortestSide / 2;
    final bezel = Paint()
      ..shader = const SweepGradient(
        colors: [Color(0xFF8B7018), AppColors.gold, AppColors.goldSoft, AppColors.gold, Color(0xFF8B7018)],
      ).createShader(Rect.fromCircle(center: center, radius: radius));
    canvas.drawCircle(center, radius, bezel);
    canvas.drawCircle(
      center,
      radius * 0.92,
      Paint()
        ..shader = const RadialGradient(
          colors: [Color(0xFF245C45), Color(0xFF06140F)],
        ).createShader(Rect.fromCircle(center: center, radius: radius * 0.92)),
    );
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class _RosePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final center = size.center(Offset.zero);
    final radius = size.shortestSide / 2 * 0.86;
    final tick = Paint()
      ..color = AppColors.goldSoft
      ..strokeWidth = 1.4
      ..strokeCap = StrokeCap.round;

    for (var i = 0; i < 72; i++) {
      final angle = i * 5 * math.pi / 180 - math.pi / 2;
      final major = i % 18 == 0;
      final medium = i % 6 == 0;
      final inner = radius * (major ? 0.74 : (medium ? 0.80 : 0.84));
      final outer = radius * 0.90;
      canvas.drawLine(
        Offset(center.dx + math.cos(angle) * inner, center.dy + math.sin(angle) * inner),
        Offset(center.dx + math.cos(angle) * outer, center.dy + math.sin(angle) * outer),
        tick..strokeWidth = major ? 2.6 : (medium ? 1.8 : 1.0),
      );
    }

    const labels = {'N': 0.0, 'E': 90.0, 'S': 180.0, 'W': 270.0};
    final textPainter = TextPainter(textDirection: TextDirection.ltr);
    labels.forEach((label, deg) {
      final angle = deg * math.pi / 180 - math.pi / 2;
      textPainter.text = TextSpan(
        text: label,
        style: TextStyle(
          color: label == 'N' ? AppColors.gold : AppColors.goldSoft,
          fontSize: label == 'N' ? 22 : 16,
          fontWeight: FontWeight.w700,
        ),
      );
      textPainter.layout();
      final pos = Offset(
        center.dx + math.cos(angle) * radius * 0.62 - textPainter.width / 2,
        center.dy + math.sin(angle) * radius * 0.62 - textPainter.height / 2,
      );
      textPainter.paint(canvas, pos);
    });
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class _NeedlePainter extends CustomPainter {
  _NeedlePainter({required this.aligned});

  final bool aligned;

  @override
  void paint(Canvas canvas, Size size) {
    final center = size.center(Offset.zero);
    final radius = size.shortestSide / 2 * 0.58;
    final path = Path()
      ..moveTo(center.dx, center.dy - radius)
      ..lineTo(center.dx + 11, center.dy + 8)
      ..lineTo(center.dx, center.dy - 8)
      ..lineTo(center.dx - 11, center.dy + 8)
      ..close();

    canvas.drawPath(
      path,
      Paint()..color = aligned ? AppColors.goldSoft : AppColors.gold,
    );

    final kaaba = TextPainter(
      text: const TextSpan(
        text: 'ك',
        style: TextStyle(color: AppColors.ink, fontSize: 18, fontWeight: FontWeight.w700),
      ),
      textDirection: TextDirection.rtl,
    )..layout();
    kaaba.paint(canvas, Offset(center.dx - kaaba.width / 2, center.dy - radius + 18));
  }

  @override
  bool shouldRepaint(covariant _NeedlePainter oldDelegate) => oldDelegate.aligned != aligned;
}
