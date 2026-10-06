/// Map tile URLs (no API keys).
enum MapVisualStyle {
  streets,
  overview,
}

MapVisualStyle mapStyleForZoom(double zoom) {
  if (zoom >= 13) {
    return MapVisualStyle.streets;
  }
  return MapVisualStyle.overview;
}

class MapTileSource {
  const MapTileSource({
    required this.urlTemplate,
    required this.attribution,
    this.subdomains = const [],
    this.maxNativeZoom = 19,
    this.tms = false,
  });

  final String urlTemplate;
  final String attribution;
  final List<String> subdomains;
  final int maxNativeZoom;
  final bool tms;

  static const _osmRaster = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';

  /// Esri World Imagery — local inset (satellite); attribution required.
  static const satellite = MapTileSource(
    urlTemplate:
        'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
    attribution: '© Esri · Maxar · Earthstar',
    maxNativeZoom: 19,
    tms: true,
  );

  static MapTileSource forStyle(MapVisualStyle style) {
    return const MapTileSource(
      urlTemplate: _osmRaster,
      attribution: '© OpenStreetMap',
      maxNativeZoom: 19,
    );
  }
}
