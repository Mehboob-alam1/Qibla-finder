import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';

import '../theme/app_theme.dart';

class LiveScreen extends StatefulWidget {
  const LiveScreen({super.key});

  @override
  State<LiveScreen> createState() => _LiveScreenState();
}

class _LiveScreenState extends State<LiveScreen> {
  late final WebViewController _controller;
  var _loading = true;
  var _playing = true;

  static const _embedHtml = '''
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { background: #000; }
  iframe { position: fixed; top: 0; left: 0; width: 100%; height: 100%; border: 0; }
</style>
</head>
<body>
<iframe
  src="https://www.youtube.com/embed/live_stream?channel=UC21cQ8lE5Ih15sP3XyP7YHA&autoplay=1&mute=0"
  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
  allowfullscreen>
</iframe>
</body>
</html>
''';

  @override
  void initState() {
    super.initState();
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setBackgroundColor(Colors.black)
      ..setNavigationDelegate(
        NavigationDelegate(
          onPageFinished: (_) {
            if (mounted) {
              setState(() => _loading = false);
            }
          },
        ),
      )
      ..loadHtmlString(_embedHtml);
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        gradient: RadialGradient(
          center: Alignment(0, -0.3),
          colors: [Color(0xFF241C10), Color(0xFF05070A)],
          stops: [0, 0.7],
        ),
      ),
      child: Stack(
        children: [
          Positioned.fill(
            child: Opacity(
              opacity: _loading ? 0 : 1,
              child: WebViewWidget(controller: _controller),
            ),
          ),
          if (_loading)
            Center(child: CustomPaint(size: const Size(150, 130), painter: _KaabaPainter())),
          Positioned(
            top: 18,
            left: 16,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
              decoration: BoxDecoration(
                color: AppColors.danger,
                borderRadius: BorderRadius.circular(6),
              ),
              child: const Text(
                '● LIVE',
                style: TextStyle(
                  fontSize: 10,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 0.5,
                  color: Colors.white,
                ),
              ),
            ),
          ),
          const Positioned(
            top: 20,
            right: 16,
            child: Row(
              children: [
                Icon(Icons.remove_red_eye_outlined, size: 13, color: AppColors.sub),
                SizedBox(width: 5),
                Text('Masjid al-Haram', style: TextStyle(fontSize: 11, color: AppColors.sub)),
              ],
            ),
          ),
          Positioned(
            left: 0,
            right: 0,
            bottom: 32,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                _RoundBtn(
                  icon: Icons.skip_previous_outlined,
                  onPressed: () => _controller.reload(),
                ),
                const SizedBox(width: 26),
                GestureDetector(
                  onTap: () => setState(() => _playing = !_playing),
                  child: Container(
                    width: 64,
                    height: 64,
                    decoration: const BoxDecoration(shape: BoxShape.circle, color: AppColors.gold),
                    child: Icon(
                      _playing ? Icons.pause : Icons.play_arrow,
                      color: AppColors.bg,
                      size: 26,
                    ),
                  ),
                ),
                const SizedBox(width: 26),
                _RoundBtn(icon: Icons.skip_next_outlined),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _RoundBtn extends StatelessWidget {
  const _RoundBtn({required this.icon, this.onPressed});

  final IconData icon;
  final VoidCallback? onPressed;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      shape: const CircleBorder(side: BorderSide(color: AppColors.line)),
      child: InkWell(
        onTap: onPressed,
        customBorder: const CircleBorder(),
        child: SizedBox(
          width: 44,
          height: 44,
          child: Icon(icon, size: 18, color: AppColors.text),
        ),
      ),
    );
  }
}

class _KaabaPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final w = size.width;
    final h = size.height;
    final body = Rect.fromLTWH(w * 0.17, h * 0.23, w * 0.66, h * 0.62);
    canvas.drawRect(body, Paint()..color = const Color(0xFF111417));
    canvas.drawRect(
      body,
      Paint()
        ..color = AppColors.gold
        ..style = PaintingStyle.stroke
        ..strokeWidth = 1.5,
    );
    canvas.drawRect(
      Rect.fromLTWH(w * 0.17, h * 0.23, w * 0.66, h * 0.14),
      Paint()..color = AppColors.gold.withValues(alpha: 0.85),
    );
    final door = Rect.fromLTWH(w * 0.4, h * 0.54, w * 0.2, h * 0.31);
    canvas.drawRect(door, Paint()..color = const Color(0xFF0A0E13));
    canvas.drawRect(
      door,
      Paint()
        ..color = AppColors.gold
        ..style = PaintingStyle.stroke
        ..strokeWidth = 1,
    );
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
