import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../models/compass_instrument.dart';
import '../state/qibla_state.dart';
import '../theme/app_theme.dart';
import '../widgets/instrument_qibla_compass.dart';

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
        final styleIndex = state.settings.compassStyleIndex.clamp(0, CompassInstrument.count - 1);
        final instrument = CompassInstrument.at(styleIndex);
        final roseAngle = state.roseAngleForDisplay(heading);

        return SafeArea(
          bottom: false,
          child: LayoutBuilder(
            builder: (context, constraints) {
              final compassSize =
                  math.min(constraints.maxWidth - 40, constraints.maxHeight * 0.46).clamp(240.0, 320.0);

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
                            style: const TextStyle(fontWeight: FontWeight.w700, color: AppColors.text, fontSize: 15),
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
                    child: InstrumentQiblaCompass(
                      instrument: instrument,
                      roseAngle: roseAngle,
                      needleAngle: state.needleAngle,
                      aligned: state.aligned,
                      locked: state.locked,
                      headingLabel: heading == null ? '—°' : '${heading.round()}°',
                      qiblaLabel: snapshot == null ? 'Qibla' : '${snapshot.bearing.toStringAsFixed(0)}°',
                      placeLabel: state.place?.name,
                    ),
                  ),
                  const SizedBox(height: 16),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 24),
                    child: Row(
                      children: [
                        Expanded(
                          child: _MetricText(
                            label: 'Heading',
                            value: heading == null ? '—' : '${heading.round()}°',
                          ),
                        ),
                        Expanded(
                          child: _MetricText(
                            label: 'Qibla',
                            value: snapshot == null ? '—' : '${snapshot.bearing.toStringAsFixed(1)}°',
                            emphasized: true,
                          ),
                        ),
                        Expanded(
                          child: _MetricText(
                            label: 'Distance',
                            value: snapshot == null ? '—' : '${snapshot.distanceKm.toStringAsFixed(0)} km',
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 18),
                  const Text('COMPASS STYLE', style: AppTextStyles.label),
                  const SizedBox(height: 10),
                  SizedBox(
                    height: 92,
                    child: ListView.separated(
                      scrollDirection: Axis.horizontal,
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      itemCount: CompassInstrument.count,
                      separatorBuilder: (_, __) => const SizedBox(width: 10),
                      itemBuilder: (context, i) {
                        final active = i == styleIndex;
                        final item = CompassInstrument.at(i);
                        return GestureDetector(
                          onTap: () => state.setCompassStyleIndex(i),
                          child: Column(
                            children: [
                              AnimatedContainer(
                                duration: const Duration(milliseconds: 200),
                                width: 56,
                                height: 56,
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  border: Border.all(color: active ? AppColors.gold : AppColors.line, width: active ? 2.5 : 1),
                                  boxShadow: active
                                      ? [BoxShadow(color: AppColors.gold.withValues(alpha: 0.35), blurRadius: 12)]
                                      : null,
                                ),
                                child: ClipOval(
                                  child: Image.asset(item.dialAsset, fit: BoxFit.cover),
                                ),
                              ),
                              const SizedBox(height: 6),
                              SizedBox(
                                width: 72,
                                child: Text(
                                  item.name,
                                  textAlign: TextAlign.center,
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: active ? FontWeight.w800 : FontWeight.w600,
                                    color: active ? AppColors.gold : AppColors.sub,
                                  ),
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
        );
      },
    );
  }
}

class _MetricText extends StatelessWidget {
  const _MetricText({
    required this.label,
    required this.value,
    this.emphasized = false,
  });

  final String label;
  final String value;
  final bool emphasized;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(
          value,
          textAlign: TextAlign.center,
          style: TextStyle(
            color: emphasized ? AppColors.gold : AppColors.text,
            fontSize: emphasized ? 18 : 16,
            fontWeight: FontWeight.w800,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          label.toUpperCase(),
          textAlign: TextAlign.center,
          style: AppTextStyles.label,
        ),
      ],
    );
  }
}
