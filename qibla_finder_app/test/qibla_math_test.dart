import 'package:flutter_test/flutter_test.dart';
import 'package:qibla_finder_app/services/qibla_math.dart';

void main() {
  test('New York qibla bearing and distance match the website math', () {
    const lat = 40.7128;
    const lng = -74.0060;
    final bearing = QiblaMath.bearing(lat, lng);
    final km = QiblaMath.distanceKm(lat, lng);

    expect(bearing, closeTo(58.4817, 0.01));
    expect(km, closeTo(10306.3, 0.5));
    expect(QiblaMath.cardinal(bearing), 'ENE');
  });

  test('Kaaba location has a near-zero distance', () {
    expect(QiblaMath.distanceKm(QiblaMath.kaabaLat, QiblaMath.kaabaLng), closeTo(0, 0.02));
  });

  test('shortest delta wraps across north', () {
    expect(QiblaMath.shortestDelta(350, 10), closeTo(20, 0.001));
    expect(QiblaMath.shortestDelta(10, 350), closeTo(-20, 0.001));
  });
}
