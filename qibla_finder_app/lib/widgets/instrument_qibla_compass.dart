import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../models/compass_instrument.dart';
import '../theme/app_theme.dart';
import 'qibla_ui/kaaba_icon.dart';

/// Compass dial rotates with device heading; needle points to Qibla on top.
class InstrumentQiblaCompass extends StatelessWidget {
  const InstrumentQiblaCompass({
    super.key,
    required this.instrument,
    required this.roseAngle,
    required this.needleAngle,
    required this.aligned,
    required this.locked,
    this.headingLabel,
    this.qiblaLabel,
    this.placeLabel,
    this.lightHub = false,
  });

  final CompassInstrument instrument;
  final double roseAngle;
  final double needleAngle;
  final bool aligned;
  final bool locked;
  final String? headingLabel;
  final String? qiblaLabel;
  final String? placeLabel;
  final bool lightHub;

  @override
  Widget build(BuildContext context) {
    return AspectRatio(
      aspectRatio: 1,
      child: LayoutBuilder(
        builder: (context, constraints) {
          final size = constraints.maxWidth;
          return Stack(
            alignment: Alignment.center,
            clipBehavior: Clip.none,
            children: [
              AnimatedRotation(
                turns: roseAngle / 360,
                duration: const Duration(milliseconds: 160),
                curve: Curves.easeOut,
                child: SizedBox(
                  width: size,
                  height: size,
                  child: Stack(
                    fit: StackFit.expand,
                    children: [
                      if (!instrument.roseOnly)
                        Image.asset(
                          instrument.dialAsset,
                          fit: BoxFit.contain,
                          filterQuality: FilterQuality.high,
                        )
                      else
                        Image.asset(
                          instrument.dialAsset,
                          fit: BoxFit.contain,
                          filterQuality: FilterQuality.high,
                        ),
                      if (instrument.roseOnly)
                        CustomPaint(
                          painter: _CardinalRosePainter(
                            northColor: instrument.cardinalNorthColor,
                            otherColor: instrument.cardinalColor,
                          ),
                        ),
                      if (!instrument.roseOnly)
                        CustomPaint(
                          painter: _CenterMaskPainter(
                            color: instrument.centerCoverColor,
                            radiusFactor: 0.39,
                          ),
                        ),
                    ],
                  ),
                ),
              ),
              AnimatedRotation(
                turns: needleAngle / 360,
                duration: Duration(milliseconds: locked ? 240 : 160),
                curve: locked ? Curves.easeOutCubic : Curves.easeOut,
                child: CustomPaint(
                  size: Size(size, size),
                  painter: _QiblaNeedlePainter(
                    primary: aligned ? instrument.needleAlignedColor : instrument.needleColor,
                    tail: instrument.needleTailColor,
                    aligned: aligned,
                  ),
                ),
              ),
              Container(
                width: size * 0.34,
                height: size * 0.34,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: lightHub ? Colors.white.withValues(alpha: 0.94) : instrument.hubFill,
                  border: Border.all(
                    color: lightHub ? Colors.white : instrument.hubBorder.withValues(alpha: 0.75),
                    width: lightHub ? 2 : 1,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.25),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                padding: EdgeInsets.all(size * 0.04),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    KaabaIcon(size: size * 0.09, shadow: false),
                    SizedBox(height: size * 0.02),
                    if (!lightHub) ...[
                      Text(
                        placeLabel ?? '—',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        textAlign: TextAlign.center,
                        style: TextStyle(color: instrument.hubText.withValues(alpha: 0.85), fontSize: size * 0.034),
                      ),
                      Text(
                        qiblaLabel ?? 'Qibla',
                        style: TextStyle(
                          color: instrument.hubText,
                          fontSize: size * 0.075,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      Text(
                        headingLabel ?? '—°',
                        style: TextStyle(
                          color: instrument.hubText.withValues(alpha: 0.75),
                          fontSize: size * 0.038,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ] else
                      Text(
                        qiblaLabel ?? 'Qibla',
                        style: TextStyle(
                          color: const Color(0xFF1A2332),
                          fontSize: size * 0.08,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                  ],
                ),
              ),
              Align(
                alignment: Alignment.topCenter,
                child: Padding(
                  padding: EdgeInsets.only(top: size * 0.01),
                  child: Icon(
                    Icons.arrow_drop_down,
                    color: lightHub ? Colors.white : (aligned ? AppColors.gold : instrument.cardinalNorthColor),
                    size: size * 0.12,
                  ),
                ),
              ),
            ],
          );
        },
      ),
    );
  }
}

class _CenterMaskPainter extends CustomPainter {
  _CenterMaskPainter({required this.color, required this.radiusFactor});

  final Color color;
  final double radiusFactor;

  @override
  void paint(Canvas canvas, Size size) {
    final center = size.center(Offset.zero);
    canvas.drawCircle(center, size.shortestSide * radiusFactor, Paint()..color = color);
  }

  @override
  bool shouldRepaint(covariant _CenterMaskPainter oldDelegate) =>
      oldDelegate.color != color || oldDelegate.radiusFactor != radiusFactor;
}

class _CardinalRosePainter extends CustomPainter {
  _CardinalRosePainter({required this.northColor, required this.otherColor});

  final Color northColor;
  final Color otherColor;

  @override
  void paint(Canvas canvas, Size size) {
    final center = size.center(Offset.zero);
    final radius = size.shortestSide * 0.42;
    const labels = {'N': 0.0, 'E': 90.0, 'S': 180.0, 'W': 270.0};
    final tp = TextPainter(textDirection: TextDirection.ltr);

    labels.forEach((label, deg) {
      final angle = deg * math.pi / 180 - math.pi / 2;
      tp.text = TextSpan(
        text: label,
        style: TextStyle(
          color: label == 'N' ? northColor : otherColor,
          fontSize: label == 'N' ? 20 : 15,
          fontWeight: FontWeight.w800,
        ),
      );
      tp.layout();
      tp.paint(
        canvas,
        Offset(
          center.dx + math.cos(angle) * radius - tp.width / 2,
          center.dy + math.sin(angle) * radius - tp.height / 2,
        ),
      );
    });
  }

  @override
  bool shouldRepaint(covariant _CardinalRosePainter oldDelegate) => false;
}

class _QiblaNeedlePainter extends CustomPainter {
  _QiblaNeedlePainter({
    required this.primary,
    required this.tail,
    required this.aligned,
  });

  final Color primary;
  final Color tail;
  final bool aligned;

  @override
  void paint(Canvas canvas, Size size) {
    final center = size.center(Offset.zero);
    final len = size.shortestSide * 0.38;

    final tip = Path()
      ..moveTo(center.dx, center.dy - len)
      ..lineTo(center.dx + size.shortestSide * 0.045, center.dy + size.shortestSide * 0.04)
      ..lineTo(center.dx, center.dy - size.shortestSide * 0.02)
      ..lineTo(center.dx - size.shortestSide * 0.045, center.dy + size.shortestSide * 0.04)
      ..close();

    canvas.drawShadow(tip, Colors.black54, aligned ? 6 : 3, true);
    canvas.drawPath(tip, Paint()..color = primary);

    final tailPath = Path()
      ..moveTo(center.dx, center.dy + len * 0.55)
      ..lineTo(center.dx + size.shortestSide * 0.028, center.dy + size.shortestSide * 0.08)
      ..lineTo(center.dx, center.dy + size.shortestSide * 0.02)
      ..lineTo(center.dx - size.shortestSide * 0.028, center.dy + size.shortestSide * 0.08)
      ..close();
    canvas.drawPath(tailPath, Paint()..color = tail.withValues(alpha: 0.85));

    canvas.drawCircle(
      center,
      size.shortestSide * 0.028,
      Paint()
        ..color = Colors.white.withValues(alpha: 0.9)
        ..style = PaintingStyle.fill,
    );
    canvas.drawCircle(
      center,
      size.shortestSide * 0.028,
      Paint()
        ..color = primary
        ..style = PaintingStyle.stroke
        ..strokeWidth = 2,
    );
  }

  @override
  bool shouldRepaint(covariant _QiblaNeedlePainter oldDelegate) =>
      oldDelegate.primary != primary || oldDelegate.tail != tail || oldDelegate.aligned != aligned;
}
