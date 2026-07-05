import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_colors.dart';

// ============================================================
// MyBookingsScreen
// ============================================================
//
// Displays all of the user's parking bookings grouped by status.
//
// SECTIONS:
//   1. AppBar        — "My Bookings" + total count badge
//   2. Summary row   — stat chips (Active / Upcoming / History)
//   3. Filter chips  — Active / Upcoming / History tabs
//   4. Booking cards — filtered, scrollable list
//   5. Empty state   — shown when no bookings match the filter
//
// BOOKING STATUSES:
//   active    → currently checked in
//   upcoming  → confirmed, not yet started
//   completed → finished successfully
//   cancelled → cancelled by user or system
//
// ARCHITECTURE:
//   Pure StatefulWidget — no Riverpod, Bloc, Provider.
//   All data is local dummy data.
// ============================================================

// ── Booking Status Enum ───────────────────────────────────────

enum _BookingStatus { active, upcoming, completed, cancelled }

extension _BookingStatusX on _BookingStatus {
  String get label => switch (this) {
        _BookingStatus.active    => 'Active',
        _BookingStatus.upcoming  => 'Upcoming',
        _BookingStatus.completed => 'Completed',
        _BookingStatus.cancelled => 'Cancelled',
      };

  Color get color => switch (this) {
        _BookingStatus.active    => AppColors.info,
        _BookingStatus.upcoming  => AppColors.secondary,
        _BookingStatus.completed => AppColors.secondaryDark,
        _BookingStatus.cancelled => AppColors.error,
      };

  Color get bgColor => switch (this) {
        _BookingStatus.active    => AppColors.infoLight,
        _BookingStatus.upcoming  => AppColors.successLight,
        _BookingStatus.completed => AppColors.successLight,
        _BookingStatus.cancelled => AppColors.errorLight,
      };

  IconData get icon => switch (this) {
        _BookingStatus.active    => Icons.radio_button_checked_rounded,
        _BookingStatus.upcoming  => Icons.schedule_rounded,
        _BookingStatus.completed => Icons.check_circle_rounded,
        _BookingStatus.cancelled => Icons.cancel_rounded,
      };
}

// ── Filter Tab Enum ───────────────────────────────────────────

enum _FilterTab { active, upcoming, history }

extension _FilterTabX on _FilterTab {
  String get label => switch (this) {
        _FilterTab.active   => 'Active',
        _FilterTab.upcoming => 'Upcoming',
        _FilterTab.history  => 'History',
      };

  bool matches(_BookingStatus status) => switch (this) {
        _FilterTab.active   => status == _BookingStatus.active,
        _FilterTab.upcoming => status == _BookingStatus.upcoming,
        _FilterTab.history  =>
          status == _BookingStatus.completed ||
          status == _BookingStatus.cancelled,
      };
}

// ── Booking Model ─────────────────────────────────────────────

class _Booking {
  final String         id;
  final String         bookingId;
  final String         parkingName;
  final String         address;
  final String         date;
  final String         startTime;
  final String         endTime;
  final String         slot;
  final String         floor;
  final String         duration;
  final String         amountPaid;
  final String         vehicleNumber;
  final _BookingStatus status;

  const _Booking({
    required this.id,
    required this.bookingId,
    required this.parkingName,
    required this.address,
    required this.date,
    required this.startTime,
    required this.endTime,
    required this.slot,
    required this.floor,
    required this.duration,
    required this.amountPaid,
    required this.vehicleNumber,
    required this.status,
  });
}

// ── Dummy Data ────────────────────────────────────────────────

