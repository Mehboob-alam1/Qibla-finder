import 'package:flutter_test/flutter_test.dart';
import 'package:qibla_finder_app/services/prayer_times_calculator.dart';

void main() {
  test('prayer times stay in chronological daytime order', () {
    final day = PrayerTimesCalculator().times(
      latitude: 40.7128,
      longitude: -74.0060,
      method: 'MWL',
      date: DateTime(2026, 9, 5),
    );

    int minutes(String hhmm) {
      final parts = hhmm.split(':');
      return int.parse(parts[0]) * 60 + int.parse(parts[1]);
    }

    expect(minutes(day.times['fajr']!), lessThan(minutes(day.times['sunrise']!)));
    expect(minutes(day.times['sunrise']!), lessThan(minutes(day.times['dhuhr']!)));
    expect(minutes(day.times['dhuhr']!), lessThan(minutes(day.times['asr']!)));
    expect(minutes(day.times['asr']!), lessThan(minutes(day.times['maghrib']!)));
    expect(minutes(day.times['isha']!) != minutes(day.times['maghrib']!), isTrue);
    expect(day.next.name, isNotEmpty);
  });
}
