import 'dart:math' as math;

import 'package:flutter/material.dart';
import 'package:flutter/scheduler.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';

import '../services/qibla_math.dart';
import '../theme/app_theme.dart';
import 'qibla_ui/kaaba_edge_indicator.dart';
import 'qibla_ui/map_pin_widgets.dart';
import 'qibla_ui/map_tile_config.dart';

enum _MapCameraMode { followUser, routeOverview, userGesture }

/// Heading-up map with smooth compass tracking and street-first zoom.
class QiblaMapView extends StatefulWidget {
  const QiblaMapView({
    super.key,
    required this.latitude,
    required this.longitude,
    required this.qiblaBearing,
    this.heading,
    this.aligned = false,
    this.interactive = true,
    this.hasUserLocation = true,
  });

  final double latitude;
  final double longitude;
  final double qiblaBearing;
  final double? heading;
  final bool aligned;
  final bool interactive;
  final bool hasUserLocation;

  @override
  State<QiblaMapView> createState() => QiblaMapViewState();
}

class QiblaMapViewState extends State<QiblaMapView> with SingleTickerProviderStateMixin {
  static const _streetZoom = 16.5;
  static const _kaabaPreviewZoom = 4.5;
  static const _smoothFollowId = 'qiblaSmoothFollow';
  static const _routeBlue = Color(0xFF4285F4);

  /// True when the map rotates with the phone compass (heading-up).
  bool get isFollowingCompass =>
      _mode == _MapCameraMode.followUser && widget.hasUserLocation && widget.heading != null;

  final _mapController = MapController();
  static final _kaaba = LatLng(QiblaMath.kaabaLat, QiblaMath.kaabaLng);

  _MapCameraMode _mode = _MapCameraMode.followUser;
  var _kaabaOnScreen = true;
  var _tileLoadFailed = false;
  var _visualStyle = MapVisualStyle.streets;

  late Ticker _followTicker;
  var _currentLat = QiblaMath.kaabaLat;
  var _currentLng = QiblaMath.kaabaLng;
  var _currentZoom = _streetZoom;
  var _currentRotation = 0.0;
  var _targetLat = QiblaMath.kaabaLat;
  var _targetLng = QiblaMath.kaabaLng;
  var _targetZoom = _streetZoom;
  var _targetRotation = 0.0;

  double get distanceKm => QiblaMath.distanceKm(widget.latitude, widget.longitude);

  LatLng get _user => LatLng(widget.latitude, widget.longitude);

