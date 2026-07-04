import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_colors.dart';

// ============================================================
// SlotSelectionScreen
// ============================================================
//
// Allows a user to pick a specific parking slot for a chosen
// date, time window, and floor level.
//
// SECTIONS:
//   1. AppBar             — parking name + back button
//   2. Date Card          — selected booking date
//   3. Time Selector      — Start Time / End Time pickers
//   4. Level Selector     — floor tabs (G, 1, 2, 3)
//   5. Legend             — Available / Selected / Booked / Disabled
//   6. Slot Grid          — ~30 slots per floor, single-select
//   7. Booking Summary    — slot, duration, price, estimated total
//   8. Sticky Bottom Bar  — "Continue Booking" button
//
// STATE:
//   _selectedSlotId  — which slot is currently chosen (nullable)
//   _selectedLevel   — active floor index
//   _startTime       — TimeOfDay for start
//   _endTime         — TimeOfDay for end
//
// ARCHITECTURE:
//   Pure StatefulWidget — no Riverpod, Bloc, Provider.
//   All slot data is local dummy data.
// ============================================================

// ── Slot State Enum ───────────────────────────────────────────

enum _SlotState { available, selected, booked, disabled }

// ── Slot Model ────────────────────────────────────────────────

class _Slot {
  final String    id;
  final String    label;
  final _SlotState state;
  final bool      isEV;
  final bool      isHandicap;

  const _Slot({
    required this.id,
    required this.label,
    required this.state,
    this.isEV       = false,
    this.isHandicap = false,
  });

  _Slot copyWith({_SlotState? state}) => _Slot(
        id:         id,
        label:      label,
        state:      state ?? this.state,
        isEV:       isEV,
        isHandicap: isHandicap,
      );
}

// ── Floor Data ────────────────────────────────────────────────

class _Floor {
  final String       label;
  final List<_Slot>  slots;
  const _Floor({required this.label, required this.slots});
}

List<_Slot> _buildSlots(List<Map<String, dynamic>> specs) {
  return specs.map((s) {
    return _Slot(
      id:         s['id'] as String,
      label:      s['label'] as String,
      state:      s['state'] as _SlotState,
      isEV:       s['ev']   as bool? ?? false,
      isHandicap: s['hc']   as bool? ?? false,
    );
  }).toList();
}