const List<_Booking> _allBookings = [
  _Booking(
    id:            '1',
    bookingId:     'BKG-2025-78421',
    parkingName:   'Cyber Hub Parking Complex',
    address:       'DLF Cyber Hub, Sector 24, Gurugram',
    date:          'Sat, 12 Jul 2025',
    startTime:     '10:00 AM',
    endTime:       '12:00 PM',
    slot:          'G09',
    floor:         'Ground Floor',
    duration:      '2 hrs',
    amountPaid:    '₹80',
    vehicleNumber: 'DL 01 AB 1234',
    status:        _BookingStatus.active,
  ),
  _Booking(
    id:            '2',
    bookingId:     'BKG-2025-78356',
    parkingName:   'Ambience Mall Parking',
    address:       'NH-48, Sheetla Mata Rd, Gurugram',
    date:          'Sun, 13 Jul 2025',
    startTime:     '02:00 PM',
    endTime:       '05:00 PM',
    slot:          '108',
    floor:         'Floor 1',
    duration:      '3 hrs',
    amountPaid:    '₹90',
    vehicleNumber: 'HR 26 BC 5678',
    status:        _BookingStatus.upcoming,
  ),
  _Booking(
    id:            '3',
    bookingId:     'BKG-2025-77910',
    parkingName:   'MGF Metropolitan Mall',
    address:       'MG Road, Sikanderpur, Gurugram',
    date:          'Mon, 7 Jul 2025',
    startTime:     '11:00 AM',
    endTime:       '01:00 PM',
    slot:          '215',
    floor:         'Floor 2',
    duration:      '2 hrs',
    amountPaid:    '₹70',
    vehicleNumber: 'DL 01 AB 1234',
    status:        _BookingStatus.completed,
  ),
  _Booking(
    id:            '4',
    bookingId:     'BKG-2025-77603',
    parkingName:   'Sector 29 Public Parking',
    address:       'Sector 29, HUDA Market, Gurugram',
    date:          'Fri, 4 Jul 2025',
    startTime:     '09:00 AM',
    endTime:       '10:00 AM',
    slot:          'G14',
    floor:         'Ground Floor',
    duration:      '1 hr',
    amountPaid:    '₹20',
    vehicleNumber: 'UP 16 CD 9012',
    status:        _BookingStatus.cancelled,
  ),
  _Booking(
    id:            '5',
    bookingId:     'BKG-2025-77280',
    parkingName:   'DLF Phase 2 EV Hub',
    address:       'DLF Phase 2, Sector 25, Gurugram',
    date:          'Wed, 2 Jul 2025',
    startTime:     '03:00 PM',
    endTime:       '06:00 PM',
    slot:          '316',
    floor:         'Floor 3',
    duration:      '3 hrs',
    amountPaid:    '₹150',
    vehicleNumber: 'UP 16 CD 9012',
    status:        _BookingStatus.completed,
  ),
];

// ── Screen ────────────────────────────────────────────────────

class MyBookingsScreen extends StatefulWidget {
  const MyBookingsScreen({super.key});

  @override
  State<MyBookingsScreen> createState() => _MyBookingsScreenState();
}

class _MyBookingsScreenState extends State<MyBookingsScreen>
    with SingleTickerProviderStateMixin {
  _FilterTab _activeFilter = _FilterTab.active;

  late final AnimationController _fadeController;

  @override
  void initState() {
    super.initState();

    SystemChrome.setSystemUIOverlayStyle(
      const SystemUiOverlayStyle(
        statusBarColor:          Colors.transparent,
        statusBarIconBrightness: Brightness.dark,
        statusBarBrightness:     Brightness.light,
      ),
    );

    _fadeController = AnimationController(
      vsync:    this,
      duration: const Duration(milliseconds: 300),
    )..forward();
  }

  @override
  void dispose() {
    _fadeController.dispose();
    super.dispose();
  }

  void _switchFilter(_FilterTab tab) {
    if (_activeFilter == tab) return;
    setState(() => _activeFilter = tab);
    _fadeController
      ..reset()
      ..forward();
  }

  List<_Booking> get _filtered =>
      _allBookings.where((b) => _activeFilter.matches(b.status)).toList();

  int _countFor(_FilterTab tab) =>
      _allBookings.where((b) => tab.matches(b.status)).length;

  // ── Build ─────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.sizeOf(context).width;
    final hPad        = screenWidth > 600 ? screenWidth * 0.08 : 20.0;
    final filtered    = _filtered;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar:          _buildAppBar(context),
      body: Column(
        children: [

          // ── Stats + Filters (sticky header) ─────────
          Container(
            color: AppColors.surface,
            child: Column(
              children: [
                // Stats row
                Padding(
                  padding: EdgeInsets.fromLTRB(hPad, 16, hPad, 12),
                  child:   _StatsRow(countFor: _countFor),
                ),

                // Filter chips
                SizedBox(
                  height: 44,
                  child: ListView(
                    scrollDirection:  Axis.horizontal,
                    padding: EdgeInsets.symmetric(horizontal: hPad),
                    children: _FilterTab.values.map((tab) {
                      return Padding(
                        padding: const EdgeInsets.only(right: 10),
                        child: _FilterChip(
                          tab:        tab,
                          isSelected: _activeFilter == tab,
                          count:      _countFor(tab),
                          onTap:      () => _switchFilter(tab),
                        ),
                      );
                    }).toList(),
                  ),
                ),

                const SizedBox(height: 12),
                Divider(height: 1, color: AppColors.divider),
              ],
            ),
          ),

          // ── Booking list / empty state ───────────────
          Expanded(
            child: filtered.isEmpty
                ? _EmptyState(filter: _activeFilter)
                : FadeTransition(
                    opacity: CurvedAnimation(
                      parent: _fadeController,
                      curve:  Curves.easeOut,
                    ),
                    child: ListView.separated(
                      padding: EdgeInsets.fromLTRB(hPad, 20, hPad, 32),
                      itemCount:    filtered.length,
                      separatorBuilder: (_, __) => const SizedBox(height: 16),
                      itemBuilder: (context, index) => _BookingCard(
                        booking: filtered[index],
                      ),
                    ),
                  ),
          ),
        ],
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
      title: Text(
        'My Bookings',
        style: textTheme.titleLarge?.copyWith(
          color:      AppColors.textPrimary,
          fontWeight: FontWeight.w700,
        ),
      ),
      centerTitle: true,
      actions: [
        // Total booking count badge
        Container(
          margin: const EdgeInsets.only(right: 16),
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
          decoration: BoxDecoration(
            color:        AppColors.primary.withAlpha(12),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(
              color: AppColors.primary.withAlpha(35),
              width: 1,
            ),
          ),
          child: Text(
            '${_allBookings.length} total',
            style: textTheme.labelSmall?.copyWith(
              color:      AppColors.primary,
              fontWeight: FontWeight.w700,
            ),
          ),
        ),
      ],
      bottom: PreferredSize(
        preferredSize: const Size.fromHeight(1),
        child:         Container(), // divider drawn inside body column
      ),
    );
  }
}

