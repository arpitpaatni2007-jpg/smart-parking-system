import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_colors.dart';

// ============================================================
// BookingConfirmationScreen
// ============================================================
//
// Displayed immediately after a successful parking booking.
//
// SECTIONS:
//   1. Animated success illustration placeholder
//   2. "Booking Confirmed" title + booking ID
//   3. Booking detail card  — parking, date, time, slot, duration
//   4. Vehicle & payment row
//   5. QR Code placeholder  — scan at entry gate
//   6. Important instructions card
//   7. Bottom action buttons — Download Receipt / View My Bookings
//
// DESIGN:
//   Matches the design language of ParkingDetailsScreen and
//   SlotSelectionScreen — same AppColors palette, card shadows,
//   border-radius, section dividers, and typography scale.
//
// ARCHITECTURE:
//   StatefulWidget — drives the entrance animation sequence.
//   No Riverpod, Bloc, Provider. All data is static dummy data.
// ============================================================

// ── Dummy booking data ────────────────────────────────────────

const _bookingId      = 'BKG-2025-78421';
const _parkingName    = 'Cyber Hub Parking Complex';
const _parkingAddress = 'DLF Cyber Hub, Sector 24, Gurugram, Haryana 122002';
const _bookingDate    = 'Saturday, 12 July 2025';
const _startTime      = '10:00 AM';
const _endTime        = '12:00 PM';
const _selectedSlot   = 'G09';
const _floor          = 'Ground Floor';
const _duration       = '2 hours';
const _vehicleNumber  = 'DL 01 AB 1234';
const _vehicleType    = 'Car';
const _amountPaid     = '₹80';
const _paymentMethod  = 'UPI — GPay';
const _transactionId  = 'TXN9284710234';

const List<String> _instructions = [
  'Show this QR code at the entry gate for seamless access.',
  'Arrive within 15 minutes of your start time to retain your slot.',
  'Overtime beyond the booked duration is charged at ₹40/hr.',
  'In case of early exit, no refund is applicable for unused time.',
  'For assistance, call the helpdesk: 1800-123-4567.',
];

// ── Screen ────────────────────────────────────────────────────

class BookingConfirmationScreen extends StatefulWidget {
  const BookingConfirmationScreen({super.key});

  @override
  State<BookingConfirmationScreen> createState() =>
      _BookingConfirmationScreenState();
}