final List<_Floor> _floors = [
  // ── Ground Floor ──────────────────────────────────────────
  _Floor(
    label: 'G',
    slots: _buildSlots([
      {'id': 'G-01', 'label': 'G01', 'state': _SlotState.booked},
      {'id': 'G-02', 'label': 'G02', 'state': _SlotState.booked},
      {'id': 'G-03', 'label': 'G03', 'state': _SlotState.available},
      {'id': 'G-04', 'label': 'G04', 'state': _SlotState.available},
      {'id': 'G-05', 'label': 'G05', 'state': _SlotState.disabled},
      {'id': 'G-06', 'label': 'G06', 'state': _SlotState.available, 'hc': true},
      {'id': 'G-07', 'label': 'G07', 'state': _SlotState.booked},
      {'id': 'G-08', 'label': 'G08', 'state': _SlotState.available},
      {'id': 'G-09', 'label': 'G09', 'state': _SlotState.available, 'ev': true},
      {'id': 'G-10', 'label': 'G10', 'state': _SlotState.booked},
      {'id': 'G-11', 'label': 'G11', 'state': _SlotState.available},
      {'id': 'G-12', 'label': 'G12', 'state': _SlotState.available},
      {'id': 'G-13', 'label': 'G13', 'state': _SlotState.disabled},
      {'id': 'G-14', 'label': 'G14', 'state': _SlotState.booked},
      {'id': 'G-15', 'label': 'G15', 'state': _SlotState.available},
      {'id': 'G-16', 'label': 'G16', 'state': _SlotState.available, 'ev': true},
      {'id': 'G-17', 'label': 'G17', 'state': _SlotState.booked},
      {'id': 'G-18', 'label': 'G18', 'state': _SlotState.available},
      {'id': 'G-19', 'label': 'G19', 'state': _SlotState.available},
      {'id': 'G-20', 'label': 'G20', 'state': _SlotState.booked},
      {'id': 'G-21', 'label': 'G21', 'state': _SlotState.available},
      {'id': 'G-22', 'label': 'G22', 'state': _SlotState.disabled},
      {'id': 'G-23', 'label': 'G23', 'state': _SlotState.available},
      {'id': 'G-24', 'label': 'G24', 'state': _SlotState.booked},
      {'id': 'G-25', 'label': 'G25', 'state': _SlotState.available},
      {'id': 'G-26', 'label': 'G26', 'state': _SlotState.available, 'hc': true},
      {'id': 'G-27', 'label': 'G27', 'state': _SlotState.booked},
      {'id': 'G-28', 'label': 'G28', 'state': _SlotState.available},
      {'id': 'G-29', 'label': 'G29', 'state': _SlotState.available},
      {'id': 'G-30', 'label': 'G30', 'state': _SlotState.booked},
    ]),
  ),

  // ── Floor 1 ───────────────────────────────────────────────
  _Floor(
    label: '1',
    slots: _buildSlots([
      {'id': '1-01', 'label': '101', 'state': _SlotState.available},
      {'id': '1-02', 'label': '102', 'state': _SlotState.available},
      {'id': '1-03', 'label': '103', 'state': _SlotState.booked},
      {'id': '1-04', 'label': '104', 'state': _SlotState.available},
      {'id': '1-05', 'label': '105', 'state': _SlotState.available, 'ev': true},
      {'id': '1-06', 'label': '106', 'state': _SlotState.booked},
      {'id': '1-07', 'label': '107', 'state': _SlotState.available},
      {'id': '1-08', 'label': '108', 'state': _SlotState.disabled},
      {'id': '1-09', 'label': '109', 'state': _SlotState.available},
      {'id': '1-10', 'label': '110', 'state': _SlotState.booked},
      {'id': '1-11', 'label': '111', 'state': _SlotState.available},
      {'id': '1-12', 'label': '112', 'state': _SlotState.available},
      {'id': '1-13', 'label': '113', 'state': _SlotState.booked},
      {'id': '1-14', 'label': '114', 'state': _SlotState.available, 'hc': true},
      {'id': '1-15', 'label': '115', 'state': _SlotState.available},
      {'id': '1-16', 'label': '116', 'state': _SlotState.booked},
      {'id': '1-17', 'label': '117', 'state': _SlotState.available},
      {'id': '1-18', 'label': '118', 'state': _SlotState.available},
      {'id': '1-19', 'label': '119', 'state': _SlotState.disabled},
      {'id': '1-20', 'label': '120', 'state': _SlotState.available},
      {'id': '1-21', 'label': '121', 'state': _SlotState.booked},
      {'id': '1-22', 'label': '122', 'state': _SlotState.available, 'ev': true},
      {'id': '1-23', 'label': '123', 'state': _SlotState.available},
      {'id': '1-24', 'label': '124', 'state': _SlotState.booked},
      {'id': '1-25', 'label': '125', 'state': _SlotState.available},
      {'id': '1-26', 'label': '126', 'state': _SlotState.available},
      {'id': '1-27', 'label': '127', 'state': _SlotState.booked},
      {'id': '1-28', 'label': '128', 'state': _SlotState.available},
      {'id': '1-29', 'label': '129', 'state': _SlotState.available},
      {'id': '1-30', 'label': '130', 'state': _SlotState.disabled},
    ]),
  ),

  // ── Floor 2 ───────────────────────────────────────────────
  _Floor(
    label: '2',
    slots: _buildSlots([
      {'id': '2-01', 'label': '201', 'state': _SlotState.available},
      {'id': '2-02', 'label': '202', 'state': _SlotState.booked},
      {'id': '2-03', 'label': '203', 'state': _SlotState.available},
      {'id': '2-04', 'label': '204', 'state': _SlotState.available, 'ev': true},
      {'id': '2-05', 'label': '205', 'state': _SlotState.booked},
      {'id': '2-06', 'label': '206', 'state': _SlotState.available},
      {'id': '2-07', 'label': '207', 'state': _SlotState.disabled},
      {'id': '2-08', 'label': '208', 'state': _SlotState.available},
      {'id': '2-09', 'label': '209', 'state': _SlotState.available},
      {'id': '2-10', 'label': '210', 'state': _SlotState.booked},
      {'id': '2-11', 'label': '211', 'state': _SlotState.available},
      {'id': '2-12', 'label': '212', 'state': _SlotState.available, 'hc': true},
      {'id': '2-13', 'label': '213', 'state': _SlotState.booked},
      {'id': '2-14', 'label': '214', 'state': _SlotState.available},
      {'id': '2-15', 'label': '215', 'state': _SlotState.available},
      {'id': '2-16', 'label': '216', 'state': _SlotState.available},
      {'id': '2-17', 'label': '217', 'state': _SlotState.booked},
      {'id': '2-18', 'label': '218', 'state': _SlotState.available},
      {'id': '2-19', 'label': '219', 'state': _SlotState.available, 'ev': true},
      {'id': '2-20', 'label': '220', 'state': _SlotState.disabled},
      {'id': '2-21', 'label': '221', 'state': _SlotState.available},
      {'id': '2-22', 'label': '222', 'state': _SlotState.booked},
      {'id': '2-23', 'label': '223', 'state': _SlotState.available},
      {'id': '2-24', 'label': '224', 'state': _SlotState.available},
      {'id': '2-25', 'label': '225', 'state': _SlotState.booked},
      {'id': '2-26', 'label': '226', 'state': _SlotState.available},
      {'id': '2-27', 'label': '227', 'state': _SlotState.available},
      {'id': '2-28', 'label': '228', 'state': _SlotState.disabled},
      {'id': '2-29', 'label': '229', 'state': _SlotState.available},
      {'id': '2-30', 'label': '230', 'state': _SlotState.booked},
    ]),
  ),

  // ── Floor 3 ───────────────────────────────────────────────
  _Floor(
    label: '3',
    slots: _buildSlots([
      {'id': '3-01', 'label': '301', 'state': _SlotState.available},
      {'id': '3-02', 'label': '302', 'state': _SlotState.available},
      {'id': '3-03', 'label': '303', 'state': _SlotState.available},
      {'id': '3-04', 'label': '304', 'state': _SlotState.booked},
      {'id': '3-05', 'label': '305', 'state': _SlotState.available, 'ev': true},
      {'id': '3-06', 'label': '306', 'state': _SlotState.available},
      {'id': '3-07', 'label': '307', 'state': _SlotState.available},
      {'id': '3-08', 'label': '308', 'state': _SlotState.booked},
      {'id': '3-09', 'label': '309', 'state': _SlotState.disabled},
      {'id': '3-10', 'label': '310', 'state': _SlotState.available},
      {'id': '3-11', 'label': '311', 'state': _SlotState.available, 'hc': true},
      {'id': '3-12', 'label': '312', 'state': _SlotState.available},
      {'id': '3-13', 'label': '313', 'state': _SlotState.booked},
      {'id': '3-14', 'label': '314', 'state': _SlotState.available},
      {'id': '3-15', 'label': '315', 'state': _SlotState.available},
      {'id': '3-16', 'label': '316', 'state': _SlotState.available, 'ev': true},
      {'id': '3-17', 'label': '317', 'state': _SlotState.booked},
      {'id': '3-18', 'label': '318', 'state': _SlotState.available},
      {'id': '3-19', 'label': '319', 'state': _SlotState.available},
      {'id': '3-20', 'label': '320', 'state': _SlotState.available},
      {'id': '3-21', 'label': '321', 'state': _SlotState.disabled},
      {'id': '3-22', 'label': '322', 'state': _SlotState.available},
      {'id': '3-23', 'label': '323', 'state': _SlotState.booked},
      {'id': '3-24', 'label': '324', 'state': _SlotState.available},
      {'id': '3-25', 'label': '325', 'state': _SlotState.available},
      {'id': '3-26', 'label': '326', 'state': _SlotState.available},
      {'id': '3-27', 'label': '327', 'state': _SlotState.booked},
      {'id': '3-28', 'label': '328', 'state': _SlotState.available},
      {'id': '3-29', 'label': '329', 'state': _SlotState.available},
      {'id': '3-30', 'label': '330', 'state': _SlotState.disabled},
    ]),
  ),
];

