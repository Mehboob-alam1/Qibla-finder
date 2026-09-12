import 'dart:async';

import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../models/prayer_day.dart';
import '../state/qibla_state.dart';
import '../theme/app_theme.dart';

class PrayerScreen extends StatefulWidget {
  const PrayerScreen({super.key, required this.state});

  final QiblaState state;

  @override
  State<PrayerScreen> createState() => _PrayerScreenState();
}

class _PrayerScreenState extends State<PrayerScreen> {
  late Timer _tick;
  late DateTime _now;

  static const _order = [
    ('imsak', 'Imsak'),
    ('fajr', 'Fajr'),
    ('sunrise', 'Sunrise'),
    ('dhuhr', 'Dhuhr'),
    ('asr', 'Asr'),
    ('maghrib', 'Maghrib'),
    ('isha', 'Isha'),
    ('midnight', 'Midnight'),
  ];

  @override
  void initState() {
    super.initState();
    _now = DateTime.now();
    _tick = Timer.periodic(const Duration(seconds: 1), (_) {
      setState(() => _now = DateTime.now());
    });
  }

  @override
  void dispose() {
    _tick.cancel();
    super.dispose();
  }

  String _countdown(int seconds) {
    final elapsed = DateTime.now().difference(_now).inSeconds.abs();
    final safe = (seconds - elapsed).clamp(0, 1 << 30);
    final h = safe ~/ 3600;
    final m = (safe % 3600) ~/ 60;
    final s = safe % 60;
    return '${h.toString().padLeft(2, '0')}:${m.toString().padLeft(2, '0')}:${s.toString().padLeft(2, '0')}';
  }

  Future<void> _pickMethod() async {
    final selected = await showModalBottomSheet<String>(
      context: context,
      showDragHandle: true,
      builder: (context) {
        return SafeArea(
          child: ListView(
            shrinkWrap: true,
            children: [
              for (final method in calculationMethods)
                ListTile(
                  title: Text(method.name),
                  subtitle: Text(method.key),
                  trailing: method.key == widget.state.settings.method
                      ? const Icon(Icons.check)
                      : null,
                  onTap: () => Navigator.pop(context, method.key),
                ),
            ],
          ),
        );
      },
    );
    if (selected != null) {
      await widget.state.updateSettings(method: selected);
    }
  }

  Future<void> _pickAsr() async {
    final selected = await showModalBottomSheet<String>(
      context: context,
      showDragHandle: true,
      builder: (context) {
        return SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              ListTile(
                title: const Text('Standard'),
                trailing: widget.state.settings.asr == 'Standard' ? const Icon(Icons.check) : null,
                onTap: () => Navigator.pop(context, 'Standard'),
              ),
              ListTile(
                title: const Text('Hanafi'),
                trailing: widget.state.settings.asr == 'Hanafi' ? const Icon(Icons.check) : null,
                onTap: () => Navigator.pop(context, 'Hanafi'),
              ),
            ],
          ),
        );
      },
    );
    if (selected != null) {
      await widget.state.updateSettings(asr: selected);
    }
  }

  @override
  Widget build(BuildContext context) {
    return ListenableBuilder(
      listenable: widget.state,
      builder: (context, _) {
        final day = widget.state.prayerDay;
        final method = calculationMethods.firstWhere(
          (item) => item.key == widget.state.settings.method,
          orElse: () => calculationMethods.first,
        );

        return ListView(
          padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
          children: [
            Text(
              DateFormat.jm().format(_now),
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 36, fontWeight: FontWeight.w800),
            ),
            Text(
              DateFormat.MMMEd().format(_now),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              widget.state.place?.label ?? 'Set a location from the search icon',
              textAlign: TextAlign.center,
              style: Theme.of(context).textTheme.bodySmall,
            ),
            if (day != null) ...[
              const SizedBox(height: 16),
              Card(
                color: AppColors.forest,
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('Next prayer', style: TextStyle(color: AppColors.goldSoft, fontSize: 12)),
                            const SizedBox(height: 4),
                            Text(
                              '${day.next.name[0].toUpperCase()}${day.next.name.substring(1)}  ${day.next.time}',
                              style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w800),
                            ),
                          ],
                        ),
                      ),
                      Text(
                        _countdown(day.next.inSeconds),
                        style: const TextStyle(color: AppColors.goldSoft, fontWeight: FontWeight.w700),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 12),
              Card(
                child: Column(
                  children: [
                    for (var i = 0; i < _order.length; i++) ...[
                      if (i > 0) const Divider(height: 1),
                      _PrayerRow(
                        name: _order[i].$2,
                        time: day.times[_order[i].$1] ?? '—',
                        current: day.next.name == _order[i].$1,
                      ),
                    ],
                  ],
                ),
              ),
            ],
            const SizedBox(height: 12),
            Card(
              child: Column(
                children: [
                  ListTile(
                    title: const Text('Calculation method'),
                    subtitle: Text(method.name, overflow: TextOverflow.ellipsis),
                    trailing: const Icon(Icons.chevron_right),
                    onTap: _pickMethod,
                  ),
                  const Divider(height: 1),
                  ListTile(
                    title: const Text('Asr'),
                    subtitle: Text(widget.state.settings.asr),
                    trailing: const Icon(Icons.chevron_right),
                    onTap: _pickAsr,
                  ),
                ],
              ),
            ),
          ],
        );
      },
    );
  }
}

class _PrayerRow extends StatelessWidget {
  const _PrayerRow({required this.name, required this.time, required this.current});

  final String name;
  final String time;
  final bool current;

  @override
  Widget build(BuildContext context) {
    return ListTile(
      selected: current,
      selectedTileColor: AppColors.forest.withValues(alpha: 0.08),
      title: Text(name, style: TextStyle(fontWeight: current ? FontWeight.w800 : FontWeight.w600)),
      trailing: Text(
        time,
        style: TextStyle(
          fontWeight: FontWeight.w700,
          color: current ? AppColors.forest : AppColors.gold,
        ),
      ),
    );
  }
}
