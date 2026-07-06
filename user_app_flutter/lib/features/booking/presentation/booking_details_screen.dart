import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_colors.dart';

// ============================================================
// BookingDetailsScreen
// ============================================================
//
// Full-detail view for a single parking booking.
//
// SECTIONS:
//   1. AppBar                — "Booking Details" + share action
//   2. Status card           — confirmed badge, booking ID, QR
//   3. Parking info card     — name, address, level, slot
//   4. Booking info card     — date, start/end time, duration
//   5. Vehicle info card     — number, type, colour
//   6. Payment summary card  — charges, tax, discount, total
//   7. Sticky bottom bar     — Extend Booking / Cancel Booking
//
// DATA: all static dummy data — no API, no state management.
//
// ARCHITECTURE:
//   StatefulWidget — drives the QR shimmer animation and
//   the cancel confirmation dialog.
//   No Riverpod, Bloc, Provider.
// ============================================================

// ── Dummy booking data ────────────────────────────────────────

const _bookingId      = 'BKG-2025-78421';
const _transactionId  = 'TXN9284710234';
const _parkingName    = 'Cyber Hub Parking Complex';
const _parkingAddress = 'DLF Cyber Hub, Sector 24, Gurugram, Haryana 122002';
const _parkingLevel   = 'Ground Floor';
const _slotNumber     = 'G09';
const _bookingDate    = 'Saturday, 12 July 2025';
const _startTime      = '10:00 AM';
const _endTime        = '12:00 PM';
const _duration       = '2 hours';
const _vehicleNumber  = 'DL 01 AB 1234';
const _vehicleType    = 'Car';
const _vehicleColor   = 'Pearl White';
const _parkingCharge  = 80.0;
const _tax            = 4.0;
const _discount       = 10.0;
const _total          = 74.0;

// ── Screen ────────────────────────────────────────────────────

class BookingDetailsScreen extends StatefulWidget {
  const BookingDetailsScreen({super.key});

  @override
  State<BookingDetailsScreen> createState() => _BookingDetailsScreenState();
}

