import 'package:camera/camera.dart';
import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:permission_handler/permission_handler.dart';

import '../services/qibla_math.dart';
import '../state/qibla_state.dart';
import '../theme/app_theme.dart';
import '../constants/app_assets.dart';
import '../widgets/degree_ruler_bar.dart';
import '../widgets/qibla_ui/ar_prayer_mat_asset.dart';
import '../widgets/qibla_ui/kaaba_edge_indicator.dart';
import '../widgets/qibla_ui/ar_prayer_mat.dart';

class ArScreen extends StatefulWidget {
  const ArScreen({super.key, required this.state});

  final QiblaState state;

  @override
  State<ArScreen> createState() => _ArScreenState();
}

class _ArScreenState extends State<ArScreen> {
  CameraController? _camera;
  var _permissionDenied = false;
  var _initializing = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _initCamera();
  }

  Future<void> _initCamera() async {
    final status = await Permission.camera.request();
    if (!status.isGranted) {
      if (mounted) {
        setState(() {
          _permissionDenied = true;
          _initializing = false;
        });
      }
      return;
    }

    try {
      final cameras = await availableCameras();
      final back = cameras.firstWhere(
        (c) => c.lensDirection == CameraLensDirection.back,
        orElse: () => cameras.first,
      );
      final controller = CameraController(back, ResolutionPreset.high, enableAudio: false);
      await controller.initialize();
      if (!mounted) {
        await controller.dispose();
        return;
      }
      setState(() {
        _camera = controller;
        _initializing = false;
      });
    } catch (e) {
      if (mounted) {
        setState(() {
          _initializing = false;
          _error = 'Could not start camera';
        });
      }
    }
  }

  Future<void> _cyclePrayerMat() async {
    final next = (widget.state.settings.prayerMatIndex + 1) % AppAssets.prayerMats.length;
    widget.state.settings.prayerMatIndex = next;
    await widget.state.settings.save();
    if (mounted) {
      setState(() {});
    }
  }

  ({String text, IconData icon}) _turnHint(double? heading, double qibla, bool aligned) {
    if (heading == null) {
      return (text: 'Move phone in a figure-8 to calibrate', icon: Icons.screen_rotation);
    }
    if (aligned) {
      return (text: 'Facing Qibla — you can pray', icon: Icons.check_circle_outline);
    }
    final delta = QiblaMath.shortestDelta(heading, qibla);
    if (delta > 0) {
      return (text: 'Turn right ${delta.abs().toStringAsFixed(0)}°', icon: Icons.turn_right);
    }
    return (text: 'Turn left ${delta.abs().toStringAsFixed(0)}°', icon: Icons.turn_left);
  }

  @override
  void dispose() {
    _camera?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (_permissionDenied) {
      return _EmptyState(
        icon: Icons.videocam_off_outlined,
        title: 'Camera access needed',
        detail: 'Allow camera to use AR Qibla guidance with a prayer mat overlay.',
        actionLabel: 'Open settings',
        onAction: () => Geolocator.openAppSettings(),
      );
    }

    if (_initializing) {
      return const Center(child: CircularProgressIndicator(color: AppColors.gold));
    }

    if (_camera == null || !_camera!.value.isInitialized) {
      return _EmptyState(
        icon: Icons.error_outline,
        title: _error ?? 'Camera unavailable',
        detail: 'Try closing other camera apps and reopen this tab.',
        actionLabel: 'Retry',
        onAction: () {
          setState(() {
            _initializing = true;
            _error = null;
          });
          _initCamera();
        },
      );
    }

    return ListenableBuilder(
      listenable: widget.state,
      builder: (context, _) {
        final heading = widget.state.heading;
        final snapshot = widget.state.snapshot;
        final qibla = snapshot?.bearing ?? 0;
        final aligned = widget.state.aligned;
        final matAsset = prayerMatAssetForIndex(widget.state.settings.prayerMatIndex);
        final hint = _turnHint(heading, qibla, aligned);
        final distanceKm = snapshot?.distanceKm ?? 0.0;

        return Stack(
          fit: StackFit.expand,
          children: [
            CameraPreview(_camera!),
            Container(
              decoration: BoxDecoration(
                gradient: RadialGradient(
                  center: Alignment.center,
                  radius: 1.2,
                  colors: [
                    Colors.transparent,
                    Colors.black.withValues(alpha: 0.08),
                    Colors.black.withValues(alpha: 0.45),
                  ],
                  stops: const [0.55, 0.82, 1],
                ),
              ),
            ),
            if (heading != null && snapshot != null)
              Positioned(
                top: 12,
                left: 12,
                right: 12,
                child: DecoratedBox(
                  decoration: BoxDecoration(
                    color: Colors.black.withValues(alpha: 0.5),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: AppColors.line.withValues(alpha: 0.8)),
                  ),
                  child: DegreeRulerBar(centerHeading: heading, qiblaBearing: qibla),
                ),
              ),
            Positioned(
              top: 78,
              left: 0,
              right: 0,
              child: Center(
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  decoration: BoxDecoration(
                    color: const Color(0xFF0D131A).withValues(alpha: 0.88),
                    borderRadius: BorderRadius.circular(24),
                    border: Border.all(color: aligned ? AppColors.gold : AppColors.line),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(hint.icon, size: 18, color: AppColors.gold),
                      const SizedBox(width: 8),
                      Text(
                        hint.text,
                        style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: AppColors.text),
                      ),
                    ],
                  ),
                ),
              ),
            ),
            Center(
              child: Stack(
                alignment: Alignment.center,
                children: [
                  if (aligned)
                    Container(
                      width: 240,
                      height: 240,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        border: Border.all(color: AppColors.gold.withValues(alpha: 0.6), width: 2),
                        boxShadow: [
                          BoxShadow(color: AppColors.gold.withValues(alpha: 0.25), blurRadius: 24, spreadRadius: 4),
                        ],
                      ),
                    ),
                  Padding(
                    padding: const EdgeInsets.only(top: 48),
                    child: ArPrayerMatAsset(
                      assetPath: matAsset,
                      rotationDegrees: widget.state.needleAngle,
                      width: 210,
                    ),
                  ),
                ],
              ),
            ),
            if (heading != null && snapshot != null && !aligned)
              KaabaEdgeIndicator(
                qiblaBearing: qibla,
                heading: heading,
                distanceKm: distanceKm,
              ),
            Positioned(
              right: 16,
              top: 130,
              child: Column(
                children: [
                  ArSideFab(icon: Icons.my_location, onPressed: () => widget.state.locate()),
                  const SizedBox(height: 10),
                  ArSideFab(icon: Icons.layers_outlined, onPressed: _cyclePrayerMat),
                ],
              ),
            ),
            Positioned(
              left: 16,
              right: 16,
              bottom: 12,
              child: ArStatBar(
                compass: heading == null ? '—' : '${heading.round()}°',
                qibla: snapshot == null ? '—' : '${qibla.toStringAsFixed(1)}°',
                distance: snapshot == null ? '—' : '${snapshot.distanceKm.toStringAsFixed(0)} km',
              ),
            ),
          ],
        );
      },
    );
  }
}

class _EmptyState extends StatelessWidget {
  const _EmptyState({
    required this.icon,
    required this.title,
    required this.detail,
    required this.actionLabel,
    required this.onAction,
  });

  final IconData icon;
  final String title;
  final String detail;
  final String actionLabel;
  final VoidCallback onAction;

  @override
  Widget build(BuildContext context) {
    return Container(
      color: AppColors.bg,
      child: Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, size: 56, color: AppColors.sub),
              const SizedBox(height: 16),
              Text(title, style: AppTextStyles.heading.copyWith(fontSize: 18)),
              const SizedBox(height: 8),
              Text(detail, textAlign: TextAlign.center, style: AppTextStyles.body),
              const SizedBox(height: 20),
              FilledButton(onPressed: onAction, child: Text(actionLabel)),
            ],
          ),
        ),
      ),
    );
  }
}