// ── Constants ─────────────────────────────────────────────────

const _parkingName    = 'Cyber Hub Parking Complex';
const _pricePerHour   = 40.0;

// ── Screen ────────────────────────────────────────────────────

class SlotSelectionScreen extends StatefulWidget {
  const SlotSelectionScreen({super.key});

  @override
  State<SlotSelectionScreen> createState() => _SlotSelectionScreenState();
}

class _SlotSelectionScreenState extends State<SlotSelectionScreen> {
  // ── Floor state ───────────────────────────────────────────
  int _selectedLevel = 0;

  // Per-floor mutable slot lists (so selection persists per floor)
  late final List<List<_Slot>> _floorSlots;

  // ── Selection state ───────────────────────────────────────
  String?   _selectedSlotId;
  int?      _selectedFloorIndex;

  // ── Time state ────────────────────────────────────────────
  TimeOfDay _startTime = const TimeOfDay(hour: 10, minute: 0);
  TimeOfDay _endTime   = const TimeOfDay(hour: 12, minute: 0);

  // ── Date ──────────────────────────────────────────────────
  DateTime  _selectedDate = DateTime.now().add(const Duration(days: 1));

  @override
  void initState() {
    super.initState();
    // Deep-copy floor slot lists so mutations don't affect the source.
    _floorSlots = _floors.map((f) => [...f.slots]).toList();
  }

  // ── Helpers ───────────────────────────────────────────────

  double get _durationHours {
    final startMins = _startTime.hour * 60 + _startTime.minute;
    final endMins   = _endTime.hour   * 60 + _endTime.minute;
    final diff      = endMins - startMins;
    return diff > 0 ? diff / 60.0 : 0;
  }

  double get _estimatedTotal => _durationHours * _pricePerHour;

  String _formatTime(TimeOfDay t) {
    final h  = t.hourOfPeriod == 0 ? 12 : t.hourOfPeriod;
    final m  = t.minute.toString().padLeft(2, '0');
    final ap = t.period == DayPeriod.am ? 'AM' : 'PM';
    return '$h:$m $ap';
  }

  String _formatDate(DateTime d) {
    const months = [
      '', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
      'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
    ];
    const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    return '${days[d.weekday - 1]}, ${d.day} ${months[d.month]} ${d.year}';
  }

  _Slot? get _selectedSlot {
    if (_selectedSlotId == null || _selectedFloorIndex == null) return null;
    try {
      return _floorSlots[_selectedFloorIndex!]
          .firstWhere((s) => s.id == _selectedSlotId);
    } catch (_) {
      return null;
    }
  }

  void _onSlotTap(int floorIndex, _Slot slot) {
    if (slot.state == _SlotState.booked ||
        slot.state == _SlotState.disabled) return;

    setState(() {
      // Deselect previously selected slot on any floor
      if (_selectedFloorIndex != null && _selectedSlotId != null) {
        final prev = _floorSlots[_selectedFloorIndex!];
        final prevIdx = prev.indexWhere((s) => s.id == _selectedSlotId);
        if (prevIdx != -1 && prev[prevIdx].state == _SlotState.selected) {
          prev[prevIdx] = prev[prevIdx].copyWith(state: _SlotState.available);
        }
      }

      final slots = _floorSlots[floorIndex];
      final idx   = slots.indexWhere((s) => s.id == slot.id);
      if (idx == -1) return;

      if (slot.state == _SlotState.selected) {
        // Deselect same slot
        slots[idx]       = slots[idx].copyWith(state: _SlotState.available);
        _selectedSlotId  = null;
        _selectedFloorIndex = null;
      } else {
        // Select new slot
        slots[idx]          = slots[idx].copyWith(state: _SlotState.selected);
        _selectedSlotId     = slot.id;
        _selectedFloorIndex = floorIndex;
      }
    });
  }

