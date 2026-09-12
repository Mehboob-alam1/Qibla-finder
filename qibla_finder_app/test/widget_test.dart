import 'package:flutter_test/flutter_test.dart';
import 'package:qibla_finder_app/main.dart';
import 'package:qibla_finder_app/services/settings_store.dart';
import 'package:qibla_finder_app/state/qibla_state.dart';

void main() {
  testWidgets('app shell shows Qibla Finder', (tester) async {
    final state = QiblaState(SettingsStore());
    await tester.pumpWidget(QiblaFinderApp(state: state));
    await tester.pump();
    expect(find.text('Qibla'), findsWidgets);
  });
}
