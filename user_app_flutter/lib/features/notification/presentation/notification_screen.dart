import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_colors.dart';

// ============================================================
// NotificationScreen
// ============================================================
//
// Displays all in-app notifications with filter tabs.
//
// SECTIONS:
//   1. AppBar          — "Notifications" + mark-all-read action
//   2. Unread summary  — animated count banner
//   3. Filter chips    — All / Unread / Bookings / Payments / Offers
//   4. Notification cards — grouped by Today / Earlier
//   5. Empty state     — shown when no notifications match
//
// NOTIFICATION TYPES:
//   booking → parking reservation updates
//   payment → transaction & refund alerts
//   offer   → promotions & discounts
//   system  → general app alerts
//
// ARCHITECTURE:
//   Pure StatefulWidget — no Riverpod, Bloc, Provider.
//   All data is local mutable dummy data.
//   Tapping a card marks it read. "Mark all as read" clears
//   the unread indicator from every card.
// ============================================================

// ── Notification Type ─────────────────────────────────────────

enum _NotifType { booking, payment, offer, system }

extension _NotifTypeX on _NotifType {
  String get label => switch (this) {
        _NotifType.booking => 'Booking',
        _NotifType.payment => 'Payment',
        _NotifType.offer   => 'Offer',
        _NotifType.system  => 'System',
      };

  IconData get icon => switch (this) {
        _NotifType.booking => Icons.local_parking_rounded,
        _NotifType.payment => Icons.currency_rupee_rounded,
        _NotifType.offer   => Icons.local_offer_rounded,
        _NotifType.system  => Icons.notifications_rounded,
      };

  Color get color => switch (this) {
        _NotifType.booking => AppColors.primary,
        _NotifType.payment => AppColors.secondaryDark,
        _NotifType.offer   => AppColors.accent,
        _NotifType.system  => AppColors.info,
      };

  Color get bgColor => switch (this) {
        _NotifType.booking => AppColors.primary.withAlpha(14),
        _NotifType.payment => AppColors.successLight,
        _NotifType.offer   => AppColors.warningLight,
        _NotifType.system  => AppColors.infoLight,
      };
}

// ── Filter Tab ────────────────────────────────────────────────

enum _FilterTab { all, unread, bookings, payments, offers }

extension _FilterTabX on _FilterTab {
  String get label => switch (this) {
        _FilterTab.all      => 'All',
        _FilterTab.unread   => 'Unread',
        _FilterTab.bookings => 'Bookings',
        _FilterTab.payments => 'Payments',
        _FilterTab.offers   => 'Offers',
      };

  bool matches(_Notification n) => switch (this) {
        _FilterTab.all      => true,
        _FilterTab.unread   => !n.isRead,
        _FilterTab.bookings => n.type == _NotifType.booking,
        _FilterTab.payments => n.type == _NotifType.payment,
        _FilterTab.offers   => n.type == _NotifType.offer,
      };
}

// ── Notification Model ────────────────────────────────────────

class _Notification {
  final String      id;
  final String      title;
  final String      message;
  final String      time;
  final _NotifType  type;
  final bool        isToday;
  bool              isRead;
  final String?     actionLabel;

  _Notification({
    required this.id,
    required this.title,
    required this.message,
    required this.time,
    required this.type,
    required this.isToday,
    this.isRead      = false,
    this.actionLabel,
  });
}

// ── Dummy Data ────────────────────────────────────────────────

