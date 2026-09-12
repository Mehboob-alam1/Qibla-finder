import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';

import '../services/qibla_math.dart';

class QiblaMapView extends StatelessWidget {
  const QiblaMapView({
    super.key,
    required this.latitude,
    required this.longitude,
    this.interactive = true,
  });

  final double latitude;
  final double longitude;
  final bool interactive;

  @override
  Widget build(BuildContext context) {
    final user = LatLng(latitude, longitude);
    const kaaba = LatLng(QiblaMath.kaabaLat, QiblaMath.kaabaLng);

    return FlutterMap(
      key: ValueKey('${latitude.toStringAsFixed(4)},${longitude.toStringAsFixed(4)},$interactive'),
      options: MapOptions(
        initialZoom: 3,
        initialCameraFit: CameraFit.bounds(
          bounds: LatLngBounds.fromPoints([user, kaaba]),
          padding: EdgeInsets.all(interactive ? 48 : 24),
        ),
        maxZoom: 18,
        interactionOptions: InteractionOptions(
          flags: interactive
              ? InteractiveFlag.pinchZoom | InteractiveFlag.drag | InteractiveFlag.doubleTapZoom
              : InteractiveFlag.none,
        ),
      ),
      children: [
        TileLayer(
          urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
          userAgentPackageName: 'io.qiblafinders.qibla_finder_app',
          maxZoom: 18,
        ),
        PolylineLayer(
          polylines: [
            Polyline(
              points: [user, kaaba],
              color: const Color(0xFF0D3B2E),
              strokeWidth: 2,
              pattern: StrokePattern.dashed(segments: const [6, 8]),
            ),
          ],
        ),
        MarkerLayer(
          markers: [
            Marker(
              point: user,
              width: 36,
              height: 36,
              alignment: Alignment.topCenter,
              child: const Icon(Icons.location_on, color: Color(0xFF1A6B4A), size: 32),
            ),
            Marker(
              point: kaaba,
              width: 16,
              height: 16,
              child: const DecoratedBox(
                decoration: BoxDecoration(
                  color: Color(0xFFC9A227),
                  shape: BoxShape.circle,
                ),
              ),
            ),
          ],
        ),
        if (interactive)
          const SimpleAttributionWidget(
            source: Text('© OpenStreetMap'),
          ),
      ],
    );
  }
}
