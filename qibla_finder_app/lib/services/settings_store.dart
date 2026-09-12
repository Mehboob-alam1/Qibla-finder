import 'dart:convert';

import 'package:shared_preferences/shared_preferences.dart';

import '../models/place.dart';

class SettingsStore {
  SettingsStore({
    this.vibration = true,
    this.audio = false,
    this.displayMode = 'compass',
    this.updateIntervalSeconds = 300,
    this.method = 'MWL',
    this.asr = 'Standard',
    this.darkMode = false,
    this.lastPlace,
  });

  bool vibration;
  bool audio;
  String displayMode;
  int updateIntervalSeconds;
  String method;
  String asr;
  bool darkMode;
  Place? lastPlace;

  static const _vib = 'qf_vib';
  static const _audio = 'qf_audio';
  static const _mode = 'qf_mode';
  static const _interval = 'qf_interval';
  static const _method = 'qf_method';
  static const _asr = 'qf_asr';
  static const _theme = 'qf_theme';
  static const _place = 'qf_last_location';

  static Future<SettingsStore> load() async {
    final prefs = await SharedPreferences.getInstance();
    Place? place;
    final raw = prefs.getString(_place);
    if (raw != null) {
      try {
        place = Place.fromJson(jsonDecode(raw) as Map<String, dynamic>);
      } catch (_) {}
    }

    return SettingsStore(
      vibration: prefs.getString(_vib) != '0',
      audio: prefs.getString(_audio) == '1',
      displayMode: prefs.getString(_mode) ?? 'compass',
      updateIntervalSeconds: int.tryParse(prefs.getString(_interval) ?? '') ?? 300,
      method: prefs.getString(_method) ?? 'MWL',
      asr: prefs.getString(_asr) ?? 'Standard',
      darkMode: prefs.getString(_theme) == 'dark',
      lastPlace: place,
    );
  }

  Future<void> save() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_vib, vibration ? '1' : '0');
    await prefs.setString(_audio, audio ? '1' : '0');
    await prefs.setString(_mode, displayMode);
    await prefs.setString(_interval, '$updateIntervalSeconds');
    await prefs.setString(_method, method);
    await prefs.setString(_asr, asr);
    await prefs.setString(_theme, darkMode ? 'dark' : 'light');
    if (lastPlace != null) {
      await prefs.setString(_place, jsonEncode(lastPlace!.toJson()));
    }
  }
}