final List<_Notification> _allNotifications = [
  _Notification(
    id:          '1',
    title:       'Booking Confirmed! 🎉',
    message:     'Your slot G09 at Cyber Hub Parking is confirmed for 12 Jul, 10:00 AM – 12:00 PM. Show QR at entry.',
    time:        '2 min ago',
    type:        _NotifType.booking,
    isToday:     true,
    actionLabel: 'View Booking',
  ),
  _Notification(
    id:      '2',
    title:   'Payment Successful',
    message: '₹80 paid via GPay for BKG-2025-78421. Transaction ID: TXN9284710234.',
    time:    '3 min ago',
    type:    _NotifType.payment,
    isToday: true,
  ),
  _Notification(
    id:          '3',
    title:       '🎁 Weekend Special — 20% Off',
    message:     'Book any parking slot this Saturday or Sunday and get 20% off. Use code WEEKEND20 at checkout.',
    time:        '1 hr ago',
    type:        _NotifType.offer,
    isToday:     true,
    actionLabel: 'Book Now',
  ),
  _Notification(
    id:      '4',
    title:   'Slot Check-in Reminder',
    message: 'Your booking at Ambience Mall Parking starts in 30 minutes. Head to Floor 1, Slot 108.',
    time:    '3 hrs ago',
    type:    _NotifType.booking,
    isToday: true,
    isRead:  true,
  ),
  _Notification(
    id:          '5',
    title:       'Refund Processed',
    message:     '₹150 refunded for cancelled booking BKG-2025-77280. Amount will reflect in 3–5 business days.',
    time:        '5 hrs ago',
    type:        _NotifType.payment,
    isToday:     true,
    isRead:      true,
    actionLabel: 'View Receipt',
  ),
  _Notification(
    id:          '6',
    title:       'New Parking Lot Near You',
    message:     'DLF Phase 3 Parking is now live! 200 slots, EV charging, and covered bays. Starting ₹25/hr.',
    time:        'Yesterday',
    type:        _NotifType.system,
    isToday:     false,
    actionLabel: 'Explore',
  ),
  _Notification(
    id:      '7',
    title:   'Booking Cancelled',
    message: 'Your booking BKG-2025-77603 at Sector 29 Parking has been cancelled. Refund initiated.',
    time:    'Yesterday',
    type:    _NotifType.booking,
    isToday: false,
    isRead:  true,
  ),
  _Notification(
    id:          '8',
    title:       '🔥 Flash Deal: 30% Off Today Only',
    message:     'Limited slots available at MGF Metropolitan Mall at ₹24.50/hr. Offer expires at midnight!',
    time:        '2 days ago',
    type:        _NotifType.offer,
    isToday:     false,
    actionLabel: 'Grab Deal',
  ),
  _Notification(
    id:      '9',
    title:   'Payment Failed',
    message: 'Your payment of ₹20 for BKG-2025-77603 was declined. Please retry with a different payment method.',
    time:    '2 days ago',
    type:    _NotifType.payment,
    isToday: false,
    isRead:  true,
  ),
];

// ── Screen ────────────────────────────────────────────────────

class NotificationScreen extends StatefulWidget {
  const NotificationScreen({super.key});

  @override
  State<NotificationScreen> createState() => _NotificationScreenState();
}

