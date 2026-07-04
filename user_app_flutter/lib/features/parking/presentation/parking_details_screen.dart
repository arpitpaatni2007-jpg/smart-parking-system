import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_colors.dart';

// ============================================================
// ParkingDetailsScreen
// ============================================================
//
// Full-detail view for a selected parking lot.
//
// SECTIONS:
//   1. Collapsible hero banner   — gradient image placeholder
//   2. Core info card            — name, address, rating, distance
//   3. Quick stats row           — price / slots / status
//   4. Description               — about the parking lot
//   5. Amenities                 — icon grid (CCTV, EV, etc.)
//   6. Working Hours card        — day-by-day schedule
//   7. Map Preview               — placeholder with "Get Directions"
//   8. Reviews Summary           — star breakdown + top review
//   9. Sticky bottom bar         — "Select Slot" FilledButton
//
// DATA: all dummy / static — no API, no state management.
//
// ARCHITECTURE:
//   StatefulWidget — SliverAppBar scroll state only.
//   No Riverpod, Bloc, Provider.
// ============================================================

// ── Dummy Data ────────────────────────────────────────────────

class _Amenity {
  final IconData icon;
  final String   label;
  final bool     available;
  const _Amenity({
    required this.icon,
    required this.label,
    required this.available,
  });
}

class _WorkingHours {
  final String day;
  final String hours;
  final bool   isToday;
  const _WorkingHours({
    required this.day,
    required this.hours,
    this.isToday = false,
  });
}

class _Review {
  final String name;
  final String initials;
  final double rating;
  final String comment;
  final String timeAgo;
  final Color  avatarColor;
  const _Review({
    required this.name,
    required this.initials,
    required this.rating,
    required this.comment,
    required this.timeAgo,
    required this.avatarColor,
  });
}

// ── Static dummy content ──────────────────────────────────────

const _parkingName    = 'Cyber Hub Parking Complex';
const _parkingAddress = 'DLF Cyber Hub, Sector 24, Gurugram, Haryana 122002';
const _parkingRating  = 4.8;
const _reviewCount    = 1240;
const _distance       = '0.4 km away';
const _pricePerHour   = 40.0;
const _totalSlots     = 120;
const _availableSlots = 34;
const _isOpen         = true;

const _description =
    'Cyber Hub Parking Complex is a premium multi-level covered parking '
    'facility located at the heart of DLF Cyber Hub. With 120 spacious '
    'bays across 4 floors, 24/7 CCTV surveillance, dedicated EV charging '
    'stations, and round-the-clock security personnel, it is the most '
    'convenient parking option for visitors, office-goers, and shoppers '
    'in Gurugram\'s busiest commercial district.';

final List<_Amenity> _amenities = [
  const _Amenity(icon: Icons.videocam_outlined,           label: 'CCTV',           available: true),
  const _Amenity(icon: Icons.security_outlined,           label: 'Security',        available: true),
  const _Amenity(icon: Icons.ev_station_outlined,         label: 'EV Charging',     available: true),
  const _Amenity(icon: Icons.roofing_outlined,            label: 'Covered',         available: true),
  const _Amenity(icon: Icons.wc_outlined,                 label: 'Washroom',        available: true),
  const _Amenity(icon: Icons.local_car_wash_outlined,     label: 'Car Wash',        available: false),
  const _Amenity(icon: Icons.wheelchair_pickup_outlined,  label: 'Accessible',      available: true),
  const _Amenity(icon: Icons.receipt_long_outlined,       label: 'E-Receipt',       available: true),
];

final List<_WorkingHours> _workingHours = [
  const _WorkingHours(day: 'Monday',    hours: '06:00 AM – 11:00 PM'),
  const _WorkingHours(day: 'Tuesday',   hours: '06:00 AM – 11:00 PM'),
  const _WorkingHours(day: 'Wednesday', hours: '06:00 AM – 11:00 PM'),
  const _WorkingHours(day: 'Thursday',  hours: '06:00 AM – 11:00 PM'),
  const _WorkingHours(day: 'Friday',    hours: '06:00 AM – 12:00 AM'),
  const _WorkingHours(day: 'Saturday',  hours: '07:00 AM – 12:00 AM', isToday: true),
  const _WorkingHours(day: 'Sunday',    hours: '08:00 AM – 10:00 PM'),
];