  @override
  void initState() {
    super.initState();
    _syncTargetsFromWidget();
    _currentLat = _targetLat;
    _currentLng = _targetLng;
    _currentZoom = _targetZoom;
    _currentRotation = _targetRotation;

    _followTicker = createTicker(_onFollowTick)..start();

    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) {
        return;
      }
      if (widget.hasUserLocation) {
        _mode = _MapCameraMode.followUser;
        _syncTargetsFromWidget();
        _pushCameraInstant();
      } else {
        _pushCameraInstant();
      }
      _updateZoomStyle(_mapController.camera.zoom);
      _refreshKaabaVisibility();
    });
  }

  @override
  void dispose() {
    _followTicker.dispose();
    _mapController.dispose();
    super.dispose();
  }

  @override
  void didUpdateWidget(covariant QiblaMapView oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (!oldWidget.hasUserLocation && widget.hasUserLocation) {
      _mode = _MapCameraMode.followUser;
      _syncTargetsFromWidget();
      _pushCameraInstant();
    }
    if (!widget.hasUserLocation) {
      _syncTargetsFromWidget();
      return;
    }
    if (_mode == _MapCameraMode.userGesture) {
      return;
    }
    final moved = oldWidget.latitude != widget.latitude || oldWidget.longitude != widget.longitude;
    final headingChanged = oldWidget.heading != widget.heading;
    if (_mode == _MapCameraMode.followUser && (moved || headingChanged)) {
      _syncTargetsFromWidget();
      if (moved) {
        _pushCameraInstant();
      }
      return;
    }
    if (_mode == _MapCameraMode.routeOverview && moved) {
      _syncTargetsFromWidget();
    }
  }

  void _syncTargetsFromWidget() {
    if (!widget.hasUserLocation) {
      _targetLat = QiblaMath.kaabaLat;
      _targetLng = QiblaMath.kaabaLng;
      _targetZoom = _kaabaPreviewZoom;
      _targetRotation = 0;
      return;
    }
    _targetLat = widget.latitude;
    _targetLng = widget.longitude;
    _targetZoom = _mode == _MapCameraMode.routeOverview ? _mapController.camera.zoom : _streetZoom;
    _targetRotation = _mode == _MapCameraMode.routeOverview ? 0 : -(widget.heading ?? 0);
  }

  void _onFollowTick(Duration elapsed) {
    if (!mounted || !widget.hasUserLocation || _mode != _MapCameraMode.followUser) {
      return;
    }

    const smoothPos = 0.22;
    const smoothRot = 0.58;
    _currentLat += (_targetLat - _currentLat) * smoothPos;
    _currentLng += (_targetLng - _currentLng) * smoothPos;
    _currentZoom += (_targetZoom - _currentZoom) * smoothPos;
    _currentRotation += _shortestRotationDelta(_currentRotation, _targetRotation) * smoothRot;

    _mapController.moveAndRotate(
      LatLng(_currentLat, _currentLng),
      _currentZoom,
      _currentRotation,
      id: _smoothFollowId,
    );
    _updateZoomStyle(_currentZoom);
    _refreshKaabaVisibility();
    setState(() {});
  }

  double _shortestRotationDelta(double from, double to) {
    var delta = to - from;
    while (delta > 180) {
      delta -= 360;
    }
    while (delta < -180) {
      delta += 360;
    }
    return delta;
  }

  void _pushCameraInstant() {
    _currentLat = _targetLat;
    _currentLng = _targetLng;
    _currentZoom = _targetZoom;
    _currentRotation = _targetRotation;
    _mapController.moveAndRotate(
      LatLng(_currentLat, _currentLng),
      _currentZoom,
      _currentRotation,
      id: _smoothFollowId,
    );
  }

  Future<void> showRouteOverview({bool animated = true}) async {
    _mode = _MapCameraMode.routeOverview;
    _targetRotation = 0;
    _fitUserAndKaaba();
    _refreshKaabaVisibility();
  }

  Future<void> recenterOnUser({bool animated = true}) async {
    _mode = _MapCameraMode.followUser;
    _targetZoom = _streetZoom;
    _syncTargetsFromWidget();
    if (!animated) {
      _pushCameraInstant();
    }
    _refreshKaabaVisibility();
  }

  void _fitUserAndKaaba() {
    final bounds = _userKaabaBounds();
    _mapController.fitCamera(
      CameraFit.bounds(
        bounds: bounds,
        padding: const EdgeInsets.all(72),
      ),
    );
    _mapController.rotate(0);
    _currentRotation = 0;
    _targetRotation = 0;
    _currentLat = _mapController.camera.center.latitude;
    _currentLng = _mapController.camera.center.longitude;
    _currentZoom = _mapController.camera.zoom;
    _targetLat = _currentLat;
    _targetLng = _currentLng;
    _targetZoom = _currentZoom;
    _updateZoomStyle(_currentZoom);
    _refreshKaabaVisibility();
  }

  LatLngBounds _userKaabaBounds() {
    final south = math.min(widget.latitude, QiblaMath.kaabaLat);
    final north = math.max(widget.latitude, QiblaMath.kaabaLat);
    final west = math.min(widget.longitude, QiblaMath.kaabaLng);
    final east = math.max(widget.longitude, QiblaMath.kaabaLng);
    const pad = 0.35;
    return LatLngBounds(
      LatLng(south - pad, west - pad),
      LatLng(north + pad, east + pad),
    );
  }

  void _updateZoomStyle(double zoom) {
    final style = mapStyleForZoom(zoom);
    if (style != _visualStyle) {
      setState(() => _visualStyle = style);
    }
  }

  void _refreshKaabaVisibility() {
    if (!mounted) {
      return;
    }
    final visible = _mapController.camera.visibleBounds.contains(_kaaba);
    if (visible != _kaabaOnScreen) {
      setState(() => _kaabaOnScreen = visible);
    }
  }

  bool _isProgrammaticEvent(MapEvent event) {
    if (event.source == MapEventSource.mapController) {
      return true;
    }
    final id = switch (event) {
      MapEventMove(:final id) => id,
      MapEventRotate(:final id) => id,
      _ => null,
    };
    return id == _smoothFollowId || (id?.startsWith(_smoothFollowId) ?? false);
  }

  void _onMapEvent(MapEvent event) {
    if (event is MapEventMove) {
      if (!_isProgrammaticEvent(event)) {
        _updateZoomStyle(event.camera.zoom);
      }
    }

    if (!widget.interactive) {
      return;
    }

    if ((event is MapEventMoveStart || event is MapEventRotateStart) && !_isProgrammaticEvent(event)) {
      _mode = _MapCameraMode.userGesture;
    }

    if (event is MapEventMoveEnd || event is MapEventRotateEnd) {
      if (_isProgrammaticEvent(event)) {
        return;
      }
      _updateZoomStyle(_mapController.camera.zoom);
      if (_mode == _MapCameraMode.userGesture && _mapController.camera.zoom <= 9.5 && widget.hasUserLocation) {
        _mode = _MapCameraMode.routeOverview;
        _fitUserAndKaaba();
      }
      _refreshKaabaVisibility();
    }
  }

  @override
  Widget build(BuildContext context) {
    final showEdgeKaaba =
        widget.hasUserLocation && !_kaabaOnScreen && _mode != _MapCameraMode.routeOverview;
    final lineColor = widget.aligned ? AppColors.gold : _routeBlue;
    final arc = widget.hasUserLocation ? qiblaArcPoints(widget.latitude, widget.longitude) : <LatLng>[];
    final tileSource = MapTileSource.forStyle(_visualStyle);
    final userArrow = widget.qiblaBearing + _currentRotation;
    final darkOverview = _visualStyle == MapVisualStyle.overview;

    final initialCenter = widget.hasUserLocation ? _user : _kaaba;
    final initialZoom = widget.hasUserLocation ? _streetZoom : _kaabaPreviewZoom;
    final initialRotation =
        widget.hasUserLocation && widget.heading != null ? -widget.heading! : _currentRotation;

    Widget map = FlutterMap(
          mapController: _mapController,
          options: MapOptions(
            initialCenter: initialCenter,
            initialZoom: initialZoom,
            initialRotation: initialRotation,
            minZoom: 3,
            maxZoom: 19,
            interactionOptions: InteractionOptions(
              flags: widget.interactive
                  ? InteractiveFlag.all & ~InteractiveFlag.rotate
                  : InteractiveFlag.none,
              pinchZoomThreshold: 0.4,
              pinchMoveThreshold: 20,
            ),
            onMapEvent: _onMapEvent,
          ),
          children: [
            AnimatedSwitcher(
              duration: const Duration(milliseconds: 380),
              child: TileLayer(
                key: ValueKey(_visualStyle),
                urlTemplate: tileSource.urlTemplate,
                subdomains: tileSource.subdomains,
                userAgentPackageName: 'io.qiblafinders.qibla_finder_app',
                maxNativeZoom: tileSource.maxNativeZoom,
                tileDisplay: const TileDisplay.fadeIn(
                  duration: Duration(milliseconds: 200),
                  startOpacity: 0,
                  reloadStartOpacity: 0,
                ),
                errorTileCallback: (tile, error, stackTrace) {
                  if (mounted && !_tileLoadFailed) {
                    setState(() => _tileLoadFailed = true);
                  }
                },
              ),
            ),
            if (arc.length >= 2)
              PolylineLayer(
                polylines: [
                  Polyline(
                    points: arc,
                    color: lineColor.withValues(alpha: 0.35),
                    strokeWidth: 11,
                    borderColor: Colors.black.withValues(alpha: 0.2),
                    borderStrokeWidth: 1,
                  ),
                  Polyline(
                    points: arc,
                    color: lineColor.withValues(alpha: 0.92),
                    strokeWidth: widget.aligned ? 6 : 4.5,
                  ),
                ],
              ),
            MarkerLayer(
              markers: [
                Marker(
                  point: _kaaba,
                  width: 52,
                  height: 52,
                  alignment: Alignment.center,
                  child: const KaabaMapPin(),
                ),
                if (widget.hasUserLocation)
                  Marker(
                    point: _user,
                    width: 52,
                    height: 52,
                    alignment: Alignment.center,
                    child: UserMapPin(arrowDegrees: userArrow),
                  ),
              ],
            ),
            RichAttributionWidget(
              alignment: AttributionAlignment.bottomLeft,
              attributions: [
                TextSourceAttribution(
                  tileSource.attribution,
                  onTap: () {},
                ),
              ],
            ),
          ],
        );

    if (darkOverview) {
      map = ColorFiltered(
        colorFilter: const ColorFilter.matrix([
          0.42, 0.06, 0.10, 0, -12,
          0.05, 0.46, 0.12, 0, -12,
          0.05, 0.10, 0.58, 0, -14,
          0, 0, 0, 1, 0,
        ]),
        child: map,
      );
    }

    return Stack(
      fit: StackFit.expand,
      children: [
        map,
        if (_tileLoadFailed)
          Positioned(
            left: 16,
            right: 16,
            top: 48,
            child: _TileErrorBanner(onRetry: () => setState(() => _tileLoadFailed = false)),
          ),
        if (showEdgeKaaba)
          KaabaEdgeIndicator(
            qiblaBearing: widget.qiblaBearing,
            heading: widget.heading,
            distanceKm: distanceKm,
          ),
        if (_mode == _MapCameraMode.userGesture)
          const Positioned(
            left: 16,
            bottom: 24,
            child: _MapHintChip(
              label: 'Zoom out for route overview to Kaaba',
              icon: Icons.zoom_out_map,
            ),
          ),
      ],
    );
  }
}

