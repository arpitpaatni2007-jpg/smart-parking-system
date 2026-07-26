import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_colors.dart';
import '../../../config/routes/app_routes.dart';

// ============================================================
// ParkingListScreen
// ============================================================
//
// Displays a searchable, filterable list of nearby parking lots.
//
// SECTIONS:
//   1. AppBar        — "Find Parking" title + map-view toggle
//   2. Search Field  — live filter by name or address
//   3. Filter Chips  — All / Nearby / EV Charging / Covered / Open
//   4. Results count — "Showing N parking lots"
//   5. Parking Cards — scrollable list with full detail + Book Now
//
// DATA:
//   All data is local dummy data — no API, no state management.
//   Filtering is done in-memory against the dummy list.
//
// ARCHITECTURE:
//   StatefulWidget — local state only.
//   _searchQuery and _selectedFilter drive the filtered list.
//   No Riverpod, Bloc, Provider.
// ============================================================

// ── Data Model ────────────────────────────────────────────────

enum _ParkingFeature { evCharging, covered, open, nearby }

class _ParkingLot {
  final String                  id;
  final String                  name;
  final String                  address;
  final String                  distance;
  final double                  rating;
  final int                     reviewCount;
  final double                  pricePerHour;
  final int                     totalSlots;
  final int                     availableSlots;
  final bool                    isOpen;
  final Set<_ParkingFeature>    features;
  final Color                   accentColor;

  const _ParkingLot({
    required this.id,
    required this.name,
    required this.address,
    required this.distance,
    required this.rating,
    required this.reviewCount,
    required this.pricePerHour,
    required this.totalSlots,
    required this.availableSlots,
    required this.isOpen,
    required this.features,
    required this.accentColor,
  });

  bool get isAlmostFull => availableSlots <= 5;
  bool get isFull       => availableSlots == 0;

  double get occupancyRatio =>
      (totalSlots - availableSlots) / totalSlots.toDouble();
}

// ── Dummy Data ────────────────────────────────────────────────

final List<_ParkingLot> _allParkingLots = [
  _ParkingLot(
    id:             '1',
    name:           'Cyber Hub Parking Complex',
    address:        'DLF Cyber Hub, Gurugram, Haryana',
    distance:       '0.4 km',
    rating:         4.8,
    reviewCount:    1240,
    pricePerHour:   40.0,
    totalSlots:     120,
    availableSlots: 34,
    isOpen:         true,
    features: {
      _ParkingFeature.nearby,
      _ParkingFeature.covered,
      _ParkingFeature.evCharging,
    },
    accentColor: AppColors.primary,
  ),
  _ParkingLot(
    id:             '2',
    name:           'Ambience Mall Parking',
    address:        'NH-48, Sheetla Mata Rd, Gurugram',
    distance:       '1.2 km',
    rating:         4.5,
    reviewCount:    876,
    pricePerHour:   30.0,
    totalSlots:     200,
    availableSlots: 52,
    isOpen:         true,
    features: {
      _ParkingFeature.nearby,
      _ParkingFeature.covered,
    },
    accentColor: AppColors.primaryLight,
  ),
  _ParkingLot(
    id:             '3',
    name:           'Sector 29 Public Parking',
    address:        'Sector 29, HUDA Market, Gurugram',
    distance:       '2.0 km',
    rating:         4.1,
    reviewCount:    432,
    pricePerHour:   20.0,
    totalSlots:     80,
    availableSlots: 4,
    isOpen:         true,
    features: {
      _ParkingFeature.nearby,
      _ParkingFeature.open,
    },
    accentColor: AppColors.primaryLighter,
  ),
  _ParkingLot(
    id:             '4',
    name:           'MGF Metropolitan Mall',
    address:        'MG Road, Sikanderpur, Gurugram',
    distance:       '3.5 km',
    rating:         4.6,
    reviewCount:    1890,
    pricePerHour:   35.0,
    totalSlots:     150,
    availableSlots: 67,
    isOpen:         true,
    features: {
      _ParkingFeature.covered,
      _ParkingFeature.evCharging,
    },
    accentColor: AppColors.primary,
  ),
  _ParkingLot(
    id:             '5',
    name:           'IFFCO Chowk Metro Parking',
    address:        'IFFCO Chowk, Gurugram, Haryana',
    distance:       '4.1 km',
    rating:         3.9,
    reviewCount:    318,
    pricePerHour:   15.0,
    totalSlots:     60,
    availableSlots: 0,
    isOpen:         false,
    features: {
      _ParkingFeature.open,
    },
    accentColor: AppColors.primaryDark,
  ),
  _ParkingLot(
    id:             '6',
    name:           'DLF Phase 2 EV Hub',
    address:        'DLF Phase 2, Sector 25, Gurugram',
    distance:       '5.0 km',
    rating:         4.7,
    reviewCount:    654,
    pricePerHour:   50.0,
    totalSlots:     40,
    availableSlots: 18,
    isOpen:         true,
    features: {
      _ParkingFeature.covered,
      _ParkingFeature.evCharging,
    },
    accentColor: AppColors.secondary,
  ),
];