final List<_Review> _reviews = [
  _Review(
    name:        'Ravi Sharma',
    initials:    'RS',
    rating:      5.0,
    comment:     'Extremely well-managed facility. Found a spot within minutes '
                 'and the EV charging worked perfectly. Will book again!',
    timeAgo:     '2 days ago',
    avatarColor: AppColors.primary,
  ),
  _Review(
    name:        'Priya Mehta',
    initials:    'PM',
    rating:      4.5,
    comment:     'Clean, safe, and affordable. The real-time slot display at '
                 'entry was very helpful. Security staff are courteous.',
    timeAgo:     '1 week ago',
    avatarColor: AppColors.secondary,
  ),
];

// ── Star Rating Breakdown ─────────────────────────────────────

const Map<int, double> _starBreakdown = {
  5: 0.72,
  4: 0.18,
  3: 0.06,
  2: 0.02,
  1: 0.02,
};

// ── Screen ────────────────────────────────────────────────────

class ParkingDetailsScreen extends StatefulWidget {
  const ParkingDetailsScreen({super.key});

  @override
  State<ParkingDetailsScreen> createState() => _ParkingDetailsScreenState();
}

class _ParkingDetailsScreenState extends State<ParkingDetailsScreen> {
  final _scrollController = ScrollController();
  bool  _appBarCollapsed  = false;

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
  }

  void _onScroll() {
    // Banner height ~280. Collapse title when banner is scrolled past.
    final collapsed = _scrollController.offset > 200;
    if (collapsed != _appBarCollapsed) {
      setState(() => _appBarCollapsed = collapsed);
    }
  }

  @override
  void dispose() {
    _scrollController
      ..removeListener(_onScroll)
      ..dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.sizeOf(context).width;
    final hPad        = screenWidth > 600 ? screenWidth * 0.08 : 16.0;

    return AnnotatedRegion<SystemUiOverlayStyle>(
      value: SystemUiOverlayStyle.light,
      child: Scaffold(
        backgroundColor: AppColors.background,
        extendBodyBehindAppBar: true,
        body: Stack(
          children: [

            // ── Scrollable content ─────────────────────────
            CustomScrollView(
              controller: _scrollController,
              physics:    const BouncingScrollPhysics(),
              slivers: [

                // ── 1. Hero Banner SliverAppBar ────────────
                _HeroBannerAppBar(collapsed: _appBarCollapsed),

                // ── Body content ───────────────────────────
                SliverToBoxAdapter(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [

                      // ── 2. Core Info Card ────────────────
                      _CoreInfoCard(hPad: hPad),

                      const SizedBox(height: 14),

                      // ── 3. Quick Stats ───────────────────
                      _QuickStatsRow(hPad: hPad),

                      const SizedBox(height: 22),

                      // ── 4. Description ───────────────────
                      _SectionBlock(
                        hPad:  hPad,
                        title: 'About This Parking',
                        child: _Description(),
                      ),

                      const SizedBox(height: 22),

                      // ── 5. Amenities ─────────────────────
                      _SectionBlock(
                        hPad:  hPad,
                        title: 'Amenities',
                        child: _AmenitiesGrid(),
                      ),

                      const SizedBox(height: 22),

                      // ── 6. Working Hours ─────────────────
                      _SectionBlock(
                        hPad:  hPad,
                        title: 'Working Hours',
                        child: _WorkingHoursCard(),
                      ),

                      const SizedBox(height: 22),

                      // ── 7. Map Preview ───────────────────
                      _SectionBlock(
                        hPad:  hPad,
                        title: 'Location',
                        child: _MapPreview(),
                      ),

                      const SizedBox(height: 22),

                      // ── 8. Reviews ───────────────────────
                      _SectionBlock(
                        hPad:  hPad,
                        title: 'Reviews & Ratings',
                        child: _ReviewsSection(),
                      ),

                      // Sticky bar clearance
                      const SizedBox(height: 110),
                    ],
                  ),
                ),
              ],
            ),

            // ── 9. Sticky Bottom Bar ───────────────────────
            const Positioned(
              bottom: 0,
              left:   0,
              right:  0,
              child:  _StickyBottomBar(),
            ),
          ],
        ),
      ),
    );
  }
}

