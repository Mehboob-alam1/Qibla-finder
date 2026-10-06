import 'package:flutter/material.dart';

import 'screens/onboarding_flow.dart';
import 'screens/shell.dart';
import 'services/notification_service.dart';
import 'services/settings_store.dart';
import 'state/qibla_state.dart';
import 'theme/app_theme.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await NotificationService.initialize();
  final settings = await SettingsStore.load();
  final state = QiblaState(settings);
  runApp(QiblaFinderApp(state: state));
  WidgetsBinding.instance.addPostFrameCallback((_) => state.start());
}

class QiblaFinderApp extends StatelessWidget {
  const QiblaFinderApp({super.key, required this.state});

  final QiblaState state;

  @override
  Widget build(BuildContext context) {
    return ListenableBuilder(
      listenable: state,
      builder: (context, _) {
        final home = state.settings.onboardingComplete
            ? AppShell(state: state)
            : OnboardingFlow(state: state);

        return MaterialApp(
          title: 'Qibla Finder',
          debugShowCheckedModeBanner: false,
          theme: AppTheme.light(),
          darkTheme: AppTheme.dark(),
          themeMode: state.settings.darkMode ? ThemeMode.dark : ThemeMode.light,
          home: home,
        );
      },
    );
  }
}
