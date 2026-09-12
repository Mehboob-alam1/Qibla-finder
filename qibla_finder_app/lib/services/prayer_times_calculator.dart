import 'dart:math' as math;

import '../models/prayer_day.dart';

/// PrayTimes.org-style calculator, matching the website's PrayerTimeService.
class PrayerTimesCalculator {
  PrayerDay times({
    required double latitude,
    required double longitude,
    String method = 'MWL',
    String asr = 'Standard',
    DateTime? date,
  }) {
    final now = date ?? DateTime.now();
    final day = DateTime(now.year, now.month, now.day);
    final params = calculationMethods.firstWhere(
      (item) => item.key == method,
      orElse: () => calculationMethods.first,
    );

    final jDate = _julian(day) - longitude / (15 * 24);
    final sun = _sunPosition(jDate);
    final dhuhrHours = 12 + (-longitude / 15) - sun.equation;
    final sunrise = _sunAngleTime(latitude, sun.declination, longitude, 0.833, dhuhrHours, true);
    final sunset = _sunAngleTime(latitude, sun.declination, longitude, 0.833, dhuhrHours, false);
    final fajr = _sunAngleTime(latitude, sun.declination, longitude, params.fajr, dhuhrHours, true);
    final isha = params.ishaIsMinutesAfterSunset
        ? sunset + params.isha / 60
        : _sunAngleTime(latitude, sun.declination, longitude, params.isha, dhuhrHours, false);
    final asrShadow = asr == 'Hanafi' ? 2 : 1;
    final asrHours = _asrTime(latitude, sun.declination, longitude, asrShadow, dhuhrHours);
    final midnight = _normalizeHours(sunset + _timeDiff(sunset, sunrise) / 2);
    final imsak = fajr - 10 / 60;
    final offsetHours = (longitude / 15).roundToDouble();

    final map = <String, double>{
      'imsak': imsak,
      'fajr': fajr,
      'sunrise': sunrise,
      'dhuhr': dhuhrHours,
      'asr': asrHours,
      'maghrib': sunset,
      'isha': isha,
      'midnight': midnight,
    };

    final formatted = <String, String>{
      for (final entry in map.entries) entry.key: _formatTime(entry.value + offsetHours),
    };

    return PrayerDay(
      date: day,
      hijri: '',
      timezone: now.timeZoneName,
      method: params.key,
      times: formatted,
      next: _nextPrayer(formatted, day, DateTime.now()),
    );
  }

  List<PrayerDay> month({
    required double latitude,
    required double longitude,
    required int year,
    required int month,
    String method = 'MWL',
    String asr = 'Standard',
  }) {
    final last = DateTime(year, month + 1, 0).day;
    return [
      for (var day = 1; day <= last; day++)
        times(
          latitude: latitude,
          longitude: longitude,
          method: method,
          asr: asr,
          date: DateTime(year, month, day),
        ),
    ];
  }

  NextPrayer _nextPrayer(Map<String, String> times, DateTime dayStart, DateTime now) {
    const sequence = ['fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha'];
    for (final name in sequence) {
      final at = _parseToday(dayStart, times[name]!);
      if (at.isAfter(now)) {
        return NextPrayer(
          name: name,
          time: times[name]!,
          inSeconds: at.difference(now).inSeconds,
        );
      }
    }

    final tomorrow = _parseToday(dayStart.add(const Duration(days: 1)), times['fajr']!);
    return NextPrayer(
      name: 'fajr',
      time: times['fajr']!,
      inSeconds: tomorrow.difference(now).inSeconds,
      tomorrow: true,
    );
  }

  DateTime _parseToday(DateTime day, String hhmm) {
    final parts = hhmm.split(':');
    return DateTime(day.year, day.month, day.day, int.parse(parts[0]), int.parse(parts[1]));
  }

  double _julian(DateTime date) {
    var y = date.year;
    var m = date.month;
    final d = date.day;
    if (m <= 2) {
      y -= 1;
      m += 12;
    }
    final a = (y / 100).floor();
    final b = 2 - a + (a / 4).floor();
    return (365.25 * (y + 4716)).floor() + (30.6001 * (m + 1)).floor() + d + b - 1524.5;
  }

  ({double declination, double equation}) _sunPosition(double jd) {
    final d = jd - 2451545.0;
    final g = _fixAngle(357.529 + 0.98560028 * d);
    final q = _fixAngle(280.459 + 0.98564736 * d);
    final l = _fixAngle(q + 1.915 * math.sin(_rad(g)) + 0.020 * math.sin(_rad(2 * g)));
    final e = 23.439 - 0.00000036 * d;
    var ra = _deg(math.atan2(math.cos(_rad(e)) * math.sin(_rad(l)), math.cos(_rad(l)))) / 15;
    ra = _normalizeHours(ra);
    final decl = _deg(math.asin(math.sin(_rad(e)) * math.sin(_rad(l))));
    return (declination: decl, equation: q / 15 - ra);
  }

  double _sunAngleTime(
    double lat,
    double decl,
    double lng,
    double angle,
    double dhuhr,
    bool ccw,
  ) {
    final term = -math.sin(_rad(angle)) - math.sin(_rad(lat)) * math.sin(_rad(decl));
    final denom = math.cos(_rad(lat)) * math.cos(_rad(decl));
    var ratio = denom == 0 ? 1.0 : term / denom;
    ratio = ratio.clamp(-1.0, 1.0);
    final t = _deg(math.acos(ratio)) / 15;
    return _normalizeHours(dhuhr + (ccw ? -t : t));
  }

  double _asrTime(double lat, double decl, double lng, int shadow, double dhuhr) {
    final angle = -_deg(math.atan(1 / (shadow + math.tan((_rad(lat) - _rad(decl)).abs()))));
    return _sunAngleTime(lat, decl, lng, angle, dhuhr, false);
  }

  String _formatTime(double hours) {
    final normalized = _normalizeHours(hours + 0.5 / 60);
    final h = normalized.floor() % 24;
    final m = ((normalized - normalized.floor()) * 60).floor();
    return '${h.toString().padLeft(2, '0')}:${m.toString().padLeft(2, '0')}';
  }

  double _timeDiff(double a, double b) => _normalizeHours(b - a);

  double _normalizeHours(double hours) {
    var value = hours % 24;
    if (value < 0) {
      value += 24;
    }
    return value;
  }

  double _fixAngle(double angle) {
    var value = angle % 360;
    if (value < 0) {
      value += 360;
    }
    return value;
  }

  double _rad(double degrees) => degrees * math.pi / 180;
  double _deg(double radians) => radians * 180 / math.pi;
}
