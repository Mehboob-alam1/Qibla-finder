import 'package:flutter/material.dart';

/// Stylized prayer mat drawn over the AR camera view.
class PrayerMatOverlay extends StatelessWidget {
  const PrayerMatOverlay({super.key, required this.color, this.size = 200});

  final Color color;
  final double size;

  @override
  Widget build(BuildContext context) {
    return CustomPaint(
      size: Size(size, size * 1.35),
      painter: _MatPainter(color: color),
    );
  }
}

class _MatPainter extends CustomPainter {
  _MatPainter({required this.color});

  final Color color;

  @override
  void paint(Canvas canvas, Size size) {
    final w = size.width;
    final h = size.height;
    final body = RRect.fromRectAndRadius(
      Rect.fromLTWH(w * 0.08, h * 0.12, w * 0.84, h * 0.76),
      const Radius.circular(8),
    );
    canvas.drawRRect(body, Paint()..color = color);
    canvas.drawRRect(
      body,
      Paint()
        ..color = Colors.white.withValues(alpha: 0.35)
        ..style = PaintingStyle.stroke
        ..strokeWidth = 2,
    );

    final archPath = Path()
      ..moveTo(w * 0.22, h * 0.12)
      ..quadraticBezierTo(w * 0.5, h * 0.02, w * 0.78, h * 0.12);
    canvas.drawPath(
      archPath,
      Paint()
        ..color = color.withValues(alpha: 0.85)
        ..style = PaintingStyle.stroke
        ..strokeWidth = 3,
    );

    for (var i = 0; i < 5; i++) {
      final y = h * (0.35 + i * 0.1);
      canvas.drawLine(
        Offset(w * 0.2, y),
        Offset(w * 0.8, y),
        Paint()
          ..color = Colors.white.withValues(alpha: 0.25)
          ..strokeWidth = 1.5,
      );
    }

    canvas.drawCircle(
      Offset(w * 0.5, h * 0.55),
      w * 0.12,
      Paint()
        ..color = Colors.white.withValues(alpha: 0.2)
        ..style = PaintingStyle.stroke
        ..strokeWidth = 2,
    );
  }

  @override
  bool shouldRepaint(covariant _MatPainter oldDelegate) => oldDelegate.color != color;
}

const prayerMatColors = [
  Color(0xFF1A6B4A),
  Color(0xFF1565C0),
  Color(0xFF6A1B9A),
  Color(0xFFBF360C),
  Color(0xFF37474F),
  Color(0xFFC9A227),
  Color(0xFF00838F),
  Color(0xFFAD1457),
];