// ── Filter Chip Definition ────────────────────────────────────

class _FilterOption {
  final String             label;
  final IconData           icon;
  final _ParkingFeature?   feature; // null = "All"

  const _FilterOption({
    required this.label,
    required this.icon,
    this.feature,
  });
}

const List<_FilterOption> _filterOptions = [
  _FilterOption(label: 'All',         icon: Icons.dashboard_outlined),
  _FilterOption(label: 'Nearby',      icon: Icons.near_me_outlined,          feature: _ParkingFeature.nearby),
  _FilterOption(label: 'EV Charging', icon: Icons.ev_station_outlined,        feature: _ParkingFeature.evCharging),
  _FilterOption(label: 'Covered',     icon: Icons.roofing_outlined,           feature: _ParkingFeature.covered),
  _FilterOption(label: 'Open',        icon: Icons.wb_sunny_outlined,          feature: _ParkingFeature.open),
];

// ── Screen ────────────────────────────────────────────────────

class ParkingListScreen extends StatefulWidget {
  const ParkingListScreen({super.key});

  @override
  State<ParkingListScreen> createState() => _ParkingListScreenState();
}

class _ParkingListScreenState extends State<ParkingListScreen> {
  final _searchController = TextEditingController();
  final _searchFocus      = FocusNode();