  Future<void> _pickTime({required bool isStart}) async {
    final initial = isStart ? _startTime : _endTime;
    final picked  = await showTimePicker(
      context:     context,
      initialTime: initial,
      builder: (ctx, child) => Theme(
        data: Theme.of(ctx).copyWith(
          colorScheme: const ColorScheme.light(
            primary:   AppColors.primary,
            onPrimary: AppColors.onPrimary,
            surface:   AppColors.surface,
          ),
        ),
        child: child!,
      ),
    );
    if (picked == null || !mounted) return;
    setState(() {
      if (isStart) {
        _startTime = picked;
        // Ensure end is always after start
        final startMins = picked.hour * 60 + picked.minute;
        final endMins   = _endTime.hour * 60 + _endTime.minute;
        if (endMins <= startMins) {
          final newEnd = startMins + 60;
          _endTime = TimeOfDay(
            hour:   (newEnd ~/ 60) % 24,
            minute: newEnd % 60,
          );
        }
      } else {
        _endTime = picked;
      }
    });
  }

  Future<void> _pickDate() async {
    final now    = DateTime.now();
    final picked = await showDatePicker(
      context:      context,
      initialDate:  _selectedDate,
      firstDate:    now,
      lastDate:     now.add(const Duration(days: 30)),
      builder: (ctx, child) => Theme(
        data: Theme.of(ctx).copyWith(
          colorScheme: const ColorScheme.light(
            primary:   AppColors.primary,
            onPrimary: AppColors.onPrimary,
            surface:   AppColors.surface,
          ),
        ),
        child: child!,
      ),
    );
    if (picked == null || !mounted) return;
    setState(() => _selectedDate = picked);
  }

  // ── Build ─────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.sizeOf(context).width;
    final hPad        = screenWidth > 600 ? screenWidth * 0.08 : 16.0;

    return AnnotatedRegion<SystemUiOverlayStyle>(
      value: SystemUiOverlayStyle.dark,
      child: Scaffold(
        backgroundColor: AppColors.background,
        appBar:          _buildAppBar(context),
        body: Stack(
          children: [
            ListView(
              padding: EdgeInsets.fromLTRB(hPad, 16, hPad, 140),
              children: [

                // ── 1. Date Card ─────────────────────────
                _DateCard(
                  date:     _formatDate(_selectedDate),
                  onTap:    _pickDate,
                ),

                const SizedBox(height: 16),

                // ── 2. Time Selector ─────────────────────
                _TimeSelector(
                  startTime:   _formatTime(_startTime),
                  endTime:     _formatTime(_endTime),
                  duration:    _durationHours,
                  onStartTap:  () => _pickTime(isStart: true),
                  onEndTap:    () => _pickTime(isStart: false),
                ),

                const SizedBox(height: 20),

                // ── 3. Level Selector ─────────────────────
                _LevelSelector(
                  levels:        _floors.map((f) => f.label).toList(),
                  selectedIndex: _selectedLevel,
                  floorSlots:    _floorSlots,
                  onTap: (i) => setState(() => _selectedLevel = i),
                ),

                const SizedBox(height: 18),

                // ── 4. Legend ────────────────────────────
                const _Legend(),

                const SizedBox(height: 18),

                // ── 5. Slot Grid ─────────────────────────
                _SlotGrid(
                  slots:       _floorSlots[_selectedLevel],
                  floorIndex:  _selectedLevel,
                  onSlotTap:   _onSlotTap,
                ),

                const SizedBox(height: 20),

                // ── 6. Booking Summary ───────────────────
                _BookingSummary(
                  selectedSlot:   _selectedSlot,
                  duration:       _durationHours,
                  pricePerHour:   _pricePerHour,
                  estimatedTotal: _estimatedTotal,
                ),
              ],
            ),

            // ── 7. Sticky Bottom Bar ─────────────────────
            Positioned(
              bottom: 0,
              left:   0,
              right:  0,
              child:  _StickyBottomBar(
                hasSelection:  _selectedSlotId != null,
                total:         _estimatedTotal,
                slotLabel:     _selectedSlot?.label,
              ),
            ),
          ],
        ),
      ),
    );
  }

  PreferredSizeWidget _buildAppBar(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return AppBar(
      backgroundColor:  AppColors.surface,
      elevation:        0,
      surfaceTintColor: Colors.transparent,
      leading: IconButton(
        icon:  const Icon(Icons.arrow_back_ios_rounded, size: 20),
        color: AppColors.textPrimary,
        onPressed: () => Navigator.of(context).maybePop(),
      ),
      title: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Select Slot',
            style: textTheme.titleMedium?.copyWith(
              color:      AppColors.textPrimary,
              fontWeight: FontWeight.w700,
            ),
          ),
          Text(
            _parkingName,
            style: textTheme.bodySmall?.copyWith(
              color: AppColors.textSecondary,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
      bottom: PreferredSize(
        preferredSize: const Size.fromHeight(1),
        child:         Divider(height: 1, color: AppColors.divider),
      ),
    );
  }
}