class _NotificationScreenState extends State<NotificationScreen>
    with SingleTickerProviderStateMixin {

  _FilterTab _activeFilter = _FilterTab.all;

  late final List<_Notification> _notifications;
  late final AnimationController _fadeController;

  @override
  void initState() {
    super.initState();
    // Deep copy so mutations stay local to this screen session.
    _notifications = _allNotifications
        .map((n) => _Notification(
              id:          n.id,
              title:       n.title,
              message:     n.message,
              time:        n.time,
              type:        n.type,
              isToday:     n.isToday,
              isRead:      n.isRead,
              actionLabel: n.actionLabel,
            ))
        .toList();

    _fadeController = AnimationController(
      vsync:    this,
      duration: const Duration(milliseconds: 280),
    )..forward();

    SystemChrome.setSystemUIOverlayStyle(
      const SystemUiOverlayStyle(
        statusBarColor:          Colors.transparent,
        statusBarIconBrightness: Brightness.dark,
        statusBarBrightness:     Brightness.light,
      ),
    );
  }

  @override
  void dispose() {
    _fadeController.dispose();
    super.dispose();
  }

  // ── Computed ──────────────────────────────────────────────

  int get _unreadCount =>
      _notifications.where((n) => !n.isRead).length;

  List<_Notification> get _filtered =>
      _notifications.where((n) => _activeFilter.matches(n)).toList();

  List<_Notification> _todayGroup(List<_Notification> list) =>
      list.where((n) => n.isToday).toList();

  List<_Notification> _earlierGroup(List<_Notification> list) =>
      list.where((n) => !n.isToday).toList();

  int _countFor(_FilterTab tab) =>
      _notifications.where((n) => tab.matches(n)).length;

  // ── Actions ───────────────────────────────────────────────

  void _switchFilter(_FilterTab tab) {
    if (_activeFilter == tab) return;
    setState(() => _activeFilter = tab);
    _fadeController
      ..reset()
      ..forward();
  }

  void _markAllRead() {
    setState(() {
      for (final n in _notifications) {
        n.isRead = true;
      }
    });
  }

  void _markRead(String id) {
    setState(() {
      final idx = _notifications.indexWhere((n) => n.id == id);
      if (idx != -1) _notifications[idx].isRead = true;
    });
  }

  // ── Build ─────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.sizeOf(context).width;
    final hPad        = screenWidth > 600 ? screenWidth * 0.08 : 20.0;
    final filtered    = _filtered;
    final todayList   = _todayGroup(filtered);
    final earlierList = _earlierGroup(filtered);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar:          _buildAppBar(context),
      body: Column(
        children: [

          // ── Sticky header ────────────────────────────
          Container(
            color: AppColors.surface,
            child: Column(
              children: [

                // Unread banner
                if (_unreadCount > 0)
                  Padding(
                    padding: EdgeInsets.fromLTRB(hPad, 14, hPad, 2),
                    child: _UnreadBanner(
                      count:      _unreadCount,
                      onMarkAll:  _markAllRead,
                    ),
                  ),

                // Filter chips
                SizedBox(
                  height: 52,
                  child: ListView(
                    scrollDirection: Axis.horizontal,
                    padding: EdgeInsets.fromLTRB(hPad, 8, hPad, 8),
                    children: _FilterTab.values.map((tab) {
                      return Padding(
                        padding: const EdgeInsets.only(right: 8),
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

                Divider(height: 1, color: AppColors.divider),
              ],
            ),
          ),

          // ── Notification list ─────────────────────────
          Expanded(
            child: filtered.isEmpty
                ? _EmptyState(filter: _activeFilter)
                : FadeTransition(
                    opacity: CurvedAnimation(
                      parent: _fadeController,
                      curve:  Curves.easeOut,
                    ),
                    child: ListView(
                      padding: EdgeInsets.fromLTRB(hPad, 16, hPad, 32),
                      children: [

                        if (todayList.isNotEmpty) ...[
                          _GroupLabel(label: 'Today'),
                          const SizedBox(height: 10),
                          ...todayList.map((n) => Padding(
                            padding: const EdgeInsets.only(bottom: 12),
                            child:   _NotifCard(
                              notif:   n,
                              onTap:   () => _markRead(n.id),
                            ),
                          )),
                        ],

                        if (earlierList.isNotEmpty) ...[
                          if (todayList.isNotEmpty)
                            const SizedBox(height: 8),
                          _GroupLabel(label: 'Earlier'),
                          const SizedBox(height: 10),
                          ...earlierList.map((n) => Padding(
                            padding: const EdgeInsets.only(bottom: 12),
                            child:   _NotifCard(
                              notif:   n,
                              onTap:   () => _markRead(n.id),
                            ),
                          )),
                        ],
                      ],
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
        'Notifications',
        style: textTheme.titleLarge?.copyWith(
          color:      AppColors.textPrimary,
          fontWeight: FontWeight.w700,
        ),
      ),
      centerTitle: true,
      actions: [
        if (_unreadCount > 0)
          TextButton(
            onPressed: _markAllRead,
            style: TextButton.styleFrom(
              foregroundColor: AppColors.primary,
              padding: const EdgeInsets.symmetric(horizontal: 12),
              tapTargetSize: MaterialTapTargetSize.shrinkWrap,
            ),
            child: Text(
              'Mark all read',
              style: textTheme.bodySmall?.copyWith(
                color:      AppColors.primary,
                fontWeight: FontWeight.w700,
              ),
            ),
          )
        else
          IconButton(
            icon:    const Icon(Icons.done_all_rounded, size: 20),
            color:   AppColors.textTertiary,
            tooltip: 'All caught up',
            onPressed: null,
          ),
        const SizedBox(width: 4),
      ],
      bottom: PreferredSize(
        preferredSize: const Size.fromHeight(1),
        child:         Container(),
      ),
    );
  }
}

// ── Unread Banner ─────────────────────────────────────────────

class _UnreadBanner extends StatelessWidget {
  final int          count;
  final VoidCallback onMarkAll;

  const _UnreadBanner({required this.count, required this.onMarkAll});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin:  Alignment.centerLeft,
          end:    Alignment.centerRight,
          colors: [AppColors.primary, AppColors.primaryLight],
        ),
        borderRadius: BorderRadius.circular(14),
        boxShadow: [
          BoxShadow(
            color:       AppColors.primary.withAlpha(50),
            blurRadius:  10,
            offset:      const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            width:  32,
            height: 32,
            decoration: BoxDecoration(
              color:        AppColors.onPrimary.withAlpha(25),
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Icon(
              Icons.notifications_active_rounded,
              color: AppColors.onPrimary,
              size:  17,
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: RichText(
              text: TextSpan(
                children: [
                  TextSpan(
                    text: '$count ',
                    style: textTheme.bodyMedium?.copyWith(
                      color:      AppColors.onPrimary,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  TextSpan(
                    text: 'unread notification${count == 1 ? '' : 's'}',
                    style: textTheme.bodySmall?.copyWith(
                      color: AppColors.onPrimary.withAlpha(200),
                    ),
                  ),
                ],
              ),
            ),
          ),
          GestureDetector(
            onTap: onMarkAll,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
              decoration: BoxDecoration(
                color:        AppColors.onPrimary.withAlpha(25),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(
                  color: AppColors.onPrimary.withAlpha(40),
                  width: 1,
                ),
              ),
              child: Text(
                'Mark all',
                style: textTheme.labelSmall?.copyWith(
                  color:      AppColors.onPrimary,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ),
        ],
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
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
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
                color:      isSelected
                    ? AppColors.onPrimary
                    : AppColors.textSecondary,
                fontWeight: FontWeight.w700,
              ),
            ),
            if (count > 0) ...[
              const SizedBox(width: 5),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 6,
                  vertical:   1,
                ),
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
          ],
        ),
      ),
    );
  }
}

// ── Group Label ───────────────────────────────────────────────

class _GroupLabel extends StatelessWidget {
  final String label;
  const _GroupLabel({required this.label});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Row(
      children: [
        Text(
          label,
          style: textTheme.titleSmall?.copyWith(
            color:      AppColors.textPrimary,
            fontWeight: FontWeight.w700,
            fontSize:   13,
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Divider(color: AppColors.divider, height: 1),
        ),
      ],
    );
  }
}

// ── Notification Card ─────────────────────────────────────────

class _NotifCard extends StatelessWidget {
  final _Notification notif;
  final VoidCallback  onTap;

  const _NotifCard({required this.notif, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;
    final isUnread  = !notif.isRead;

    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 220),
        decoration: BoxDecoration(
          color:        isUnread
              ? AppColors.surface
              : AppColors.surface,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(
            color: isUnread
                ? notif.type.color.withAlpha(50)
                : AppColors.divider,
            width: isUnread ? 1.5 : 1,
          ),
          boxShadow: [
            BoxShadow(
              color:       isUnread
                  ? notif.type.color.withAlpha(18)
                  : AppColors.shadow,
              blurRadius:  isUnread ? 14 : 8,
              spreadRadius: 0,
              offset:      const Offset(0, 4),
            ),
          ],
        ),
        child: Stack(
          children: [

            // ── Unread left accent ──────────────────────
            if (isUnread)
              Positioned(
                left:   0,
                top:    16,
                bottom: 16,
                child: Container(
                  width:        3,
                  decoration: BoxDecoration(
                    color:        notif.type.color,
                    borderRadius: const BorderRadius.only(
                      topRight:    Radius.circular(3),
                      bottomRight: Radius.circular(3),
                    ),
                  ),
                ),
              ),

            Padding(
              padding: const EdgeInsets.fromLTRB(16, 14, 14, 14),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [

                  // ── Type icon ─────────────────────────
                  Stack(
                    children: [
                      Container(
                        width:  46,
                        height: 46,
                        decoration: BoxDecoration(
                          color:        notif.type.bgColor,
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(
                            color: notif.type.color.withAlpha(35),
                            width: 1,
                          ),
                        ),
                        child: Icon(
                          notif.type.icon,
                          color: notif.type.color,
                          size:  22,
                        ),
                      ),
                      // Unread dot on icon
                      if (isUnread)
                        Positioned(
                          top:   0,
                          right: 0,
                          child: Container(
                            width:  10,
                            height: 10,
                            decoration: BoxDecoration(
                              color:  notif.type.color,
                              shape:  BoxShape.circle,
                              border: Border.all(
                                color: AppColors.surface,
                                width: 1.5,
                              ),
                            ),
                          ),
                        ),
                    ],
                  ),

                  const SizedBox(width: 12),

                  // ── Content ───────────────────────────
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [

                        // Title + time row
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Expanded(
                              child: Text(
                                notif.title,
                                style: textTheme.titleSmall?.copyWith(
                                  color:      AppColors.textPrimary,
                                  fontWeight: isUnread
                                      ? FontWeight.w700
                                      : FontWeight.w600,
                                  height:     1.2,
                                ),
                              ),
                            ),
                            const SizedBox(width: 6),
                            Text(
                              notif.time,
                              style: textTheme.bodySmall?.copyWith(
                                color:    AppColors.textTertiary,
                                fontSize: 10,
                              ),
                            ),
                          ],
                        ),

                        const SizedBox(height: 5),

                        // Message
                        Text(
                          notif.message,
                          style: textTheme.bodySmall?.copyWith(
                            color:  isUnread
                                ? AppColors.textSecondary
                                : AppColors.textTertiary,
                            height: 1.5,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),

                        const SizedBox(height: 10),

                        // Badge + action row
                        Row(
                          children: [
                            // Type badge
                            _TypeBadge(type: notif.type),

                            const Spacer(),

                            // Optional action button
                            if (notif.actionLabel != null)
                              _ActionButton(
                                label: notif.actionLabel!,
                                color: notif.type.color,
                                onTap: () {},
                              ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── Type Badge ────────────────────────────────────────────────

class _TypeBadge extends StatelessWidget {
  final _NotifType type;
  const _TypeBadge({required this.type});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color:        type.bgColor,
        borderRadius: BorderRadius.circular(6),
        border: Border.all(
          color: type.color.withAlpha(45),
          width: 1,
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(type.icon, size: 11, color: type.color),
          const SizedBox(width: 4),
          Text(
            type.label,
            style: textTheme.labelSmall?.copyWith(
              color:      type.color,
              fontWeight: FontWeight.w700,
              fontSize:   10,
            ),
          ),
        ],
      ),
    );
  }
}

// ── Action Button ─────────────────────────────────────────────

class _ActionButton extends StatelessWidget {
  final String       label;
  final Color        color;
  final VoidCallback onTap;

  const _ActionButton({
    required this.label,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
        decoration: BoxDecoration(
          color:        color.withAlpha(12),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: color.withAlpha(40), width: 1),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              label,
              style: textTheme.labelSmall?.copyWith(
                color:      color,
                fontWeight: FontWeight.w700,
                fontSize:   11,
              ),
            ),
            const SizedBox(width: 3),
            Icon(
              Icons.arrow_forward_ios_rounded,
              color: color,
              size:  10,
            ),
          ],
        ),
      ),
    );
  }
}

// ── Empty State ───────────────────────────────────────────────

class _EmptyState extends StatelessWidget {
  final _FilterTab filter;
  const _EmptyState({required this.filter});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    final (IconData icon, String title, String subtitle) = switch (filter) {
      _FilterTab.all      => (
          Icons.notifications_off_outlined,
          'No Notifications',
          'You are all caught up! Check back later for updates.',
        ),
      _FilterTab.unread   => (
          Icons.mark_email_read_outlined,
          'All Caught Up!',
          'You have no unread notifications right now.',
        ),
      _FilterTab.bookings => (
          Icons.local_parking_outlined,
          'No Booking Alerts',
          'Booking updates and reminders will appear here.',
        ),
      _FilterTab.payments => (
          Icons.receipt_long_outlined,
          'No Payment Alerts',
          'Payment confirmations and refund alerts will appear here.',
        ),
      _FilterTab.offers   => (
          Icons.local_offer_outlined,
          'No Offers',
          'Exclusive deals and discount offers will appear here.',
        ),
    };

    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 40),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width:  88,
              height: 88,
              decoration: BoxDecoration(
                color:        AppColors.surfaceVariant,
                borderRadius: BorderRadius.circular(24),
                border: Border.all(color: AppColors.divider, width: 1),
              ),
              child: Icon(icon, color: AppColors.textTertiary, size: 40),
            ),
            const SizedBox(height: 22),
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