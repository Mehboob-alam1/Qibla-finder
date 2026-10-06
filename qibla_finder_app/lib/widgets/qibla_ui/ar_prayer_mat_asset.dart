import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../../constants/app_assets.dart';

/// Prayer mat artwork laid on the "floor" in AR (perspective + optional Qibla rotation).
class ArPrayerMatAsset extends StatelessWidget {
  const ArPrayerMatAsset({
    super.key,
    required this.assetPath,
    this.rotationDegrees = 0,
    this.width = 220,
  });

  final String assetPath;
  final double rotationDegrees;
  final double width;

  @override
  Widget build(BuildContext context) {
    final height = width * 1.35;
    return Transform(
      alignment: Alignment.center,
      transform: Matrix4.identity()
        ..setEntry(3, 2, 0.0018)
        ..rotateX(-0.52),
      child: Transform.rotate(
        angle: rotationDegrees * math.pi / 180,
        child: Image.asset(
          assetPath,
          width: width,
          height: height,
          fit: BoxFit.contain,
          filterQuality: FilterQuality.high,
        ),
      ),
    );
  }
}

String prayerMatAssetForIndex(int index) {
  final i = index.clamp(0, AppAssets.prayerMats.length - 1);
  return AppAssets.prayerMats[i];
}
