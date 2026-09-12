class Place {
  const Place({
    required this.name,
    required this.country,
    required this.lat,
    required this.lng,
    this.timezone,
  });

  final String name;
  final String country;
  final double lat;
  final double lng;
  final String? timezone;

  String get label => country.isEmpty ? name : '$name, $country';

  Map<String, dynamic> toJson() => {
        'name': name,
        'country': country,
        'lat': lat,
        'lng': lng,
        'timezone': timezone,
      };

  factory Place.fromJson(Map<String, dynamic> json) {
    return Place(
      name: json['name'] as String? ?? json['label'] as String? ?? 'Location',
      country: json['country'] as String? ?? '',
      lat: (json['lat'] as num).toDouble(),
      lng: (json['lng'] as num).toDouble(),
      timezone: json['timezone'] as String?,
    );
  }
}
