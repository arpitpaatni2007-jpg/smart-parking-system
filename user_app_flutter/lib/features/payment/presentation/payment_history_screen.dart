import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_colors.dart';

// ============================================================
// PaymentHistoryScreen
// ============================================================
//
// Displays the full payment history for the logged-in user.
//
// SECTIONS:
//   1. AppBar            — "Payment History" + export icon
//   2. Total summary     — total paid amount + transaction count
//   3. Filter chips      — All / Successful / Pending / Failed
//   4. Payment cards     — filtered, scrollable list
//   5. Empty state       — shown when no transactions match
//
// PAYMENT STATUSES:
//   successful → payment captured and confirmed
//   pending    → awaiting gateway confirmation
//   failed     → payment declined or timed out
//   refunded   → amount returned to source
//
// ARCHITECTURE:
//   Pure StatefulWidget — no Riverpod, Bloc, Provider.
//   All data is local dummy data.
// ============================================================

// ── Payment Status Enum ───────────────────────────────────────

enum _PaymentStatus { successful, pending, failed, refunded }

extension _PaymentStatusX on _PaymentStatus {
  String get label => switch (this) {
        _PaymentStatus.successful => 'Successful',
        _PaymentStatus.pending    => 'Pending',
        _PaymentStatus.failed     => 'Failed',
        _PaymentStatus.refunded   => 'Refunded',
      };

  Color get color => switch (this) {
        _PaymentStatus.successful => AppColors.secondaryDark,
        _PaymentStatus.pending    => AppColors.accent,
        _PaymentStatus.failed     => AppColors.error,
        _PaymentStatus.refunded   => AppColors.info,
      };

  Color get bgColor => switch (this) {
        _PaymentStatus.successful => AppColors.successLight,
        _PaymentStatus.pending    => AppColors.warningLight,
        _PaymentStatus.failed     => AppColors.errorLight,
        _PaymentStatus.refunded   => AppColors.infoLight,
      };

  IconData get icon => switch (this) {
        _PaymentStatus.successful => Icons.check_circle_rounded,
        _PaymentStatus.pending    => Icons.access_time_rounded,
        _PaymentStatus.failed     => Icons.cancel_rounded,
        _PaymentStatus.refunded   => Icons.keyboard_return_rounded,
      };
}

// ── Payment Method Enum ───────────────────────────────────────

enum _PaymentMethod { upi, card, netBanking, wallet }

extension _PaymentMethodX on _PaymentMethod {
  String get label => switch (this) {
        _PaymentMethod.upi        => 'UPI',
        _PaymentMethod.card       => 'Credit Card',
        _PaymentMethod.netBanking => 'Net Banking',
        _PaymentMethod.wallet     => 'Wallet',
      };

  IconData get icon => switch (this) {
        _PaymentMethod.upi        => Icons.account_balance_rounded,
        _PaymentMethod.card       => Icons.credit_card_rounded,
        _PaymentMethod.netBanking => Icons.account_balance_wallet_rounded,
        _PaymentMethod.wallet     => Icons.wallet_rounded,
      };

  String get detail => switch (this) {
        _PaymentMethod.upi        => 'GPay · arpit@okaxis',
        _PaymentMethod.card       => 'HDFC •••• 4291',
        _PaymentMethod.netBanking => 'SBI Net Banking',
        _PaymentMethod.wallet     => 'Paytm Wallet',
      };
}

// ── Filter Tab Enum ───────────────────────────────────────────

enum _FilterTab { all, successful, pending, failed }

extension _FilterTabX on _FilterTab {
  String get label => switch (this) {
        _FilterTab.all        => 'All',
        _FilterTab.successful => 'Successful',
        _FilterTab.pending    => 'Pending',
        _FilterTab.failed     => 'Failed',
      };

  bool matches(_PaymentStatus status) => switch (this) {
        _FilterTab.all        => true,
        _FilterTab.successful => status == _PaymentStatus.successful ||
                                 status == _PaymentStatus.refunded,
        _FilterTab.pending    => status == _PaymentStatus.pending,
        _FilterTab.failed     => status == _PaymentStatus.failed,
      };
}

