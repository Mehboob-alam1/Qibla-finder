import 'package:flutter/material.dart';

import '../../theme/app_theme.dart';

class ArPrayerMatPainter extends CustomPainter {
  ArPrayerMatPainter({this.fill = const Color(0xFF0F5C4E), this.archFill = const Color(0xFF0A3C33)});

  final Color fill;
  final Color archFill;

  @override
  void paint(Canvas canvas, Size size) {
    final w = size.width;
    final h = size.height;
    final matPath = Path()
      ..moveTo(w * 0.05, h * 0.9)
      ..lineTo(w * 0.95, h * 0.9)
      ..lineTo(w * 0.79, h * 0.1)
      ..lineTo(w * 0.21, h * 0.1)
      ..close();
    canvas.drawPath(matPath, Paint()..color = fill);
    canvas.drawPath(
      matPath,
      Paint()
        ..color = AppColors.gold
        ..style = PaintingStyle.stroke
        ..strokeWidth = 4,
    );

    final archPath = Path()
      ..moveTo(w * 0.37, h * 0.62)
      ..quadraticBezierTo(w * 0.5, h * 0.19, w * 0.63, h * 0.62)
      ..close();
    canvas.drawPath(archPath, Paint()..color = archFill);
    canvas.drawPath(
      archPath,
      Paint()
        ..color = AppColors.gold
        ..style = PaintingStyle.stroke
        ..strokeWidth = 2,
    );

    canvas.drawLine(
      Offset(w * 0.5, h * 0.19),
      Offset(w * 0.5, h * 0.09),
      Paint()
        ..color = AppColors.gold
        ..strokeWidth = 2,
    );
    canvas.drawOval(
      Rect.fromCenter(center: Offset(w * 0.5, h * 0.08), width: 18, height: 12),
      Paint()..color = AppColors.gold,
    );
  }

  @override
  bool shouldRepaint(covariant ArPrayerMatPainter oldDelegate) =>
      oldDelegate.fill != fill || oldDelegate.archFill != archFill;
}

class ArGridPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = const Color(0xFF161D24).withValues(alpha: 0.35)
      ..strokeWidth = 1;
    for (var x = 0.0; x < size.width; x += 38) {
      canvas.drawLine(Offset(x, 0), Offset(x, size.height), paint);
    }
    for (var y = 0.0; y < size.height; y += 38) {
      canvas.drawLine(Offset(0, y), Offset(size.width, y), paint);
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class ArSideFab extends StatelessWidget {
  const ArSideFab({super.key, required this.icon, this.onPressed});

  final IconData icon;
  final VoidCallback? onPressed;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: const Color(0xFF0D131A),
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        onTap: onPressed,
        borderRadius: BorderRadius.circular(14),
        child: Container(
          width: 44,
          height: 44,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppColors.line),
          ),
          child: Icon(icon, size: 19, color: AppColors.text),
        ),
      ),
    );
  }
}

class ArStatBar extends StatelessWidget {
  const ArStatBar({
    super.key,
    required this.compass,
    required this.qibla,
    required this.distance,
  });

  final String compass;
  final String qibla;
  final String distance;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 14),
      decoration: BoxDecoration(
        color: const Color(0xFF0D131A).withValues(alpha: 0.85),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.line),
      ),
      child: Row(
        children: [
          _ArStat(value: compass, label: 'COMPASS'),
          _ArStat(value: qibla, label: 'QIBLA', gold: true),
          _ArStat(value: distance, label: 'KAABA'),
        ],
      ),
    );
  }
}

class _ArStat extends StatelessWidget {
  const _ArStat({required this.value, required this.label, this.gold = false});

  final String value;
  final String label;
  final bool gold;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Column(
        children: [
          Text(
            value,
            style: TextStyle(
              fontWeight: FontWeight.w700,
              fontSize: 15,
              color: gold ? AppColors.gold : AppColors.text,
            ),
          ),
          const SizedBox(height: 2),
          Text(label, style: AppTextStyles.label),
        ],
      ),
    );
  }
}
