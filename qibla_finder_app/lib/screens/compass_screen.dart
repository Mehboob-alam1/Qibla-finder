import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../models/compass_palette.dart';
import '../state/qibla_state.dart';
import '../theme/app_theme.dart';
import '../widgets/qibla_compass.dart';
import '../widgets/qibla_ui/metric_row.dart';

class CompassScreen extends StatelessWidget {
  const CompassScreen({super.key, required this.state});

  final QiblaState state;

  @override
  Widget build(BuildContext context) {
    return ListenableBuilder(
      listenable: state,
      builder: (context, _) {
        final snapshot = state.snapshot;
        final heading = state.heading;
        final styleIndex = state.settings.compassStyleIndex.clamp(0, CompassPalette.presets.length - 1);
        final palette = CompassPalette.presets[styleIndex];

        return Container(
          decoration: const BoxDecoration(
            gradient: RadialGradient(
              center: Alignment(0, -0.35),
              radius: 1.1,
              colors: [Color(0xFF15202B), Color(0xFF05070A)],
            ),
          ),
          child: SafeArea(
            bottom: false,
            child: LayoutBuilder(
              builder: (context, constraints) {
                final compassSize = math.min(constraints.maxWidth - 40, constraints.maxHeight * 0.46).clamp(240.0, 320.0);

                return Column(
                  children: [
                    const SizedBox(height: 8),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 20),
                      child: Row(
                        children: [
                          Expanded(
                            child: Text(
                              state.place?.label ?? 'Waiting for location…',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(fontWeight: FontWeight.w700, color: AppColors.text),
                            ),
                          ),
                          if (state.aligned)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: AppColors.gold.withValues(alpha: 0.15),
                                borderRadius: BorderRadius.circular(999),
                                border: Border.all(color: AppColors.gold.withValues(alpha: 0.5)),
                              ),
                              child: const Text(
                                'ALIGNED',
                                style: TextStyle(color: AppColors.gold, fontSize: 10, fontWeight: FontWeight.w800),
                              ),
                            ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 8),
                    SizedBox(
                      width: compassSize,
                      height: compassSize,
                      child: QiblaCompass(
                        roseAngle: state.roseAngle,
                        needleAngle: state.needleAngle,
                        aligned: state.aligned,
                        locked: state.locked,
                        headingLabel: heading == null ? '—°' : '${heading.round()}°',
                        qiblaLabel: snapshot == null ? 'Qibla' : '${snapshot.bearing.toStringAsFixed(0)}°',
                        placeLabel: state.place?.name,
                        palette: palette,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 20),
                      child: MetricRow(
                        items: [
                          MetricItem(
                            label: 'Heading',
                            value: heading == null ? '—' : '${heading.round()}°',
                          ),
                          MetricItem(
                            label: 'Qibla',
                            value: snapshot == null ? '—' : '${snapshot.bearing.toStringAsFixed(1)}°',
                            highlight: true,
                          ),
                          MetricItem(
                            label: 'Distance',
                            value: snapshot == null ? '—' : '${snapshot.distanceKm.toStringAsFixed(0)} km',
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),
                    const Text('COMPASS STYLE', style: AppTextStyles.label),
                    const SizedBox(height: 10),
                    SizedBox(
                      height: 76,
                      child: ListView.separated(
                        scrollDirection: Axis.horizontal,
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        itemCount: CompassPalette.presets.length,
                        separatorBuilder: (_, __) => const SizedBox(width: 10),
                        itemBuilder: (context, i) {
                          final active = i == styleIndex;
                          final item = CompassPalette.presets[i];
                          return GestureDetector(
                            onTap: () => state.setCompassStyleIndex(i),
                            child: Column(
                              children: [
                                AnimatedContainer(
                                  duration: const Duration(milliseconds: 200),
                                  width: 52,
                                  height: 52,
                                  decoration: BoxDecoration(
                                    shape: BoxShape.circle,
                                    gradient: RadialGradient(colors: [item.roseInner, item.roseOuter]),
                                    border: Border.all(color: active ? AppColors.gold : AppColors.line, width: active ? 2.5 : 1),
                                    boxShadow: active
                                        ? [BoxShadow(color: item.gold.withValues(alpha: 0.35), blurRadius: 12)]
                                        : null,
                                  ),
                                  child: Center(
                                    child: Container(
                                      width: 8,
                                      height: 24,
                                      decoration: BoxDecoration(
                                        color: item.needle,
                                        borderRadius: BorderRadius.circular(4),
                                      ),
                                    ),
                                  ),
                                ),
                                const SizedBox(height: 6),
                                Text(
                                  item.name,
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: active ? FontWeight.w800 : FontWeight.w600,
                                    color: active ? AppColors.gold : AppColors.sub,
                                  ),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
                    ),
                    const Spacer(),
                    Padding(
                      padding: const EdgeInsets.only(bottom: 8),
                      child: TextButton.icon(
                        onPressed: () => state.recalibrate(),
                        icon: const Icon(Icons.sync, size: 18, color: AppColors.sub),
                        label: const Text('Calibrate compass', style: TextStyle(color: AppColors.sub)),
                      ),
                    ),
                  ],
                );
              },
            ),
          ),
        );
      },
    );
  }
}