// ── 1. Date Card ──────────────────────────────────────────────

class _DateCard extends StatelessWidget {
  final String       date;
  final VoidCallback onTap;

  const _DateCard({required this.date, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color:        AppColors.surface,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.divider, width: 1),
          boxShadow: [
            BoxShadow(
              color:       AppColors.shadow,
              blurRadius:  8,
              offset:      const Offset(0, 3),
            ),
          ],
        ),
        child: Row(
          children: [
            Container(
              width:  42,
              height: 42,
              decoration: BoxDecoration(
                color:        AppColors.primary.withAlpha(12),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: AppColors.primary.withAlpha(35),
                  width: 1,
                ),
              ),
              child: const Icon(
                Icons.calendar_today_rounded,
                color: AppColors.primary,
                size:  20,
              ),
            ),

            const SizedBox(width: 14),

            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Booking Date',
                    style: textTheme.bodySmall?.copyWith(
                      color: AppColors.textTertiary,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    date,
                    style: textTheme.titleSmall?.copyWith(
                      color:      AppColors.textPrimary,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ],
              ),
            ),

            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
              decoration: BoxDecoration(
                color:        AppColors.primary.withAlpha(12),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                'Change',
                style: textTheme.labelSmall?.copyWith(
                  color:      AppColors.primary,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── 2. Time Selector ──────────────────────────────────────────

class _TimeSelector extends StatelessWidget {
  final String       startTime;
  final String       endTime;
  final double       duration;
  final VoidCallback onStartTap;
  final VoidCallback onEndTap;

  const _TimeSelector({
    required this.startTime,
    required this.endTime,
    required this.duration,
    required this.onStartTap,
    required this.onEndTap,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      padding:      const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color:        AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.divider, width: 1),
        boxShadow: [
          BoxShadow(
            color:       AppColors.shadow,
            blurRadius:  8,
            offset:      const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        children: [
          Row(
            children: [
              // Start Time
              Expanded(
                child: _TimeButton(
                  label:   'Start Time',
                  time:    startTime,
                  icon:    Icons.login_rounded,
                  color:   AppColors.primary,
                  onTap:   onStartTap,
                ),
              ),

              // Arrow + duration
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 10),
                child: Column(
                  children: [
                    const Icon(
                      Icons.arrow_forward_rounded,
                      color: AppColors.textTertiary,
                      size:  20,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      duration > 0
                          ? '${duration.toStringAsFixed(1)}h'
                          : '—',
                      style: textTheme.labelSmall?.copyWith(
                        color:      AppColors.primary,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ],
                ),
              ),

              // End Time
              Expanded(
                child: _TimeButton(
                  label:   'End Time',
                  time:    endTime,
                  icon:    Icons.logout_rounded,
                  color:   AppColors.secondaryDark,
                  onTap:   onEndTap,
                ),
              ),
            ],
          ),

          if (duration <= 0) ...[
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color:        AppColors.errorLight,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(
                    Icons.warning_amber_rounded,
                    color: AppColors.error,
                    size:  14,
                  ),
                  const SizedBox(width: 6),
                  Text(
                    'End time must be after start time',
                    style: textTheme.bodySmall?.copyWith(
                      color:      AppColors.error,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _TimeButton extends StatelessWidget {
  final String       label;
  final String       time;
  final IconData     icon;
  final Color        color;
  final VoidCallback onTap;

  const _TimeButton({
    required this.label,
    required this.time,
    required this.icon,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding:      const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color:        color.withAlpha(12),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: color.withAlpha(40), width: 1),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(icon, size: 13, color: color),
                const SizedBox(width: 5),
                Text(
                  label,
                  style: textTheme.bodySmall?.copyWith(
                    color:    color,
                    fontSize: 11,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 6),
            Text(
              time,
              style: textTheme.titleSmall?.copyWith(
                color:      AppColors.textPrimary,
                fontWeight: FontWeight.w800,
                fontSize:   16,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── 3. Level Selector ─────────────────────────────────────────

class _LevelSelector extends StatelessWidget {
  final List<String>       levels;
  final int                selectedIndex;
  final List<List<_Slot>>  floorSlots;
  final ValueChanged<int>  onTap;

  const _LevelSelector({
    required this.levels,
    required this.selectedIndex,
    required this.floorSlots,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Container(
              width:  4,
              height: 18,
              decoration: BoxDecoration(
                color:        AppColors.primary,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            const SizedBox(width: 8),
            Text(
              'Select Level',
              style: textTheme.titleSmall?.copyWith(
                color:      AppColors.textPrimary,
                fontWeight: FontWeight.w700,
                fontSize:   15,
              ),
            ),
          ],
        ),

        const SizedBox(height: 12),

        Row(
          children: List.generate(levels.length, (index) {
            final isSelected  = index == selectedIndex;
            final available   = floorSlots[index]
                .where((s) => s.state == _SlotState.available)
                .length;

            return Expanded(
              child: GestureDetector(
                onTap: () => onTap(index),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 180),
                  margin: EdgeInsets.only(right: index < levels.length - 1 ? 10 : 0),
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  decoration: BoxDecoration(
                    color: isSelected ? AppColors.primary : AppColors.surface,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(
                      color: isSelected ? AppColors.primary : AppColors.divider,
                      width: isSelected ? 0 : 1,
                    ),
                    boxShadow: isSelected
                        ? [
                            BoxShadow(
                              color:       AppColors.primary.withAlpha(60),
                              blurRadius:  10,
                              offset:      const Offset(0, 4),
                            ),
                          ]
                        : [
                            BoxShadow(
                              color:       AppColors.shadow,
                              blurRadius:  6,
                              offset:      const Offset(0, 2),
                            ),
                          ],
                  ),
                  child: Column(
                    children: [
                      Text(
                        index == 0 ? 'G' : 'F${levels[index]}',
                        style: textTheme.titleSmall?.copyWith(
                          color:      isSelected
                              ? AppColors.onPrimary
                              : AppColors.textPrimary,
                          fontWeight: FontWeight.w800,
                          fontSize:   16,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '$available free',
                        style: textTheme.bodySmall?.copyWith(
                          color:    isSelected
                              ? AppColors.onPrimary.withAlpha(200)
                              : AppColors.textTertiary,
                          fontSize: 10,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            );
          }),
        ),
      ],
    );
  }
}

// ── 4. Legend ─────────────────────────────────────────────────

class _Legend extends StatelessWidget {
  const _Legend();

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceAround,
      children: const [
        _LegendItem(
          color:  AppColors.successLight,
          border: AppColors.secondaryDark,
          label:  'Available',
        ),
        _LegendItem(
          color:  AppColors.primary,
          border: AppColors.primaryDark,
          label:  'Selected',
          isSelected: true,
        ),
        _LegendItem(
          color:  AppColors.errorLight,
          border: AppColors.error,
          label:  'Booked',
        ),
        _LegendItem(
          color:  AppColors.surfaceVariant,
          border: AppColors.divider,
          label:  'Disabled',
        ),
      ],
    );
  }
}

class _LegendItem extends StatelessWidget {
  final Color  color;
  final Color  border;
  final String label;
  final bool   isSelected;

  const _LegendItem({
    required this.color,
    required this.border,
    required this.label,
    this.isSelected = false,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width:  18,
          height: 18,
          decoration: BoxDecoration(
            color:        color,
            borderRadius: BorderRadius.circular(5),
            border: Border.all(color: border, width: 1.5),
          ),
          child: isSelected
              ? const Icon(Icons.check, size: 11, color: AppColors.onPrimary)
              : null,
        ),
        const SizedBox(width: 5),
        Text(
          label,
          style: textTheme.bodySmall?.copyWith(
            color:    AppColors.textSecondary,
            fontSize: 11,
          ),
        ),
      ],
    );
  }
}

// ── 5. Slot Grid ──────────────────────────────────────────────

class _SlotGrid extends StatelessWidget {
  final List<_Slot>                         slots;
  final int                                 floorIndex;
  final void Function(int, _Slot)           onSlotTap;

  const _SlotGrid({
    required this.slots,
    required this.floorIndex,
    required this.onSlotTap,
  });

  @override
  Widget build(BuildContext context) {
    // Divider row every 10 slots to simulate parking lane separators
    final rows  = <Widget>[];
    const cols  = 5;
    const lanes = 10; // slots per lane

    for (int i = 0; i < slots.length; i += lanes) {
      final laneSlots = slots.sublist(
        i,
        (i + lanes) > slots.length ? slots.length : i + lanes,
      );

      rows.add(_LaneRow(
        slots:      laneSlots,
        floorIndex: floorIndex,
        onSlotTap:  onSlotTap,
        cols:       cols,
      ));

      if (i + lanes < slots.length) {
        // Lane separator
        rows.add(
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 6),
            child: Row(
              children: [
                Expanded(child: Divider(color: AppColors.divider, height: 1)),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 10),
                  child: Text(
                    '← LANE ${rows.length ~/ 2 + 1} →',
                    style: TextStyle(
                      color:    AppColors.textTertiary,
                      fontSize: 9,
                      fontWeight: FontWeight.w600,
                      letterSpacing: 1,
                    ),
                  ),
                ),
                Expanded(child: Divider(color: AppColors.divider, height: 1)),
              ],
            ),
          ),
        );
      }
    }

    return Container(
      padding:      const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color:        AppColors.surface,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.divider, width: 1),
        boxShadow: [
          BoxShadow(
            color:       AppColors.shadow,
            blurRadius:  10,
            offset:      const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: [
          // Entry / Exit label
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              _EntryExitChip(label: '↑ ENTRY', color: AppColors.secondary),
              _EntryExitChip(label: 'EXIT ↑', color: AppColors.error),
            ],
          ),
          const SizedBox(height: 12),
          ...rows,
        ],
      ),
    );
  }
}

class _EntryExitChip extends StatelessWidget {
  final String label;
  final Color  color;
  const _EntryExitChip({required this.label, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color:        color.withAlpha(15),
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: color.withAlpha(50), width: 1),
      ),
      child: Text(
        label,
        style: TextStyle(
          color:      color,
          fontSize:   10,
          fontWeight: FontWeight.w700,
          letterSpacing: 0.5,
        ),
      ),
    );
  }
}

class _LaneRow extends StatelessWidget {
  final List<_Slot>               slots;
  final int                       floorIndex;
  final void Function(int, _Slot) onSlotTap;
  final int                       cols;

  const _LaneRow({
    required this.slots,
    required this.floorIndex,
    required this.onSlotTap,
    required this.cols,
  });

  @override
  Widget build(BuildContext context) {
    return GridView.builder(
      shrinkWrap:  true,
      physics:     const NeverScrollableScrollPhysics(),
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount:   cols,
        mainAxisSpacing:  8,
        crossAxisSpacing: 8,
        childAspectRatio: 0.9,
      ),
      itemCount: slots.length,
      itemBuilder: (context, index) => _SlotTile(
        slot:       slots[index],
        floorIndex: floorIndex,
        onTap:      () => onSlotTap(floorIndex, slots[index]),
      ),
    );
  }
}

class _SlotTile extends StatelessWidget {
  final _Slot    slot;
  final int      floorIndex;
  final VoidCallback onTap;

  const _SlotTile({
    required this.slot,
    required this.floorIndex,
    required this.onTap,
  });

  Color get _bgColor {
    return switch (slot.state) {
      _SlotState.available => AppColors.successLight,
      _SlotState.selected  => AppColors.primary,
      _SlotState.booked    => AppColors.errorLight,
      _SlotState.disabled  => AppColors.surfaceVariant,
    };
  }

  Color get _borderColor {
    return switch (slot.state) {
      _SlotState.available => AppColors.secondaryDark,
      _SlotState.selected  => AppColors.primaryDark,
      _SlotState.booked    => AppColors.error,
      _SlotState.disabled  => AppColors.divider,
    };
  }

  Color get _labelColor {
    return switch (slot.state) {
      _SlotState.available => AppColors.secondaryDark,
      _SlotState.selected  => AppColors.onPrimary,
      _SlotState.booked    => AppColors.error,
      _SlotState.disabled  => AppColors.textDisabled,
    };
  }

  bool get _isInteractive =>
      slot.state == _SlotState.available ||
      slot.state == _SlotState.selected;

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return GestureDetector(
      onTap: _isInteractive ? onTap : null,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 160),
        decoration: BoxDecoration(
          color:        _bgColor,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(
            color: _borderColor,
            width: slot.state == _SlotState.selected ? 2 : 1,
          ),
          boxShadow: slot.state == _SlotState.selected
              ? [
                  BoxShadow(
                    color:       AppColors.primary.withAlpha(70),
                    blurRadius:  8,
                    offset:      const Offset(0, 3),
                  ),
                ]
              : null,
        ),
        child: Stack(
          children: [
            // Slot label
            Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (slot.state == _SlotState.selected)
                    const Icon(Icons.check_rounded, color: AppColors.onPrimary, size: 14)
                  else if (slot.state == _SlotState.booked)
                    Icon(Icons.directions_car_rounded, color: AppColors.error, size: 14)
                  else if (slot.state == _SlotState.disabled)
                    Icon(Icons.block_rounded, color: AppColors.textDisabled, size: 14)
                  else
                    const SizedBox(height: 14),
                  const SizedBox(height: 2),
                  Text(
                    slot.label,
                    style: textTheme.labelSmall?.copyWith(
                      color:      _labelColor,
                      fontWeight: FontWeight.w700,
                      fontSize:   9,
                    ),
                  ),
                ],
              ),
            ),

            // EV badge
            if (slot.isEV)
              Positioned(
                top:   3,
                right: 3,
                child: Container(
                  width:  10,
                  height: 10,
                  decoration: BoxDecoration(
                    color:  AppColors.info,
                    shape:  BoxShape.circle,
                  ),
                  child: const Center(
                    child: Text(
                      'E',
                      style: TextStyle(
                        color:      AppColors.white,
                        fontSize:   6,
                        fontWeight: FontWeight.w900,
                        height:     1,
                      ),
                    ),
                  ),
                ),
              ),

            // Handicap badge
            if (slot.isHandicap)
              Positioned(
                top:   3,
                left:  3,
                child: Icon(
                  Icons.accessible_rounded,
                  size:  9,
                  color: slot.state == _SlotState.selected
                      ? AppColors.onPrimary
                      : AppColors.info,
                ),
              ),
          ],
        ),
      ),
    );
  }
}

// ── 6. Booking Summary ────────────────────────────────────────

class _BookingSummary extends StatelessWidget {
  final _Slot?  selectedSlot;
  final double  duration;
  final double  pricePerHour;
  final double  estimatedTotal;