// ── Payment Model ─────────────────────────────────────────────

class _Payment {
  final String          id;
  final String          transactionId;
  final String          bookingId;
  final String          parkingName;
  final String          address;
  final String          date;
  final String          time;
  final double          amount;
  final _PaymentMethod  method;
  final _PaymentStatus  status;

  const _Payment({
    required this.id,
    required this.transactionId,
    required this.bookingId,
    required this.parkingName,
    required this.address,
    required this.date,
    required this.time,
    required this.amount,
    required this.method,
    required this.status,
  });
}

// ── Dummy Data ────────────────────────────────────────────────

const List<_Payment> _allPayments = [
  _Payment(
    id:            '1',
    transactionId: 'TXN9284710234',
    bookingId:     'BKG-2025-78421',
    parkingName:   'Cyber Hub Parking Complex',
    address:       'DLF Cyber Hub, Sector 24, Gurugram',
    date:          'Sat, 12 Jul 2025',
    time:          '09:45 AM',
    amount:        80.0,
    method:        _PaymentMethod.upi,
    status:        _PaymentStatus.successful,
  ),
  _Payment(
    id:            '2',
    transactionId: 'TXN8371029183',
    bookingId:     'BKG-2025-78356',
    parkingName:   'Ambience Mall Parking',
    address:       'NH-48, Sheetla Mata Rd, Gurugram',
    date:          'Sun, 13 Jul 2025',
    time:          '01:55 PM',
    amount:        90.0,
    method:        _PaymentMethod.card,
    status:        _PaymentStatus.pending,
  ),
  _Payment(
    id:            '3',
    transactionId: 'TXN7102938471',
    bookingId:     'BKG-2025-77910',
    parkingName:   'MGF Metropolitan Mall',
    address:       'MG Road, Sikanderpur, Gurugram',
    date:          'Mon, 07 Jul 2025',
    time:          '10:30 AM',
    amount:        70.0,
    method:        _PaymentMethod.upi,
    status:        _PaymentStatus.successful,
  ),
  _Payment(
    id:            '4',
    transactionId: 'TXN6019283746',
    bookingId:     'BKG-2025-77603',
    parkingName:   'Sector 29 Public Parking',
    address:       'Sector 29, HUDA Market, Gurugram',
    date:          'Fri, 04 Jul 2025',
    time:          '08:52 AM',
    amount:        20.0,
    method:        _PaymentMethod.wallet,
    status:        _PaymentStatus.failed,
  ),
  _Payment(
    id:            '5',
    transactionId: 'TXN5928374610',
    bookingId:     'BKG-2025-77280',
    parkingName:   'DLF Phase 2 EV Hub',
    address:       'DLF Phase 2, Sector 25, Gurugram',
    date:          'Wed, 02 Jul 2025',
    time:          '02:40 PM',
    amount:        150.0,
    method:        _PaymentMethod.netBanking,
    status:        _PaymentStatus.refunded,
  ),
  _Payment(
    id:            '6',
    transactionId: 'TXN4817263504',
    bookingId:     'BKG-2025-76941',
    parkingName:   'Cyber Hub Parking Complex',
    address:       'DLF Cyber Hub, Sector 24, Gurugram',
    date:          'Mon, 30 Jun 2025',
    time:          '11:10 AM',
    amount:        40.0,
    method:        _PaymentMethod.card,
    status:        _PaymentStatus.successful,
  ),
];

// ── Screen ────────────────────────────────────────────────────

class PaymentHistoryScreen extends StatefulWidget {
  const PaymentHistoryScreen({super.key});

  @override
  State<PaymentHistoryScreen> createState() => _PaymentHistoryScreenState();
}

