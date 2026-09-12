import 'dart:math' as math;

import '../models/qibla_snapshot.dart';

class QiblaMath {
  static const kaabaLat = 21.422487;
  static const kaabaLng = 39.826206;
  static const earthRadiusKm = 6371.0088;
  static const lockDegrees = 10.0;
  static const unlockDegrees = 22.0;
  static const headingSmoothing = 0.4;

  static const _cardinals = [
    'N',
    'NNE',
    'NE',
    'ENE',
    'E',
    'ESE',
    'SE',
    'SSE',
    'S',
    'SSW',
    'SW',
    'WSW',
    'W',
    'WNW',
    'NW',
    'NNW',
  ];

  static double bearing(double latitude, double longitude) {
    final lat1 = _rad(latitude);
    final lat2 = _rad(kaabaLat);
    final dLng = _rad(kaabaLng - longitude);
    final y = math.sin(dLng);
    final x = math.cos(lat1) * math.tan(lat2) - math.sin(lat1) * math.cos(dLng);
    return normalize((_deg(math.atan2(y, x)) + 360) % 360);
  }

  static double distanceKm(double latitude, double longitude) {
    final dLat = _rad(kaabaLat - latitude);
    final dLng = _rad(kaabaLng - longitude);
    final a = math.pow(math.sin(dLat / 2), 2) +
        math.cos(_rad(latitude)) * math.cos(_rad(kaabaLat)) * math.pow(math.sin(dLng / 2), 2);
    final c = 2 * math.atan2(math.sqrt(a), math.sqrt(1 - a));
    return earthRadiusKm * c;
  }

  static String cardinal(double bearingDeg) {
    final index = (bearingDeg / 22.5).round() % 16;
    return _cardinals[index];
  }

  static QiblaSnapshot snapshot(double latitude, double longitude) {
    final qibla = bearing(latitude, longitude);
    return QiblaSnapshot(
      latitude: latitude,
      longitude: longitude,
      bearing: qibla,
      cardinal: cardinal(qibla),
      distanceKm: distanceKm(latitude, longitude),
    );
  }

  static double normalize(double degrees) {
    var value = degrees % 360;
    if (value < 0) {
      value += 360;
    }
    return value;
  }

  static double shortestDelta(double from, double to) {
    var delta = normalize(to - from);
    if (delta > 180) {
      delta -= 360;
    }
    return delta;
  }

  static double smoothHeading(double current, double next) {
    return normalize(current + shortestDelta(current, next) * headingSmoothing);
  }

  static double _rad(double degrees) => degrees * math.pi / 180;
  static double _deg(double radians) => radians * 180 / math.pi;
}