  const _BookingSummary({
    required this.selectedSlot,
    required this.duration,
    required this.pricePerHour,
    required this.estimatedTotal,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme  = Theme.of(context).textTheme;
    final hasSlot    = selectedSlot != null;
    final validTime  = duration > 0;

    return AnimatedContainer(
      duration: const Duration(milliseconds: 250),
      padding:      const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: hasSlot ? AppColors.surface : AppColors.surfaceVariant,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: hasSlot ? AppColors.primary.withAlpha(60) : AppColors.divider,
          width: hasSlot ? 1.5 : 1,
        ),
        boxShadow: hasSlot
            ? [
                BoxShadow(
                  color:       AppColors.primary.withAlpha(20),
                  blurRadius:  14,
                  offset:      const Offset(0, 5),
                ),
              ]
            : null,
      ),
      child: hasSlot
          ? Column(
              children: [
                // ── Header ─────────────────────────────
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color:        AppColors.primary.withAlpha(12),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Icon(
                        Icons.receipt_long_rounded,
                        color: AppColors.primary,
                        size:  18,
                      ),
                    ),
                    const SizedBox(width: 10),
                    Text(
                      'Booking Summary',
                      style: textTheme.titleSmall?.copyWith(
                        color:      AppColors.textPrimary,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 16),
                Divider(color: AppColors.divider, height: 1),
                const SizedBox(height: 14),

                // ── Rows ────────────────────────────────
                _SummaryRow(
                  label: 'Selected Slot',
                  value: selectedSlot!.label,
                  icon:  Icons.local_parking_rounded,
                  valueColor: AppColors.primary,
                ),
                const SizedBox(height: 10),
                _SummaryRow(
                  label: 'Duration',
                  value: validTime
                      ? '${duration.toStringAsFixed(1)} hrs'
                      : '—',
                  icon:  Icons.access_time_rounded,
                ),
                const SizedBox(height: 10),
                _SummaryRow(
                  label: 'Price per Hour',
                  value: '₹${pricePerHour.toStringAsFixed(0)}',
                  icon:  Icons.currency_rupee_rounded,
                ),

                const SizedBox(height: 14),
                Divider(color: AppColors.divider, height: 1),
                const SizedBox(height: 14),

                // ── Total ────────────────────────────────
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Estimated Total',
                      style: textTheme.titleSmall?.copyWith(
                        color:      AppColors.textPrimary,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    Text(
                      validTime
                          ? '₹${estimatedTotal.toStringAsFixed(0)}'
                          : '—',
                      style: textTheme.titleMedium?.copyWith(
                        color:      AppColors.primary,
                        fontWeight: FontWeight.w800,
                        fontSize:   20,
                      ),
                    ),
                  ],
                ),

                if (selectedSlot!.isEV) ...[
                  const SizedBox(height: 10),
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color:        AppColors.infoLight,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Row(
                      children: [
                        const Icon(
                          Icons.ev_station_rounded,
                          color: AppColors.info,
                          size:  16,
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            'EV charging included at no extra cost.',
                            style: textTheme.bodySmall?.copyWith(
                              color:      AppColors.info,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ],
            )
          : Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(
                  Icons.touch_app_outlined,
                  color: AppColors.textTertiary,
                  size:  20,
                ),
                const SizedBox(width: 10),
                Text(
                  'Tap an available slot to see summary',
                  style: textTheme.bodyMedium?.copyWith(
                    color: AppColors.textTertiary,
                  ),
                ),
              ],
            ),
    );
  }
}

class _SummaryRow extends StatelessWidget {
  final String  label;
  final String  value;
  final IconData icon;
  final Color?  valueColor;

