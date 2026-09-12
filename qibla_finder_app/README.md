# Qibla Finder (Flutter)

Standalone mobile app for the [qiblafinders.io](https://qiblafinders.io) website. It lives in this folder so the Laravel site stays untouched.

## What it includes

- Live Qibla compass (magnetometer) with the same Kaaba math as the site: `21.422487, 39.826206`
- Lock when you are within 10° of Qibla; unlock if you turn more than 22°
- Compass or arrow display modes
- GPS location, city search (local cities + the live website API)
- Prayer times (MWL and the other site methods, Standard / Hanafi Asr)
- Map line from you to the Kaaba
- Vibration, optional click on lock, share, dark mode, FAQ

## Run it

Flutter SDK is expected on your PATH (this machine has it at `/Users/mac/development/flutter/bin`).

```sh
cd qibla_finder_app
flutter pub get
flutter test
flutter run
```

On a physical phone, allow Location and Motion / Compass when asked. Desktop simulators usually have no magnetometer; the numeric bearing and map still work.

## Project layout

- `lib/services/qibla_math.dart` — geodesic bearing and distance
- `lib/services/prayer_times_calculator.dart` — PrayTimes.org-style timetable
- `lib/screens/` — Compass, Prayer, FAQ, Settings
