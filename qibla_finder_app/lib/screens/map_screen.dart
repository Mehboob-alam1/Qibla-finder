import 'package:flutter/material.dart';

import '../state/qibla_state.dart';
import '../theme/app_theme.dart';
import '../widgets/qibla_map.dart';

class MapScreen extends StatelessWidget {
  const MapScreen({super.key, required this.state});

  final QiblaState state;

  @override
  Widget build(BuildContext context) {
    return ListenableBuilder(
      listenable: state,
      builder: (context, _) {
        final snapshot = state.snapshot;
        if (snapshot == null) {
          return const Scaffold(
            backgroundColor: AppColors.bg,
            body: Center(child: Text('No location set', style: AppTextStyles.body)),
          );
        }

        return Scaffold(
          backgroundColor: AppColors.bg,
          appBar: AppBar(title: const Text('Map')),
          body: QiblaMapView(
            latitude: snapshot.latitude,
            longitude: snapshot.longitude,
            qiblaBearing: snapshot.bearing,
            heading: state.heading,
            aligned: state.aligned,
          ),
        );
      },
    );
  }
}
