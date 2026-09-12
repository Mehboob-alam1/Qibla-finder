import 'dart:convert';

import 'package:http/http.dart' as http;

import '../data/cities.dart';
import '../models/place.dart';

class PlaceSearch {
  static const siteUrl = 'https://qiblafinders.io';

  static List<Place> local(String query) {
    final needle = query.trim().toLowerCase();
    if (needle.length < 2) {
      return const [];
    }
    return presetCities
        .where(
          (city) =>
              city.name.toLowerCase().contains(needle) || city.country.toLowerCase().contains(needle),
        )
        .take(8)
        .toList();
  }

  static Future<List<Place>> search(String query) async {
    final localMatches = local(query);
    if (query.trim().length < 2) {
      return localMatches;
    }

    try {
      final uri = Uri.parse('$siteUrl/places/search').replace(queryParameters: {'q': query.trim()});
      final response = await http.get(uri).timeout(const Duration(seconds: 8));
      if (response.statusCode != 200) {
        return localMatches;
      }
      final decoded = jsonDecode(response.body);
      if (decoded is! List) {
        return localMatches;
      }
      final remote = decoded
          .whereType<Map<String, dynamic>>()
          .map(
            (item) => Place(
              name: item['name'] as String? ?? '',
              country: item['country'] as String? ?? '',
              lat: (item['lat'] as num).toDouble(),
              lng: (item['lng'] as num).toDouble(),
            ),
          )
          .where((place) => place.name.isNotEmpty)
          .toList();

      final merged = <Place>[...localMatches];
      for (final place in remote) {
        final duplicate = merged.any(
          (existing) =>
              (existing.lat - place.lat).abs() < 0.02 && (existing.lng - place.lng).abs() < 0.02,
        );
        if (!duplicate) {
          merged.add(place);
        }
      }
      return merged.take(10).toList();
    } catch (_) {
      return localMatches;
    }
  }
}
