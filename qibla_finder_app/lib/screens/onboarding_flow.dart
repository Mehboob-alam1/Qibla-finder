import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import '../services/notification_service.dart';
import '../state/qibla_state.dart';
import '../theme/app_theme.dart';
import 'shell.dart';

class OnboardingFlow extends StatefulWidget {
  const OnboardingFlow({super.key, required this.state});

  final QiblaState state;

  @override
  State<OnboardingFlow> createState() => _OnboardingFlowState();
}

class _OnboardingFlowState extends State<OnboardingFlow> {
  int _step = 0;

  Future<void> _requestLocation() async {
    await Geolocator.requestPermission();
    if (mounted) {
      setState(() => _step = 1);
    }
  }

  Future<void> _requestNotifications() async {
    await NotificationService.requestPermission();
    await _finish();
  }

  Future<void> _finish() async {
    widget.state.settings.onboardingComplete = true;
    await widget.state.settings.save();
    if (!mounted) {
      return;
    }
    await Navigator.of(context).pushReplacement(
      MaterialPageRoute<void>(
        builder: (_) => AppShell(state: widget.state),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 28),
          child: _step == 0 ? _LocationStep(onContinue: _requestLocation) : _NotificationStep(
            onEnable: _requestNotifications,
            onSkip: _finish,
          ),
        ),
      ),
    );
  }
}

class _LocationStep extends StatelessWidget {
  const _LocationStep({required this.onContinue});

  final VoidCallback onContinue;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const Spacer(flex: 2),
        Icon(Icons.location_on_rounded, size: 88, color: AppColors.gold),
        const SizedBox(height: 28),
        Text(
          'Location access',
          textAlign: TextAlign.center,
          style: theme.textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.w800),
        ),
        const SizedBox(height: 12),
        Text(
          'We need your location to calculate the Qibla direction, distance to the Kaaba, and accurate prayer times for your city.',
          textAlign: TextAlign.center,
          style: theme.textTheme.bodyLarge?.copyWith(
            color: theme.colorScheme.onSurface.withValues(alpha: 0.72),
            height: 1.45,
          ),
        ),
        const Spacer(flex: 3),
        FilledButton(
          onPressed: onContinue,
          style: FilledButton.styleFrom(
            backgroundColor: AppColors.teal,
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(vertical: 16),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
          ),
          child: const Text('Allow location', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
        ),
        const SizedBox(height: 32),
      ],
    );
  }
}

class _NotificationStep extends StatelessWidget {
  const _NotificationStep({required this.onEnable, required this.onSkip});

  final VoidCallback onEnable;
  final VoidCallback onSkip;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const Spacer(flex: 2),
        Icon(Icons.notifications_active_rounded, size: 88, color: AppColors.gold),
        const SizedBox(height: 28),
        Text(
          'Notification access',
          textAlign: TextAlign.center,
          style: theme.textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.w800),
        ),
        const SizedBox(height: 12),
        Text(
          'Get prayer time reminders and alerts when you are facing the Qibla. You can change this anytime in Settings.',
          textAlign: TextAlign.center,
          style: theme.textTheme.bodyLarge?.copyWith(
            color: theme.colorScheme.onSurface.withValues(alpha: 0.72),
            height: 1.45,
          ),
        ),
        const Spacer(flex: 3),
        FilledButton(
          onPressed: onEnable,
          style: FilledButton.styleFrom(
            backgroundColor: AppColors.teal,
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(vertical: 16),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
          ),
          child: const Text('Allow notifications', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
        ),
        const SizedBox(height: 12),
        TextButton(
          onPressed: onSkip,
          child: const Text('Not now'),
        ),
        const SizedBox(height: 24),
      ],
    );
  }
}
