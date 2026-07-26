import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../config/routes/app_routes.dart';
import '../../../core/theme/app_colors.dart';

// ============================================================
// HomeScreen
// ============================================================
//
// Smart Parking dashboard — the main screen users land on
// after a successful login.
//
// SECTIONS:
//   1. Custom AppBar   — greeting + avatar + notification bell
//   2. Search Bar      — find parking by name or location
//   3. Quick Stats     — Available Slots / My Bookings / Nearby
//   4. Featured Card   — hero parking card with Book Now CTA
//   5. Nearby List     — 3 compact parking cards
//   6. Bottom Nav Bar  — Home / Bookings / Notifications / Profile
//
// DESIGN:
//   Matches all auth screens — same AppColors palette, 12 px
//   border-radius on inputs, shadows, and typography scale.
//   Warm off-white scaffold, white cards with subtle shadows,
//   primary teal-navy brand color throughout.
//
// ARCHITECTURE:
//   StatelessWidget with a single _selectedNavIndex int held
//   in a StatefulWidget shell so the bottom nav indicator
//   updates visually. All data is local dummy data.
//   No Riverpod, Bloc, Provider, or API calls.
// ============================================================

// ── Dummy Data ────────────────────────────────────────────────

class _ParkingSpot {
  final String name;
  final String address;
  final String distance;
  final double rating;
  final int    totalSlots;
  final int    availableSlots;
  final double pricePerHour;
  final bool   isFeatured;

  const _ParkingSpot({
    required this.name,
    required this.address,
    required this.distance,
    required this.rating,
    required this.totalSlots,
    required this.availableSlots,
    required this.pricePerHour,
    this.isFeatured = false,
  });
}

const _featuredParking = _ParkingSpot(
  name:            'Cyber Hub Parking Complex',
  address:         'DLF Cyber Hub, Gurugram, Haryana',
  distance:        '0.4 km away',
  rating:          4.8,
  totalSlots:      120,
  availableSlots:  34,
  pricePerHour:    40.0,
  isFeatured:      true,
);

const List<_ParkingSpot> _nearbyParkings = [
  _ParkingSpot(
    name:           'Ambience Mall Parking',
    address:        'NH-48, Gurugram',
    distance:       '1.2 km',
    rating:         4.5,
    totalSlots:     200,
    availableSlots: 52,
    pricePerHour:   30.0,
  ),
  _ParkingSpot(
    name:           'Sector 29 Public Parking',
    address:        'Sector 29, Gurugram',
    distance:       '2.0 km',
    rating:         4.1,
    totalSlots:     80,
    availableSlots: 8,
    pricePerHour:   20.0,
  ),
  _ParkingSpot(
    name:           'MGF Metropolitan Mall',
    address:        'MG Road, Gurugram',
    distance:       '3.5 km',
    rating:         4.6,
    totalSlots:     150,
    availableSlots: 67,
    pricePerHour:   35.0,
  ),
];

// ── Entry Point ───────────────────────────────────────────────

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _selectedNavIndex = 0;

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
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor:          AppColors.background,
      extendBodyBehindAppBar:   false,
      resizeToAvoidBottomInset: false,
      body: SafeArea(
        child: _HomeBody(),
      ),
      bottomNavigationBar: _BottomNavBar(
        selectedIndex: _selectedNavIndex,
        onTap: (i) {
          switch (i) {
            case 0:
              setState(() => _selectedNavIndex = 0);
            case 1:
              setState(() => _selectedNavIndex = 1);
              Navigator.of(context).pushNamed(AppRoutes.myBookings);
            case 2:
              setState(() => _selectedNavIndex = 2);
              Navigator.of(context).pushNamed(AppRoutes.notifications);
            case 3:
              setState(() => _selectedNavIndex = 3);
              Navigator.of(context).pushNamed(AppRoutes.profile);
          }
        },
      ),
    );
  }
}

// ── Home Body ─────────────────────────────────────────────────

class _HomeBody extends StatelessWidget {
  const _HomeBody();

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.sizeOf(context).width;
    final hPad        = screenWidth > 600 ? screenWidth * 0.08 : 20.0;

