import 'package:flutter/material.dart';
import 'package:share_plus/share_plus.dart';

import '../state/qibla_state.dart';
import '../theme/app_theme.dart';
import '../widgets/place_search_field.dart';
import '../widgets/qibla_ui/settings_widgets.dart';
import 'help_screen.dart';
import 'prayer_screen.dart';

class SettingsScreen extends StatelessWidget {
  const SettingsScreen({super.key, required this.state});

  final QiblaState state;

  @override
  Widget build(BuildContext context) {
    return ListenableBuilder(
      listenable: state,
      builder: (context, _) {
        return ListView(
          padding: const EdgeInsets.fromLTRB(18, 24, 18, 20),
          children: [
            const Text(
              'Qibla Compass',
              textAlign: TextAlign.center,
              style: TextStyle(fontWeight: FontWeight.w700, fontSize: 18, color: AppColors.text),
            ),
            const SizedBox(height: 18),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                gradient: const LinearGradient(colors: [Color(0xFF2A2213), Color(0xFF171008)]),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFF3A2E15)),
              ),
              child: const Row(
                children: [
                  Icon(Icons.diamond_outlined, color: AppColors.gold, size: 24),
                  SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Try Premium',
                          style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: AppColors.text),
                        ),
                        SizedBox(height: 2),
                        Text('Remove ads, unlock all compass skins', style: AppTextStyles.label),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 18),
            SettingsGroup(
              rows: [
                SettingsRowData(
                  icon: Icons.schedule_outlined,
                  label: 'Prayer times',
                  chevron: true,
                  onTap: () => Navigator.of(context).push(
                    MaterialPageRoute<void>(builder: (_) => PrayerScreen(state: state)),
                  ),
                ),
                SettingsRowData(
                  icon: Icons.location_on_outlined,
                  label: 'Location marker',
                  trailingText: state.place?.name ?? 'Not set',
                  chevron: true,
                  onTap: () => showPlaceSearchSheet(
                    context: context,
                    onSelected: state.setPlace,
                    onUseLocation: () => state.locate(),
                  ),
                ),
                SettingsRowData(
                  icon: Icons.language,
                  label: 'Language',
                  trailingText: 'English',
                  chevron: true,
                ),
              ],
            ),
            const SizedBox(height: 14),
            SettingsGroup(
              rows: [
                SettingsRowData(
                  icon: Icons.vibration,
                  label: 'Vibration',
                  trailingSwitch: state.settings.vibration,
                  onSwitch: (v) => state.updateSettings(vibration: v),
                ),
                SettingsRowData(
                  icon: Icons.dark_mode_outlined,
                  label: 'Dark mode',
                  trailingSwitch: state.settings.darkMode,
                  onSwitch: (v) => state.updateSettings(darkMode: v),
                ),
                SettingsRowData(
                  icon: Icons.explore_outlined,
                  label: 'Calibrate my device',
                  chevron: true,
                  onTap: () => state.recalibrate(),
                ),
                SettingsRowData(
                  icon: Icons.volume_up_outlined,
                  label: 'Sound on Qibla lock',
                  trailingSwitch: state.settings.audio,
                  onSwitch: (v) => state.updateSettings(audio: v),
                ),
              ],
            ),
            const SizedBox(height: 14),
            SettingsGroup(
              rows: [
                SettingsRowData(
                  icon: Icons.help_outline,
                  label: 'Help',
                  chevron: true,
                  onTap: () => Navigator.of(context).push(
                    MaterialPageRoute<void>(builder: (_) => const HelpScreen()),
                  ),
                ),
                SettingsRowData(
                  icon: Icons.mail_outline,
                  label: 'Share app',
                  onTap: () {
                    SharePlus.instance.share(
                      ShareParams(
                        text: 'Find the Qibla with a live compass. https://qiblafinders.io',
                        title: 'Qibla Finder',
                      ),
                    );
                  },
                ),
                SettingsRowData(
                  icon: Icons.star_border,
                  label: 'Rate us',
                  chevron: true,
                ),
              ],
            ),
          ],
        );
      },
    );
  }
}