// ── Stats Row ─────────────────────────────────────────────────

class _StatsRow extends StatelessWidget {
  final int Function(_FilterTab) countFor;
  const _StatsRow({required this.countFor});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        _StatBubble(
          label: 'Active',
          count: countFor(_FilterTab.active),
          color: AppColors.info,
          bg:    AppColors.infoLight,
          icon:  Icons.radio_button_checked_rounded,
        ),
        const SizedBox(width: 10),
        _StatBubble(
          label: 'Upcoming',
          count: countFor(_FilterTab.upcoming),
          color: AppColors.secondaryDark,
          bg:    AppColors.successLight,
          icon:  Icons.schedule_rounded,
        ),
        const SizedBox(width: 10),
        _StatBubble(
          label: 'History',
          count: countFor(_FilterTab.history),
          color: AppColors.primary,
          bg:    AppColors.primary.withAlpha(12),
          icon:  Icons.history_rounded,
        ),
      ],
    );
  }
}

class _StatBubble extends StatelessWidget {
  final String   label;
  final int      count;
  final Color    color;
  final Color    bg;
  final IconData icon;

  const _StatBubble({
    required this.label,
    required this.count,
    required this.color,
    required this.bg,
    required this.icon,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 12),
        decoration: BoxDecoration(
          color:        bg,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: color.withAlpha(40), width: 1),
        ),
        child: Row(
          children: [
            Icon(icon, color: color, size: 18),
            const SizedBox(width: 8),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '$count',
                  style: textTheme.titleSmall?.copyWith(
                    color:      color,
                    fontWeight: FontWeight.w800,
                    fontSize:   16,
                  ),
                ),
                Text(
                  label,
                  style: textTheme.bodySmall?.copyWith(
                    color:    color,
                    fontSize: 10,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

// ── Filter Chip ───────────────────────────────────────────────

class _FilterChip extends StatelessWidget {
  final _FilterTab   tab;
  final bool         isSelected;
  final int          count;
  final VoidCallback onTap;

  const _FilterChip({
    required this.tab,
    required this.isSelected,
    required this.count,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.primary : AppColors.surface,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected ? AppColors.primary : AppColors.divider,
            width: 1.5,
          ),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color:       AppColors.primary.withAlpha(50),
                    blurRadius:  8,
                    offset:      const Offset(0, 3),
                  ),
                ]
              : null,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              tab.label,
              style: textTheme.labelMedium?.copyWith(
                color: isSelected
                    ? AppColors.onPrimary
                    : AppColors.textSecondary,
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(width: 6),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
              decoration: BoxDecoration(
                color:        isSelected
                    ? AppColors.onPrimary.withAlpha(30)
                    : AppColors.surfaceVariant,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Text(
                '$count',
                style: textTheme.labelSmall?.copyWith(
                  color:      isSelected
                      ? AppColors.onPrimary
                      : AppColors.textTertiary,
                  fontWeight: FontWeight.w700,
                  fontSize:   10,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── Booking Card ──────────────────────────────────────────────

class _BookingCard extends StatefulWidget {
  final _Booking booking;
  const _BookingCard({required this.booking});

  @override
  State<_BookingCard> createState() => _BookingCardState();
}

class _BookingCardState extends State<_BookingCard> {
  bool _expanded = false;

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;
    final b         = widget.booking;
    final status    = b.status;
    final isActive  = status == _BookingStatus.active;

    return AnimatedContainer(
      duration: const Duration(milliseconds: 250),
      decoration: BoxDecoration(
        color:        AppColors.surface,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: isActive
              ? AppColors.info.withAlpha(70)
              : AppColors.divider,
          width: isActive ? 1.5 : 1,
        ),
        boxShadow: [
          BoxShadow(
            color:        isActive
                ? AppColors.info.withAlpha(20)
                : AppColors.shadow,
            blurRadius:   isActive ? 16 : 10,
            spreadRadius: 0,
            offset:       const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: [

          // ── Card Header ───────────────────────────────
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [

                // ── Parking icon ─────────────────────
                Container(
                  width:  50,
                  height: 50,
                  decoration: BoxDecoration(
                    color:        AppColors.primary.withAlpha(12),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(
                      color: AppColors.primary.withAlpha(30),
                      width: 1,
                    ),
                  ),
                  child: const Icon(
                    Icons.local_parking_rounded,
                    color: AppColors.primary,
                    size:  24,
                  ),
                ),

                const SizedBox(width: 12),

                // ── Parking info ──────────────────────
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        b.parkingName,
                        style: textTheme.titleSmall?.copyWith(
                          color:      AppColors.textPrimary,
                          fontWeight: FontWeight.w700,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 3),
                      Row(
                        children: [
                          const Icon(
                            Icons.location_on_outlined,
                            color: AppColors.textTertiary,
                            size:  12,
                          ),
                          const SizedBox(width: 3),
                          Expanded(
                            child: Text(
                              b.address,
                              style: textTheme.bodySmall?.copyWith(
                                color: AppColors.textTertiary,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),

                const SizedBox(width: 8),

                // ── Status badge ──────────────────────
                _StatusBadge(status: status),
              ],
            ),
          ),

          const SizedBox(height: 14),

          // ── Divider ───────────────────────────────────
          Divider(height: 1, color: AppColors.divider, indent: 16, endIndent: 16),

          // ── Quick info row ─────────────────────────────
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            child: Row(
              children: [
                _QuickInfo(
                  icon:  Icons.calendar_today_rounded,
                  label: b.date,
                ),
                _dot(),
                _QuickInfo(
                  icon:  Icons.access_time_rounded,
                  label: '${b.startTime} – ${b.endTime}',
                ),
                _dot(),
                _QuickInfo(
                  icon:  Icons.local_parking_rounded,
                  label: b.slot,
                  bold:  true,
                  color: AppColors.primary,
                ),
              ],
            ),
          ),

          // ── Expandable detail section ──────────────────
          AnimatedCrossFade(
            duration: const Duration(milliseconds: 250),
            crossFadeState: _expanded
                ? CrossFadeState.showSecond
                : CrossFadeState.showFirst,
            firstChild:  const SizedBox(width: double.infinity),
            secondChild: _ExpandedDetails(booking: b),
          ),

          Divider(height: 1, color: AppColors.divider),

          // ── Footer actions ─────────────────────────────
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            child: Row(
              children: [

                // Booking ID
                Expanded(
                  child: Row(
                    children: [
                      const Icon(
                        Icons.tag_rounded,
                        color: AppColors.textTertiary,
                        size:  13,
                      ),
                      const SizedBox(width: 4),
                      Flexible(
                        child: Text(
                          b.bookingId,
                          style: textTheme.bodySmall?.copyWith(
                            color:         AppColors.textTertiary,
                            fontFeatures:  const [FontFeature.tabularFigures()],
                            fontSize:      11,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(width: 8),

                // QR Code icon button
                _FooterIconButton(
                  icon:    Icons.qr_code_rounded,
                  tooltip: 'Show QR',
                  onTap:   () => _showQrSheet(context, b),
                ),

                const SizedBox(width: 6),

                // View Details button
                _ViewDetailsButton(
                  expanded: _expanded,
                  onTap:    () => setState(() => _expanded = !_expanded),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _dot() => Padding(
        padding: const EdgeInsets.symmetric(horizontal: 6),
        child: Container(
          width:  4,
          height: 4,
          decoration: const BoxDecoration(
            color: AppColors.textDisabled,
            shape: BoxShape.circle,
          ),
        ),
      );

  void _showQrSheet(BuildContext context, _Booking b) {
    showModalBottomSheet(
      context:         context,
      backgroundColor: Colors.transparent,
      isScrollControlled: false,
      builder: (_) => _QrSheet(booking: b),
    );
  }
}

// ── Status Badge ──────────────────────────────────────────────

class _StatusBadge extends StatelessWidget {
  final _BookingStatus status;
  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(
        color:        status.bgColor,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: status.color.withAlpha(60),
          width: 1,
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Pulsing dot for active
          if (status == _BookingStatus.active)
            _PulseDot(color: status.color)
          else
            Icon(status.icon, color: status.color, size: 10),
          const SizedBox(width: 4),
          Text(
            status.label,
            style: textTheme.labelSmall?.copyWith(
              color:      status.color,
              fontWeight: FontWeight.w700,
              fontSize:   10,
            ),
          ),
        ],
      ),
    );
  }
}

// ── Pulse Dot (for active status) ─────────────────────────────

class _PulseDot extends StatefulWidget {
  final Color color;
  const _PulseDot({required this.color});

  @override
  State<_PulseDot> createState() => _PulseDotState();
}

class _PulseDotState extends State<_PulseDot>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  late final Animation<double>   _scale;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync:    this,
      duration: const Duration(milliseconds: 900),
    )..repeat(reverse: true);
    _scale = Tween<double>(begin: 0.7, end: 1.3).animate(
      CurvedAnimation(parent: _ctrl, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return ScaleTransition(
      scale: _scale,
      child: Container(
        width:  8,
        height: 8,
        decoration: BoxDecoration(
          color: widget.color,
          shape: BoxShape.circle,
        ),
      ),
    );
  }
}

// ── Quick Info Item ───────────────────────────────────────────

class _QuickInfo extends StatelessWidget {
  final IconData icon;
  final String   label;
  final bool     bold;
  final Color?   color;

  const _QuickInfo({
    required this.icon,
    required this.label,
    this.bold  = false,
    this.color,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;
    final c         = color ?? AppColors.textSecondary;

    return Flexible(
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 12, color: c),
          const SizedBox(width: 4),
          Flexible(
            child: Text(
              label,
              style: textTheme.bodySmall?.copyWith(
                color:      c,
                fontWeight: bold ? FontWeight.w700 : FontWeight.w400,
                fontSize:   11,
              ),
              overflow: TextOverflow.ellipsis,
            ),
          ),
        ],
      ),
    );
  }
}

// ── Expanded Details ──────────────────────────────────────────

class _ExpandedDetails extends StatelessWidget {
  final _Booking booking;
  const _ExpandedDetails({required this.booking});

  @override
  Widget build(BuildContext context) {
    final b = booking;

    return Container(
      margin: const EdgeInsets.fromLTRB(16, 0, 16, 14),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color:        AppColors.surfaceVariant,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.divider, width: 1),
      ),
      child: Column(
        children: [
          _DetailRow(
            icon:  Icons.layers_outlined,
            label: 'Floor',
            value: b.floor,
          ),
          const SizedBox(height: 10),
          _DetailRow(
            icon:  Icons.timelapse_rounded,
            label: 'Duration',
            value: b.duration,
          ),
          const SizedBox(height: 10),
          _DetailRow(
            icon:  Icons.currency_rupee_rounded,
            label: 'Amount Paid',
            value: b.amountPaid,
            valueColor: AppColors.secondaryDark,
          ),
          const SizedBox(height: 10),
          _DetailRow(
            icon:  Icons.directions_car_outlined,
            label: 'Vehicle',
            value: b.vehicleNumber,
          ),
        ],
      ),
    );
  }
}

class _DetailRow extends StatelessWidget {
  final IconData icon;
  final String   label;
  final String   value;
  final Color?   valueColor;

  const _DetailRow({
    required this.icon,
    required this.label,
    required this.value,
    this.valueColor,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Row(
      children: [
        Icon(icon, color: AppColors.textTertiary, size: 15),
        const SizedBox(width: 8),
        Text(
          label,
          style: textTheme.bodySmall?.copyWith(
            color: AppColors.textSecondary,
          ),
        ),
        const Spacer(),
        Text(
          value,
          style: textTheme.bodySmall?.copyWith(
            color:      valueColor ?? AppColors.textPrimary,
            fontWeight: FontWeight.w700,
          ),
        ),
      ],
    );
  }
}

// ── Footer Icon Button ────────────────────────────────────────

class _FooterIconButton extends StatelessWidget {
  final IconData     icon;
  final String       tooltip;
  final VoidCallback onTap;

  const _FooterIconButton({
    required this.icon,
    required this.tooltip,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Tooltip(
      message: tooltip,
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          width:  36,
          height: 36,
          decoration: BoxDecoration(
            color:        AppColors.surfaceVariant,
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: AppColors.divider, width: 1),
          ),
          child: Icon(icon, color: AppColors.primary, size: 18),
        ),
      ),
    );
  }
}

// ── View Details Button ───────────────────────────────────────

class _ViewDetailsButton extends StatelessWidget {
  final bool         expanded;
  final VoidCallback onTap;

  const _ViewDetailsButton({
    required this.expanded,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color:        AppColors.primary.withAlpha(12),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(
            color: AppColors.primary.withAlpha(35),
            width: 1,
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              expanded ? 'Hide Details' : 'View Details',
              style: textTheme.labelSmall?.copyWith(
                color:      AppColors.primary,
                fontWeight: FontWeight.w700,
                fontSize:   11,
              ),
            ),
            const SizedBox(width: 4),
            AnimatedRotation(
              turns:    expanded ? 0.5 : 0.0,
              duration: const Duration(milliseconds: 250),
              child: const Icon(
                Icons.keyboard_arrow_down_rounded,
                color: AppColors.primary,
                size:  16,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── QR Code Bottom Sheet ──────────────────────────────────────

class _QrSheet extends StatelessWidget {
  final _Booking booking;
  const _QrSheet({required this.booking});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;
    final b         = booking;

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 12),
      decoration: const BoxDecoration(
        color:        AppColors.surface,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(24, 0, 24, 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [

              // Handle
              Center(
                child: Container(
                  margin: const EdgeInsets.only(top: 12, bottom: 20),
                  width:  40,
                  height: 4,
                  decoration: BoxDecoration(
                    color:        AppColors.divider,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),

              // Title
              Text(
                'Entry QR Code',
                style: textTheme.titleMedium?.copyWith(
                  color:      AppColors.textPrimary,
                  fontWeight: FontWeight.w700,
                ),
              ),

              const SizedBox(height: 4),

              Text(
                b.parkingName,
                style: textTheme.bodySmall?.copyWith(
                  color: AppColors.textSecondary,
                ),
              ),

              const SizedBox(height: 24),

              // QR placeholder
              Container(
                width:  180,
                height: 180,
                decoration: BoxDecoration(
                  color:        AppColors.surface,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppColors.divider, width: 1.5),
                  boxShadow: [
                    BoxShadow(
                      color:       AppColors.shadow,
                      blurRadius:  12,
                      offset:      const Offset(0, 4),
                    ),
                  ],
                ),
                child: Stack(
                  children: [
                    _QrCorner(alignment: Alignment.topLeft,
                        borderRadius: const BorderRadius.only(topLeft: Radius.circular(4))),
                    _QrCorner(alignment: Alignment.topRight,
                        borderRadius: const BorderRadius.only(topRight: Radius.circular(4))),
                    _QrCorner(alignment: Alignment.bottomLeft,
                        borderRadius: const BorderRadius.only(bottomLeft: Radius.circular(4))),
                    _QrCorner(alignment: Alignment.bottomRight,
                        borderRadius: const BorderRadius.only(bottomRight: Radius.circular(4))),
                    Center(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Container(
                            width:  56,
                            height: 56,
                            decoration: BoxDecoration(
                              color:        AppColors.primary,
                              borderRadius: BorderRadius.circular(14),
                            ),
                            child: const Icon(
                              Icons.local_parking_rounded,
                              color: AppColors.onPrimary,
                              size:  28,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'QR CODE',
                            style: textTheme.labelSmall?.copyWith(
                              color:         AppColors.textTertiary,
                              letterSpacing: 2,
                              fontSize:      9,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 16),

              // Booking ID
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 14, vertical: 8,
                ),
                decoration: BoxDecoration(
                  color:        AppColors.surfaceVariant,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  b.bookingId,
                  style: textTheme.bodySmall?.copyWith(
                    color:         AppColors.textSecondary,
                    fontWeight:    FontWeight.w600,
                    letterSpacing: 0.5,
                    fontFeatures:  const [FontFeature.tabularFigures()],
                  ),
                ),
              ),

              const SizedBox(height: 12),

              // Valid for banner
              Container(
                width:   double.infinity,
                padding: const EdgeInsets.symmetric(vertical: 10),
                decoration: BoxDecoration(
                  color:        AppColors.successLight,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(
                      Icons.verified_rounded,
                      color: AppColors.secondaryDark,
                      size:  14,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      'Valid · ${b.date} · ${b.startTime} – ${b.endTime}',
                      style: textTheme.bodySmall?.copyWith(
                        color:      AppColors.secondaryDark,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 20),

              // Close button
              SizedBox(
                width:  double.infinity,
                height: 50,
                child: OutlinedButton(
                  onPressed: () => Navigator.of(context).pop(),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppColors.primary,
                    side: const BorderSide(
                      color: AppColors.primary,
                      width: 1.5,
                    ),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                  ),
                  child: const Text('Close'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _QrCorner extends StatelessWidget {
  final AlignmentGeometry alignment;
  final BorderRadius      borderRadius;

  const _QrCorner({
    required this.alignment,
    required this.borderRadius,
  });

  @override
  Widget build(BuildContext context) {
    return Positioned.fill(
      child: Align(
        alignment: alignment,
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: SizedBox(
            width:  20,
            height: 20,
            child: CustomPaint(
              painter: _CornerPainter(
                borderRadius: borderRadius,
                color:        AppColors.primary,
                strokeWidth:  2.5,
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _CornerPainter extends CustomPainter {
  final BorderRadius borderRadius;
  final Color        color;
  final double       strokeWidth;

  const _CornerPainter({
    required this.borderRadius,
    required this.color,
    required this.strokeWidth,
  });

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color       = color
      ..strokeWidth = strokeWidth
      ..style       = PaintingStyle.stroke
      ..strokeCap   = StrokeCap.round;

    canvas.drawRRect(
      RRect.fromRectAndCorners(
        Rect.fromLTWH(0, 0, size.width, size.height),
        topLeft:     borderRadius.topLeft,
        topRight:    borderRadius.topRight,
        bottomLeft:  borderRadius.bottomLeft,
        bottomRight: borderRadius.bottomRight,
      ),
      paint,
    );
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

// ── Empty State ───────────────────────────────────────────────

class _EmptyState extends StatelessWidget {
  final _FilterTab filter;
  const _EmptyState({required this.filter});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    final (icon, title, subtitle) = switch (filter) {
      _FilterTab.active   => (
          Icons.radio_button_off_rounded,
          'No Active Bookings',
          'You have no ongoing parking sessions right now.',
        ),
      _FilterTab.upcoming => (
          Icons.event_available_outlined,
          'No Upcoming Bookings',
          'You have no upcoming reservations. Book a slot now!',
        ),
      _FilterTab.history  => (
          Icons.history_rounded,
          'No Booking History',
          'Your completed and cancelled bookings will appear here.',
        ),
    };

    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 40),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width:  80,
              height: 80,
              decoration: BoxDecoration(
                color:        AppColors.surfaceVariant,
                borderRadius: BorderRadius.circular(24),
              ),
              child: Icon(icon, color: AppColors.textTertiary, size: 38),
            ),
            const SizedBox(height: 20),
            Text(
              title,
              style: textTheme.titleMedium?.copyWith(
                color:      AppColors.textPrimary,
                fontWeight: FontWeight.w700,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              subtitle,
              style: textTheme.bodyMedium?.copyWith(
                color:  AppColors.textSecondary,
                height: 1.5,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}