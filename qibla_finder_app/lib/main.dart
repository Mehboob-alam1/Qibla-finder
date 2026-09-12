import 'package:flutter/material.dart';

import 'screens/shell.dart';
import 'services/settings_store.dart';
import 'state/qibla_state.dart';
import 'theme/app_theme.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
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
        return MaterialApp(
          title: 'Qibla Finder',
          debugShowCheckedModeBanner: false,
          theme: AppTheme.light(),
          darkTheme: AppTheme.dark(),
          themeMode: state.settings.darkMode ? ThemeMode.dark : ThemeMode.light,
          home: AppShell(state: state),
        );
      },
    );
  }
}