// ── 1. Hero Banner SliverAppBar ───────────────────────────────

class _HeroBannerAppBar extends StatelessWidget {
  final bool collapsed;
  const _HeroBannerAppBar({required this.collapsed});

  @override
  Widget build(BuildContext context) {
    return SliverAppBar(
      expandedHeight:      280,
      pinned:              true,
      stretch:             true,
      backgroundColor:     AppColors.primary,
      surfaceTintColor:    Colors.transparent,
      systemOverlayStyle:  SystemUiOverlayStyle.light,
      leading: Padding(
        padding: const EdgeInsets.all(8),
        child: _CircleNavButton(
          icon:    Icons.arrow_back_ios_rounded,
          onTap:   () => Navigator.of(context).maybePop(),
        ),
      ),
      actions: [
        Padding(
          padding: const EdgeInsets.all(8),
          child: _CircleNavButton(
            icon:  Icons.favorite_outline_rounded,
            onTap: () {},
          ),
        ),
        Padding(
          padding: const EdgeInsets.only(right: 8, top: 8, bottom: 8),
          child: _CircleNavButton(
            icon:  Icons.share_outlined,
            onTap: () {},
          ),
        ),
      ],
      title: AnimatedOpacity(
        opacity:  collapsed ? 1.0 : 0.0,
        duration: const Duration(milliseconds: 200),
        child: Text(
          _parkingName,
          style: const TextStyle(
            color:      AppColors.onPrimary,
            fontSize:   16,
            fontWeight: FontWeight.w700,
          ),
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
        ),
      ),
      flexibleSpace: FlexibleSpaceBar(
        stretchModes: const [
          StretchMode.zoomBackground,
          StretchMode.blurBackground,
        ],
        background: _BannerPlaceholder(),
      ),
    );
  }
}

class _CircleNavButton extends StatelessWidget {
  final IconData     icon;
  final VoidCallback onTap;
  const _CircleNavButton({required this.icon, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width:  38,
        height: 38,
        decoration: BoxDecoration(
          color:        AppColors.onPrimary.withAlpha(30),
          shape:        BoxShape.circle,
          border: Border.all(
            color: AppColors.onPrimary.withAlpha(40),
            width: 1,
          ),
        ),
        child: Icon(icon, color: AppColors.onPrimary, size: 18),
      ),
    );
  }
}

