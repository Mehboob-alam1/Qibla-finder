import 'package:flutter/material.dart';

import '../../theme/app_theme.dart';

class SettingsRowData {
  SettingsRowData({
    required this.icon,
    required this.label,
    this.trailingText,
    this.chevron = false,
    this.trailingSwitch,
    this.onSwitch,
    this.onTap,
  });

  final IconData icon;
  final String label;
  final String? trailingText;
  final bool chevron;
  final bool? trailingSwitch;
  final ValueChanged<bool>? onSwitch;
  final VoidCallback? onTap;
}

class SettingsGroup extends StatelessWidget {
  const SettingsGroup({super.key, required this.rows});

  final List<SettingsRowData> rows;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.uiCard,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.line),
      ),
      child: Column(
        children: List.generate(rows.length, (i) {
          final r = rows[i];
          final isLast = i == rows.length - 1;
          return Material(
            color: Colors.transparent,
            child: InkWell(
              onTap: r.onTap,
              borderRadius: BorderRadius.vertical(
                top: i == 0 ? const Radius.circular(16) : Radius.zero,
                bottom: isLast ? const Radius.circular(16) : Radius.zero,
              ),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                decoration: BoxDecoration(
                  border: isLast ? null : const Border(bottom: BorderSide(color: AppColors.line)),
                ),
                child: Row(
                  children: [
                    Icon(r.icon, size: 18, color: AppColors.sub),
                    const SizedBox(width: 12),
                    Expanded(child: Text(r.label, style: AppTextStyles.body)),
                    if (r.trailingText != null)
                      Padding(
                        padding: const EdgeInsets.only(right: 6),
                        child: Text(r.trailingText!, style: const TextStyle(fontSize: 12, color: AppColors.sub)),
                      ),
                    if (r.trailingSwitch != null)
                      Switch(value: r.trailingSwitch!, onChanged: r.onSwitch)
                    else if (r.chevron)
                      const Icon(Icons.chevron_right, size: 18, color: Color(0xFF4A5866)),
                  ],
                ),
              ),
            ),
          );
        }),
      ),
    );
  }
}