class _BookingConfirmationScreenState extends State<BookingConfirmationScreen>
    with TickerProviderStateMixin {
  // ── Entrance animations ───────────────────────────────────
  late final AnimationController _controller;
  late final Animation<double>   _illustrationScale;
  late final Animation<double>   _illustrationOpacity;
  late final Animation<double>   _contentSlide;
  late final Animation<double>   _contentOpacity;
  late final Animation<double>   _buttonsOpacity;

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

    _controller = AnimationController(
      vsync:    this,
      duration: const Duration(milliseconds: 1200),
    );

    _illustrationScale = Tween<double>(begin: 0.5, end: 1.0).animate(
      CurvedAnimation(
        parent: _controller,
        curve:  const Interval(0.0, 0.45, curve: Curves.elasticOut),
      ),
    );

    _illustrationOpacity = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _controller,
        curve:  const Interval(0.0, 0.30, curve: Curves.easeOut),
      ),
    );

    _contentSlide = Tween<double>(begin: 40.0, end: 0.0).animate(
      CurvedAnimation(
        parent: _controller,
        curve:  const Interval(0.30, 0.70, curve: Curves.easeOutCubic),
      ),
    );

    _contentOpacity = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _controller,
        curve:  const Interval(0.30, 0.70, curve: Curves.easeOut),
      ),
    );

    _buttonsOpacity = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _controller,
        curve:  const Interval(0.65, 1.0, curve: Curves.easeOut),
      ),
    );

    _controller.forward();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  // ── Build ─────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.sizeOf(context).width;
    final hPad        = screenWidth > 600 ? screenWidth * 0.08 : 20.0;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar:          _buildAppBar(context),
      body: Stack(
        children: [
          // ── Scrollable body ──────────────────────────
          CustomScrollView(
            physics: const BouncingScrollPhysics(),
            slivers: [
              SliverToBoxAdapter(
                child: Padding(
                  padding: EdgeInsets.fromLTRB(hPad, 12, hPad, 140),
                  child: Column(
                    children: [

                      // ── 1. Success Illustration ────────
                      AnimatedBuilder(
                        animation: _controller,
                        builder: (_, __) => FadeTransition(
                          opacity: _illustrationOpacity,
                          child: ScaleTransition(
                            scale: _illustrationScale,
                            child: const _SuccessIllustration(),
                          ),
                        ),
                      ),

                      const SizedBox(height: 24),

                      // ── 2. Title + Booking ID ──────────
                      AnimatedBuilder(
                        animation: _controller,
                        builder: (_, child) => Transform.translate(
                          offset: Offset(0, _contentSlide.value),
                          child:  FadeTransition(
                            opacity: _contentOpacity,
                            child:   child,
                          ),
                        ),
                        child: const _ConfirmationHeader(),
                      ),

                      const SizedBox(height: 20),

                      // ── 3. Booking Details Card ─────────
                      AnimatedBuilder(
                        animation: _controller,
                        builder: (_, child) => Transform.translate(
                          offset: Offset(0, _contentSlide.value),
                          child:  FadeTransition(
                            opacity: _contentOpacity,
                            child:   child,
                          ),
                        ),
                        child: const _BookingDetailsCard(),
                      ),

                      const SizedBox(height: 16),

                      // ── 4. Vehicle & Payment Row ────────
                      AnimatedBuilder(
                        animation: _controller,
                        builder: (_, child) => FadeTransition(
                          opacity: _contentOpacity,
                          child:   child,
                        ),
                        child: const _VehiclePaymentRow(),
                      ),

                      const SizedBox(height: 16),

                      // ── 5. QR Code ─────────────────────
                      AnimatedBuilder(
                        animation: _controller,
                        builder: (_, child) => FadeTransition(
                          opacity: _contentOpacity,
                          child:   child,
                        ),
                        child: const _QrCodeCard(),
                      ),

                      const SizedBox(height: 16),

                      // ── 6. Instructions ─────────────────
                      AnimatedBuilder(
                        animation: _controller,
                        builder: (_, child) => FadeTransition(
                          opacity: _contentOpacity,
                          child:   child,
                        ),
                        child: const _InstructionsCard(),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),

          // ── 7. Sticky Bottom Buttons ─────────────────
          Positioned(
            bottom: 0,
            left:   0,
            right:  0,
            child: AnimatedBuilder(
              animation: _controller,
              builder: (_, child) => FadeTransition(
                opacity: _buttonsOpacity,
                child:   child,
              ),
              child: const _BottomActions(),
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
      automaticallyImplyLeading: false,
      title: Text(
        'Booking Confirmation',
        style: textTheme.titleMedium?.copyWith(
          color:      AppColors.textPrimary,
          fontWeight: FontWeight.w700,
        ),
      ),
      centerTitle: true,
      actions: [
        IconButton(
          icon:    const Icon(Icons.home_outlined, size: 22),
          color:   AppColors.primary,
          tooltip: 'Go Home',
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

// ── 1. Success Illustration ───────────────────────────────────

class _SuccessIllustration extends StatelessWidget {
  const _SuccessIllustration();

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // ── Outer glow ring ─────────────────────────
        Container(
          width:  130,
          height: 130,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: AppColors.successLight,
            boxShadow: [
              BoxShadow(
                color:        AppColors.secondary.withAlpha(50),
                blurRadius:   30,
                spreadRadius: 6,
              ),
            ],
          ),
          child: Center(
            // ── Inner circle ────────────────────────
            child: Container(
              width:  98,
              height: 98,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: AppColors.secondary,
                boxShadow: [
                  BoxShadow(
                    color:       AppColors.secondary.withAlpha(80),
                    blurRadius:  16,
                    spreadRadius: 0,
                    offset:      const Offset(0, 6),
                  ),
                ],
              ),
              child: const Icon(
                Icons.check_rounded,
                color: AppColors.onPrimary,
                size:  52,
              ),
            ),
          ),
        ),

        const SizedBox(height: 16),

        // ── Parking icon row ─────────────────────────
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            _SmallBadge(
              icon:  Icons.local_parking_rounded,
              color: AppColors.primary,
            ),
            const SizedBox(width: 10),
            _SmallBadge(
              icon:  Icons.shield_outlined,
              color: AppColors.secondary,
            ),
            const SizedBox(width: 10),
            _SmallBadge(
              icon:  Icons.bolt_rounded,
              color: AppColors.accent,
            ),
          ],
        ),
      ],
    );
  }
}

class _SmallBadge extends StatelessWidget {
  final IconData icon;
  final Color    color;
  const _SmallBadge({required this.icon, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      width:  36,
      height: 36,
      decoration: BoxDecoration(
        color:        color.withAlpha(18),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: color.withAlpha(50), width: 1),
      ),
      child: Icon(icon, color: color, size: 18),
    );
  }
}

// ── 2. Confirmation Header ────────────────────────────────────

class _ConfirmationHeader extends StatelessWidget {
  const _ConfirmationHeader();

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Column(
      children: [
        Text(
          'Booking Confirmed!',
          style: textTheme.headlineMedium?.copyWith(
            color:      AppColors.textPrimary,
            fontWeight: FontWeight.w800,
          ),
          textAlign: TextAlign.center,
        ),

        const SizedBox(height: 8),

        Text(
          'Your parking slot has been reserved.\nSee you at the lot!',
          style: textTheme.bodyMedium?.copyWith(
            color:  AppColors.textSecondary,
            height: 1.5,
          ),
          textAlign: TextAlign.center,
        ),

        const SizedBox(height: 14),

        // ── Booking ID chip ──────────────────────────
        GestureDetector(
          onTap: () {
            Clipboard.setData(const ClipboardData(text: _bookingId));
            final ctx = context;
            if (ctx.mounted) {
              ScaffoldMessenger.of(ctx).showSnackBar(
                SnackBar(
                  content: const Text('Booking ID copied!'),
                  behavior:      SnackBarBehavior.floating,
                  duration:      const Duration(seconds: 2),
                  backgroundColor: AppColors.textPrimary,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              );
            }
          },
          child: Container(
            padding: const EdgeInsets.symmetric(
              horizontal: 16,
              vertical:    10,
            ),
            decoration: BoxDecoration(
              color:        AppColors.primary.withAlpha(10),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(
                color: AppColors.primary.withAlpha(40),
                width: 1.5,
              ),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(
                  Icons.confirmation_number_outlined,
                  color: AppColors.primary,
                  size:  16,
                ),
                const SizedBox(width: 8),
                Text(
                  _bookingId,
                  style: textTheme.titleSmall?.copyWith(
                    color:      AppColors.primary,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 0.5,
                  ),
                ),
                const SizedBox(width: 8),
                const Icon(
                  Icons.copy_rounded,
                  color: AppColors.primary,
                  size:  14,
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}

// ── 3. Booking Details Card ───────────────────────────────────

class _BookingDetailsCard extends StatelessWidget {
  const _BookingDetailsCard();

  @override
  Widget build(BuildContext context) {
    return _ShadowCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [

          // ── Card header ──────────────────────────────
          _CardHeader(
            icon:  Icons.receipt_long_rounded,
            label: 'Booking Details',
          ),

          const SizedBox(height: 16),

          // ── Parking info block ───────────────────────
          Container(
            padding:      const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color:        AppColors.primary.withAlpha(8),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: AppColors.primary.withAlpha(25),
                width: 1,
              ),
            ),
            child: Row(
              children: [
                Container(
                  width:  44,
                  height: 44,
                  decoration: BoxDecoration(
                    color:        AppColors.primary,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(
                    Icons.local_parking_rounded,
                    color: AppColors.onPrimary,
                    size:  22,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _parkingName,
                        style: Theme.of(context).textTheme.titleSmall?.copyWith(
                          color:      AppColors.textPrimary,
                          fontWeight: FontWeight.w700,
                        ),
                        maxLines: 2,
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
                              _parkingAddress,
                              style: Theme.of(context).textTheme.bodySmall?.copyWith(
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
              ],
            ),
          ),

          const SizedBox(height: 16),
          Divider(color: AppColors.divider, height: 1),
          const SizedBox(height: 14),

          // ── Detail rows ──────────────────────────────
          _DetailRow(
            icon:  Icons.calendar_today_rounded,
            label: 'Date',
            value: _bookingDate,
          ),
          const SizedBox(height: 12),
          _DetailRow(
            icon:  Icons.access_time_rounded,
            label: 'Time',
            value: '$_startTime – $_endTime',
          ),
          const SizedBox(height: 12),
          _DetailRow(
            icon:  Icons.local_parking_rounded,
            label: 'Slot',
            value: '$_selectedSlot · $_floor',
            valueColor: AppColors.primary,
          ),
          const SizedBox(height: 12),
          _DetailRow(
            icon:  Icons.timelapse_rounded,
            label: 'Duration',
            value: _duration,
          ),
        ],
      ),
    );
  }
}

// ── 4. Vehicle & Payment Row ──────────────────────────────────

class _VehiclePaymentRow extends StatelessWidget {
  const _VehiclePaymentRow();

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: _MiniInfoCard(
            icon:       Icons.directions_car_outlined,
            iconColor:  AppColors.info,
            iconBg:     AppColors.infoLight,
            title:      'Vehicle',
            line1:      _vehicleNumber,
            line2:      _vehicleType,
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _MiniInfoCard(
            icon:       Icons.payment_rounded,
            iconColor:  AppColors.secondaryDark,
            iconBg:     AppColors.successLight,
            title:      'Payment',
            line1:      _amountPaid,
            line2:      _paymentMethod,
            line1Style: Theme.of(context).textTheme.titleMedium?.copyWith(
              color:      AppColors.secondaryDark,
              fontWeight: FontWeight.w800,
              fontSize:   18,
            ),
          ),
        ),
      ],
    );
  }
}

class _MiniInfoCard extends StatelessWidget {
  final IconData   icon;
  final Color      iconColor;
  final Color      iconBg;
  final String     title;
  final String     line1;
  final String     line2;
  final TextStyle? line1Style;

  const _MiniInfoCard({
    required this.icon,
    required this.iconColor,
    required this.iconBg,
    required this.title,
    required this.line1,
    required this.line2,
    this.line1Style,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return _ShadowCard(
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width:  38,
            height: 38,
            decoration: BoxDecoration(
              color:        iconBg,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: iconColor, size: 18),
          ),
          const SizedBox(height: 10),
          Text(
            title,
            style: textTheme.bodySmall?.copyWith(
              color: AppColors.textTertiary,
            ),
          ),
          const SizedBox(height: 3),
          Text(
            line1,
            style: line1Style ?? textTheme.titleSmall?.copyWith(
              color:      AppColors.textPrimary,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            line2,
            style: textTheme.bodySmall?.copyWith(
              color: AppColors.textSecondary,
            ),
          ),
        ],
      ),
    );
  }
}

// ── 5. QR Code Card ───────────────────────────────────────────

class _QrCodeCard extends StatelessWidget {
  const _QrCodeCard();

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return _ShadowCard(
      child: Column(
        children: [

          _CardHeader(
            icon:  Icons.qr_code_scanner_rounded,
            label: 'Entry QR Code',
          ),

          const SizedBox(height: 16),

          Text(
            'Show this code at the entry gate for quick access.',
            style: textTheme.bodySmall?.copyWith(
              color:  AppColors.textSecondary,
              height: 1.4,
            ),
            textAlign: TextAlign.center,
          ),

          const SizedBox(height: 20),

          // ── QR placeholder ───────────────────────────
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
                // Corner marks
                ..._qrCorners(),
                // Center content
                Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        width:  60,
                        height: 60,
                        decoration: BoxDecoration(
                          color:        AppColors.primary,
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: const Icon(
                          Icons.local_parking_rounded,
                          color: AppColors.onPrimary,
                          size:  32,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'QR CODE',
                        style: textTheme.labelSmall?.copyWith(
                          color:         AppColors.textTertiary,
                          letterSpacing: 2,
                          fontSize:      10,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 16),

          // ── Transaction ID ───────────────────────────
          Container(
            padding: const EdgeInsets.symmetric(
              horizontal: 14,
              vertical:    8,
            ),
            decoration: BoxDecoration(
              color:        AppColors.surfaceVariant,
              borderRadius: BorderRadius.circular(8),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(
                  Icons.tag_rounded,
                  color: AppColors.textTertiary,
                  size:  13,
                ),
                const SizedBox(width: 5),
                Text(
                  _transactionId,
                  style: textTheme.bodySmall?.copyWith(
                    color:         AppColors.textSecondary,
                    fontWeight:    FontWeight.w600,
                    letterSpacing: 0.5,
                    fontFeatures:  const [FontFeature.tabularFigures()],
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 12),

          // ── Valid banner ─────────────────────────────
          Container(
            width:   double.infinity,
            padding: const EdgeInsets.symmetric(vertical: 10),
            decoration: BoxDecoration(
              color:        AppColors.successLight,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(
                color: AppColors.secondaryDark.withAlpha(50),
                width: 1,
              ),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(
                  Icons.verified_rounded,
                  color: AppColors.secondaryDark,
                  size:  16,
                ),
                const SizedBox(width: 7),
                Text(
                  'Valid for $_bookingDate · $_startTime – $_endTime',
                  style: textTheme.bodySmall?.copyWith(
                    color:      AppColors.secondaryDark,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  List<Widget> _qrCorners() {
    const size   = 20.0;
    const thick  = 3.0;
    const color  = AppColors.primary;
    const radius = 4.0;
    const pad    = 12.0;

    Widget corner({
      required AlignmentGeometry alignment,
      required BorderRadius borderRadius,
    }) =>
        Positioned.fill(
          child: Align(
            alignment: alignment,
            child: Padding(
              padding: const EdgeInsets.all(pad),
              child: SizedBox(
                width:  size,
                height: size,
                child: CustomPaint(
                  painter: _CornerPainter(
                    borderRadius: borderRadius,
                    color:        color,
                    strokeWidth:  thick,
                  ),
                ),
              ),
            ),
          ),
        );

    return [
      corner(
        alignment:    Alignment.topLeft,
        borderRadius: const BorderRadius.only(
          topLeft: Radius.circular(radius),
        ),
      ),
      corner(
        alignment:    Alignment.topRight,
        borderRadius: const BorderRadius.only(
          topRight: Radius.circular(radius),
        ),
      ),
      corner(
        alignment:    Alignment.bottomLeft,
        borderRadius: const BorderRadius.only(
          bottomLeft: Radius.circular(radius),
        ),
      ),
      corner(
        alignment:    Alignment.bottomRight,
        borderRadius: const BorderRadius.only(
          bottomRight: Radius.circular(radius),
        ),
      ),
    ];
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

    final path = Path()
      ..addRRect(RRect.fromRectAndCorners(
        Rect.fromLTWH(0, 0, size.width, size.height),
        topLeft:     borderRadius.topLeft,
        topRight:    borderRadius.topRight,
        bottomLeft:  borderRadius.bottomLeft,
        bottomRight: borderRadius.bottomRight,
      ));

    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

// ── 6. Instructions Card ──────────────────────────────────────

class _InstructionsCard extends StatelessWidget {
  const _InstructionsCard();

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return _ShadowCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [

          _CardHeader(
            icon:       Icons.info_outline_rounded,
            iconColor:  AppColors.accent,
            iconBg:     AppColors.warningLight,
            label:      'Important Instructions',
          ),

          const SizedBox(height: 14),

          ..._instructions.asMap().entries.map((entry) {
            final index       = entry.key;
            final instruction = entry.value;

            return Padding(
              padding: EdgeInsets.only(
                bottom: index < _instructions.length - 1 ? 12 : 0,
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    width:  22,
                    height: 22,
                    decoration: BoxDecoration(
                      color:        AppColors.accent.withAlpha(20),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Center(
                      child: Text(
                        '${index + 1}',
                        style: textTheme.labelSmall?.copyWith(
                          color:      AppColors.accent,
                          fontWeight: FontWeight.w800,
                          fontSize:   11,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      instruction,
                      style: textTheme.bodySmall?.copyWith(
                        color:  AppColors.textSecondary,
                        height: 1.5,
                      ),
                    ),
                  ),
                ],
              ),
            );
          }),
        ],
      ),
    );
  }
}

// ── 7. Bottom Actions ─────────────────────────────────────────

class _BottomActions extends StatelessWidget {
  const _BottomActions();

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
          padding: const EdgeInsets.fromLTRB(20, 12, 20, 12),
          child: Row(
            children: [

              // ── Download Receipt (outlined) ──────────
              Expanded(
                child: SizedBox(
                  height: 52,
                  child: OutlinedButton(
                    onPressed: () {},
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.primary,
                      side: const BorderSide(
                        color: AppColors.primary,
                        width: 1.5,
                      ),
                      backgroundColor: AppColors.surface,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                      textStyle: textTheme.labelLarge?.copyWith(
                        fontSize:      14,
                        fontWeight:    FontWeight.w700,
                        letterSpacing: 0.2,
                      ),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(
                          Icons.download_rounded,
                          size: 18,
                        ),
                        const SizedBox(width: 6),
                        const Text('Receipt'),
                      ],
                    ),
                  ),
                ),
              ),

              const SizedBox(width: 12),

              // ── View My Bookings (filled) ─────────────
              Expanded(
                flex: 2,
                child: SizedBox(
                  height: 52,
                  child: FilledButton(
                    onPressed: () {},
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
                        const Icon(
                          Icons.bookmark_rounded,
                          size: 18,
                        ),
                        const SizedBox(width: 8),
                        const Text('My Bookings'),
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

// ── Shared Private Widgets ────────────────────────────────────

class _ShadowCard extends StatelessWidget {
  final Widget  child;
  final EdgeInsetsGeometry padding;

  const _ShadowCard({
    required this.child,
    this.padding = const EdgeInsets.all(18),
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width:        double.infinity,
      padding:      padding,
      decoration: BoxDecoration(
        color:        AppColors.surface,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.divider, width: 1),
        boxShadow: [
          BoxShadow(
            color:        AppColors.shadow,
            blurRadius:   12,
            spreadRadius: 0,
            offset:       const Offset(0, 4),
          ),
        ],
      ),
      child: child,
    );
  }
}

class _CardHeader extends StatelessWidget {
  final IconData icon;
  final String   label;
  final Color    iconColor;
  final Color    iconBg;

  const _CardHeader({
    required this.icon,
    required this.label,
    this.iconColor = AppColors.primary,
    this.iconBg    = const Color(0x1A0F3D56), // primary @ 10%
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Row(
      children: [
        Container(
          width:  36,
          height: 36,
          decoration: BoxDecoration(
            color:        iconBg,
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(icon, color: iconColor, size: 18),
        ),
        const SizedBox(width: 10),
        Text(
          label,
          style: textTheme.titleSmall?.copyWith(
            color:      AppColors.textPrimary,
            fontWeight: FontWeight.w700,
            fontSize:   15,
          ),
        ),
      ],
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
      crossAxisAlignment: CrossAxisAlignment.center,
      children: [
        Container(
          width:  32,
          height: 32,
          decoration: BoxDecoration(
            color:        AppColors.surfaceVariant,
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, color: AppColors.textSecondary, size: 16),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Text(
            label,
            style: textTheme.bodyMedium?.copyWith(
              color: AppColors.textSecondary,
            ),
          ),
        ),
        Text(
          value,
          style: textTheme.bodyMedium?.copyWith(
            color:      valueColor ?? AppColors.textPrimary,
            fontWeight: FontWeight.w700,
          ),
          textAlign: TextAlign.end,
        ),
      ],
    );
  }
}