class _BookingDetailsScreenState extends State<BookingDetailsScreen>
    with SingleTickerProviderStateMixin {
  // Subtle shimmer on QR placeholder
  late final AnimationController _shimmerCtrl;
  late final Animation<double>   _shimmerAnim;

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

    _shimmerCtrl = AnimationController(
      vsync:    this,
      duration: const Duration(seconds: 2),
    )..repeat(reverse: true);

    _shimmerAnim = Tween<double>(begin: 0.3, end: 0.7).animate(
      CurvedAnimation(parent: _shimmerCtrl, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _shimmerCtrl.dispose();
    super.dispose();
  }

  // ── Actions ───────────────────────────────────────────────

  void _showExtendSheet() {
    showModalBottomSheet(
      context:             context,
      isScrollControlled:  true,
      backgroundColor:     Colors.transparent,
      builder: (_) => const _ExtendBookingSheet(),
    );
  }

  void _showCancelDialog() {
    showDialog(
      context: context,
      builder: (_) => _CancelDialog(
        bookingId: _bookingId,
        onConfirm: () {
          // TODO: cancel booking via repository
          Navigator.of(context).pop();
          _showSnack('Booking cancellation requested.');
        },
      ),
    );
  }

  void _showSnack(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content:         Text(message),
        behavior:        SnackBarBehavior.floating,
        duration:        const Duration(seconds: 2),
        backgroundColor: AppColors.textPrimary,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
      ),
    );
  }

  void _copyToClipboard(String value, String label) {
    Clipboard.setData(ClipboardData(text: value));
    _showSnack('$label copied to clipboard.');
  }

  // ── Build ─────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.sizeOf(context).width;
    final hPad        = screenWidth > 600 ? screenWidth * 0.08 : 16.0;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar:          _buildAppBar(context),
      body: Stack(
        children: [

          // ── Scrollable content ───────────────────────
          CustomScrollView(
            physics: const BouncingScrollPhysics(),
            slivers: [
              SliverToBoxAdapter(
                child: Padding(
                  padding: EdgeInsets.fromLTRB(hPad, 16, hPad, 130),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [

                      // ── 1. Status card ─────────────────
                      _StatusCard(
                        shimmerAnim:  _shimmerAnim,
                        onCopyId:     () => _copyToClipboard(
                          _bookingId, 'Booking ID',
                        ),
                        onCopyTxn:    () => _copyToClipboard(
                          _transactionId, 'Transaction ID',
                        ),
                      ),

                      const SizedBox(height: 16),

                      // ── 2. Parking info ─────────────────
                      _DetailCard(
                        sectionIcon:  Icons.local_parking_rounded,
                        sectionTitle: 'Parking Information',
                        children: [
                          _InfoRow(
                            icon:  Icons.apartment_rounded,
                            label: 'Parking Name',
                            value: _parkingName,
                          ),
                          _InfoRow(
                            icon:  Icons.location_on_outlined,
                            label: 'Address',
                            value: _parkingAddress,
                            multiLine: true,
                          ),
                          _InfoRow(
                            icon:  Icons.layers_outlined,
                            label: 'Level / Floor',
                            value: _parkingLevel,
                          ),
                          _InfoRow(
                            icon:      Icons.local_parking_rounded,
                            label:     'Slot Number',
                            value:     _slotNumber,
                            valueColor: AppColors.primary,
                            isLast:    true,
                          ),
                        ],
                      ),

                      const SizedBox(height: 16),

                      // ── 3. Booking info ─────────────────
                      _DetailCard(
                        sectionIcon:  Icons.calendar_today_rounded,
                        sectionTitle: 'Booking Information',
                        children: [
                          _InfoRow(
                            icon:  Icons.event_rounded,
                            label: 'Booking Date',
                            value: _bookingDate,
                          ),
                          _InfoRow(
                            icon:  Icons.login_rounded,
                            label: 'Start Time',
                            value: _startTime,
                          ),
                          _InfoRow(
                            icon:  Icons.logout_rounded,
                            label: 'End Time',
                            value: _endTime,
                          ),
                          _InfoRow(
                            icon:      Icons.timelapse_rounded,
                            label:     'Duration',
                            value:     _duration,
                            isLast:    true,
                          ),
                        ],
                      ),

                      const SizedBox(height: 16),

                      // ── 4. Vehicle info ─────────────────
                      _DetailCard(
                        sectionIcon:  Icons.directions_car_rounded,
                        sectionTitle: 'Vehicle Information',
                        children: [
                          _InfoRow(
                            icon:  Icons.pin_outlined,
                            label: 'Vehicle Number',
                            value: _vehicleNumber,
                          ),
                          _InfoRow(
                            icon:  Icons.airport_shuttle_outlined,
                            label: 'Vehicle Type',
                            value: _vehicleType,
                          ),
                          _InfoRow(
                            icon:    Icons.palette_outlined,
                            label:   'Colour',
                            value:   _vehicleColor,
                            isLast:  true,
                          ),
                        ],
                      ),

                      const SizedBox(height: 16),

                      // ── 5. Payment summary ──────────────
                      _PaymentSummaryCard(),
                    ],
                  ),
                ),
              ),
            ],
          ),

          // ── Sticky bottom bar ────────────────────────
          Positioned(
            bottom: 0,
            left:   0,
            right:  0,
            child:  _StickyBottomBar(
              onExtend: _showExtendSheet,
              onCancel: _showCancelDialog,
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
        'Booking Details',
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
          tooltip: 'Share',
          onPressed: () {},
        ),
        const SizedBox(width: 4),
      ],
      bottom: PreferredSize(
        preferredSize: const Size.fromHeight(1),
        child: Divider(height: 1, color: AppColors.divider),
      ),
    );
  }
}

// ── 1. Status Card ────────────────────────────────────────────

class _StatusCard extends StatelessWidget {
  final Animation<double> shimmerAnim;
  final VoidCallback      onCopyId;
  final VoidCallback      onCopyTxn;

