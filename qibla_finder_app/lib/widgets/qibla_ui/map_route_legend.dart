import 'package:flutter/material.dart';

import '../../services/qibla_math.dart';
import '../../theme/app_theme.dart';

class MapRouteLegend extends StatelessWidget {
  const MapRouteLegend({
    super.key,
    required this.distanceKm,
    required this.bearing,
    required this.aligned,
    required this.onShowRoute,
    required this.onRecenter,
  });

  final double distanceKm;
  final double bearing;
  final bool aligned;
  final VoidCallback onShowRoute;
  final VoidCallback onRecenter;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(14, 12, 14, 12),
      decoration: BoxDecoration(
        color: const Color(0xFF0D131A).withValues(alpha: 0.94),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: aligned ? AppColors.gold.withValues(alpha: 0.5) : AppColors.line),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.4), blurRadius: 16, offset: const Offset(0, 6)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            children: [
              _LegendSwatch(color: aligned ? AppColors.gold : AppColors.teal),
              const SizedBox(width: 8),
              const Expanded(
                child: Text(
                  'Great-circle path to the Kaaba',
                  style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12, color: AppColors.text),
                ),
              ),
              Text(
                QiblaMath.cardinal(bearing),
                style: TextStyle(
                  fontWeight: FontWeight.w800,
                  fontSize: 11,
                  color: aligned ? AppColors.gold : AppColors.teal,
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: onShowRoute,
                  icon: const Icon(Icons.public, size: 16),
                  label: const Text('Full route'),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppColors.text,
                    side: const BorderSide(color: AppColors.line),
                    padding: const EdgeInsets.symmetric(vertical: 10),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: FilledButton.icon(
                  onPressed: onRecenter,
                  icon: const Icon(Icons.navigation, size: 16),
                  label: const Text('Follow me'),
                  style: FilledButton.styleFrom(
                    backgroundColor: AppColors.teal,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 10),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _LegendSwatch extends StatelessWidget {
  const _LegendSwatch({required this.color});

  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 28,
      height: 4,
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(2),
        boxShadow: [BoxShadow(color: color.withValues(alpha: 0.45), blurRadius: 6)],
      ),
    );
  }
}