class _BannerPlaceholder extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin:  Alignment.topLeft,
          end:    Alignment.bottomRight,
          colors: [
            AppColors.primaryDark,
            AppColors.primary,
            AppColors.primaryLight,
          ],
          stops: [0.0, 0.55, 1.0],
        ),
      ),
      child: Stack(
        children: [

          // ── Background "P" watermark ─────────────────
          Positioned(
            right:  -30,
            bottom: -30,
            child:  Icon(
              Icons.local_parking_rounded,
              size:  200,
              color: AppColors.onPrimary.withAlpha(15),
            ),
          ),

          // ── Floating feature chips ───────────────────
          Positioned(
            top:  72,
            left: 16,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _BannerChip(
                  icon:  Icons.roofing_rounded,
                  label: 'Multi-level Covered',
                ),
                const SizedBox(height: 8),
                _BannerChip(
                  icon:  Icons.ev_station_rounded,
                  label: 'EV Charging Available',
                ),
              ],
            ),
          ),

          // ── Floor count label ────────────────────────
          Positioned(
            right:  16,
            bottom: 56,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                  '4',
                  style: const TextStyle(
                    color:      AppColors.onPrimary,
                    fontSize:   48,
                    fontWeight: FontWeight.w800,
                    height:     1,
                  ),
                ),
                Text(
                  'Floors',
                  style: TextStyle(
                    color:      AppColors.onPrimary.withAlpha(200),
                    fontSize:   13,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),

          // ── Bottom gradient scrim for card overlap ───
          Positioned(
            left:   0,
            right:  0,
            bottom: 0,
            child: Container(
              height: 60,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin:  Alignment.bottomCenter,
                  end:    Alignment.topCenter,
                  colors: [
                    AppColors.background,
                    AppColors.background.withAlpha(0),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _BannerChip extends StatelessWidget {
  final IconData icon;
  final String   label;
  const _BannerChip({required this.icon, required this.label});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color:        AppColors.onPrimary.withAlpha(25),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: AppColors.onPrimary.withAlpha(40),
          width: 1,
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, color: AppColors.onPrimary, size: 13),
          const SizedBox(width: 5),
          Text(
            label,
            style: const TextStyle(
              color:      AppColors.onPrimary,
              fontSize:   11,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}

// ── 2. Core Info Card ─────────────────────────────────────────

class _CoreInfoCard extends StatelessWidget {
  final double hPad;
  const _CoreInfoCard({required this.hPad});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Padding(
      padding: EdgeInsets.fromLTRB(hPad, 4, hPad, 0),
      child: Container(
        padding:      const EdgeInsets.all(18),
        decoration: BoxDecoration(
          color:        AppColors.surface,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: AppColors.divider, width: 1),
          boxShadow: [
            BoxShadow(
              color:        AppColors.shadow,
              blurRadius:   14,
              spreadRadius: 0,
              offset:       const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [

            // ── Name + Open badge ──────────────────────
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Text(
                    _parkingName,
                    style: textTheme.titleLarge?.copyWith(
                      color:      AppColors.textPrimary,
                      fontWeight: FontWeight.w800,
                      height:     1.2,
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                _OpenStatusBadge(isOpen: _isOpen),
              ],
            ),

            const SizedBox(height: 10),

            // ── Address ─────────────────────────────────
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Icon(
                  Icons.location_on_outlined,
                  color: AppColors.textSecondary,
                  size:  16,
                ),
                const SizedBox(width: 4),
                Expanded(
                  child: Text(
                    _parkingAddress,
                    style: textTheme.bodyMedium?.copyWith(
                      color:  AppColors.textSecondary,
                      height: 1.4,
                    ),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 14),
            Divider(color: AppColors.divider, height: 1),
            const SizedBox(height: 14),

            // ── Rating + Distance row ────────────────────
            Row(
              children: [
                // Rating
                _MetaItem(
                  icon:      Icons.star_rounded,
                  iconColor: AppColors.accent,
                  label:     '$_parkingRating',
                  sub:       '($_reviewCount reviews)',
                ),

                _VerticalDivider(),

                // Distance
                _MetaItem(
                  icon:      Icons.near_me_rounded,
                  iconColor: AppColors.info,
                  label:     _distance,
                  sub:       'from you',
                ),

                _VerticalDivider(),

                // Vehicle types
                _MetaItem(
                  icon:      Icons.directions_car_outlined,
                  iconColor: AppColors.secondary,
                  label:     'Car / Bike',
                  sub:       'accepted',
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _MetaItem extends StatelessWidget {
  final IconData icon;
  final Color    iconColor;
  final String   label;
  final String   sub;

  const _MetaItem({
    required this.icon,
    required this.iconColor,
    required this.label,
    required this.sub,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Expanded(
      child: Row(
        children: [
          Icon(icon, color: iconColor, size: 16),
          const SizedBox(width: 5),
          Flexible(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: textTheme.bodySmall?.copyWith(
                    color:      AppColors.textPrimary,
                    fontWeight: FontWeight.w700,
                    fontSize:   12,
                  ),
                ),
                Text(
                  sub,
                  style: textTheme.bodySmall?.copyWith(
                    color:    AppColors.textTertiary,
                    fontSize: 10,
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

class _VerticalDivider extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      width:  1,
      height: 32,
      color:  AppColors.divider,
      margin: const EdgeInsets.symmetric(horizontal: 10),
    );
  }
}

// ── 3. Quick Stats Row ────────────────────────────────────────

class _QuickStatsRow extends StatelessWidget {
  final double hPad;
  const _QuickStatsRow({required this.hPad});

  @override
  Widget build(BuildContext context) {
    final availabilityRatio = _availableSlots / _totalSlots;
    final slotColor = _availableSlots <= 5
        ? AppColors.error
        : _availableSlots <= 20
            ? AppColors.accent
            : AppColors.secondary;

    return Padding(
      padding: EdgeInsets.symmetric(horizontal: hPad),
      child: Row(
        children: [
          Expanded(
            child: _StatBubble(
              icon:       Icons.currency_rupee_rounded,
              iconBg:     AppColors.primaryLighter.withAlpha(25),
              iconColor:  AppColors.primary,
              value:      '₹${_pricePerHour.toStringAsFixed(0)}',
              label:      'per hour',
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: _StatBubble(
              icon:       Icons.local_parking_rounded,
              iconBg:     slotColor.withAlpha(25),
              iconColor:  slotColor,
              value:      '$_availableSlots/$_totalSlots',
              label:      'slots free',
              extra: ClipRRect(
                borderRadius: BorderRadius.circular(3),
                child: LinearProgressIndicator(
                  value:           availabilityRatio,
                  minHeight:       4,
                  backgroundColor: AppColors.surfaceVariant,
                  valueColor:      AlwaysStoppedAnimation<Color>(slotColor),
                ),
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: _StatBubble(
              icon:       Icons.access_time_rounded,
              iconBg:     AppColors.successLight,
              iconColor:  AppColors.secondaryDark,
              value:      '24/7',
              label:      'accessible',
            ),
          ),
        ],
      ),
    );
  }
}

class _StatBubble extends StatelessWidget {
  final IconData  icon;
  final Color     iconBg;
  final Color     iconColor;
  final String    value;
  final String    label;
  final Widget?   extra;

  const _StatBubble({
    required this.icon,
    required this.iconBg,
    required this.iconColor,
    required this.value,
    required this.label,
    this.extra,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      padding:      const EdgeInsets.all(14),
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
        crossAxisAlignment: CrossAxisAlignment.start,
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
          const SizedBox(height: 10),
          Text(
            value,
            style: textTheme.titleMedium?.copyWith(
              color:      AppColors.textPrimary,
              fontWeight: FontWeight.w800,
              fontSize:   16,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: textTheme.bodySmall?.copyWith(
              color:    AppColors.textTertiary,
              fontSize: 11,
            ),
          ),
          if (extra != null) ...[
            const SizedBox(height: 8),
            extra!,
          ],
        ],
      ),
    );
  }
}

// ── Section Block Wrapper ─────────────────────────────────────

class _SectionBlock extends StatelessWidget {
  final double  hPad;
  final String  title;
  final Widget  child;

  const _SectionBlock({
    required this.hPad,
    required this.title,
    required this.child,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Padding(
      padding: EdgeInsets.symmetric(horizontal: hPad),
      child: Column(
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
                title,
                style: textTheme.titleSmall?.copyWith(
                  color:      AppColors.textPrimary,
                  fontWeight: FontWeight.w700,
                  fontSize:   15,
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          child,
        ],
      ),
    );
  }
}

// ── 4. Description ────────────────────────────────────────────

class _Description extends StatefulWidget {
  @override
  State<_Description> createState() => _DescriptionState();
}

class _DescriptionState extends State<_Description> {
  bool _expanded = false;

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      padding:      const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color:        AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.divider, width: 1),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            _description,
            style: textTheme.bodyMedium?.copyWith(
              color:  AppColors.textSecondary,
              height: 1.6,
            ),
            maxLines:  _expanded ? null : 3,
            overflow:  _expanded ? null : TextOverflow.ellipsis,
          ),
          const SizedBox(height: 8),
          GestureDetector(
            onTap: () => setState(() => _expanded = !_expanded),
            child: Text(
              _expanded ? 'Show less' : 'Read more',
              style: textTheme.bodySmall?.copyWith(
                color:      AppColors.primary,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ── 5. Amenities Grid ─────────────────────────────────────────

class _AmenitiesGrid extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return GridView.builder(
      shrinkWrap:  true,
      physics:     const NeverScrollableScrollPhysics(),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount:   4,
        mainAxisSpacing:  10,
        crossAxisSpacing: 10,
        childAspectRatio: 0.85,
      ),
      itemCount: _amenities.length,
      itemBuilder: (context, index) =>
          _AmenityTile(amenity: _amenities[index]),
    );
  }
}

class _AmenityTile extends StatelessWidget {
  final _Amenity amenity;
  const _AmenityTile({required this.amenity});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;
    final available = amenity.available;

    return Container(
      padding:      const EdgeInsets.all(8),
      decoration: BoxDecoration(
        color: available
            ? AppColors.primary.withAlpha(10)
            : AppColors.surfaceVariant,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: available
              ? AppColors.primary.withAlpha(35)
              : AppColors.divider,
          width: 1,
        ),
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            amenity.icon,
            size:  24,
            color: available
                ? AppColors.primary
                : AppColors.textDisabled,
          ),
          const SizedBox(height: 6),
          Text(
            amenity.label,
            textAlign: TextAlign.center,
            style: textTheme.labelSmall?.copyWith(
              color: available
                  ? AppColors.textPrimary
                  : AppColors.textDisabled,
              fontWeight: FontWeight.w600,
              fontSize:   10,
              height:     1.2,
            ),
          ),
          if (!available)
            const SizedBox(height: 2),
          if (!available)
            Text(
              'N/A',
              style: textTheme.labelSmall?.copyWith(
                color:    AppColors.textDisabled,
                fontSize: 9,
              ),
            ),
        ],
      ),
    );
  }
}

// ── 6. Working Hours Card ─────────────────────────────────────

class _WorkingHoursCard extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
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
        children: List.generate(_workingHours.length, (index) {
          final entry   = _workingHours[index];
          final isLast  = index == _workingHours.length - 1;

          return Column(
            children: [
              _WorkingHoursRow(entry: entry),
              if (!isLast)
                Divider(
                  height:  1,
                  color:   AppColors.divider,
                  indent:  16,
                  endIndent: 16,
                ),
            ],
          );
        }),
      ),
    );
  }
}

class _WorkingHoursRow extends StatelessWidget {
  final _WorkingHours entry;
  const _WorkingHoursRow({required this.entry});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      color:   entry.isToday
          ? AppColors.primary.withAlpha(8)
          : Colors.transparent,
      child: Row(
        children: [
          if (entry.isToday)
            Container(
              width:  6,
              height: 6,
              margin: const EdgeInsets.only(right: 8),
              decoration: const BoxDecoration(
                color: AppColors.secondary,
                shape: BoxShape.circle,
              ),
            )
          else
            const SizedBox(width: 14),
          Expanded(
            child: Text(
              entry.day,
              style: textTheme.bodyMedium?.copyWith(
                color: entry.isToday
                    ? AppColors.primary
                    : AppColors.textSecondary,
                fontWeight: entry.isToday
                    ? FontWeight.w700
                    : FontWeight.w500,
              ),
            ),
          ),
          Text(
            entry.hours,
            style: textTheme.bodyMedium?.copyWith(
              color: entry.isToday
                  ? AppColors.primary
                  : AppColors.textPrimary,
              fontWeight: entry.isToday
                  ? FontWeight.w700
                  : FontWeight.w500,
            ),
          ),
          if (entry.isToday) ...[
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
              decoration: BoxDecoration(
                color:        AppColors.successLight,
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(
                'Today',
                style: textTheme.labelSmall?.copyWith(
                  color:      AppColors.secondaryDark,
                  fontWeight: FontWeight.w700,
                  fontSize:   10,
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

// ── 7. Map Preview ────────────────────────────────────────────

class _MapPreview extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return ClipRRect(
      borderRadius: BorderRadius.circular(16),
      child: Stack(
        children: [

          // ── Map placeholder ──────────────────────────
          Container(
            height: 180,
            width:  double.infinity,
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                begin:  Alignment.topLeft,
                end:    Alignment.bottomRight,
                colors: [
                  Color(0xFFE8F4F8),
                  Color(0xFFD0E8F0),
                ],
              ),
            ),
            child: Stack(
              children: [
                // Grid lines for map feel
                CustomPaint(
                  painter: _MapGridPainter(),
                  size: const Size(double.infinity, 180),
                ),

                // Center pin
                Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        width:  48,
                        height: 48,
                        decoration: BoxDecoration(
                          color:        AppColors.primary,
                          shape:        BoxShape.circle,
                          boxShadow: [
                            BoxShadow(
                              color:       AppColors.primary.withAlpha(80),
                              blurRadius:  16,
                              offset:      const Offset(0, 6),
                            ),
                          ],
                        ),
                        child: const Icon(
                          Icons.local_parking_rounded,
                          color: AppColors.onPrimary,
                          size:  26,
                        ),
                      ),
                      const SizedBox(height: 4),
                      // Pin tail
                      Container(
                        width:  2,
                        height: 12,
                        color:  AppColors.primary,
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // ── Get Directions overlay ───────────────────
          Positioned(
            bottom: 12,
            right:  12,
            child: GestureDetector(
              onTap: () {},
              child: Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical:   9,
                ),
                decoration: BoxDecoration(
                  color:        AppColors.primary,
                  borderRadius: BorderRadius.circular(10),
                  boxShadow: [
                    BoxShadow(
                      color:       AppColors.primary.withAlpha(70),
                      blurRadius:  8,
                      offset:      const Offset(0, 3),
                    ),
                  ],
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(
                      Icons.directions_rounded,
                      color: AppColors.onPrimary,
                      size:  16,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      'Get Directions',
                      style: textTheme.labelMedium?.copyWith(
                        color:      AppColors.onPrimary,
                        fontWeight: FontWeight.w700,
                        fontSize:   12,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),

          // ── Address overlay ──────────────────────────
          Positioned(
            top:  12,
            left: 12,
            child: Container(
              padding: const EdgeInsets.symmetric(
                horizontal: 10,
                vertical:    6,
              ),
              decoration: BoxDecoration(
                color:        AppColors.surface.withAlpha(230),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: AppColors.divider, width: 1),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(
                    Icons.location_on_rounded,
                    color: AppColors.primary,
                    size:  13,
                  ),
                  const SizedBox(width: 4),
                  Text(
                    'Cyber Hub, Gurugram',
                    style: textTheme.bodySmall?.copyWith(
                      color:      AppColors.textPrimary,
                      fontWeight: FontWeight.w600,
                      fontSize:   11,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _MapGridPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color       = AppColors.primary.withAlpha(12)
      ..strokeWidth = 1;

    const step = 30.0;
    for (double x = 0; x < size.width; x += step) {
      canvas.drawLine(Offset(x, 0), Offset(x, size.height), paint);
    }
    for (double y = 0; y < size.height; y += step) {
      canvas.drawLine(Offset(0, y), Offset(size.width, y), paint);
    }

    // Draw a couple of "road" lines
    final roadPaint = Paint()
      ..color       = AppColors.primary.withAlpha(22)
      ..strokeWidth = 6;
    canvas.drawLine(
      Offset(0, size.height * 0.55),
      Offset(size.width, size.height * 0.55),
      roadPaint,
    );
    canvas.drawLine(
      Offset(size.width * 0.45, 0),
      Offset(size.width * 0.45, size.height),
      roadPaint,
    );
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

// ── 8. Reviews Section ────────────────────────────────────────

class _ReviewsSection extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        _RatingSummaryCard(),
        const SizedBox(height: 12),
        ..._reviews.map((r) => Padding(
          padding: const EdgeInsets.only(bottom: 12),
          child:   _ReviewCard(review: r),
        )),
      ],
    );
  }
}

class _RatingSummaryCard extends StatelessWidget {
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
      child: Row(
        children: [

          // ── Big number ─────────────────────────────
          Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                '$_parkingRating',
                style: textTheme.displaySmall?.copyWith(
                  color:      AppColors.textPrimary,
                  fontWeight: FontWeight.w800,
                ),
              ),
              _StarRow(rating: _parkingRating, size: 14),
              const SizedBox(height: 4),
              Text(
                '$_reviewCount reviews',
                style: textTheme.bodySmall?.copyWith(
                  color: AppColors.textTertiary,
                ),
              ),
            ],
          ),

          Container(
            width:  1,
            height: 80,
            color:  AppColors.divider,
            margin: const EdgeInsets.symmetric(horizontal: 20),
          ),

          // ── Bar breakdown ──────────────────────────
          Expanded(
            child: Column(
              children: _starBreakdown.entries.map((e) {
                return Padding(
                  padding: const EdgeInsets.only(bottom: 5),
                  child: Row(
                    children: [
                      Text(
                        '${e.key}',
                        style: textTheme.bodySmall?.copyWith(
                          color:      AppColors.textSecondary,
                          fontWeight: FontWeight.w600,
                          fontSize:   11,
                        ),
                      ),
                      const SizedBox(width: 4),
                      const Icon(
                        Icons.star_rounded,
                        color: AppColors.accent,
                        size:  11,
                      ),
                      const SizedBox(width: 6),
                      Expanded(
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(3),
                          child: LinearProgressIndicator(
                            value:           e.value,
                            minHeight:       6,
                            backgroundColor: AppColors.surfaceVariant,
                            valueColor: const AlwaysStoppedAnimation<Color>(
                              AppColors.accent,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 6),
                      Text(
                        '${(e.value * 100).toInt()}%',
                        style: textTheme.bodySmall?.copyWith(
                          color:    AppColors.textTertiary,
                          fontSize: 10,
                        ),
                      ),
                    ],
                  ),
                );
              }).toList(),
            ),
          ),
        ],
      ),
    );
  }
}

class _ReviewCard extends StatelessWidget {
  final _Review review;
  const _ReviewCard({required this.review});

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
            blurRadius:  6,
            offset:      const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              // Avatar
              Container(
                width:  40,
                height: 40,
                decoration: BoxDecoration(
                  color:  review.avatarColor,
                  shape:  BoxShape.circle,
                ),
                child: Center(
                  child: Text(
                    review.initials,
                    style: const TextStyle(
                      color:      AppColors.onPrimary,
                      fontWeight: FontWeight.w700,
                      fontSize:   14,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      review.name,
                      style: textTheme.bodyMedium?.copyWith(
                        color:      AppColors.textPrimary,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    _StarRow(rating: review.rating, size: 12),
                  ],
                ),
              ),
              Text(
                review.timeAgo,
                style: textTheme.bodySmall?.copyWith(
                  color: AppColors.textTertiary,
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            review.comment,
            style: textTheme.bodyMedium?.copyWith(
              color:  AppColors.textSecondary,
              height: 1.5,
            ),
          ),
        ],
      ),
    );
  }
}

class _StarRow extends StatelessWidget {
  final double rating;
  final double size;
  const _StarRow({required this.rating, required this.size});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: List.generate(5, (index) {
        final filled = index < rating.floor();
        final half   = !filled && index < rating;
        return Icon(
          filled
              ? Icons.star_rounded
              : half
                  ? Icons.star_half_rounded
                  : Icons.star_outline_rounded,
          color: AppColors.accent,
          size:  size,
        );
      }),
    );
  }
}

// ── Open Status Badge ─────────────────────────────────────────

class _OpenStatusBadge extends StatelessWidget {
  final bool isOpen;
  const _OpenStatusBadge({required this.isOpen});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color:        isOpen ? AppColors.successLight : AppColors.surfaceVariant,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: isOpen
              ? AppColors.secondaryDark.withAlpha(60)
              : AppColors.divider,
          width: 1,
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width:  7,
            height: 7,
            decoration: BoxDecoration(
              color: isOpen ? AppColors.secondary : AppColors.textDisabled,
              shape: BoxShape.circle,
            ),
          ),
          const SizedBox(width: 5),
          Text(
            isOpen ? 'Open Now' : 'Closed',
            style: TextStyle(
              color:      isOpen ? AppColors.secondaryDark : AppColors.textSecondary,
              fontSize:   11,
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }
}

// ── 9. Sticky Bottom Bar ──────────────────────────────────────

class _StickyBottomBar extends StatelessWidget {
  const _StickyBottomBar();

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

              // ── Price preview ──────────────────────
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize:       MainAxisSize.min,
                children: [
                  Text(
                    '₹${_pricePerHour.toStringAsFixed(0)}',
                    style: textTheme.headlineSmall?.copyWith(
                      color:      AppColors.primary,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  Text(
                    'per hour',
                    style: textTheme.bodySmall?.copyWith(
                      color: AppColors.textTertiary,
                    ),
                  ),
                ],
              ),

              const SizedBox(width: 20),

              // ── Select Slot button ─────────────────
              Expanded(
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
                        const Icon(
                          Icons.grid_view_rounded,
                          size: 18,
                        ),
                        const SizedBox(width: 8),
                        const Text('Select Slot'),
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