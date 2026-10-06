import 'package:flutter/material.dart';

import '../state/qibla_state.dart';
import '../theme/app_theme.dart';
import 'ar_screen.dart';
import 'compass_screen.dart';
import 'home_map_screen.dart';
import 'live_screen.dart';
import 'settings_screen.dart';

class AppShell extends StatefulWidget {
  const AppShell({super.key, required this.state});

  final QiblaState state;

  @override
  State<AppShell> createState() => _AppShellState();
}

class _AppShellState extends State<AppShell> {
  int _index = 0;
  final _visitedTabs = <int>{0};

  static const _items = [
    (Icons.map_outlined, Icons.map, 'Map'),
    (Icons.explore_outlined, Icons.explore, 'Compass'),
    (Icons.view_in_ar_outlined, Icons.view_in_ar, 'AR'),
    (Icons.live_tv_outlined, Icons.live_tv, 'Live'),
    (Icons.settings_outlined, Icons.settings, 'Settings'),
  ];

  @override
  Widget build(BuildContext context) {
    final pages = [
      HomeMapScreen(state: widget.state),
      CompassScreen(state: widget.state),
      ArScreen(state: widget.state),
      const LiveScreen(),
      SettingsScreen(state: widget.state),
    ];

    final bottomInset = MediaQuery.paddingOf(context).bottom;

    return Scaffold(
      backgroundColor: AppColors.bg,
      extendBody: true,
      body: IndexedStack(
        index: _index,
        children: [
          for (var i = 0; i < pages.length; i++)
            if (_visitedTabs.contains(i)) pages[i] else const SizedBox.shrink(),
        ],
      ),
      bottomNavigationBar: Padding(
        padding: EdgeInsets.fromLTRB(16, 0, 16, 10 + bottomInset),
        child: Material(
          elevation: 12,
          shadowColor: Colors.black54,
          borderRadius: BorderRadius.circular(28),
          color: const Color(0xFF0D131A),
          child: Container(
            height: 64,
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(28),
              border: Border.all(color: AppColors.line),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: List.generate(_items.length, (i) {
                final active = i == _index;
                final (icon, selectedIcon, label) = _items[i];
                return Expanded(
                  child: InkWell(
                    borderRadius: BorderRadius.circular(24),
                    onTap: () => setState(() {
                      _visitedTabs.add(i);
                      _index = i;
                    }),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Container(
                          width: 40,
                          height: 32,
                          alignment: Alignment.center,
                          decoration: active
                              ? BoxDecoration(
                                  color: AppColors.moss,
                                  borderRadius: BorderRadius.circular(10),
                                )
                              : null,
                          child: Icon(
                            active ? selectedIcon : icon,
                            size: 22,
                            color: active ? Colors.white : AppColors.navInactive,
                          ),
                        ),
                        const SizedBox(height: 3),
                        Text(
                          label,
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.w600,
                            color: active ? AppColors.text : AppColors.navInactive,
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              }),
            ),
          ),
        ),
      ),
    );
  }
}
