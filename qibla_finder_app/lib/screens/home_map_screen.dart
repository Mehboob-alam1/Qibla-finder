import 'package:flutter/material.dart';

import '../services/qibla_math.dart';
import '../state/qibla_state.dart';
import '../theme/app_theme.dart';
import '../widgets/place_search_field.dart';
import '../widgets/qibla_map.dart';
import '../widgets/qibla_ui/map_chrome.dart';
import '../widgets/qibla_ui/map_local_inset.dart';

class HomeMapScreen extends StatefulWidget {
  const HomeMapScreen({super.key, required this.state});

  final QiblaState state;

  @override
  State<HomeMapScreen> createState() => _HomeMapScreenState();
}

class _HomeMapScreenState extends State<HomeMapScreen> {
  final _mapKey = GlobalKey<QiblaMapViewState>();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!widget.state.hasLocation && !widget.state.locating) {
        widget.state.locate(userGesture: false);
      }
    });
  }

  void _toggleMapMode() {
    final map = _mapKey.currentState;
    if (map == null) {
      return;
    }
    if (map.isFollowingCompass) {
      map.showRouteOverview();
    } else {
      map.recenterOnUser();
    }
  }

  @override
  Widget build(BuildContext context) {
    final bottomNavClearance = 88.0 + MediaQuery.paddingOf(context).bottom;

    return ListenableBuilder(
      listenable: widget.state,
      builder: (context, _) {
        final snapshot = widget.state.snapshot;
        final hasLocation = snapshot != null;

        final lat = snapshot?.latitude ?? QiblaMath.kaabaLat;
        final lng = snapshot?.longitude ?? QiblaMath.kaabaLng;
        final bearing = snapshot?.bearing ?? 0.0;

        return Stack(
          fit: StackFit.expand,
          children: [
            QiblaMapView(
              key: _mapKey,
              latitude: lat,
              longitude: lng,
              qiblaBearing: bearing,
              heading: hasLocation ? widget.state.heading : null,
              aligned: hasLocation && widget.state.aligned,
              hasUserLocation: hasLocation,
            ),
            if (!hasLocation)
              Positioned(
                left: 16,
                right: 16,
                bottom: bottomNavClearance,
                child: _LocationPromptCard(
                  locating: widget.state.locating,
                  onEnableLocation: () => widget.state.locate(),
                  onSearchCity: () => showPlaceSearchSheet(
                    context: context,
                    onSelected: widget.state.setPlace,
                    onUseLocation: () => widget.state.locate(),
                  ),
                ),
              ),
            if (hasLocation) ...[
              Positioned(
                left: 12,
                bottom: bottomNavClearance,
                child: MapLocalInset(
                  latitude: lat,
                  longitude: lng,
                  qiblaBearing: bearing,
                  heading: widget.state.heading,
                ),
              ),
              Positioned(
                right: 14,
                bottom: bottomNavClearance + 24,
                child: Column(
                  children: [
                    QiblaMapFab(
                      icon: Icons.layers_outlined,
                      onPressed: _toggleMapMode,
                    ),
                    const SizedBox(height: 12),
                    QiblaMapFab(
                      icon: Icons.gps_fixed,
                      loading: widget.state.locating,
                      onPressed: widget.state.locating
                          ? null
                          : () {
                              widget.state.locate();
                              _mapKey.currentState?.recenterOnUser();
                            },
                    ),
                    const SizedBox(height: 12),
                    QiblaMapFab(
                      icon: Icons.mosque_outlined,
                      onPressed: () => _mapKey.currentState?.showRouteOverview(),
                    ),
                  ],
                ),
              ),
            ],
          ],
        );
      },
    );
  }
}

class _LocationPromptCard extends StatelessWidget {
  const _LocationPromptCard({
    required this.locating,
    required this.onEnableLocation,
    required this.onSearchCity,
  });

  final bool locating;
  final VoidCallback onEnableLocation;
  final VoidCallback onSearchCity;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF0D131A).withValues(alpha: 0.92),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.line),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.35), blurRadius: 16, offset: const Offset(0, 6)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        mainAxisSize: MainAxisSize.min,
        children: [
          const Text(
            'Map ready · Mecca',
            style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: AppColors.text),
          ),
          const SizedBox(height: 6),
          const Text(
            'Turn on location or choose a city to see your Qibla line and distance to the Kaaba on the map.',
            style: AppTextStyles.body,
          ),
          const SizedBox(height: 14),
          FilledButton.icon(
            style: FilledButton.styleFrom(
              backgroundColor: AppColors.teal,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 12),
            ),
            onPressed: locating ? null : onEnableLocation,
            icon: locating
                ? const SizedBox(
                    width: 18,
                    height: 18,
                    child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                  )
                : const Icon(Icons.my_location),
            label: Text(locating ? 'Finding you…' : 'Use my location'),
          ),
          TextButton(onPressed: onSearchCity, child: const Text('Pick a city')),
        ],
      ),
    );
  }
}