  const _StatusCard({
    required this.shimmerAnim,
    required this.onCopyId,
    required this.onCopyTxn,
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
        borderRadius: BorderRadius.circular(22),
        boxShadow: [
          BoxShadow(
            color:        AppColors.primary.withAlpha(70),
            blurRadius:   20,
            spreadRadius: 0,
            offset:       const Offset(0, 8),
          ),
        ],
      ),
      child: Stack(
        children: [

          // Watermark
          Positioned(
            right:  -20,
            top:    -20,
            child:  Icon(
              Icons.local_parking_rounded,
              size:  140,
              color: AppColors.onPrimary.withAlpha(12),
            ),
          ),

          Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              children: [

                // ── Confirmed badge ─────────────────────
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 14,
                    vertical:    7,
                  ),
                  decoration: BoxDecoration(
                    color:        AppColors.secondary,
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: [
                      BoxShadow(
                        color:       AppColors.secondary.withAlpha(60),
                        blurRadius:  10,
                        offset:      const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(
                        Icons.check_circle_rounded,
                        color: AppColors.onSecondary,
                        size:  16,
                      ),
                      const SizedBox(width: 6),
                      Text(
                        'Booking Confirmed',
                        style: textTheme.labelMedium?.copyWith(
                          color:      AppColors.onSecondary,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 20),

                // ── QR Code placeholder ─────────────────
                _QrPlaceholder(shimmerAnim: shimmerAnim),

                const SizedBox(height: 20),

                // ── Booking ID ──────────────────────────
                GestureDetector(
                  onTap: onCopyId,
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical:    10,
                    ),
                    decoration: BoxDecoration(
                      color:        AppColors.onPrimary.withAlpha(20),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: AppColors.onPrimary.withAlpha(35),
                        width: 1,
                      ),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(
                          Icons.confirmation_number_outlined,
                          color: AppColors.onPrimary,
                          size:  15,
                        ),
                        const SizedBox(width: 8),
                        Text(
                          _bookingId,
                          style: textTheme.titleSmall?.copyWith(
                            color:         AppColors.onPrimary,
                            fontWeight:    FontWeight.w700,
                            letterSpacing: 0.5,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Icon(
                          Icons.copy_rounded,
                          color: AppColors.onPrimary.withAlpha(180),
                          size:  13,
                        ),
                      ],
                    ),
                  ),
                ),

                const SizedBox(height: 10),

                // ── Transaction ID ──────────────────────
                GestureDetector(
                  onTap: onCopyTxn,
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(
                        Icons.tag_rounded,
                        color: AppColors.onPrimary,
                        size:  13,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        _transactionId,
                        style: textTheme.bodySmall?.copyWith(
                          color:         AppColors.onPrimary.withAlpha(200),
                          fontFeatures:  const [FontFeature.tabularFigures()],
                          letterSpacing: 0.3,
                        ),
                      ),
                      const SizedBox(width: 5),
                      Icon(
                        Icons.copy_rounded,
                        color: AppColors.onPrimary.withAlpha(150),
                        size:  11,
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 14),

                // ── Valid info ──────────────────────────
                Container(
                  width:   double.infinity,
                  padding: const EdgeInsets.symmetric(vertical: 10),
                  decoration: BoxDecoration(
                    color:        AppColors.onPrimary.withAlpha(18),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(
                        Icons.access_time_rounded,
                        color: AppColors.onPrimary,
                        size:  14,
                      ),
                      const SizedBox(width: 6),
                      Text(
                        '$_bookingDate · $_startTime – $_endTime',
                        style: textTheme.bodySmall?.copyWith(
                          color:      AppColors.onPrimary,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ── QR Placeholder ────────────────────────────────────────────

class _QrPlaceholder extends StatelessWidget {
  final Animation<double> shimmerAnim;
  const _QrPlaceholder({required this.shimmerAnim});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      width:  180,
      height: 180,
      decoration: BoxDecoration(
        color:        AppColors.onPrimary,
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color:       AppColors.primaryDark.withAlpha(80),
            blurRadius:  20,
            offset:      const Offset(0, 8),
          ),
        ],
      ),
      child: Stack(
        children: [

          // Corner marks
          _qrCorner(Alignment.topLeft,
              const BorderRadius.only(topLeft: Radius.circular(4))),
          _qrCorner(Alignment.topRight,
              const BorderRadius.only(topRight: Radius.circular(4))),
          _qrCorner(Alignment.bottomLeft,
              const BorderRadius.only(bottomLeft: Radius.circular(4))),
          _qrCorner(Alignment.bottomRight,
              const BorderRadius.only(bottomRight: Radius.circular(4))),

          // Shimmer scan line
          AnimatedBuilder(
            animation: shimmerAnim,
            builder: (_, __) => Positioned(
              left:  18,
              right: 18,
              top:   18 + shimmerAnim.value * (180 - 36),
              child: Container(
                height: 2,
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [
                      AppColors.primary.withAlpha(0),
                      AppColors.primary.withAlpha(160),
                      AppColors.primary.withAlpha(0),
                    ],
                  ),
                  borderRadius: BorderRadius.circular(1),
                ),
              ),
            ),
          ),

          // Centre icon
          Center(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width:  52,
                  height: 52,
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
                  'SCAN TO ENTER',
                  style: textTheme.labelSmall?.copyWith(
                    color:         AppColors.primary,
                    fontSize:      9,
                    letterSpacing: 1.5,
                    fontWeight:    FontWeight.w700,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _qrCorner(AlignmentGeometry alignment, BorderRadius borderRadius) {
    return Positioned.fill(
      child: Align(
        alignment: alignment,
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: SizedBox(
            width:  22,
            height: 22,
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

// ── 2–4. Detail Card ──────────────────────────────────────────

class _DetailCard extends StatelessWidget {
  final IconData     sectionIcon;
  final String       sectionTitle;
  final List<Widget> children;

  const _DetailCard({
    required this.sectionIcon,
    required this.sectionTitle,
    required this.children,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      decoration: BoxDecoration(
        color:        AppColors.surface,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppColors.divider, width: 1),
        boxShadow: [
          BoxShadow(
            color:       AppColors.shadow,
            blurRadius:  10,
            spreadRadius: 0,
            offset:      const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [

          // Section header
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 12),
            child: Row(
              children: [
                Container(
                  width:  34,
                  height: 34,
                  decoration: BoxDecoration(
                    color:        AppColors.primary.withAlpha(12),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(sectionIcon, color: AppColors.primary, size: 17),
                ),
                const SizedBox(width: 10),
                Text(
                  sectionTitle,
                  style: textTheme.titleSmall?.copyWith(
                    color:      AppColors.textPrimary,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
          ),

          Divider(height: 1, color: AppColors.divider),

          // Rows
          ...children,
        ],
      ),
    );
  }
}

// ── Info Row ──────────────────────────────────────────────────

class _InfoRow extends StatelessWidget {
  final IconData icon;
  final String   label;
  final String   value;
  final Color?   valueColor;
  final bool     multiLine;
  final bool     isLast;

  const _InfoRow({
    required this.icon,
    required this.label,
    required this.value,
    this.valueColor,
    this.multiLine = false,
    this.isLast    = false,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 13),
          child: Row(
            crossAxisAlignment: multiLine
                ? CrossAxisAlignment.start
                : CrossAxisAlignment.center,
            children: [

              Container(
                width:  32,
                height: 32,
                decoration: BoxDecoration(
                  color:        AppColors.surfaceVariant,
                  borderRadius: BorderRadius.circular(9),
                ),
                child: Icon(icon, color: AppColors.textSecondary, size: 16),
              ),

              const SizedBox(width: 12),

              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      label,
                      style: textTheme.bodySmall?.copyWith(
                        color:    AppColors.textTertiary,
                        fontSize: 11,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      value,
                      style: textTheme.bodyMedium?.copyWith(
                        color:      valueColor ?? AppColors.textPrimary,
                        fontWeight: FontWeight.w600,
                        height:     multiLine ? 1.4 : 1,
                      ),
                    ),
                  ],
                ),
              ),

              if (valueColor != null)
                Container(
                  width:  8,
                  height: 8,
                  decoration: BoxDecoration(
                    color:  valueColor,
                    shape:  BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color:       valueColor!.withAlpha(80),
                        blurRadius:  6,
                        spreadRadius: 1,
                      ),
                    ],
                  ),
                ),
            ],
          ),
        ),
        if (!isLast)
          Divider(
            height:    1,
            color:     AppColors.divider,
            indent:    60,
            endIndent: 0,
          ),
      ],
    );
  }
}

// ── 5. Payment Summary Card ───────────────────────────────────

class _PaymentSummaryCard extends StatelessWidget {
  const _PaymentSummaryCard();

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      decoration: BoxDecoration(
        color:        AppColors.surface,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppColors.divider, width: 1),
        boxShadow: [
          BoxShadow(
            color:       AppColors.shadow,
            blurRadius:  10,
            spreadRadius: 0,
            offset:      const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: [

          // Header
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 12),
            child: Row(
              children: [
                Container(
                  width:  34,
                  height: 34,
                  decoration: BoxDecoration(
                    color:        AppColors.successLight,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(
                    Icons.receipt_long_rounded,
                    color: AppColors.secondaryDark,
                    size:  17,
                  ),
                ),
                const SizedBox(width: 10),
                Text(
                  'Payment Summary',
                  style: textTheme.titleSmall?.copyWith(
                    color:      AppColors.textPrimary,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const Spacer(),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical:   3,
                  ),
                  decoration: BoxDecoration(
                    color:        AppColors.successLight,
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(
                      color: AppColors.secondaryDark.withAlpha(50),
                      width: 1,
                    ),
                  ),
                  child: Text(
                    'Paid',
                    style: textTheme.labelSmall?.copyWith(
                      color:      AppColors.secondaryDark,
                      fontWeight: FontWeight.w700,
                      fontSize:   10,
                    ),
                  ),
                ),
              ],
            ),
          ),

          Divider(height: 1, color: AppColors.divider),

          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [

                _PayRow(
                  label: 'Parking Charges',
                  value: '₹${_parkingCharge.toStringAsFixed(0)}',
                  icon:  Icons.local_parking_rounded,
                ),
                const SizedBox(height: 12),
                _PayRow(
                  label: 'GST & Taxes',
                  value: '₹${_tax.toStringAsFixed(0)}',
                  icon:  Icons.account_balance_outlined,
                ),
                const SizedBox(height: 12),
                _PayRow(
                  label:      'Discount Applied',
                  value:      '− ₹${_discount.toStringAsFixed(0)}',
                  icon:       Icons.local_offer_rounded,
                  valueColor: AppColors.secondaryDark,
                ),

                const SizedBox(height: 14),

                Container(
                  width:  double.infinity,
                  height: 1,
                  color:  AppColors.divider,
                ),

                const SizedBox(height: 14),

                // Total
                Row(
                  children: [
                    Container(
                      width:  34,
                      height: 34,
                      decoration: BoxDecoration(
                        color:        AppColors.primary,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Icon(
                        Icons.currency_rupee_rounded,
                        color: AppColors.onPrimary,
                        size:  17,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Text(
                      'Total Paid',
                      style: textTheme.titleSmall?.copyWith(
                        color:      AppColors.textPrimary,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    const Spacer(),
                    Text(
                      '₹${_total.toStringAsFixed(0)}',
                      style: textTheme.titleLarge?.copyWith(
                        color:      AppColors.primary,
                        fontWeight: FontWeight.w800,
                        fontSize:   24,
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 14),

                // Payment method strip
                Container(
                  width:   double.infinity,
                  padding: const EdgeInsets.symmetric(
                    horizontal: 14,
                    vertical:   10,
                  ),
                  decoration: BoxDecoration(
                    color:        AppColors.surfaceVariant,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppColors.divider, width: 1),
                  ),
                  child: Row(
                    children: [
                      const Icon(
                        Icons.account_balance_rounded,
                        color: AppColors.primary,
                        size:  16,
                      ),
                      const SizedBox(width: 8),
                      Text(
                        'Paid via GPay · UPI  ·  $_transactionId',
                        style: textTheme.bodySmall?.copyWith(
                          color:         AppColors.textSecondary,
                          fontFeatures:  const [FontFeature.tabularFigures()],
                          fontSize:      11,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _PayRow extends StatelessWidget {
  final String  label;
  final String  value;
  final IconData icon;
  final Color?  valueColor;

  const _PayRow({
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
        Icon(icon, color: AppColors.textTertiary, size: 15),
        const SizedBox(width: 10),
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

// ── Sticky Bottom Bar ─────────────────────────────────────────

class _StickyBottomBar extends StatelessWidget {
  final VoidCallback onExtend;
  final VoidCallback onCancel;

  const _StickyBottomBar({
    required this.onExtend,
    required this.onCancel,
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
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
          child: Row(
            children: [

              // ── Cancel (outlined) ──────────────────
              SizedBox(
                height: 52,
                child: OutlinedButton(
                  onPressed: onCancel,
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppColors.error,
                    side: const BorderSide(
                      color: AppColors.error,
                      width: 1.5,
                    ),
                    backgroundColor: AppColors.errorLight,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                    padding: const EdgeInsets.symmetric(horizontal: 18),
                    textStyle: textTheme.labelLarge?.copyWith(
                      fontSize:   14,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.cancel_outlined, size: 17),
                      const SizedBox(width: 6),
                      const Text('Cancel'),
                    ],
                  ),
                ),
              ),

              const SizedBox(width: 12),

              // ── Extend (filled) ────────────────────
              Expanded(
                child: SizedBox(
                  height: 52,
                  child: FilledButton(
                    onPressed: onExtend,
                    style: FilledButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      foregroundColor: AppColors.onPrimary,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                      textStyle: textTheme.labelLarge?.copyWith(
                        fontSize:      15,
                        fontWeight:    FontWeight.w700,
                        letterSpacing: 0.3,
                      ),
                      elevation:   0,
                      shadowColor: Colors.transparent,
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.more_time_rounded, size: 18),
                        const SizedBox(width: 8),
                        const Text('Extend Booking'),
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

// ── Extend Booking Sheet ──────────────────────────────────────

class _ExtendBookingSheet extends StatefulWidget {
  const _ExtendBookingSheet();

  @override
  State<_ExtendBookingSheet> createState() => _ExtendBookingSheetState();
}

class _ExtendBookingSheetState extends State<_ExtendBookingSheet> {
  int _selectedHours = 1;

  static const List<int> _options = [1, 2, 3, 4];

  double get _additionalCost => _selectedHours * 40.0;

  @override
  Widget build(BuildContext context) {
    final textTheme   = Theme.of(context).textTheme;
    final bottomInset = MediaQuery.viewInsetsOf(context).bottom;

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 12),
      decoration: const BoxDecoration(
        color:        AppColors.surface,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: EdgeInsets.fromLTRB(20, 0, 20, bottomInset + 20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
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
              Row(
                children: [
                  Container(
                    width:  40,
                    height: 40,
                    decoration: BoxDecoration(
                      color:        AppColors.primary.withAlpha(12),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(
                      Icons.more_time_rounded,
                      color: AppColors.primary,
                      size:  20,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Extend Booking',
                        style: textTheme.titleMedium?.copyWith(
                          color:      AppColors.textPrimary,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      Text(
                        'Slot $_slotNumber · $_parkingName',
                        style: textTheme.bodySmall?.copyWith(
                          color: AppColors.textTertiary,
                        ),
                      ),
                    ],
                  ),
                ],
              ),

              const SizedBox(height: 22),

              Text(
                'Select additional hours',
                style: textTheme.labelLarge?.copyWith(
                  color:      AppColors.textPrimary,
                  fontWeight: FontWeight.w600,
                  fontSize:   13,
                ),
              ),

              const SizedBox(height: 12),

              // Hour selector
              Row(
                children: _options.map((h) {
                  final isSelected = h == _selectedHours;
                  return Expanded(
                    child: GestureDetector(
                      onTap: () => setState(() => _selectedHours = h),
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 160),
                        margin: EdgeInsets.only(
                          right: h != _options.last ? 10 : 0,
                        ),
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        decoration: BoxDecoration(
                          color: isSelected
                              ? AppColors.primary
                              : AppColors.surfaceVariant,
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(
                            color: isSelected
                                ? AppColors.primary
                                : AppColors.divider,
                            width: isSelected ? 0 : 1,
                          ),
                          boxShadow: isSelected
                              ? [
                                  BoxShadow(
                                    color:       AppColors.primary.withAlpha(55),
                                    blurRadius:  10,
                                    offset:      const Offset(0, 4),
                                  ),
                                ]
                              : null,
                        ),
                        child: Column(
                          children: [
                            Text(
                              '+$h',
                              style: textTheme.titleMedium?.copyWith(
                                color:      isSelected
                                    ? AppColors.onPrimary
                                    : AppColors.textPrimary,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                            Text(
                              h == 1 ? 'hr' : 'hrs',
                              style: textTheme.bodySmall?.copyWith(
                                color: isSelected
                                    ? AppColors.onPrimary.withAlpha(200)
                                    : AppColors.textTertiary,
                                fontSize: 11,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  );
                }).toList(),
              ),

              const SizedBox(height: 20),

              // Cost preview
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color:        AppColors.surfaceVariant,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: AppColors.divider, width: 1),
                ),
                child: Row(
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Additional Cost',
                          style: textTheme.bodySmall?.copyWith(
                            color: AppColors.textTertiary,
                          ),
                        ),
                        Text(
                          '₹${_additionalCost.toStringAsFixed(0)}',
                          style: textTheme.titleLarge?.copyWith(
                            color:      AppColors.primary,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ],
                    ),
                    const Spacer(),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text(
                          'New End Time',
                          style: textTheme.bodySmall?.copyWith(
                            color: AppColors.textTertiary,
                          ),
                        ),
                        Text(
                          _selectedHours == 1
                              ? '01:00 PM'
                              : _selectedHours == 2
                                  ? '02:00 PM'
                                  : _selectedHours == 3
                                      ? '03:00 PM'
                                      : '04:00 PM',
                          style: textTheme.titleMedium?.copyWith(
                            color:      AppColors.textPrimary,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 20),

              // Confirm button
              SizedBox(
                height: 52,
                child: FilledButton(
                  onPressed: () {
                    Navigator.of(context).pop();
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text(
                          'Booking extended by $_selectedHours '
                          '${_selectedHours == 1 ? 'hour' : 'hours'}.',
                        ),
                        behavior:        SnackBarBehavior.floating,
                        backgroundColor: AppColors.textPrimary,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                    );
                  },
                  style: FilledButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    foregroundColor: AppColors.onPrimary,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                    textStyle: textTheme.labelLarge?.copyWith(
                      fontSize:      16,
                      fontWeight:    FontWeight.w700,
                      letterSpacing: 0.3,
                    ),
                    elevation:   0,
                    shadowColor: Colors.transparent,
                  ),
                  child: const Text('Confirm Extension'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ── Cancel Confirmation Dialog ────────────────────────────────

class _CancelDialog extends StatelessWidget {
  final String       bookingId;
  final VoidCallback onConfirm;

  const _CancelDialog({
    required this.bookingId,
    required this.onConfirm,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return AlertDialog(
      backgroundColor:  AppColors.surface,
      surfaceTintColor: Colors.transparent,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(22),
      ),
      icon: Container(
        width:  60,
        height: 60,
        decoration: BoxDecoration(
          color:        AppColors.errorLight,
          borderRadius: BorderRadius.circular(18),
        ),
        child: const Icon(
          Icons.cancel_outlined,
          color: AppColors.error,
          size:  30,
        ),
      ),
      title: Text(
        'Cancel Booking?',
        style: textTheme.titleLarge?.copyWith(
          color:      AppColors.textPrimary,
          fontWeight: FontWeight.w700,
        ),
        textAlign: TextAlign.center,
      ),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            'Are you sure you want to cancel booking $bookingId?',
            style: textTheme.bodyMedium?.copyWith(
              color:  AppColors.textSecondary,
              height: 1.5,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color:        AppColors.warningLight,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Row(
              children: [
                const Icon(
                  Icons.info_outline_rounded,
                  color: AppColors.accent,
                  size:  16,
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Refund will be processed in 3–5 business days.',
                    style: textTheme.bodySmall?.copyWith(
                      color:      AppColors.accent,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
      actionsAlignment: MainAxisAlignment.center,
      actionsPadding:   const EdgeInsets.fromLTRB(20, 0, 20, 20),
      actions: [
        Row(
          children: [
            Expanded(
              child: OutlinedButton(
                onPressed: () => Navigator.of(context).pop(),
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppColors.textSecondary,
                  side: const BorderSide(color: AppColors.divider),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                ),
                child: const Text('Keep'),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: FilledButton(
                onPressed: onConfirm,
                style: FilledButton.styleFrom(
                  backgroundColor: AppColors.error,
                  foregroundColor: AppColors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  padding:     const EdgeInsets.symmetric(vertical: 14),
                  elevation:   0,
                  shadowColor: Colors.transparent,
                ),
                child: const Text('Cancel Booking'),
              ),
            ),
          ],
        ),
      ],
    );
  }
}

// ── Corner Painter ────────────────────────────────────────────

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