  String           _searchQuery     = '';
  int              _selectedFilter  = 0; // index into _filterOptions
  bool             _isGridView      = false;

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
    _searchController.addListener(() {
      setState(() => _searchQuery = _searchController.text.toLowerCase().trim());
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    _searchFocus.dispose();
    super.dispose();
  }

  // ── Filtering ─────────────────────────────────────────────

  List<_ParkingLot> get _filteredLots {
    final feature = _filterOptions[_selectedFilter].feature;

    return _allParkingLots.where((lot) {
      // Feature filter
      final matchesFilter = feature == null || lot.features.contains(feature);

      // Search filter
      final query         = _searchQuery;
      final matchesSearch = query.isEmpty ||
          lot.name.toLowerCase().contains(query) ||
          lot.address.toLowerCase().contains(query);

      return matchesFilter && matchesSearch;
    }).toList();
  }

  // ── Build ──────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.sizeOf(context).width;
    final hPad        = screenWidth > 600 ? screenWidth * 0.08 : 16.0;
    final filtered    = _filteredLots;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: _buildAppBar(context),
      body: GestureDetector(
        onTap: () => FocusScope.of(context).unfocus(),
        behavior: HitTestBehavior.opaque,
        child: Column(
          children: [
            // ── Sticky search + filter header ────────────
            _StickyHeader(
              hPad:            hPad,
              searchController: _searchController,
              searchFocus:     _searchFocus,
              selectedFilter:  _selectedFilter,
              onFilterChanged: (i) => setState(() => _selectedFilter = i),
            ),

            // ── Results count ────────────────────────────
            _ResultsBar(
              count:        filtered.length,
              isGridView:   _isGridView,
              hPad:         hPad,
              onToggleView: () => setState(() => _isGridView = !_isGridView),
            ),

            // ── List / Empty state ───────────────────────
            Expanded(
              child: filtered.isEmpty
                  ? const _EmptyState()
                  : _isGridView
                      ? _ParkingGrid(lots: filtered, hPad: hPad)
                      : _ParkingList(lots: filtered, hPad: hPad),
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
      systemOverlayStyle: const SystemUiOverlayStyle(
        statusBarIconBrightness: Brightness.dark,
      ),
      leading: IconButton(
        icon: const Icon(Icons.arrow_back_ios_rounded, size: 20),
        color: AppColors.textPrimary,
        onPressed: () => Navigator.of(context).maybePop(),
      ),
      title: Text(
        'Find Parking',
        style: textTheme.titleLarge?.copyWith(
          color:      AppColors.textPrimary,
          fontWeight: FontWeight.w700,
        ),
      ),
      centerTitle: true,
      actions: [
        IconButton(
          icon:  const Icon(Icons.map_outlined, size: 22),
          color: AppColors.primary,
          tooltip: 'Map view',
           onPressed: () => Navigator.pushNamed(context, AppRoutes.parkingList),
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

// ── Sticky Header (Search + Chips) ────────────────────────────

class _StickyHeader extends StatelessWidget {
  final double                  hPad;
  final TextEditingController   searchController;
  final FocusNode               searchFocus;
  final int                     selectedFilter;
  final ValueChanged<int>       onFilterChanged;

  const _StickyHeader({
    required this.hPad,
    required this.searchController,
    required this.searchFocus,
    required this.selectedFilter,
    required this.onFilterChanged,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      color: AppColors.surface,
      child: Column(
        children: [
          // ── Search ────────────────────────────────────
          Padding(
            padding: EdgeInsets.fromLTRB(hPad, 14, hPad, 12),
            child: _SearchField(
              controller: searchController,
              focusNode:  searchFocus,
            ),
          ),

          // ── Filter Chips ──────────────────────────────
          SizedBox(
            height: 40,
            child: ListView.separated(
              scrollDirection:  Axis.horizontal,
              padding: EdgeInsets.only(left: hPad, right: hPad),
              itemCount: _filterOptions.length,
              separatorBuilder: (_, __) => const SizedBox(width: 8),
              itemBuilder: (context, index) {
                final opt        = _filterOptions[index];
                final isSelected = index == selectedFilter;

                return _FilterChip(
                  label:      opt.label,
                  icon:       opt.icon,
                  isSelected: isSelected,
                  onTap:      () => onFilterChanged(index),
                );
              },
            ),
          ),

          const SizedBox(height: 12),
          Divider(height: 1, color: AppColors.divider),
        ],
      ),
    );
  }
}

// ── Search Field ──────────────────────────────────────────────

class _SearchField extends StatelessWidget {
  final TextEditingController controller;
  final FocusNode             focusNode;

  const _SearchField({
    required this.controller,
    required this.focusNode,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 50,
      decoration: BoxDecoration(
        color:        AppColors.surfaceVariant,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.divider, width: 1.5),
      ),
      child: Row(
        children: [
          const SizedBox(width: 14),
          const Icon(Icons.search_rounded, color: AppColors.textSecondary, size: 20),
          const SizedBox(width: 10),
          Expanded(
            child: TextField(
              controller:      controller,
              focusNode:       focusNode,
              textInputAction: TextInputAction.search,
              style: const TextStyle(
                color:    AppColors.textPrimary,
                fontSize: 14,
              ),
              decoration: const InputDecoration(
                hintText:       'Search by name or location…',
                hintStyle:      TextStyle(
                  color:    AppColors.textTertiary,
                  fontSize: 14,
                ),
                border:         InputBorder.none,
                isDense:        true,
                contentPadding: EdgeInsets.zero,
              ),
            ),
          ),
          if (controller.text.isNotEmpty)
            GestureDetector(
              onTap: controller.clear,
              child: const Padding(
                padding: EdgeInsets.symmetric(horizontal: 12),
                child:   Icon(
                  Icons.cancel_rounded,
                  color: AppColors.textTertiary,
                  size:  18,
                ),
              ),
            )
          else
            const SizedBox(width: 14),
        ],
      ),
    );
  }
}

// ── Filter Chip ───────────────────────────────────────────────

class _FilterChip extends StatelessWidget {
  final String       label;
  final IconData     icon;
  final bool         isSelected;
  final VoidCallback onTap;

  const _FilterChip({
    required this.label,
    required this.icon,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
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
                    color:      AppColors.primary.withAlpha(50),
                    blurRadius: 6,
                    offset:     const Offset(0, 2),
                  ),
                ]
              : null,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              size:  14,
              color: isSelected ? AppColors.onPrimary : AppColors.textSecondary,
            ),
            const SizedBox(width: 6),
            Text(
              label,
              style: TextStyle(
                fontSize:   12,
                fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
                color:      isSelected ? AppColors.onPrimary : AppColors.textSecondary,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── Results Bar ───────────────────────────────────────────────

class _ResultsBar extends StatelessWidget {
  final int          count;
  final bool         isGridView;
  final double       hPad;
  final VoidCallback onToggleView;

  const _ResultsBar({
    required this.count,
    required this.isGridView,
    required this.hPad,
    required this.onToggleView,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Padding(
      padding: EdgeInsets.fromLTRB(hPad, 14, hPad, 6),
      child: Row(
        children: [
          RichText(
            text: TextSpan(
              children: [
                TextSpan(
                  text: 'Showing ',
                  style: textTheme.bodySmall?.copyWith(
                    color: AppColors.textSecondary,
                  ),
                ),
                TextSpan(
                  text: '$count ',
                  style: textTheme.bodySmall?.copyWith(
                    color:      AppColors.primary,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                TextSpan(
                  text: count == 1 ? 'parking lot' : 'parking lots',
                  style: textTheme.bodySmall?.copyWith(
                    color: AppColors.textSecondary,
                  ),
                ),
              ],
            ),
          ),
          const Spacer(),
          GestureDetector(
            onTap: onToggleView,
            child: Container(
              width:  34,
              height: 34,
              decoration: BoxDecoration(
                color:        AppColors.surface,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: AppColors.divider, width: 1),
              ),
              child: Icon(
                isGridView
                    ? Icons.view_list_rounded
                    : Icons.grid_view_rounded,
                color: AppColors.textSecondary,
                size:  18,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ── Parking List ──────────────────────────────────────────────

class _ParkingList extends StatelessWidget {
  final List<_ParkingLot> lots;
  final double            hPad;

  const _ParkingList({required this.lots, required this.hPad});

  @override
  Widget build(BuildContext context) {
    return ListView.separated(
      padding: EdgeInsets.fromLTRB(hPad, 8, hPad, 32),
      itemCount:    lots.length,
      separatorBuilder: (_, __) => const SizedBox(height: 14),
      itemBuilder: (context, index) =>
          _ParkingCard(lot: lots[index]),
    );
  }
}

// ── Parking Grid ──────────────────────────────────────────────

class _ParkingGrid extends StatelessWidget {
  final List<_ParkingLot> lots;
  final double            hPad;

  const _ParkingGrid({required this.lots, required this.hPad});

  @override
  Widget build(BuildContext context) {
    return GridView.builder(
      padding: EdgeInsets.fromLTRB(hPad, 8, hPad, 32),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount:   2,
        mainAxisSpacing:  14,
        crossAxisSpacing: 12,
        childAspectRatio: 0.72,
      ),
      itemCount: lots.length,
      itemBuilder: (context, index) =>
          _ParkingGridCard(lot: lots[index]),
    );
  }
}

// ── Parking Card (List) ───────────────────────────────────────

class _ParkingCard extends StatelessWidget {
  final _ParkingLot lot;
  const _ParkingCard({required this.lot});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
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
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [

          // ── Image Placeholder ────────────────────────
          ClipRRect(
            borderRadius: const BorderRadius.vertical(
              top: Radius.circular(18),
            ),
            child: _ParkingImagePlaceholder(
              lot:    lot,
              height: 150,
            ),
          ),

          // ── Content ───────────────────────────────────
          Padding(
            padding: const EdgeInsets.all(14),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [

                // ── Name + Status Badge ────────────────
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(
                      child: Text(
                        lot.name,
                        style: textTheme.titleSmall?.copyWith(
                          color:      AppColors.textPrimary,
                          fontWeight: FontWeight.w700,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    const SizedBox(width: 8),
                    _StatusBadge(lot: lot),
                  ],
                ),

                const SizedBox(height: 6),

                // ── Address ────────────────────────────
                Row(
                  children: [
                    const Icon(
                      Icons.location_on_outlined,
                      color: AppColors.textTertiary,
                      size:  13,
                    ),
                    const SizedBox(width: 3),
                    Expanded(
                      child: Text(
                        lot.address,
                        style: textTheme.bodySmall?.copyWith(
                          color: AppColors.textTertiary,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 10),

                // ── Distance + Rating ──────────────────
                Row(
                  children: [
                    _InfoPill(
                      icon:  Icons.near_me_rounded,
                      label: lot.distance,
                      color: AppColors.info,
                      bg:    AppColors.infoLight,
                    ),
                    const SizedBox(width: 8),
                    _InfoPill(
                      icon:  Icons.star_rounded,
                      label: '${lot.rating} (${lot.reviewCount})',
                      color: AppColors.accent,
                      bg:    AppColors.warningLight,
                    ),
                  ],
                ),

                const SizedBox(height: 10),

                // ── Occupancy Bar ──────────────────────
                _OccupancyBar(lot: lot),

                const SizedBox(height: 12),

                // ── Feature Tags ───────────────────────
                _FeatureTags(features: lot.features),

                const SizedBox(height: 14),

                // ── Price + Book Now ───────────────────
                Row(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '₹${lot.pricePerHour.toStringAsFixed(0)}',
                          style: textTheme.titleMedium?.copyWith(
                            color:      AppColors.primary,
                            fontWeight: FontWeight.w800,
                            fontSize:   20,
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
                    const Spacer(),
                    _BookNowButton(isOpen: lot.isOpen && !lot.isFull),
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

// ── Parking Grid Card ─────────────────────────────────────────

class _ParkingGridCard extends StatelessWidget {
  final _ParkingLot lot;
  const _ParkingGridCard({required this.lot});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      decoration: BoxDecoration(
        color:        AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.divider, width: 1),
        boxShadow: [
          BoxShadow(
            color:       AppColors.shadow,
            blurRadius:  10,
            offset:      const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [

          // ── Image ─────────────────────────────────────
          ClipRRect(
            borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
            child: _ParkingImagePlaceholder(lot: lot, height: 100),
          ),

          Expanded(
            child: Padding(
              padding: const EdgeInsets.all(10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    lot.name,
                    style: textTheme.labelMedium?.copyWith(
                      color:      AppColors.textPrimary,
                      fontWeight: FontWeight.w700,
                      fontSize:   12,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),

                  const SizedBox(height: 4),

                  Row(
                    children: [
                      const Icon(
                        Icons.star_rounded,
                        color: AppColors.accent,
                        size:  11,
                      ),
                      const SizedBox(width: 2),
                      Text(
                        '${lot.rating}',
                        style: textTheme.bodySmall?.copyWith(
                          color:      AppColors.textSecondary,
                          fontWeight: FontWeight.w600,
                          fontSize:   11,
                        ),
                      ),
                      const Spacer(),
                      Text(
                        lot.distance,
                        style: textTheme.bodySmall?.copyWith(
                          color:    AppColors.textTertiary,
                          fontSize: 10,
                        ),
                      ),
                    ],
                  ),

                  const Spacer(),

                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        '₹${lot.pricePerHour.toStringAsFixed(0)}/hr',
                        style: textTheme.labelMedium?.copyWith(
                          color:      AppColors.primary,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      _StatusBadge(lot: lot, compact: true),
                    ],
                  ),

                  const SizedBox(height: 8),

                  SizedBox(
                    width:  double.infinity,
                    height: 32,
                    child:  _BookNowButton(
                      isOpen:  lot.isOpen && !lot.isFull,
                      compact: true,
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

// ── Image Placeholder ─────────────────────────────────────────

class _ParkingImagePlaceholder extends StatelessWidget {
  final _ParkingLot lot;
  final double      height;

  const _ParkingImagePlaceholder({
    required this.lot,
    required this.height,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width:  double.infinity,
      height: height,
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end:   Alignment.bottomRight,
          colors: [
            lot.accentColor,
            lot.accentColor.withAlpha(180),
          ],
        ),
      ),
      child: Stack(
        children: [

          // ── Background pattern ─────────────────────
          Positioned(
            right:  -20,
            bottom: -20,
            child:  Icon(
              Icons.local_parking_rounded,
              size:  110,
              color: AppColors.onPrimary.withAlpha(18),
            ),
          ),

          // ── Top badges ─────────────────────────────
          Positioned(
            top:  10,
            left: 10,
            child: Row(
              children: [
                if (lot.features.contains(_ParkingFeature.evCharging))
                  _ImageBadge(
                    icon:  Icons.ev_station_rounded,
                    label: 'EV',
                  ),
                if (lot.features.contains(_ParkingFeature.covered)) ...[
                  const SizedBox(width: 6),
                  _ImageBadge(
                    icon:  Icons.roofing_rounded,
                    label: 'Covered',
                  ),
                ],
              ],
            ),
          ),

          // ── Slot count overlay ─────────────────────
          Positioned(
            bottom: 10,
            left:   10,
            child: Container(
              padding: const EdgeInsets.symmetric(
                horizontal: 10,
                vertical:    5,
              ),
              decoration: BoxDecoration(
                color:        AppColors.onPrimary.withAlpha(220),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    Icons.directions_car_outlined,
                    size:  13,
                    color: lot.isFull
                        ? AppColors.error
                        : lot.isAlmostFull
                            ? AppColors.accent
                            : AppColors.secondary,
                  ),
                  const SizedBox(width: 5),
                  Text(
                    lot.isFull
                        ? 'Full'
                        : '${lot.availableSlots} slots free',
                    style: TextStyle(
                      color: lot.isFull
                          ? AppColors.error
                          : lot.isAlmostFull
                              ? AppColors.accent
                              : AppColors.textPrimary,
                      fontSize:   11,
                      fontWeight: FontWeight.w700,
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

class _ImageBadge extends StatelessWidget {
  final IconData icon;
  final String   label;

  const _ImageBadge({required this.icon, required this.label});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color:        AppColors.onPrimary.withAlpha(220),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 11, color: AppColors.primary),
          const SizedBox(width: 3),
          Text(
            label,
            style: const TextStyle(
              fontSize:   10,
              fontWeight: FontWeight.w700,
              color:      AppColors.textPrimary,
            ),
          ),
        ],
      ),
    );
  }
}

// ── Status Badge ──────────────────────────────────────────────

class _StatusBadge extends StatelessWidget {
  final _ParkingLot lot;
  final bool        compact;

  const _StatusBadge({required this.lot, this.compact = false});

  @override
  Widget build(BuildContext context) {
    final Color bg;
    final Color fg;
    final String label;
    final IconData icon;

    if (!lot.isOpen) {
      bg    = AppColors.surfaceVariant;
      fg    = AppColors.textSecondary;
      label = 'Closed';
      icon  = Icons.do_not_disturb_outlined;
    } else if (lot.isFull) {
      bg    = AppColors.errorLight;
      fg    = AppColors.error;
      label = 'Full';
      icon  = Icons.block_rounded;
    } else if (lot.isAlmostFull) {
      bg    = AppColors.warningLight;
      fg    = AppColors.accent;
      label = compact ? 'Low' : 'Almost Full';
      icon  = Icons.warning_amber_rounded;
    } else {
      bg    = AppColors.successLight;
      fg    = AppColors.secondaryDark;
      label = 'Open';
      icon  = Icons.check_circle_outline_rounded;
    }

    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: compact ? 7 : 9,
        vertical:   compact ? 3 : 4,
      ),
      decoration: BoxDecoration(
        color:        bg,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: compact ? 10 : 12, color: fg),
          const SizedBox(width: 3),
          Text(
            label,
            style: TextStyle(
              fontSize:   compact ? 10 : 11,
              fontWeight: FontWeight.w700,
              color:      fg,
            ),
          ),
        ],
      ),
    );
  }
}

// ── Occupancy Bar ─────────────────────────────────────────────

class _OccupancyBar extends StatelessWidget {
  final _ParkingLot lot;
  const _OccupancyBar({required this.lot});

  @override
  Widget build(BuildContext context) {
    final textTheme   = Theme.of(context).textTheme;
    final ratio       = lot.occupancyRatio;
    final Color barColor = ratio > 0.9
        ? AppColors.error
        : ratio > 0.7
            ? AppColors.accent
            : AppColors.secondary;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              'Occupancy',
              style: textTheme.bodySmall?.copyWith(
                color: AppColors.textTertiary,
              ),
            ),
            Text(
              '${lot.totalSlots - lot.availableSlots}/${lot.totalSlots} used',
              style: textTheme.bodySmall?.copyWith(
                color:      AppColors.textSecondary,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
        const SizedBox(height: 6),
        ClipRRect(
          borderRadius: BorderRadius.circular(4),
          child: LinearProgressIndicator(
            value:           ratio,
            minHeight:       6,
            backgroundColor: AppColors.surfaceVariant,
            valueColor:      AlwaysStoppedAnimation<Color>(barColor),
          ),
        ),
      ],
    );
  }
}

// ── Feature Tags ──────────────────────────────────────────────

class _FeatureTags extends StatelessWidget {
  final Set<_ParkingFeature> features;
  const _FeatureTags({required this.features});

  static const _featureMeta = {
    _ParkingFeature.evCharging: (
      icon:  Icons.ev_station_outlined,
      label: 'EV Charging',
      color: AppColors.info,
    ),
    _ParkingFeature.covered: (
      icon:  Icons.roofing_outlined,
      label: 'Covered',
      color: AppColors.primary,
    ),
    _ParkingFeature.open: (
      icon:  Icons.wb_sunny_outlined,
      label: 'Open Air',
      color: AppColors.accent,
    ),
    _ParkingFeature.nearby: (
      icon:  Icons.near_me_rounded,
      label: 'Nearby',
      color: AppColors.secondary,
    ),
  };

  @override
  Widget build(BuildContext context) {
    final tags = features
        .where((f) => _featureMeta.containsKey(f))
        .toList();

    if (tags.isEmpty) return const SizedBox.shrink();

    return Wrap(
      spacing: 6,
      runSpacing: 6,
      children: tags.map((f) {
        final meta = _featureMeta[f]!;
        return Container(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
          decoration: BoxDecoration(
            color:        (meta.color as Color).withAlpha(18),
            borderRadius: BorderRadius.circular(6),
            border: Border.all(
              color: (meta.color as Color).withAlpha(50),
              width: 1,
            ),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(meta.icon as IconData, size: 11, color: meta.color as Color),
              const SizedBox(width: 4),
              Text(
                meta.label as String,
                style: TextStyle(
                  fontSize:   10,
                  fontWeight: FontWeight.w600,
                  color:      meta.color as Color,
                ),
              ),
            ],
          ),
        );
      }).toList(),
    );
  }
}

// ── Info Pill ─────────────────────────────────────────────────

class _InfoPill extends StatelessWidget {
  final IconData icon;
  final String   label;
  final Color    color;
  final Color    bg;

  const _InfoPill({
    required this.icon,
    required this.label,
    required this.color,
    required this.bg,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color:        bg,
        borderRadius: BorderRadius.circular(6),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 12, color: color),
          const SizedBox(width: 4),
          Text(
            label,
            style: TextStyle(
              fontSize:   11,
              fontWeight: FontWeight.w600,
              color:      color,
            ),
          ),
        ],
      ),
    );
  }
}

// ── Book Now Button ───────────────────────────────────────────

class _BookNowButton extends StatelessWidget {
  final bool isOpen;
  final bool compact;

  const _BookNowButton({
    required this.isOpen,
    this.compact = false,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return SizedBox(
      height: compact ? 32 : 46,
      child: FilledButton(
        onPressed: isOpen ? () => Navigator.pushNamed(context, AppRoutes.parkingDetails) : null,
        style: FilledButton.styleFrom(
          backgroundColor:         AppColors.primary,
          disabledBackgroundColor: AppColors.surfaceVariant,
          foregroundColor:         AppColors.onPrimary,
          disabledForegroundColor: AppColors.textTertiary,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(compact ? 10 : 12),
          ),
          padding: EdgeInsets.symmetric(
            horizontal: compact ? 12 : 24,
          ),
          textStyle: textTheme.labelLarge?.copyWith(
            fontSize:      compact ? 12 : 14,
            fontWeight:    FontWeight.w700,
            letterSpacing: 0.3,
          ),
          elevation:         0,
          shadowColor:       Colors.transparent,
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          mainAxisSize:      MainAxisSize.min,
          children: [
            if (!compact) ...[
              const Icon(Icons.bookmark_add_outlined, size: 16),
              const SizedBox(width: 6),
            ],
            Text(isOpen ? 'Book Now' : 'Unavailable'),
          ],
        ),
      ),
    );
  }
}

// ── Empty State ───────────────────────────────────────────────

class _EmptyState extends StatelessWidget {
  const _EmptyState();

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
                Icons.search_off_rounded,
                size:  40,
                color: AppColors.textTertiary,
              ),
            ),
            const SizedBox(height: 20),
            Text(
              'No parking lots found',
              style: textTheme.titleMedium?.copyWith(
                color:      AppColors.textPrimary,
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Try a different search term or remove filters to see more results.',
              textAlign: TextAlign.center,
              style: textTheme.bodyMedium?.copyWith(
                color:  AppColors.textSecondary,
                height: 1.5,
              ),
            ),
          ],
        ),
      ),
    );
  }
}