  const _SummaryRow({
    required this.label,
    required this.value,
    required this.icon,
    this.valueColor,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Row(
      children: [
        Icon(icon, size: 15, color: AppColors.textTertiary),
        const SizedBox(width: 8),
        Text(
          label,
          style: textTheme.bodyMedium?.copyWith(
            color: AppColors.textSecondary,
          ),
        ),
        const Spacer(),
        Text(
          value,
          style: textTheme.bodyMedium?.copyWith(
            color:      valueColor ?? AppColors.textPrimary,
            fontWeight: FontWeight.w700,
          ),
        ),
      ],
    );
  }
}

// ── 7. Sticky Bottom Bar ──────────────────────────────────────

class _StickyBottomBar extends StatelessWidget {
  final bool    hasSelection;
  final double  total;
  final String? slotLabel;

  const _StickyBottomBar({
    required this.hasSelection,
    required this.total,
    required this.slotLabel,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        boxShadow: [
          BoxShadow(
            color:       AppColors.shadow,
            blurRadius:  20,
            spreadRadius: 0,
            offset:      const Offset(0, -6),
          ),
        ],
        border: const Border(
          top: BorderSide(color: AppColors.divider, width: 1),
        ),
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
          child: Row(
            children: [
              // ── Summary preview ────────────────────
              if (hasSelection) ...[
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize:       MainAxisSize.min,
                  children: [
                    Text(
                      'Slot ${slotLabel ?? ''}',
                      style: textTheme.bodySmall?.copyWith(
                        color: AppColors.textTertiary,
                      ),
                    ),
                    Text(
                      total > 0
                          ? '₹${total.toStringAsFixed(0)}'
                          : '—',
                      style: textTheme.headlineSmall?.copyWith(
                        color:      AppColors.primary,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ],
                ),
                const SizedBox(width: 16),
              ],

              // ── CTA Button ─────────────────────────
              Expanded(
                child: SizedBox(
                  height: 52,
                  child: FilledButton(
                    onPressed: hasSelection && total > 0 ? () {} : null,
                    style: FilledButton.styleFrom(
                      backgroundColor:         AppColors.primary,
                      disabledBackgroundColor: AppColors.divider,
                      foregroundColor:         AppColors.onPrimary,
                      disabledForegroundColor: AppColors.textTertiary,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                      textStyle: textTheme.labelLarge?.copyWith(
                        fontSize:      16,
                        fontWeight:    FontWeight.w700,
                        letterSpacing: 0.4,
                      ),
                      elevation:   0,
                      shadowColor: Colors.transparent,
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.check_circle_outline_rounded, size: 18),
                        const SizedBox(width: 8),
                        Text(
                          hasSelection
                              ? 'Continue Booking'
                              : 'Select a Slot',
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}