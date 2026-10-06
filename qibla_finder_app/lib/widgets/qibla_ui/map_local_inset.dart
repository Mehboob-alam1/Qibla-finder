import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';

import 'map_pin_widgets.dart';
import 'map_tile_config.dart';

/// Circular satellite mini-map; rotates with device heading (heading-up).
class MapLocalInset extends StatefulWidget {
  const MapLocalInset({
    super.key,
    required this.latitude,
    required this.longitude,
    required this.qiblaBearing,
    this.heading,
  });

  final double latitude;
  final double longitude;
  final double qiblaBearing;
  final double? heading;

  @override
  State<MapLocalInset> createState() => _MapLocalInsetState();
}

class _MapLocalInsetState extends State<MapLocalInset> {
  static const _diameter = 118.0;
  static const _zoom = 17.2;

  final _controller = MapController();
  static const _satellite = MapTileSource.satellite;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _syncCamera());
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  void didUpdateWidget(covariant MapLocalInset oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.latitude != widget.latitude ||
        oldWidget.longitude != widget.longitude ||
        oldWidget.heading != widget.heading) {
      _syncCamera();
      setState(() {});
    }
  }

  void _syncCamera() {
    final rotation = widget.heading == null ? 0.0 : -widget.heading!;
    _controller.moveAndRotate(
      LatLng(widget.latitude, widget.longitude),
      _zoom,
      rotation,
    );
  }

  @override
  Widget build(BuildContext context) {
    final heading = widget.heading ?? 0.0;
    final arrow = widget.qiblaBearing + (widget.heading == null ? 0.0 : -heading);

    return Container(
      width: _diameter,
      height: _diameter,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        border: Border.all(color: Colors.white, width: 3),
        boxShadow: const [
          BoxShadow(color: Colors.black54, blurRadius: 14, offset: Offset(0, 4)),
        ],
      ),
      child: ClipOval(
        child: FlutterMap(
          mapController: _controller,
          options: MapOptions(
            initialCenter: LatLng(widget.latitude, widget.longitude),
            initialZoom: _zoom,
            initialRotation: widget.heading == null ? 0 : -widget.heading!,
            interactionOptions: const InteractionOptions(flags: InteractiveFlag.none),
          ),
          children: [
            TileLayer(
              urlTemplate: _satellite.urlTemplate,
              userAgentPackageName: 'io.qiblafinders.qibla_finder_app',
              maxNativeZoom: _satellite.maxNativeZoom,
              tms: _satellite.tms,
            ),
            MarkerLayer(
              markers: [
                Marker(
                  point: LatLng(widget.latitude, widget.longitude),
                  width: 42,
                  height: 42,
                  alignment: Alignment.center,
                  child: UserMapPin(arrowDegrees: arrow, compact: true),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
