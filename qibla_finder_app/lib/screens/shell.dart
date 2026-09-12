import 'package:flutter/material.dart';
import 'package:share_plus/share_plus.dart';

import '../state/qibla_state.dart';
import '../widgets/place_search_field.dart';
import 'compass_screen.dart';
import 'help_screen.dart';
import 'prayer_screen.dart';
import 'settings_screen.dart';

class AppShell extends StatefulWidget {
  const AppShell({super.key, required this.state});

  final QiblaState state;

  @override
  State<AppShell> createState() => _AppShellState();
}

class _AppShellState extends State<AppShell> {
  int _index = 0;

  static const _titles = ['Qibla', 'Prayer', 'Help', 'Settings'];

  @override
  Widget build(BuildContext context) {
    final pages = [
      CompassScreen(state: widget.state),
      PrayerScreen(state: widget.state),
      const HelpScreen(),
      SettingsScreen(state: widget.state),
    ];

    return ListenableBuilder(
      listenable: widget.state,
      builder: (context, _) {
        return Scaffold(
          appBar: AppBar(
            title: Text(_titles[_index]),
            actions: [
              if (_index < 2) ...[
                IconButton(
                  tooltip: 'Search city',
                  onPressed: () => showPlaceSearchSheet(
                    context: context,
                    onSelected: widget.state.setPlace,
                    onUseLocation: () => widget.state.locate(),
                  ),
                  icon: const Icon(Icons.search),
                ),
                IconButton(
                  tooltip: 'My location',
                  onPressed: widget.state.locating ? null : () => widget.state.locate(),
                  icon: widget.state.locating
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Icon(Icons.my_location),
                ),
              ],
              if (_index == 0)
                IconButton(
                  tooltip: 'Share',
                  onPressed: () {
                    SharePlus.instance.share(
                      ShareParams(
                        text:
                            'Find the Qibla with a live compass. https://qiblafinders.io',
                        title: 'Qibla Finder',
                      ),
                    );
                  },
                  icon: const Icon(Icons.ios_share),
                ),
            ],
          ),
          body: IndexedStack(index: _index, children: pages),
          bottomNavigationBar: NavigationBar(
            selectedIndex: _index,
            onDestinationSelected: (index) => setState(() => _index = index),
            destinations: const [
              NavigationDestination(
                icon: Icon(Icons.explore_outlined),
                selectedIcon: Icon(Icons.explore),
                label: 'Qibla',
              ),
              NavigationDestination(
                icon: Icon(Icons.schedule_outlined),
                selectedIcon: Icon(Icons.schedule),
                label: 'Prayer',
              ),
              NavigationDestination(
                icon: Icon(Icons.help_outline),
                selectedIcon: Icon(Icons.help),
                label: 'Help',
              ),
              NavigationDestination(
                icon: Icon(Icons.settings_outlined),
                selectedIcon: Icon(Icons.settings),
                label: 'Settings',
              ),
            ],
          ),
        );
      },
    );
  }
}
