import 'package:flutter/material.dart';

import '../services/qibla_math.dart';
import '../widgets/qibla_map.dart';

class MapScreen extends StatelessWidget {
  const MapScreen({
    super.key,
    required this.latitude,
    required this.longitude,
    this.label,
  });

  final double latitude;
  final double longitude;
  final String? label;

  @override
  Widget build(BuildContext context) {
    final snapshot = QiblaMath.snapshot(latitude, longitude);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Map'),
      ),
      body: Stack(
        children: [
          QiblaMapView(latitude: latitude, longitude: longitude),
          Positioned(
            left: 16,
            right: 16,
            bottom: 24,
            child: Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      label ?? 'Your location',
                      style: const TextStyle(fontWeight: FontWeight.w700),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Qibla ${snapshot.bearing.toStringAsFixed(1)}° ${snapshot.cardinal}  ·  ${snapshot.distanceKm.toStringAsFixed(1)} km to Kaaba',
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
