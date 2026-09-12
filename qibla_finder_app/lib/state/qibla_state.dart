import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';
import 'package:flutter_compass/flutter_compass.dart';
import 'package:geocoding/geocoding.dart';
import 'package:geolocator/geolocator.dart';
import 'package:vibration/vibration.dart';

import '../models/place.dart';
import '../models/prayer_day.dart';
import '../models/qibla_snapshot.dart';
import '../services/prayer_times_calculator.dart';
import '../services/qibla_math.dart';
import '../services/settings_store.dart';

class QiblaState extends ChangeNotifier {
  QiblaState(this.settings);

  final SettingsStore settings;
  final _prayerCalculator = PrayerTimesCalculator();

  Place? place;
  QiblaSnapshot? snapshot;
  PrayerDay? prayerDay;
  double? heading;
  double? _smoothedHeading;
  bool locked = false;
  bool locating = false;
  String? status;
  StreamSubscription<CompassEvent>? _compassSub;
  Timer? _locationTimer;

  bool get hasLocation => place != null && snapshot != null;
  bool get isCompassMode => settings.displayMode == 'compass';

  double get qibla => snapshot?.bearing ?? 0;
  double get deviceHeading => heading ?? 0;

  double get alignmentDelta {
    if (heading == null || snapshot == null) {
      return 180;
    }
    return QiblaMath.shortestDelta(heading!, snapshot!.bearing).abs();
  }

  double get needleAngle {
    if (locked) {
      return 0;
    }
    if (heading == null) {
      return qibla;
    }
    return QiblaMath.normalize(qibla - heading!);
  }

  double get roseAngle {
    if (!isCompassMode || heading == null) {
      return 0;
    }
    return QiblaMath.normalize(-heading!);
  }

  bool get aligned => hasLocation && alignmentDelta <= QiblaMath.lockDegrees;

  Future<void> start() async {
    place = settings.lastPlace;
    if (place != null) {
      _applyPlace(place!, persist: false);
    }
    _listenCompass();
    await locate(userGesture: false);
    _restartLocationTimer();
  }

  Future<void> locate({bool userGesture = true}) async {
    if (locked && !userGesture) {
      return;
    }
    locating = true;
    status = userGesture ? 'Finding your location…' : status;
    notifyListeners();

    try {
      final permission = await _ensurePermission();
      if (permission == null) {
        locating = false;
        status = 'Location permission is needed, or search a city.';
        notifyListeners();
        return;
      }

      final position = await Geolocator.getCurrentPosition(
        locationSettings: LocationSettings(
          accuracy: LocationAccuracy.high,
          timeLimit: const Duration(seconds: 15),
        ),
      );
      var label = 'Current location';
      var country = '';
      try {
        final marks = await Geocoding().placemarkFromCoordinates(position.latitude, position.longitude);
        if (marks.isNotEmpty) {
          final mark = marks.first;
          label = mark.locality?.isNotEmpty == true
              ? mark.locality!
              : (mark.subAdministrativeArea ?? mark.name ?? label);
          country = mark.country ?? '';
        }
      } catch (_) {}

      setPlace(
        Place(name: label, country: country, lat: position.latitude, lng: position.longitude),
        unlock: userGesture,
      );
      status = 'Location updated';
    } catch (_) {
      status = place == null ? 'Could not read GPS. Search a city instead.' : status;
    } finally {
      locating = false;
      notifyListeners();
    }
  }

  void setPlace(Place next, {bool unlock = true}) {
    if (unlock) {
      unlockQibla();
    }
    _applyPlace(next, persist: true);
  }

  void _applyPlace(Place next, {required bool persist}) {
    place = next;
    snapshot = QiblaMath.snapshot(next.lat, next.lng);
    prayerDay = _prayerCalculator.times(
      latitude: next.lat,
      longitude: next.lng,
      method: settings.method,
      asr: settings.asr,
    );
    if (persist) {
      settings.lastPlace = next;
      settings.save();
    }
    notifyListeners();
  }

  void refreshPrayerTimes() {
    if (place == null) {
      return;
    }
    prayerDay = _prayerCalculator.times(
      latitude: place!.lat,
      longitude: place!.lng,
      method: settings.method,
      asr: settings.asr,
    );
    notifyListeners();
  }

  Future<void> updateSettings({
    bool? vibration,
    bool? audio,
    String? displayMode,
    int? updateIntervalSeconds,
    String? method,
    String? asr,
    bool? darkMode,
  }) async {
    if (vibration != null) settings.vibration = vibration;
    if (audio != null) settings.audio = audio;
    if (displayMode != null) settings.displayMode = displayMode;
    if (updateIntervalSeconds != null) {
      settings.updateIntervalSeconds = updateIntervalSeconds.clamp(5, 3600);
      _restartLocationTimer();
    }
    if (method != null) settings.method = method;
    if (asr != null) settings.asr = asr;
    if (darkMode != null) settings.darkMode = darkMode;
    await settings.save();
    refreshPrayerTimes();
    notifyListeners();
  }

  void recalibrate() {
    unlockQibla();
    status = 'Hold the phone flat and move it in a figure-8.';
    notifyListeners();
  }

  void _listenCompass() {
    _compassSub?.cancel();
    _compassSub = FlutterCompass.events?.listen((event) {
      final raw = event.heading;
      if (raw == null) {
        return;
      }
      final next = QiblaMath.normalize(raw);
      _smoothedHeading = _smoothedHeading == null ? next : QiblaMath.smoothHeading(_smoothedHeading!, next);
      if (!locked) {
        heading = _smoothedHeading;
      }
      _evaluateLock();
      notifyListeners();
    });
  }

  void _evaluateLock() {
    final live = _smoothedHeading;
    if (live == null || snapshot == null) {
      return;
    }
    final delta = QiblaMath.shortestDelta(live, snapshot!.bearing).abs();
    if (!locked && delta <= QiblaMath.lockDegrees) {
      lockQibla();
    } else if (locked && delta > QiblaMath.unlockDegrees) {
      unlockQibla();
    }
  }

  void lockQibla() {
    if (locked) {
      return;
    }
    locked = true;
    heading = snapshot?.bearing;
    _locationTimer?.cancel();
    status = 'You are facing the Qibla';
    _celebrate();
    notifyListeners();
  }

  void unlockQibla() {
    if (!locked) {
      return;
    }
    locked = false;
    _restartLocationTimer();
    notifyListeners();
  }

  Future<void> _celebrate() async {
    if (settings.vibration && await Vibration.hasVibrator()) {
      await Vibration.vibrate(pattern: [40, 40, 80]);
    }
    if (settings.audio) {
      await SystemSound.play(SystemSoundType.click);
    }
  }

  void _restartLocationTimer() {
    _locationTimer?.cancel();
    _locationTimer = Timer.periodic(
      Duration(seconds: settings.updateIntervalSeconds.clamp(5, 3600)),
      (_) {
        if (!locked) {
          locate(userGesture: false);
        }
      },
    );
  }

  Future<LocationPermission?> _ensurePermission() async {
    final enabled = await Geolocator.isLocationServiceEnabled();
    if (!enabled) {
      return null;
    }
    var permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
    }
    if (permission == LocationPermission.denied || permission == LocationPermission.deniedForever) {
      return null;
    }
    return permission;
  }

  @override
  void dispose() {
    _compassSub?.cancel();
    _locationTimer?.cancel();
    super.dispose();
  }
}
