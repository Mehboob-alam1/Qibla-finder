class PrayerDay {
  const PrayerDay({
    required this.date,
    required this.hijri,
    required this.timezone,
    required this.method,
    required this.times,
    required this.next,
  });

  final DateTime date;
  final String hijri;
  final String timezone;
  final String method;
  final Map<String, String> times;
  final NextPrayer next;
}

class NextPrayer {
  const NextPrayer({
    required this.name,
    required this.time,
    required this.inSeconds,
    this.tomorrow = false,
  });

  final String name;
  final String time;
  final int inSeconds;
  final bool tomorrow;
}

class CalculationMethod {
  const CalculationMethod({
    required this.key,
    required this.name,
    required this.fajr,
    required this.isha,
    this.ishaIsMinutesAfterSunset = false,
  });

  final String key;
  final String name;
  final double fajr;
  final double isha;
  final bool ishaIsMinutesAfterSunset;
}

const calculationMethods = <CalculationMethod>[
  CalculationMethod(key: 'MWL', name: 'Muslim World League', fajr: 18.0, isha: 17.0),
  CalculationMethod(key: 'ISNA', name: 'Islamic Society of North America', fajr: 15.0, isha: 15.0),
  CalculationMethod(key: 'Egypt', name: 'Egyptian General Authority', fajr: 19.5, isha: 17.5),
  CalculationMethod(
    key: 'Makkah',
    name: 'Umm al-Qura, Makkah',
    fajr: 18.5,
    isha: 90,
    ishaIsMinutesAfterSunset: true,
  ),
  CalculationMethod(key: 'Karachi', name: 'University of Islamic Sciences, Karachi', fajr: 18.0, isha: 18.0),
  CalculationMethod(key: 'Tehran', name: 'Institute of Geophysics, Tehran', fajr: 17.7, isha: 14.0),
  CalculationMethod(key: 'Jafari', name: 'Shia Ithna-Ashari', fajr: 16.0, isha: 14.0),
  CalculationMethod(key: 'Dubai', name: 'Gulf / Dubai', fajr: 18.2, isha: 18.2),
];
