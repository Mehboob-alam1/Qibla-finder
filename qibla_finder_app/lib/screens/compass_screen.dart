import 'package:flutter/material.dart';

import '../state/qibla_state.dart';
import '../theme/app_theme.dart';
import '../widgets/qibla_compass.dart';
import '../widgets/qibla_map.dart';
import 'map_screen.dart';

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
        final theme = Theme.of(context);

        return ListView(
          padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
          children: [
            Row(
              children: [
                Icon(Icons.place_outlined, size: 18, color: theme.colorScheme.primary),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    state.place?.label ?? 'No location yet',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: theme.textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.w600),
                  ),
                ),
                if (state.aligned)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: AppColors.gold.withValues(alpha: 0.18),
                      borderRadius: BorderRadius.circular(999),
                    ),
                    child: const Text(
                      'Facing Qibla',
                      style: TextStyle(color: AppColors.gold, fontWeight: FontWeight.w700, fontSize: 12),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 16),
            Center(
              child: SizedBox(
                width: 280,
                height: 280,
                child: QiblaCompass(
                  roseAngle: state.roseAngle,
                  needleAngle: state.needleAngle,
                  aligned: state.aligned,
                  locked: state.locked,
                  headingLabel: heading == null ? '—°' : '${heading.round()}°',
                  qiblaLabel: snapshot == null ? 'Qibla' : '${snapshot.bearing.toStringAsFixed(0)}°',
                  placeLabel: state.place?.name,
                ),
              ),
            ),
            const SizedBox(height: 12),
            Text(
              'Turn until the gold marker meets the notch',
              textAlign: TextAlign.center,
              style: theme.textTheme.bodySmall?.copyWith(
                color: theme.colorScheme.onSurface.withValues(alpha: 0.6),
              ),
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () {
                      state.recalibrate();
                      showModalBottomSheet<void>(
                        context: context,
                        showDragHandle: true,
                        builder: (context) => const _CalibrateSheet(),
                      );
                    },
                    icon: const Icon(Icons.sync),
                    label: const Text('Calibrate'),
                  ),
                ),
              ],
            ),
            if (state.status != null) ...[
              const SizedBox(height: 8),
              Text(state.status!, textAlign: TextAlign.center, style: const TextStyle(color: AppColors.gold, fontSize: 13)),
            ],
            const SizedBox(height: 16),
            Card(
              child: Padding(
                padding: const EdgeInsets.symmetric(vertical: 14),
                child: Row(
                  children: [
                    _Metric(
                      label: 'Qibla',
                      value: snapshot == null ? '—' : '${snapshot.bearing.toStringAsFixed(1)}°',
                      detail: snapshot?.cardinal ?? '',
                    ),
                    _Metric(
                      label: 'Heading',
                      value: heading == null ? '—' : '${heading.round()}°',
                    ),
                    _Metric(
                      label: 'Distance',
                      value: snapshot == null ? '—' : snapshot.distanceKm.toStringAsFixed(0),
                      detail: 'km',
                    ),
                  ],
                ),
              ),
            ),
            if (snapshot != null) ...[
              const SizedBox(height: 12),
              _MapPreview(
                latitude: snapshot.latitude,
                longitude: snapshot.longitude,
                label: state.place?.label,
              ),
            ],
          ],
        );
      },
    );
  }
}

class _Metric extends StatelessWidget {
  const _Metric({required this.label, required this.value, this.detail});

  final String label;
  final String value;
  final String? detail;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Column(
        children: [
          Text(label, style: Theme.of(context).textTheme.labelSmall),
          const SizedBox(height: 4),
          Text(value, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
          if (detail != null && detail!.isNotEmpty)
            Text(detail!, style: Theme.of(context).textTheme.labelSmall),
        ],
      ),
    );
  }
}

class _CalibrateSheet extends StatelessWidget {
  const _CalibrateSheet();

  @override
  Widget build(BuildContext context) {
    return const SafeArea(
      child: Padding(
        padding: EdgeInsets.fromLTRB(24, 8, 24, 24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Calibrate compass', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
            SizedBox(height: 12),
            ListTile(dense: true, leading: Text('1'), title: Text('Hold the phone flat and level.')),
            ListTile(dense: true, leading: Text('2'), title: Text('Move it in a figure-8 several times.')),
            ListTile(dense: true, leading: Text('3'), title: Text('Keep it away from metal and magnets.')),
          ],
        ),
      ),
    );
  }
}

class _MapPreview extends StatelessWidget {
  const _MapPreview({
    required this.latitude,
    required this.longitude,
    this.label,
  });

  final double latitude;
  final double longitude;
  final String? label;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: () {
          Navigator.of(context).push(
            MaterialPageRoute<void>(
              builder: (_) => MapScreen(
                latitude: latitude,
                longitude: longitude,
                label: label,
              ),
            ),
          );
        },
        child: Ink(
          height: 148,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(16),
            child: Stack(
              children: [
                AbsorbPointer(
                  child: QiblaMapView(
                    latitude: latitude,
                    longitude: longitude,
                    interactive: false,
                  ),
                ),
                Positioned(
                  right: 10,
                  bottom: 10,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.92),
                      borderRadius: BorderRadius.circular(999),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.open_in_full, size: 14),
                        SizedBox(width: 6),
                        Text('Open map', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
