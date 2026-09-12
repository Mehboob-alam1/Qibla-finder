import 'package:flutter/material.dart';

class HelpScreen extends StatelessWidget {
  const HelpScreen({super.key});

  @override
  Widget build(BuildContext context) {
    const items = [
      (
        'How do I use the compass?',
        'Tap the location icon, hold the phone flat, and turn until the gold marker meets the notch at the top. It locks when you are aligned.',
      ),
      (
        'The needle does not move',
        'Allow Location and Compass / Motion in system settings. Then tap Calibrate and move the phone in a figure-8, away from metal.',
      ),
      (
        'Is this accurate for salah?',
        'The bearing to the Kaaba is a geodesic. Most error comes from the phone compass. Calibrate, then confirm with a physical compass if you can.',
      ),
      (
        'Which prayer method should I use?',
        'Use the same method as your local mosque. If you are unsure, Muslim World League is a common default.',
      ),
    ];

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
      children: [
        Card(
          child: Column(
            children: [
              for (var i = 0; i < items.length; i++) ...[
                if (i > 0) const Divider(height: 1),
                ExpansionTile(
                  title: Text(items[i].$1),
                  childrenPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                  children: [Text(items[i].$2)],
                ),
              ],
            ],
          ),
        ),
      ],
    );
  }
}
