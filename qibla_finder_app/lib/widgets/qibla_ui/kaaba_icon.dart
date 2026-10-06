import 'package:flutter/material.dart';

import '../../constants/app_assets.dart';

class KaabaIcon extends StatelessWidget {
  const KaabaIcon({
    super.key,
    this.size = 32,
    this.shadow = true,
  });

  final double size;
  final bool shadow;

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: shadow
          ? BoxDecoration(
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.45),
                  blurRadius: size * 0.2,
                  offset: Offset(0, size * 0.06),
                ),
              ],
            )
          : const BoxDecoration(),
      child: Image.asset(
        AppAssets.kaaba,
        width: size,
        height: size,
        fit: BoxFit.contain,
        filterQuality: FilterQuality.medium,
      ),
    );
  }
}
