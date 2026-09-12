import 'package:flutter/material.dart';

import '../state/qibla_state.dart';

class SettingsScreen extends StatelessWidget {
  const SettingsScreen({super.key, required this.state});

  final QiblaState state;

  @override
  Widget build(BuildContext context) {
    return ListenableBuilder(
      listenable: state,
      builder: (context, _) {
        return ListView(
          padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
          children: [
            Card(
              child: Column(
                children: [
                  SwitchListTile(
                    title: const Text('Vibration'),
                    subtitle: const Text('Haptic pulse when you face Qibla'),
                    value: state.settings.vibration,
                    onChanged: (value) => state.updateSettings(vibration: value),
                  ),
                  const Divider(height: 1),
                  SwitchListTile(
                    title: const Text('Sound'),
                    subtitle: const Text('Click when the compass locks'),
                    value: state.settings.audio,
                    onChanged: (value) => state.updateSettings(audio: value),
                  ),
                  const Divider(height: 1),
                  SwitchListTile(
                    title: const Text('Dark mode'),
                    value: state.settings.darkMode,
                    onChanged: (value) => state.updateSettings(darkMode: value),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            Card(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Display', style: TextStyle(fontWeight: FontWeight.w700)),
                    const SizedBox(height: 8),
                    SegmentedButton<String>(
                      showSelectedIcon: false,
                      segments: const [
                        ButtonSegment(value: 'compass', label: Text('Compass')),
                        ButtonSegment(value: 'arrow', label: Text('Arrow')),
                      ],
                      selected: {state.settings.displayMode},
                      onSelectionChanged: (value) => state.updateSettings(displayMode: value.first),
                    ),
                    const SizedBox(height: 16),
                    Text('Location refresh  ·  ${state.settings.updateIntervalSeconds}s'),
                    Slider(
                      min: 30,
                      max: 600,
                      divisions: 19,
                      value: state.settings.updateIntervalSeconds.clamp(30, 600).toDouble(),
                      label: '${state.settings.updateIntervalSeconds}s',
                      onChanged: (value) => state.updateSettings(updateIntervalSeconds: value.round()),
                    ),
                  ],
                ),
              ),
            ),
          ],
        );
      },
    );
  }
}