class _TileErrorBanner extends StatelessWidget {
  const _TileErrorBanner({required this.onRetry});

  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: const Color(0xFF0D131A).withValues(alpha: 0.94),
      borderRadius: BorderRadius.circular(12),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              'Map tiles could not load',
              style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.text),
            ),
            const SizedBox(height: 4),
            const Text(
              'Check mobile data or Wi‑Fi, then retry.',
              style: TextStyle(fontSize: 12, color: AppColors.sub),
            ),
            TextButton(onPressed: onRetry, child: const Text('Retry')),
          ],
        ),
      ),
    );
  }
}

class _MapHintChip extends StatelessWidget {
  const _MapHintChip({required this.label, required this.icon});

  final String label;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: const Color(0xFF0D131A).withValues(alpha: 0.88),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.line),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 16, color: AppColors.gold),
          const SizedBox(width: 8),
          Flexible(
            child: Text(
              label,
              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.text),
            ),
          ),
        ],
      ),
    );
  }
}

List<LatLng> qiblaArcPoints(double lat, double lng, {int segments = 96}) {
  final points = <LatLng>[];
  final lat1 = lat * math.pi / 180;
  final lng1 = lng * math.pi / 180;
  final lat2 = QiblaMath.kaabaLat * math.pi / 180;
  final lng2 = QiblaMath.kaabaLng * math.pi / 180;

  final d = 2 *
      math.asin(
        math.sqrt(
          math.pow(math.sin((lat2 - lat1) / 2), 2) +
              math.cos(lat1) * math.cos(lat2) * math.pow(math.sin((lng2 - lng1) / 2), 2),
        ),
      );

  if (d < 1e-8) {
    return [LatLng(lat, lng), LatLng(QiblaMath.kaabaLat, QiblaMath.kaabaLng)];
  }

  for (var i = 0; i <= segments; i++) {
    final f = i / segments;
    final a = math.sin((1 - f) * d) / math.sin(d);
    final b = math.sin(f * d) / math.sin(d);
    final x = a * math.cos(lat1) * math.cos(lng1) + b * math.cos(lat2) * math.cos(lng2);
    final y = a * math.cos(lat1) * math.sin(lng1) + b * math.cos(lat2) * math.sin(lng2);
    final z = a * math.sin(lat1) + b * math.sin(lat2);
    points.add(LatLng(math.atan2(z, math.sqrt(x * x + y * y)) * 180 / math.pi, math.atan2(y, x) * 180 / math.pi));
  }
  return points;
}