    return CustomScrollView(
      physics: const BouncingScrollPhysics(),
      slivers: [

        // ── 1. Top App Bar ─────────────────────────────────
        SliverToBoxAdapter(
          child: _TopBar(hPad: hPad),
        ),

        // ── 2. Search Bar ──────────────────────────────────
        SliverToBoxAdapter(
          child: Padding(
            padding: EdgeInsets.fromLTRB(hPad, 20, hPad, 0),
            child: GestureDetector(
              onTap: () => Navigator.of(context).pushNamed(AppRoutes.parkingList),
              child: const _SearchBar(),
            ),
          ),
        ),

        // ── 3. Quick Stats ─────────────────────────────────
        SliverToBoxAdapter(
          child: Padding(
            padding: EdgeInsets.fromLTRB(hPad, 24, hPad, 0),
            child:   const _QuickStatsRow(),
          ),
        ),

        // ── Section Label: Featured ────────────────────────
        SliverToBoxAdapter(
          child: Padding(
            padding: EdgeInsets.fromLTRB(hPad, 28, hPad, 14),
            child:   _SectionHeader(
              title:    'Featured Parking',
              actionLabel: 'See all',
              onAction: () => Navigator.of(context).pushNamed(AppRoutes.parkingList),
            ),
          ),
        ),

        // ── 4. Featured Card ───────────────────────────────
        SliverToBoxAdapter(
          child: Padding(
            padding: EdgeInsets.symmetric(horizontal: hPad),
            child:   _FeaturedParkingCard(spot: _featuredParking),
          ),
        ),

        // ── Section Label: Nearby ──────────────────────────
        SliverToBoxAdapter(
          child: Padding(
            padding: EdgeInsets.fromLTRB(hPad, 28, hPad, 14),
            child:   _SectionHeader(
              title:    'Nearby Parking',
              actionLabel: 'View map',
              onAction: () => Navigator.of(context).pushNamed(AppRoutes.parkingList),
            ),
          ),
        ),

        // ── 5. Nearby List ─────────────────────────────────
        SliverList.separated(
          itemCount:    _nearbyParkings.length,
          separatorBuilder: (_, __) => const SizedBox(height: 12),
          itemBuilder: (context, index) => Padding(
            padding: EdgeInsets.symmetric(horizontal: hPad),
            child:   _NearbyParkingCard(spot: _nearbyParkings[index]),
          ),
        ),

        // ── Bottom padding ─────────────────────────────────
        const SliverToBoxAdapter(child: SizedBox(height: 32)),
      ],
    );
  }
}

// ── 1. Top Bar ────────────────────────────────────────────────