class _PaymentHistoryScreenState extends State<PaymentHistoryScreen>
    with SingleTickerProviderStateMixin {
  _FilterTab _activeFilter = _FilterTab.all;

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
      duration: const Duration(milliseconds: 280),
    )..forward();
  }

  @override
  void dispose() {
    _fadeController.dispose();
    super.dispose();
  }

  // ── Computed ──────────────────────────────────────────────

  List<_Payment> get _filtered =>
      _allPayments.where((p) => _activeFilter.matches(p.status)).toList();

  double get _totalPaid => _allPayments
      .where((p) => p.status == _PaymentStatus.successful)
      .fold(0.0, (sum, p) => sum + p.amount);

  int _countFor(_FilterTab tab) =>
      _allPayments.where((p) => tab.matches(p.status)).length;

  void _switchFilter(_FilterTab tab) {
    if (_activeFilter == tab) return;
    setState(() => _activeFilter = tab);
    _fadeController
      ..reset()
      ..forward();
  }

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

          // ── Sticky header ────────────────────────────
          Container(
            color: AppColors.surface,
            child: Column(
              children: [

                // Total summary card
                Padding(
                  padding: EdgeInsets.fromLTRB(hPad, 16, hPad, 14),
                  child:   _TotalSummaryCard(
                    totalPaid:         _totalPaid,
                    totalTransactions: _allPayments.length,
                  ),
                ),

                // Filter chips
                SizedBox(
                  height: 44,
                  child: ListView(
                    scrollDirection: Axis.horizontal,
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

          // ── Results label ────────────────────────────
          Padding(
            padding: EdgeInsets.fromLTRB(hPad, 14, hPad, 0),
            child:   _ResultsLabel(
              count:  filtered.length,
              filter: _activeFilter,
            ),
          ),

          const SizedBox(height: 10),

          // ── Payment list / empty state ────────────────
          Expanded(
            child: filtered.isEmpty
                ? _EmptyState(filter: _activeFilter)
                : FadeTransition(
                    opacity: CurvedAnimation(
                      parent: _fadeController,
                      curve:  Curves.easeOut,
                    ),
                    child: ListView.separated(
                      padding: EdgeInsets.fromLTRB(hPad, 4, hPad, 32),
                      itemCount:    filtered.length,
                      separatorBuilder: (_, __) => const SizedBox(height: 14),
                      itemBuilder: (ctx, i) =>
                          _PaymentCard(payment: filtered[i]),
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
        'Payment History',
        style: textTheme.titleLarge?.copyWith(
          color:      AppColors.textPrimary,
          fontWeight: FontWeight.w700,
        ),
      ),
      centerTitle: true,
      actions: [
        IconButton(
          icon:    const Icon(Icons.ios_share_rounded, size: 20),
          color:   AppColors.primary,
          tooltip: 'Export',
          onPressed: () {},
        ),
        const SizedBox(width: 4),
      ],
      bottom: PreferredSize(
        preferredSize: const Size.fromHeight(1),
        child:         Divider(height: 1, color: AppColors.divider),
      ),
    );
  }
}

// ── Total Summary Card ────────────────────────────────────────

class _TotalSummaryCard extends StatelessWidget {
  final double totalPaid;
  final int    totalTransactions;

  const _TotalSummaryCard({
    required this.totalPaid,
    required this.totalTransactions,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin:  Alignment.topLeft,
          end:    Alignment.bottomRight,
          colors: [AppColors.primary, AppColors.primaryLight],
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color:        AppColors.primary.withAlpha(65),
            blurRadius:   18,
            spreadRadius: 0,
            offset:       const Offset(0, 6),
          ),
        ],
      ),
      child: Stack(
        children: [
          // Watermark
          Positioned(
            right:  -16,
            bottom: -16,
            child:  Icon(
              Icons.receipt_long_rounded,
              size:  110,
              color: AppColors.onPrimary.withAlpha(15),
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(18),
            child: Row(
              children: [

                // ── Total paid ─────────────────────────
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Container(
                            width:  32,
                            height: 32,
                            decoration: BoxDecoration(
                              color:        AppColors.onPrimary.withAlpha(25),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: const Icon(
                              Icons.currency_rupee_rounded,
                              color: AppColors.onPrimary,
                              size:  17,
                            ),
                          ),
                          const SizedBox(width: 8),
                          Text(
                            'Total Paid',
                            style: textTheme.bodySmall?.copyWith(
                              color:  AppColors.onPrimary.withAlpha(190),
                              fontSize: 12,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Text(
                        '₹${totalPaid.toStringAsFixed(0)}',
                        style: textTheme.displaySmall?.copyWith(
                          color:      AppColors.onPrimary,
                          fontWeight: FontWeight.w800,
                          fontSize:   36,
                          height:     1,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'from successful transactions',
                        style: textTheme.bodySmall?.copyWith(
                          color:   AppColors.onPrimary.withAlpha(160),
                          fontSize: 11,
                        ),
                      ),
                    ],
                  ),
                ),

                // ── Vertical divider ───────────────────
                Container(
                  width:  1,
                  height: 70,
                  color:  AppColors.onPrimary.withAlpha(40),
                  margin: const EdgeInsets.symmetric(horizontal: 18),
                ),

                // ── Total transactions ─────────────────
                Column(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    Container(
                      width:  42,
                      height: 42,
                      decoration: BoxDecoration(
                        color:        AppColors.onPrimary.withAlpha(25),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(
                        Icons.swap_horiz_rounded,
                        color: AppColors.onPrimary,
                        size:  22,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      '$totalTransactions',
                      style: textTheme.headlineMedium?.copyWith(
                        color:      AppColors.onPrimary,
                        fontWeight: FontWeight.w800,
                        height:     1,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      'Transactions',
                      style: textTheme.bodySmall?.copyWith(
                        color:   AppColors.onPrimary.withAlpha(190),
                        fontSize: 11,
                      ),
                    ),
                  ],
                ),
              ],
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
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
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
            const SizedBox(width: 6),
            AnimatedContainer(
              duration: const Duration(milliseconds: 180),
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

// ── Results Label ─────────────────────────────────────────────

class _ResultsLabel extends StatelessWidget {
  final int        count;
  final _FilterTab filter;

  const _ResultsLabel({required this.count, required this.filter});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Row(
      children: [
        RichText(
          text: TextSpan(
            children: [
              TextSpan(
                text: '$count ',
                style: textTheme.bodySmall?.copyWith(
                  color:      AppColors.primary,
                  fontWeight: FontWeight.w700,
                ),
              ),
              TextSpan(
                text: count == 1 ? 'transaction' : 'transactions',
                style: textTheme.bodySmall?.copyWith(
                  color: AppColors.textSecondary,
                ),
              ),
              if (filter != _FilterTab.all)
                TextSpan(
                  text: ' · ${filter.label}',
                  style: textTheme.bodySmall?.copyWith(
                    color: AppColors.textTertiary,
                  ),
                ),
            ],
          ),
        ),
      ],
    );
  }
}

// ── Payment Card ──────────────────────────────────────────────

class _PaymentCard extends StatefulWidget {
  final _Payment payment;
  const _PaymentCard({required this.payment});

  @override
  State<_PaymentCard> createState() => _PaymentCardState();
}

class _PaymentCardState extends State<_PaymentCard> {
  bool _expanded = false;

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;
    final p         = widget.payment;
    final isSuccess = p.status == _PaymentStatus.successful;
    final isFailed  = p.status == _PaymentStatus.failed;

    return AnimatedContainer(
      duration: const Duration(milliseconds: 220),
      decoration: BoxDecoration(
        color:        AppColors.surface,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: isSuccess
              ? AppColors.secondary.withAlpha(45)
              : isFailed
                  ? AppColors.error.withAlpha(40)
                  : AppColors.divider,
          width: (isSuccess || isFailed) ? 1.5 : 1,
        ),
        boxShadow: [
          BoxShadow(
            color:        AppColors.shadow,
            blurRadius:   10,
            spreadRadius: 0,
            offset:       const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: [

          // ── Card Header ───────────────────────────────
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 14),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [

                // Payment method icon
                Container(
                  width:  48,
                  height: 48,
                  decoration: BoxDecoration(
                    color:        p.method == _PaymentMethod.upi
                        ? AppColors.primary.withAlpha(12)
                        : p.method == _PaymentMethod.card
                            ? AppColors.infoLight
                            : p.method == _PaymentMethod.wallet
                                ? AppColors.successLight
                                : AppColors.warningLight,
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Icon(
                    p.method.icon,
                    size:  22,
                    color: p.method == _PaymentMethod.upi
                        ? AppColors.primary
                        : p.method == _PaymentMethod.card
                            ? AppColors.info
                            : p.method == _PaymentMethod.wallet
                                ? AppColors.secondaryDark
                                : AppColors.accent,
                  ),
                ),

                const SizedBox(width: 12),

                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        p.parkingName,
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
                              p.address,
                              style: textTheme.bodySmall?.copyWith(
                                color: AppColors.textTertiary,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      // Date + time
                      Row(
                        children: [
                          const Icon(
                            Icons.calendar_today_rounded,
                            color: AppColors.textTertiary,
                            size:  11,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            '${p.date}  ·  ${p.time}',
                            style: textTheme.bodySmall?.copyWith(
                              color:    AppColors.textSecondary,
                              fontSize: 11,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),

                const SizedBox(width: 8),

                // Amount + status column
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      p.status == _PaymentStatus.refunded
                          ? '+ ₹${p.amount.toStringAsFixed(0)}'
                          : '₹${p.amount.toStringAsFixed(0)}',
                      style: textTheme.titleMedium?.copyWith(
                        color:      p.status == _PaymentStatus.refunded
                            ? AppColors.info
                            : p.status == _PaymentStatus.failed
                                ? AppColors.textDisabled
                                : AppColors.textPrimary,
                        fontWeight: FontWeight.w800,
                        fontSize:   18,
                        decoration: p.status == _PaymentStatus.failed
                            ? TextDecoration.lineThrough
                            : null,
                      ),
                    ),
                    const SizedBox(height: 6),
                    _StatusBadge(status: p.status),
                  ],
                ),
              ],
            ),
          ),

          Divider(height: 1, color: AppColors.divider),

          // ── Expandable details ─────────────────────────
          AnimatedCrossFade(
            duration:       const Duration(milliseconds: 220),
            crossFadeState: _expanded
                ? CrossFadeState.showSecond
                : CrossFadeState.showFirst,
            firstChild:  const SizedBox(width: double.infinity),
            secondChild: _ExpandedDetails(payment: p),
          ),

          // ── Footer ────────────────────────────────────
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            child: Row(
              children: [

                // Booking ID
                const Icon(
                  Icons.confirmation_number_outlined,
                  color: AppColors.textTertiary,
                  size:  13,
                ),
                const SizedBox(width: 4),
                Expanded(
                  child: Text(
                    p.bookingId,
                    style: textTheme.bodySmall?.copyWith(
                      color:    AppColors.textTertiary,
                      fontSize: 11,
                    ),
                    overflow: TextOverflow.ellipsis,
                  ),
                ),

                // Receipt button
                if (p.status == _PaymentStatus.successful ||
                    p.status == _PaymentStatus.refunded) ...[
                  _ReceiptButton(onTap: () => _showReceiptSheet(context, p)),
                  const SizedBox(width: 8),
                ],

                // Expand toggle
                _ExpandToggle(
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

  void _showReceiptSheet(BuildContext context, _Payment p) {
    showModalBottomSheet(
      context:             context,
      isScrollControlled:  true,
      backgroundColor:     Colors.transparent,
      builder: (_) => _ReceiptSheet(payment: p),
    );
  }
}

// ── Status Badge ──────────────────────────────────────────────

class _StatusBadge extends StatelessWidget {
  final _PaymentStatus status;
  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color:        status.bgColor,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: status.color.withAlpha(55),
          width: 1,
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
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

// ── Expanded Detail Section ───────────────────────────────────

class _ExpandedDetails extends StatelessWidget {
  final _Payment payment;
  const _ExpandedDetails({required this.payment});

  @override
  Widget build(BuildContext context) {
    final p = payment;

    return Container(
      margin: const EdgeInsets.fromLTRB(16, 4, 16, 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color:        AppColors.surfaceVariant,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.divider, width: 1),
      ),
      child: Column(
        children: [
          _DetailRow(
            icon:  Icons.tag_rounded,
            label: 'Transaction ID',
            value: p.transactionId,
          ),
          const SizedBox(height: 10),
          _DetailRow(
            icon:  p.method.icon,
            label: 'Payment Method',
            value: p.method.detail,
          ),
          const SizedBox(height: 10),
          _DetailRow(
            icon:  Icons.confirmation_number_outlined,
            label: 'Booking ID',
            value: p.bookingId,
          ),
          if (p.status == _PaymentStatus.refunded) ...[
            const SizedBox(height: 10),
            _DetailRow(
              icon:       Icons.keyboard_return_rounded,
              label:      'Refund Status',
              value:      'Credited to source',
              valueColor: AppColors.info,
            ),
          ],
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
        Icon(icon, color: AppColors.textTertiary, size: 14),
        const SizedBox(width: 8),
        Text(
          label,
          style: textTheme.bodySmall?.copyWith(
            color: AppColors.textSecondary,
          ),
        ),
        const Spacer(),
        Flexible(
          child: Text(
            value,
            style: textTheme.bodySmall?.copyWith(
              color:      valueColor ?? AppColors.textPrimary,
              fontWeight: FontWeight.w700,
              fontFeatures: const [FontFeature.tabularFigures()],
            ),
            textAlign: TextAlign.end,
            overflow:  TextOverflow.ellipsis,
          ),
        ),
      ],
    );
  }
}

// ── Receipt Button ────────────────────────────────────────────

class _ReceiptButton extends StatelessWidget {
  final VoidCallback onTap;
  const _ReceiptButton({required this.onTap});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color:        AppColors.primary.withAlpha(12),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(
            color: AppColors.primary.withAlpha(35),
            width: 1,
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(
              Icons.receipt_outlined,
              color: AppColors.primary,
              size:  13,
            ),
            const SizedBox(width: 4),
            Text(
              'Receipt',
              style: textTheme.labelSmall?.copyWith(
                color:      AppColors.primary,
                fontWeight: FontWeight.w700,
                fontSize:   11,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── Expand Toggle ─────────────────────────────────────────────

class _ExpandToggle extends StatelessWidget {
  final bool         expanded;
  final VoidCallback onTap;

  const _ExpandToggle({required this.expanded, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color:        AppColors.surfaceVariant,
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: AppColors.divider, width: 1),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              expanded ? 'Less' : 'Details',
              style: textTheme.labelSmall?.copyWith(
                color:      AppColors.textSecondary,
                fontWeight: FontWeight.w600,
                fontSize:   11,
              ),
            ),
            const SizedBox(width: 3),
            AnimatedRotation(
              turns:    expanded ? 0.5 : 0.0,
              duration: const Duration(milliseconds: 220),
              child: const Icon(
                Icons.keyboard_arrow_down_rounded,
                color: AppColors.textSecondary,
                size:  15,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── Receipt Bottom Sheet ──────────────────────────────────────

class _ReceiptSheet extends StatelessWidget {
  final _Payment payment;
  const _ReceiptSheet({required this.payment});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;
    final p         = payment;

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

              // Header
              Row(
                children: [
                  Container(
                    width:  42,
                    height: 42,
                    decoration: BoxDecoration(
                      color:        AppColors.primary.withAlpha(12),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(
                      Icons.receipt_long_rounded,
                      color: AppColors.primary,
                      size:  20,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Payment Receipt',
                        style: textTheme.titleMedium?.copyWith(
                          color:      AppColors.textPrimary,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      Text(
                        p.transactionId,
                        style: textTheme.bodySmall?.copyWith(
                          color:         AppColors.textTertiary,
                          fontFeatures:  const [FontFeature.tabularFigures()],
                        ),
                      ),
                    ],
                  ),
                  const Spacer(),
                  _StatusBadge(status: p.status),
                ],
              ),

              const SizedBox(height: 20),
              Divider(color: AppColors.divider, height: 1),
              const SizedBox(height: 16),

              // Amount hero
              Container(
                width:   double.infinity,
                padding: const EdgeInsets.symmetric(vertical: 18),
                decoration: BoxDecoration(
                  color:        p.status == _PaymentStatus.refunded
                      ? AppColors.infoLight
                      : AppColors.successLight,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                    color: p.status == _PaymentStatus.refunded
                        ? AppColors.info.withAlpha(50)
                        : AppColors.secondary.withAlpha(50),
                    width: 1,
                  ),
                ),
                child: Column(
                  children: [
                    Text(
                      p.status == _PaymentStatus.refunded ? 'Refunded' : 'Amount Paid',
                      style: textTheme.bodySmall?.copyWith(
                        color: p.status == _PaymentStatus.refunded
                            ? AppColors.info
                            : AppColors.secondaryDark,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '₹${p.amount.toStringAsFixed(0)}',
                      style: textTheme.displaySmall?.copyWith(
                        color: p.status == _PaymentStatus.refunded
                            ? AppColors.info
                            : AppColors.secondaryDark,
                        fontWeight: FontWeight.w800,
                        fontSize:   40,
                        height:     1,
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 18),

              // Detail rows
              _ReceiptRow(label: 'Parking',    value: p.parkingName),
              _ReceiptRow(label: 'Booking ID', value: p.bookingId),
              _ReceiptRow(label: 'Date',       value: p.date),
              _ReceiptRow(label: 'Time',       value: p.time),
              _ReceiptRow(label: 'Method',     value: p.method.detail),

              const SizedBox(height: 18),
              Divider(color: AppColors.divider, height: 1),
              const SizedBox(height: 18),

              // Actions
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () {},
                      icon:  const Icon(Icons.download_rounded, size: 16),
                      label: const Text('Download'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppColors.primary,
                        side: const BorderSide(
                          color: AppColors.primary,
                          width: 1.5,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        padding: const EdgeInsets.symmetric(vertical: 14),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: FilledButton.icon(
                      onPressed: () => Navigator.of(context).pop(),
                      icon:  const Icon(Icons.close_rounded, size: 16),
                      label: const Text('Close'),
                      style: FilledButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: AppColors.onPrimary,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        elevation:   0,
                        shadowColor: Colors.transparent,
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _ReceiptRow extends StatelessWidget {
  final String label;
  final String value;

  const _ReceiptRow({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        children: [
          Text(
            label,
            style: textTheme.bodyMedium?.copyWith(
              color: AppColors.textSecondary,
            ),
          ),
          const Spacer(),
          Flexible(
            child: Text(
              value,
              style: textTheme.bodyMedium?.copyWith(
                color:      AppColors.textPrimary,
                fontWeight: FontWeight.w700,
              ),
              textAlign: TextAlign.end,
              overflow:  TextOverflow.ellipsis,
            ),
          ),
        ],
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
              child: const Icon(
                Icons.receipt_long_outlined,
                color: AppColors.textTertiary,
                size:  38,
              ),
            ),
            const SizedBox(height: 20),
            Text(
              'No ${filter.label} Payments',
              style: textTheme.titleMedium?.copyWith(
                color:      AppColors.textPrimary,
                fontWeight: FontWeight.w700,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              'No ${filter.label.toLowerCase()} transactions found in your payment history.',
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