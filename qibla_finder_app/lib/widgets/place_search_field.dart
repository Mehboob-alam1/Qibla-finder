import 'dart:async';

import 'package:flutter/material.dart';

import '../models/place.dart';
import '../services/place_search.dart';

Future<void> showPlaceSearchSheet({
  required BuildContext context,
  required ValueChanged<Place> onSelected,
  VoidCallback? onUseLocation,
}) {
  return showModalBottomSheet<void>(
    context: context,
    isScrollControlled: true,
    showDragHandle: true,
    builder: (context) {
      return Padding(
        padding: EdgeInsets.only(bottom: MediaQuery.viewInsetsOf(context).bottom),
        child: PlaceSearchField(
          autofocus: true,
          onSelected: (place) {
            onSelected(place);
            Navigator.pop(context);
          },
          onUseLocation: onUseLocation == null
              ? null
              : () {
                  onUseLocation();
                  Navigator.pop(context);
                },
        ),
      );
    },
  );
}

class PlaceSearchField extends StatefulWidget {
  const PlaceSearchField({
    super.key,
    required this.onSelected,
    this.onUseLocation,
    this.autofocus = false,
  });

  final ValueChanged<Place> onSelected;
  final VoidCallback? onUseLocation;
  final bool autofocus;

  @override
  State<PlaceSearchField> createState() => _PlaceSearchFieldState();
}

class _PlaceSearchFieldState extends State<PlaceSearchField> {
  final _controller = TextEditingController();
  Timer? _debounce;
  List<Place> _results = const [];
  bool _busy = false;

  @override
  void dispose() {
    _debounce?.cancel();
    _controller.dispose();
    super.dispose();
  }

  void _onChanged(String value) {
    _debounce?.cancel();
    _debounce = Timer(const Duration(milliseconds: 280), () async {
      if (value.trim().length < 2) {
        setState(() => _results = const []);
        return;
      }
      setState(() => _busy = true);
      final results = await PlaceSearch.search(value);
      if (!mounted) {
        return;
      }
      setState(() {
        _results = results;
        _busy = false;
      });
    });
  }

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: SizedBox(
        height: 420,
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 8),
              child: TextField(
                controller: _controller,
                autofocus: widget.autofocus,
                onChanged: _onChanged,
                textInputAction: TextInputAction.search,
                decoration: InputDecoration(
                  hintText: 'Search a city',
                  prefixIcon: const Icon(Icons.search),
                  suffixIcon: _busy
                      ? const Padding(
                          padding: EdgeInsets.all(12),
                          child: SizedBox(
                            width: 16,
                            height: 16,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          ),
                        )
                      : null,
                ),
              ),
            ),
            if (widget.onUseLocation != null)
              ListTile(
                leading: const Icon(Icons.my_location),
                title: const Text('Use current location'),
                onTap: widget.onUseLocation,
              ),
            const Divider(height: 1),
            Expanded(
              child: ListView.separated(
                itemCount: _results.length,
                separatorBuilder: (_, _) => const Divider(height: 1),
                itemBuilder: (context, index) {
                  final place = _results[index];
                  return ListTile(
                    leading: const Icon(Icons.location_city_outlined),
                    title: Text(place.name),
                    subtitle: Text(place.country),
                    onTap: () => widget.onSelected(place),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}
