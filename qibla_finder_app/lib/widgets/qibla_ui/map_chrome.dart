import 'package:flutter/material.dart';

import '../../theme/app_theme.dart';

class QiblaMapChip extends StatelessWidget {
  const QiblaMapChip({
    super.key,
    required this.title,
    required this.subtitle,
    this.aligned = false,
  });

  final String title;
  final String subtitle;
  final bool aligned;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: const Color(0xFF0D131A).withValues(alpha: 0.92),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: aligned ? AppColors.gold.withValues(alpha: 0.6) : AppColors.line),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.35),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: aligned ? AppColors.gold.withValues(alpha: 0.15) : AppColors.uiCard,
              border: Border.all(color: aligned ? AppColors.gold : AppColors.line),
            ),
            child: Icon(
              aligned ? Icons.check_rounded : Icons.explore,
              color: AppColors.gold,
              size: 22,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: AppColors.text),
                ),
                const SizedBox(height: 2),
                Text(subtitle, style: AppTextStyles.label, maxLines: 2, overflow: TextOverflow.ellipsis),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class QiblaMapFab extends StatelessWidget {
  const QiblaMapFab({super.key, required this.icon, this.onPressed, this.loading = false});

  final IconData icon;
  final VoidCallback? onPressed;
  final bool loading;

  @override
  Widget build(BuildContext context) {
    return Material(
      elevation: 6,
      shadowColor: Colors.black45,
      color: const Color(0xFF0D131A),
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        onTap: loading ? null : onPressed,
        borderRadius: BorderRadius.circular(14),
        child: Container(
          width: 48,
          height: 48,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppColors.line),
          ),
          child: loading
              ? const Padding(
                  padding: EdgeInsets.all(14),
                  child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.gold),
                )
              : Icon(icon, size: 20, color: AppColors.text),
        ),
      ),
    );
  }
}

