class QiblaSnapshot {
  const QiblaSnapshot({
    required this.latitude,
    required this.longitude,
    required this.bearing,
    required this.cardinal,
    required this.distanceKm,
  });

  final double latitude;
  final double longitude;
  final double bearing;
  final String cardinal;
  final double distanceKm;

  double get distanceMi => distanceKm * 0.621371;
}
