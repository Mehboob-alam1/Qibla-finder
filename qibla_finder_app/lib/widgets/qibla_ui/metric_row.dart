import 'package:flutter/material.dart';

import '../../theme/app_theme.dart';

class MetricRow extends StatelessWidget {
  const MetricRow({
    super.key,
    required this.items,
  });

  final List<MetricItem> items;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
      decoration: BoxDecoration(
        color: AppColors.uiCard.withValues(alpha: 0.92),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.line),
      ),
      child: Row(
        children: [
          for (var i = 0; i < items.length; i++) ...[
            if (i > 0) Container(width: 1, height: 36, color: AppColors.line),
            Expanded(child: _Cell(item: items[i])),
          ],
        ],
      ),
    );
  }
}

class MetricItem {
  const MetricItem({required this.label, required this.value, this.highlight = false});

  final String label;
  final String value;
  final bool highlight;
}

class _Cell extends StatelessWidget {
  const _Cell({required this.item});

  final MetricItem item;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(
          item.value,
          style: TextStyle(
            fontSize: 17,
            fontWeight: FontWeight.w800,
            color: item.highlight ? AppColors.gold : AppColors.text,
          ),
        ),
        const SizedBox(height: 2),
        Text(item.label.toUpperCase(), style: AppTextStyles.label),
      ],
    );
  }
}