class _TopBar extends StatelessWidget {
  final double hPad;
  const _TopBar({required this.hPad});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Padding(
      padding: EdgeInsets.fromLTRB(hPad, 16, hPad, 0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [

          // ── Greeting ──────────────────────────────────
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Good Morning,',
                  style: textTheme.bodyMedium?.copyWith(
                    color: AppColors.textSecondary,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  'Arpit 👋',
                  style: textTheme.headlineSmall?.copyWith(
                    color:      AppColors.textPrimary,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
          ),

          // ── Notification Bell ─────────────────────────
          _IconBadgeButton(
            icon:       Icons.notifications_outlined,
            badgeCount: 3,
            onTap:      () => Navigator.of(context).pushNamed(AppRoutes.notifications),
          ),

          const SizedBox(width: 10),

          // ── Avatar ─────────────────────────────────────
          GestureDetector(
            onTap: () => Navigator.of(context).pushNamed(AppRoutes.profile),
            child: Container(
              width:  44,
              height: 44,
              decoration: BoxDecoration(
                color:        AppColors.primary,
                borderRadius: BorderRadius.circular(14),
                boxShadow: [
                  BoxShadow(
                    color:       AppColors.shadow,
                    blurRadius:  8,
                    offset:      const Offset(0, 3),
                  ),
                ],
              ),
              child: const Center(
                child: Text(
                  'A',
                  style: TextStyle(
                    color:      AppColors.onPrimary,
                    fontSize:   18,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ── Icon Button with Badge ────────────────────────────────────
class _IconBadgeButton extends StatelessWidget {
  final IconData icon;
  final int      badgeCount;
  final VoidCallback onTap;

  const _IconBadgeButton({
    required this.icon,
    required this.badgeCount,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width:  44,
        height: 44,
        decoration: BoxDecoration(
          color:        AppColors.surface,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppColors.divider, width: 1),
          boxShadow: [
            BoxShadow(
              color:       AppColors.shadow,
              blurRadius:  6,
              offset:      const Offset(0, 2),
            ),
          ],
        ),
        child: Stack(
          children: [
            Center(
              child: Icon(icon, color: AppColors.textPrimary, size: 22),
            ),
            if (badgeCount > 0)
              Positioned(
                top:   8,
                right: 8,
                child: Container(
                  width:  8,
                  height: 8,
                  decoration: const BoxDecoration(
                    color:  AppColors.error,
                    shape:  BoxShape.circle,
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}

// ── 2. Search Bar ─────────────────────────────────────────────

class _SearchBar extends StatelessWidget {
  const _SearchBar();

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 52,
      decoration: BoxDecoration(
        color:        AppColors.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.divider, width: 1.5),
        boxShadow: [
          BoxShadow(
            color:       AppColors.shadow,
            blurRadius:  10,
            spreadRadius: 0,
            offset:      const Offset(0, 3),
          ),
        ],
      ),
      child: Row(
        children: [
          const SizedBox(width: 14),
          const Icon(
            Icons.search_rounded,
            color: AppColors.textSecondary,
            size:  22,
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              'Search parking, location…',
              style: TextStyle(
                color:    AppColors.textTertiary,
                fontSize: 14,
                fontWeight: FontWeight.w400,
              ),
            ),
          ),
          Container(
            margin: const EdgeInsets.all(6),
            width:  38,
            height: 38,
            decoration: BoxDecoration(
              color:        AppColors.primary,
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Icon(
              Icons.tune_rounded,
              color: AppColors.onPrimary,
              size:  18,
            ),
          ),
        ],
      ),
    );
  }
}

// ── 3. Quick Stats Row ────────────────────────────────────────

class _QuickStatsRow extends StatelessWidget {
  const _QuickStatsRow();

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: _StatCard(
            icon:       Icons.local_parking_rounded,
            iconColor:  AppColors.secondary,
            iconBg:     AppColors.successLight,
            label:      'Available',
            value:      '153',
            suffix:     'slots',
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _StatCard(
            icon:       Icons.bookmark_outline_rounded,
            iconColor:  AppColors.primary,
            iconBg:     AppColors.primaryLighter.withAlpha(30),
            label:      'My Bookings',
            value:      '4',
            suffix:     'active',
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _StatCard(
            icon:       Icons.near_me_outlined,
            iconColor:  AppColors.accent,
            iconBg:     AppColors.warningLight,
            label:      'Nearby',
            value:      '12',
            suffix:     'lots',
          ),
        ),
      ],
    );
  }
}

class _StatCard extends StatelessWidget {
  final IconData icon;
  final Color    iconColor;
  final Color    iconBg;
  final String   label;
  final String   value;
  final String   suffix;

  const _StatCard({
    required this.icon,
    required this.iconColor,
    required this.iconBg,
    required this.label,
    required this.value,
    required this.suffix,
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
            spreadRadius: 0,
            offset:      const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ── Icon ─────────────────────────────────────
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

          // ── Value ─────────────────────────────────────
          Text(
            value,
            style: textTheme.titleLarge?.copyWith(
              color:      AppColors.textPrimary,
              fontWeight: FontWeight.w700,
              fontSize:   20,
            ),
          ),

          const SizedBox(height: 2),

          // ── Label ─────────────────────────────────────
          Text(
            label,
            style: textTheme.bodySmall?.copyWith(
              color:    AppColors.textSecondary,
              fontSize: 11,
            ),
            maxLines:  1,
            overflow:  TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }
}

// ── Section Header ────────────────────────────────────────────

class _SectionHeader extends StatelessWidget {
  final String       title;
  final String       actionLabel;
  final VoidCallback onAction;

  const _SectionHeader({
    required this.title,
    required this.actionLabel,
    required this.onAction,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          title,
          style: textTheme.titleMedium?.copyWith(
            color:      AppColors.textPrimary,
            fontWeight: FontWeight.w700,
          ),
        ),
        GestureDetector(
          onTap: onAction,
          child: Text(
            actionLabel,
            style: textTheme.bodySmall?.copyWith(
              color:      AppColors.primary,
              fontWeight: FontWeight.w600,
            ),
          ),
        ),
      ],
    );
  }
}

// ── 4. Featured Parking Card ──────────────────────────────────

class _FeaturedParkingCard extends StatelessWidget {
  final _ParkingSpot spot;
  const _FeaturedParkingCard({required this.spot});

  @override
  Widget build(BuildContext context) {
    final textTheme   = Theme.of(context).textTheme;
    final availability = spot.availableSlots / spot.totalSlots;

    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(20),
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end:   Alignment.bottomRight,
          colors: [
            AppColors.primary,
            AppColors.primaryLight,
          ],
        ),
        boxShadow: [
          BoxShadow(
            color:       AppColors.primary.withAlpha(80),
            blurRadius:  20,
            spreadRadius: 0,
            offset:      const Offset(0, 8),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [

            // ── Top Row: Name + Featured Badge ──────────
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        spot.name,
                        style: textTheme.titleMedium?.copyWith(
                          color:      AppColors.onPrimary,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          const Icon(
                            Icons.location_on_outlined,
                            color: AppColors.onPrimary,
                            size:  13,
                          ),
                          const SizedBox(width: 3),
                          Expanded(
                            child: Text(
                              spot.address,
                              style: textTheme.bodySmall?.copyWith(
                                color:  AppColors.onPrimary.withAlpha(180),
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
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical:    4,
                  ),
                  decoration: BoxDecoration(
                    color:        AppColors.secondary,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    '⭐ ${spot.rating}',
                    style: textTheme.labelSmall?.copyWith(
                      color:      AppColors.onSecondary,
                      fontWeight: FontWeight.w700,
                      fontSize:   11,
                    ),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 20),

            // ── Availability Bar ─────────────────────────
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Availability',
                      style: textTheme.bodySmall?.copyWith(
                        color: AppColors.onPrimary.withAlpha(180),
                      ),
                    ),
                    Text(
                      '${spot.availableSlots}/${spot.totalSlots} slots',
                      style: textTheme.bodySmall?.copyWith(
                        color:      AppColors.onPrimary,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                ClipRRect(
                  borderRadius: BorderRadius.circular(4),
                  child: LinearProgressIndicator(
                    value:            availability,
                    minHeight:        6,
                    backgroundColor:  AppColors.onPrimary.withAlpha(40),
                    valueColor: AlwaysStoppedAnimation<Color>(
                      availability > 0.4
                          ? AppColors.secondary
                          : AppColors.accent,
                    ),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 20),

            // ── Bottom Row: Distance, Price, Book Now ────
            Row(
              children: [
                // Distance
                _FeaturedChip(
                  icon:  Icons.near_me_rounded,
                  label: spot.distance,
                ),
                const SizedBox(width: 10),
                // Price
                _FeaturedChip(
                  icon:  Icons.currency_rupee_rounded,
                  label: '${spot.pricePerHour.toStringAsFixed(0)}/hr',
                ),

                const Spacer(),

                // Book Now CTA
                GestureDetector(
                  onTap: () => Navigator.of(context).pushNamed(AppRoutes.parkingDetails),
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 20,
                      vertical:    11,
                    ),
                    decoration: BoxDecoration(
                      color:        AppColors.onPrimary,
                      borderRadius: BorderRadius.circular(12),
                      boxShadow: [
                        BoxShadow(
                          color:       AppColors.primaryDark.withAlpha(60),
                          blurRadius:  8,
                          offset:      const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Text(
                      'Book Now',
                      style: textTheme.labelMedium?.copyWith(
                        color:      AppColors.primary,
                        fontWeight: FontWeight.w700,
                        fontSize:   13,
                      ),
                    ),
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

class _FeaturedChip extends StatelessWidget {
  final IconData icon;
  final String   label;

  const _FeaturedChip({required this.icon, required this.label});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color:        AppColors.onPrimary.withAlpha(25),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(
          color: AppColors.onPrimary.withAlpha(40),
          width: 1,
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, color: AppColors.onPrimary, size: 13),
          const SizedBox(width: 4),
          Text(
            label,
            style: TextStyle(
              color:      AppColors.onPrimary,
              fontSize:   12,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}

// ── 5. Nearby Parking Card ────────────────────────────────────

class _NearbyParkingCard extends StatelessWidget {
  final _ParkingSpot spot;
  const _NearbyParkingCard({required this.spot});

  @override
  Widget build(BuildContext context) {
    final textTheme    = Theme.of(context).textTheme;
    final isLowSlots   = spot.availableSlots <= 10;
    final slotColor    = isLowSlots ? AppColors.error : AppColors.secondary;
    final slotBg       = isLowSlots
        ? AppColors.errorLight
        : AppColors.successLight;

    return Container(
      padding:      const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color:        AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.divider, width: 1),
        boxShadow: [
          BoxShadow(
            color:       AppColors.shadow,
            blurRadius:  10,
            spreadRadius: 0,
            offset:      const Offset(0, 3),
          ),
        ],
      ),
      child: Row(
        children: [

          // ── Parking Icon ────────────────────────────────
          Container(
            width:  54,
            height: 54,
            decoration: BoxDecoration(
              color:        AppColors.primary.withAlpha(15),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(
                color: AppColors.primary.withAlpha(30),
                width: 1,
              ),
            ),
            child: const Icon(
              Icons.local_parking_rounded,
              color: AppColors.primary,
              size:  26,
            ),
          ),

          const SizedBox(width: 14),

          // ── Name, Address, Distance ─────────────────────
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  spot.name,
                  style: textTheme.titleSmall?.copyWith(
                    color:      AppColors.textPrimary,
                    fontWeight: FontWeight.w700,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    const Icon(
                      Icons.location_on_outlined,
                      color: AppColors.textTertiary,
                      size:  12,
                    ),
                    const SizedBox(width: 2),
                    Text(
                      spot.distance,
                      style: textTheme.bodySmall?.copyWith(
                        color: AppColors.textTertiary,
                      ),
                    ),
                    const SizedBox(width: 8),
                    const Icon(
                      Icons.star_rounded,
                      color: AppColors.accent,
                      size:  12,
                    ),
                    const SizedBox(width: 2),
                    Text(
                      spot.rating.toString(),
                      style: textTheme.bodySmall?.copyWith(
                        color:      AppColors.textSecondary,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    // Slots badge
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical:   3,
                      ),
                      decoration: BoxDecoration(
                        color:        slotBg,
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        '${spot.availableSlots} slots',
                        style: textTheme.labelSmall?.copyWith(
                          color:      slotColor,
                          fontWeight: FontWeight.w600,
                          fontSize:   11,
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    // Price badge
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical:   3,
                      ),
                      decoration: BoxDecoration(
                        color:        AppColors.primaryLighter.withAlpha(20),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        '₹${spot.pricePerHour.toStringAsFixed(0)}/hr',
                        style: textTheme.labelSmall?.copyWith(
                          color:      AppColors.primary,
                          fontWeight: FontWeight.w600,
                          fontSize:   11,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          const SizedBox(width: 10),

          // ── Book Arrow Button ──────────────────────────
          GestureDetector(
            onTap: () => Navigator.of(context).pushNamed(AppRoutes.parkingDetails),
            child: Container(
              width:  38,
              height: 38,
              decoration: BoxDecoration(
                color:        AppColors.primary,
                borderRadius: BorderRadius.circular(12),
                boxShadow: [
                  BoxShadow(
                    color:       AppColors.primary.withAlpha(60),
                    blurRadius:  8,
                    offset:      const Offset(0, 3),
                  ),
                ],
              ),
              child: const Icon(
                Icons.arrow_forward_rounded,
                color: AppColors.onPrimary,
                size:  18,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ── 6. Bottom Navigation Bar ──────────────────────────────────

class _BottomNavBar extends StatelessWidget {
  final int          selectedIndex;
  final ValueChanged<int> onTap;

  const _BottomNavBar({
    required this.selectedIndex,
    required this.onTap,
  });

  static const _items = [
    (icon: Icons.home_outlined,          activeIcon: Icons.home_rounded,          label: 'Home'),
    (icon: Icons.bookmark_outline,       activeIcon: Icons.bookmark_rounded,       label: 'Bookings'),
    (icon: Icons.notifications_outlined, activeIcon: Icons.notifications_rounded,  label: 'Alerts'),
    (icon: Icons.person_outline_rounded, activeIcon: Icons.person_rounded,         label: 'Profile'),
  ];

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        boxShadow: [
          BoxShadow(
            color:       AppColors.shadow,
            blurRadius:  16,
            spreadRadius: 0,
            offset:      const Offset(0, -4),
          ),
        ],
        border: const Border(
          top: BorderSide(color: AppColors.divider, width: 1),
        ),
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: List.generate(_items.length, (index) {
              final item       = _items[index];
              final isSelected = index == selectedIndex;

              return GestureDetector(
                onTap:     () => onTap(index),
                behavior:  HitTestBehavior.opaque,
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  padding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical:    8,
                  ),
                  decoration: BoxDecoration(
                    color: isSelected
                        ? AppColors.primary.withAlpha(12)
                        : Colors.transparent,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        isSelected ? item.activeIcon : item.icon,
                        color: isSelected
                            ? AppColors.primary
                            : AppColors.navInactive,
                        size: 24,
                      ),
                      const SizedBox(height: 3),
                      Text(
                        item.label,
                        style: textTheme.labelSmall?.copyWith(
                          color: isSelected
                              ? AppColors.primary
                              : AppColors.navInactive,
                          fontWeight: isSelected
                              ? FontWeight.w700
                              : FontWeight.w400,
                          fontSize: 10,
                        ),
                      ),
                    ],
                  ),
                ),
              );
            }),
          ),
        ),
      ),
    );
  }
}