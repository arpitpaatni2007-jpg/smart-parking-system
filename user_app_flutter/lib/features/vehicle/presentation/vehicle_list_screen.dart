import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_colors.dart';

// ============================================================
// VehicleListScreen
// ============================================================
//
// Displays all saved vehicles for the logged-in user and lets
// them add, edit, delete, or change the default vehicle.
//
// SECTIONS:
//   1. AppBar           — "My Vehicles" + vehicle count badge
//   2. Summary card     — total added / max limit progress
//   3. Vehicle cards    — swipeable list with edit & delete
//   4. Add Vehicle FAB  — sticky at bottom
//   5. Limit note       — "You can add up to 5 vehicles."
//
// STATE:
//   _vehicles         — mutable list of _Vehicle objects
//   _defaultVehicleId — id of the current default vehicle
//
// ARCHITECTURE:
//   Pure StatefulWidget — no Riverpod, Bloc, Provider.
//   Add / Edit uses a modal bottom sheet (no navigation).
//   Delete shows a confirmation dialog.
//   All data is local dummy data.
// ============================================================

// ── Vehicle Type ──────────────────────────────────────────────

enum _VehicleType { car, bike, ev }

extension _VehicleTypeExt on _VehicleType {
  String get label {
    return switch (this) {
      _VehicleType.car  => 'Car',
      _VehicleType.bike => 'Bike',
      _VehicleType.ev   => 'EV',
    };
  }

  IconData get icon {
    return switch (this) {
      _VehicleType.car  => Icons.directions_car_rounded,
      _VehicleType.bike => Icons.two_wheeler_rounded,
      _VehicleType.ev   => Icons.electric_car_rounded,
    };
  }

  Color get color {
    return switch (this) {
      _VehicleType.car  => AppColors.primary,
      _VehicleType.bike => AppColors.accent,
      _VehicleType.ev   => AppColors.info,
    };
  }

  Color get bgColor {
    return switch (this) {
      _VehicleType.car  => AppColors.primary.withAlpha(14),
      _VehicleType.bike => AppColors.warningLight,
      _VehicleType.ev   => AppColors.infoLight,
    };
  }
}

// ── Vehicle Model ─────────────────────────────────────────────

class _Vehicle {
  final String       id;
  String             number;
  String             nickname;
  _VehicleType       type;
  String             color;

  _Vehicle({
    required this.id,
    required this.number,
    required this.nickname,
    required this.type,
    required this.color,
  });
}

// ── Dummy data ────────────────────────────────────────────────

final List<_Vehicle> _dummyVehicles = [
  _Vehicle(
    id:       '1',
    number:   'DL 01 AB 1234',
    nickname: 'My Swift',
    type:     _VehicleType.car,
    color:    'Pearl White',
  ),
  _Vehicle(
    id:       '2',
    number:   'HR 26 BC 5678',
    nickname: 'Office Bike',
    type:     _VehicleType.bike,
    color:    'Matte Black',
  ),
  _Vehicle(
    id:       '3',
    number:   'UP 16 CD 9012',
    nickname: 'Tesla Model 3',
    type:     _VehicleType.ev,
    color:    'Midnight Blue',
  ),
];

const int _maxVehicles = 5;

// ── Screen ────────────────────────────────────────────────────

class VehicleListScreen extends StatefulWidget {
  const VehicleListScreen({super.key});

  @override
  State<VehicleListScreen> createState() => _VehicleListScreenState();
}

class _VehicleListScreenState extends State<VehicleListScreen>
    with SingleTickerProviderStateMixin {
  late final List<_Vehicle> _vehicles;
  String _defaultVehicleId = '1';

  // For staggered entrance animation
  late final AnimationController _listController;

  @override
  void initState() {
    super.initState();
    _vehicles = List.from(_dummyVehicles);

    SystemChrome.setSystemUIOverlayStyle(
      const SystemUiOverlayStyle(
        statusBarColor:          Colors.transparent,
        statusBarIconBrightness: Brightness.dark,
        statusBarBrightness:     Brightness.light,
      ),
    );

    _listController = AnimationController(
      vsync:    this,
      duration: const Duration(milliseconds: 600),
    )..forward();
  }

  @override
  void dispose() {
    _listController.dispose();
    super.dispose();
  }

  // ── Actions ───────────────────────────────────────────────

  void _setDefault(String id) {
    setState(() => _defaultVehicleId = id);
    _showSnack('Default vehicle updated.');
  }

  void _deleteVehicle(String id) {
    if (_vehicles.length == 1) {
      _showSnack('You must have at least one vehicle.');
      return;
    }
    showDialog<bool>(
      context: context,
      builder: (ctx) => _DeleteDialog(
        onConfirm: () {
          setState(() {
            _vehicles.removeWhere((v) => v.id == id);
            if (_defaultVehicleId == id && _vehicles.isNotEmpty) {
              _defaultVehicleId = _vehicles.first.id;
            }
          });
          _showSnack('Vehicle removed successfully.');
        },
      ),
    );
  }

  void _openAddSheet() {
    if (_vehicles.length >= _maxVehicles) {
      _showSnack('You can add up to $_maxVehicles vehicles only.');
      return;
    }
    _openVehicleSheet(vehicle: null);
  }

  void _openEditSheet(_Vehicle vehicle) {
    _openVehicleSheet(vehicle: vehicle);
  }

  void _openVehicleSheet({_Vehicle? vehicle}) {
    showModalBottomSheet(
      context:        context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _VehicleFormSheet(
        vehicle: vehicle,
        onSave:  (number, nickname, type, color) {
          setState(() {
            if (vehicle == null) {
              _vehicles.add(
                _Vehicle(
                  id:       DateTime.now().millisecondsSinceEpoch.toString(),
                  number:   number,
                  nickname: nickname,
                  type:     type,
                  color:    color,
                ),
              );
            } else {
              vehicle.number   = number;
              vehicle.nickname = nickname;
              vehicle.type     = type;
              vehicle.color    = color;
            }
          });
          _showSnack(
            vehicle == null
                ? 'Vehicle added successfully.'
                : 'Vehicle updated successfully.',
          );
        },
      ),
    );
  }

  void _showSnack(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        behavior:        SnackBarBehavior.floating,
        duration:        const Duration(seconds: 2),
        backgroundColor: AppColors.textPrimary,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
      ),
    );
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

          // ── Scrollable content ───────────────────────
          CustomScrollView(
            physics: const BouncingScrollPhysics(),
            slivers: [

              // ── Summary card ─────────────────────────
              SliverToBoxAdapter(
                child: Padding(
                  padding: EdgeInsets.fromLTRB(hPad, 20, hPad, 0),
                  child:   _SummaryCard(
                    total: _vehicles.length,
                    max:   _maxVehicles,
                  ),
                ),
              ),

              const SliverToBoxAdapter(child: SizedBox(height: 24)),

              // ── Section label ─────────────────────────
              SliverToBoxAdapter(
                child: Padding(
                  padding: EdgeInsets.symmetric(horizontal: hPad),
                  child:   _SectionLabel(
                    title:      'Saved Vehicles',
                    count:      _vehicles.length,
                  ),
                ),
              ),

              const SliverToBoxAdapter(child: SizedBox(height: 14)),

              // ── Vehicle cards ─────────────────────────
              SliverList.separated(
                itemCount: _vehicles.length,
                separatorBuilder: (_, __) => const SizedBox(height: 14),
                itemBuilder: (context, index) {
                  final vehicle    = _vehicles[index];
                  final isDefault  = vehicle.id == _defaultVehicleId;

                  // Staggered entrance
                  final animation = CurvedAnimation(
                    parent: _listController,
                    curve:  Interval(
                      (index * 0.15).clamp(0.0, 0.6),
                      ((index * 0.15) + 0.4).clamp(0.0, 1.0),
                      curve: Curves.easeOutCubic,
                    ),
                  );

                  return SlideTransition(
                    position: Tween<Offset>(
                      begin: const Offset(0, 0.25),
                      end:   Offset.zero,
                    ).animate(animation),
                    child: FadeTransition(
                      opacity: animation,
                      child: Padding(
                        padding: EdgeInsets.symmetric(horizontal: hPad),
                        child: _VehicleCard(
                          vehicle:    vehicle,
                          isDefault:  isDefault,
                          onSetDefault: () => _setDefault(vehicle.id),
                          onEdit:     () => _openEditSheet(vehicle),
                          onDelete:   () => _deleteVehicle(vehicle.id),
                        ),
                      ),
                    ),
                  );
                },
              ),

              // ── Bottom note ───────────────────────────
              SliverToBoxAdapter(
                child: Padding(
                  padding: EdgeInsets.fromLTRB(hPad, 24, hPad, 100),
                  child: _LimitNote(
                    current: _vehicles.length,
                    max:     _maxVehicles,
                  ),
                ),
              ),
            ],
          ),

          // ── FAB ──────────────────────────────────────
          Positioned(
            bottom: 0,
            left:   0,
            right:  0,
            child: _AddVehicleBar(
              canAdd:  _vehicles.length < _maxVehicles,
              onTap:   _openAddSheet,
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
        'My Vehicles',
        style: textTheme.titleLarge?.copyWith(
          color:      AppColors.textPrimary,
          fontWeight: FontWeight.w700,
        ),
      ),
      centerTitle: true,
      actions: [
        // Vehicle count badge
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
            '${_vehicles.length}/$_maxVehicles',
            style: Theme.of(context).textTheme.labelSmall?.copyWith(
              color:      AppColors.primary,
              fontWeight: FontWeight.w700,
            ),
          ),
        ),
      ],
      bottom: PreferredSize(
        preferredSize: const Size.fromHeight(1),
        child:         Divider(height: 1, color: AppColors.divider),
      ),
    );
  }
}

// ── Summary Card ──────────────────────────────────────────────

class _SummaryCard extends StatelessWidget {
  final int total;
  final int max;
  const _SummaryCard({required this.total, required this.max});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;
    final ratio     = total / max;

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin:  Alignment.topLeft,
          end:    Alignment.bottomRight,
          colors: [AppColors.primary, AppColors.primaryLight],
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color:        AppColors.primary.withAlpha(70),
            blurRadius:   18,
            spreadRadius: 0,
            offset:       const Offset(0, 6),
          ),
        ],
      ),
      child: Row(
        children: [

          // ── Icon ─────────────────────────────────────
          Container(
            width:  56,
            height: 56,
            decoration: BoxDecoration(
              color:        AppColors.onPrimary.withAlpha(25),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(
                color: AppColors.onPrimary.withAlpha(40),
                width: 1,
              ),
            ),
            child: const Icon(
              Icons.garage_rounded,
              color: AppColors.onPrimary,
              size:  28,
            ),
          ),

          const SizedBox(width: 16),

          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '$total of $max vehicles added',
                  style: textTheme.titleSmall?.copyWith(
                    color:      AppColors.onPrimary,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 8),
                ClipRRect(
                  borderRadius: BorderRadius.circular(4),
                  child: LinearProgressIndicator(
                    value:           ratio,
                    minHeight:       6,
                    backgroundColor: AppColors.onPrimary.withAlpha(40),
                    valueColor: AlwaysStoppedAnimation<Color>(
                      ratio >= 1.0
                          ? AppColors.error
                          : AppColors.secondary,
                    ),
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  ratio >= 1.0
                      ? 'Limit reached — delete a vehicle to add another.'
                      : '${max - total} slot${max - total == 1 ? '' : 's'} remaining',
                  style: textTheme.bodySmall?.copyWith(
                    color: AppColors.onPrimary.withAlpha(190),
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

// ── Section Label ─────────────────────────────────────────────

class _SectionLabel extends StatelessWidget {
  final String title;
  final int    count;
  const _SectionLabel({required this.title, required this.count});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Row(
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
        const Spacer(),
        Text(
          '$count vehicle${count == 1 ? '' : 's'}',
          style: textTheme.bodySmall?.copyWith(
            color: AppColors.textTertiary,
          ),
        ),
      ],
    );
  }
}

// ── Vehicle Card ──────────────────────────────────────────────

class _VehicleCard extends StatelessWidget {
  final _Vehicle    vehicle;
  final bool        isDefault;
  final VoidCallback onSetDefault;
  final VoidCallback onEdit;
  final VoidCallback onDelete;

  const _VehicleCard({
    required this.vehicle,
    required this.isDefault,
    required this.onSetDefault,
    required this.onEdit,
    required this.onDelete,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme  = Theme.of(context).textTheme;
    final typeColor  = vehicle.type.color;
    final typeBg     = vehicle.type.bgColor;

    return Container(
      decoration: BoxDecoration(
        color:        AppColors.surface,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: isDefault
              ? AppColors.primary.withAlpha(60)
              : AppColors.divider,
          width: isDefault ? 1.5 : 1,
        ),
        boxShadow: [
          BoxShadow(
            color:        isDefault
                ? AppColors.primary.withAlpha(18)
                : AppColors.shadow,
            blurRadius:   isDefault ? 16 : 10,
            spreadRadius: 0,
            offset:       const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: [

          // ── Main row ─────────────────────────────────
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 12, 16),
            child: Row(
              children: [

                // ── Vehicle icon container ───────────────
                Container(
                  width:  58,
                  height: 58,
                  decoration: BoxDecoration(
                    color:        typeBg,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: typeColor.withAlpha(40),
                      width: 1,
                    ),
                  ),
                  child: Icon(
                    vehicle.type.icon,
                    color: typeColor,
                    size:  28,
                  ),
                ),

                const SizedBox(width: 14),

                // ── Info ─────────────────────────────────
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [

                      // Number + default badge
                      Row(
                        children: [
                          Flexible(
                            child: Text(
                              vehicle.number,
                              style: textTheme.titleSmall?.copyWith(
                                color:         AppColors.textPrimary,
                                fontWeight:    FontWeight.w800,
                                letterSpacing: 0.5,
                              ),
                            ),
                          ),
                          if (isDefault) ...[
                            const SizedBox(width: 8),
                            _DefaultBadge(),
                          ],
                        ],
                      ),

                      const SizedBox(height: 4),

                      // Nickname
                      Text(
                        vehicle.nickname,
                        style: textTheme.bodyMedium?.copyWith(
                          color:      AppColors.textSecondary,
                          fontWeight: FontWeight.w500,
                        ),
                      ),

                      const SizedBox(height: 6),

                      // Meta row
                      Row(
                        children: [
                          _MetaChip(
                            icon:  vehicle.type.icon,
                            label: vehicle.type.label,
                            color: typeColor,
                            bg:    typeBg,
                          ),
                          const SizedBox(width: 6),
                          _MetaChip(
                            icon:  Icons.palette_outlined,
                            label: vehicle.color,
                            color: AppColors.textSecondary,
                            bg:    AppColors.surfaceVariant,
                          ),
                        ],
                      ),
                    ],
                  ),
                ),

                // ── Action column ─────────────────────────
                Column(
                  children: [
                    _ActionIconButton(
                      icon:    Icons.edit_outlined,
                      color:   AppColors.primary,
                      bg:      AppColors.primary.withAlpha(12),
                      tooltip: 'Edit',
                      onTap:   onEdit,
                    ),
                    const SizedBox(height: 8),
                    _ActionIconButton(
                      icon:    Icons.delete_outline_rounded,
                      color:   AppColors.error,
                      bg:      AppColors.errorLight,
                      tooltip: 'Delete',
                      onTap:   onDelete,
                    ),
                  ],
                ),
              ],
            ),
          ),

          // ── Divider ───────────────────────────────────
          Divider(height: 1, color: AppColors.divider),

          // ── Footer: set as default ────────────────────
          InkWell(
            onTap:        isDefault ? null : onSetDefault,
            borderRadius: const BorderRadius.vertical(
              bottom: Radius.circular(20),
            ),
            child: Padding(
              padding: const EdgeInsets.symmetric(
                horizontal: 16,
                vertical:   11,
              ),
              child: Row(
                children: [
                  Icon(
                    isDefault
                        ? Icons.star_rounded
                        : Icons.star_outline_rounded,
                    size:  16,
                    color: isDefault
                        ? AppColors.accent
                        : AppColors.textTertiary,
                  ),
                  const SizedBox(width: 8),
                  Text(
                    isDefault
                        ? 'Default vehicle for bookings'
                        : 'Set as default vehicle',
                    style: textTheme.bodySmall?.copyWith(
                      color: isDefault
                          ? AppColors.accent
                          : AppColors.textTertiary,
                      fontWeight: isDefault
                          ? FontWeight.w600
                          : FontWeight.w400,
                    ),
                  ),
                  const Spacer(),
                  if (!isDefault)
                    Icon(
                      Icons.chevron_right_rounded,
                      size:  16,
                      color: AppColors.textTertiary,
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

// ── Default Badge ─────────────────────────────────────────────

class _DefaultBadge extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
      decoration: BoxDecoration(
        color:        AppColors.warningLight,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppColors.accent.withAlpha(60), width: 1),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.star_rounded, color: AppColors.accent, size: 10),
          const SizedBox(width: 3),
          Text(
            'Default',
            style: Theme.of(context).textTheme.labelSmall?.copyWith(
              color:      AppColors.accent,
              fontWeight: FontWeight.w700,
              fontSize:   10,
            ),
          ),
        ],
      ),
    );
  }
}

// ── Meta Chip ─────────────────────────────────────────────────

class _MetaChip extends StatelessWidget {
  final IconData icon;
  final String   label;
  final Color    color;
  final Color    bg;

  const _MetaChip({
    required this.icon,
    required this.label,
    required this.color,
    required this.bg,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
      decoration: BoxDecoration(
        color:        bg,
        borderRadius: BorderRadius.circular(6),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 11, color: color),
          const SizedBox(width: 4),
          Text(
            label,
            style: Theme.of(context).textTheme.labelSmall?.copyWith(
              color:    color,
              fontSize: 10,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}

// ── Action Icon Button ────────────────────────────────────────

class _ActionIconButton extends StatelessWidget {
  final IconData     icon;
  final Color        color;
  final Color        bg;
  final String       tooltip;
  final VoidCallback onTap;

  const _ActionIconButton({
    required this.icon,
    required this.color,
    required this.bg,
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
            color:        bg,
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(icon, color: color, size: 18),
        ),
      ),
    );
  }
}

// ── Limit Note ────────────────────────────────────────────────

class _LimitNote extends StatelessWidget {
  final int current;
  final int max;
  const _LimitNote({required this.current, required this.max});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;
    final isFull    = current >= max;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: isFull
            ? AppColors.errorLight
            : AppColors.surfaceVariant,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: isFull
              ? AppColors.error.withAlpha(50)
              : AppColors.divider,
          width: 1,
        ),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            isFull
                ? Icons.warning_amber_rounded
                : Icons.info_outline_rounded,
            size:  16,
            color: isFull ? AppColors.error : AppColors.textTertiary,
          ),
          const SizedBox(width: 8),
          Text(
            isFull
                ? 'Vehicle limit reached. Delete one to add another.'
                : 'You can add up to $max vehicles.',
            style: textTheme.bodySmall?.copyWith(
              color: isFull
                  ? AppColors.error
                  : AppColors.textTertiary,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }
}

// ── Add Vehicle Bottom Bar ────────────────────────────────────

class _AddVehicleBar extends StatelessWidget {
  final bool         canAdd;
  final VoidCallback onTap;

  const _AddVehicleBar({required this.canAdd, required this.onTap});

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
          child: SizedBox(
            height: 52,
            child: FilledButton(
              onPressed: canAdd ? onTap : null,
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
                  const Icon(Icons.add_rounded, size: 20),
                  const SizedBox(width: 8),
                  Text(
                    canAdd ? 'Add New Vehicle' : 'Vehicle Limit Reached',
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

// ── Delete Confirmation Dialog ────────────────────────────────

class _DeleteDialog extends StatelessWidget {
  final VoidCallback onConfirm;
  const _DeleteDialog({required this.onConfirm});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return AlertDialog(
      backgroundColor:  AppColors.surface,
      surfaceTintColor: Colors.transparent,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(20),
      ),
      icon: Container(
        width:  56,
        height: 56,
        decoration: BoxDecoration(
          color:        AppColors.errorLight,
          borderRadius: BorderRadius.circular(16),
        ),
        child: const Icon(
          Icons.delete_outline_rounded,
          color: AppColors.error,
          size:  28,
        ),
      ),
      title: Text(
        'Remove Vehicle?',
        style: textTheme.titleMedium?.copyWith(
          color:      AppColors.textPrimary,
          fontWeight: FontWeight.w700,
        ),
        textAlign: TextAlign.center,
      ),
      content: Text(
        'This vehicle will be removed from your account. '
        'You can always add it back later.',
        style: textTheme.bodyMedium?.copyWith(
          color:  AppColors.textSecondary,
          height: 1.5,
        ),
        textAlign: TextAlign.center,
      ),
      actionsAlignment:      MainAxisAlignment.center,
      actionsPadding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
      actions: [
        Row(
          children: [
            Expanded(
              child: OutlinedButton(
                onPressed: () => Navigator.of(context).pop(),
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppColors.textSecondary,
                  side:            const BorderSide(color: AppColors.divider),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                ),
                child: const Text('Cancel'),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: FilledButton(
                onPressed: () {
                  Navigator.of(context).pop();
                  onConfirm();
                },
                style: FilledButton.styleFrom(
                  backgroundColor: AppColors.error,
                  foregroundColor: AppColors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                ),
                child: const Text('Remove'),
              ),
            ),
          ],
        ),
      ],
    );
  }
}

// ── Vehicle Form Sheet (Add / Edit) ───────────────────────────

class _VehicleFormSheet extends StatefulWidget {
  final _Vehicle? vehicle;
  final void Function(
    String number,
    String nickname,
    _VehicleType type,
    String color,
  ) onSave;

  const _VehicleFormSheet({
    required this.vehicle,
    required this.onSave,
  });

  @override
  State<_VehicleFormSheet> createState() => _VehicleFormSheetState();
}

class _VehicleFormSheetState extends State<_VehicleFormSheet> {
  final _formKey         = GlobalKey<FormState>();
  late final TextEditingController _numberCtrl;
  late final TextEditingController _nicknameCtrl;
  late final TextEditingController _colorCtrl;
  late _VehicleType _selectedType;

  @override
  void initState() {
    super.initState();
    _numberCtrl   = TextEditingController(text: widget.vehicle?.number   ?? '');
    _nicknameCtrl = TextEditingController(text: widget.vehicle?.nickname ?? '');
    _colorCtrl    = TextEditingController(text: widget.vehicle?.color    ?? '');
    _selectedType = widget.vehicle?.type ?? _VehicleType.car;
  }

  @override
  void dispose() {
    _numberCtrl.dispose();
    _nicknameCtrl.dispose();
    _colorCtrl.dispose();
    super.dispose();
  }

  void _save() {
    if (!(_formKey.currentState?.validate() ?? false)) return;
    widget.onSave(
      _numberCtrl.text.trim().toUpperCase(),
      _nicknameCtrl.text.trim(),
      _selectedType,
      _colorCtrl.text.trim(),
    );
    Navigator.of(context).pop();
  }

  @override
  Widget build(BuildContext context) {
    final textTheme    = Theme.of(context).textTheme;
    final bottomInsets = MediaQuery.viewInsetsOf(context).bottom;
    final isEdit       = widget.vehicle != null;

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 12),
      decoration: const BoxDecoration(
        color:        AppColors.surface,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: EdgeInsets.fromLTRB(20, 0, 20, bottomInsets + 16),
          child: Form(
            key: _formKey,
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
                Text(
                  isEdit ? 'Edit Vehicle' : 'Add New Vehicle',
                  style: textTheme.titleMedium?.copyWith(
                    color:      AppColors.textPrimary,
                    fontWeight: FontWeight.w700,
                  ),
                ),

                const SizedBox(height: 20),

                // Type selector
                Text(
                  'Vehicle Type',
                  style: textTheme.labelLarge?.copyWith(
                    color:      AppColors.textPrimary,
                    fontWeight: FontWeight.w600,
                    fontSize:   13,
                  ),
                ),
                const SizedBox(height: 10),
                Row(
                  children: _VehicleType.values.map((type) {
                    final selected = _selectedType == type;
                    return Expanded(
                      child: GestureDetector(
                        onTap: () => setState(() => _selectedType = type),
                        child: AnimatedContainer(
                          duration: const Duration(milliseconds: 150),
                          margin: EdgeInsets.only(
                            right: type != _VehicleType.ev ? 10 : 0,
                          ),
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          decoration: BoxDecoration(
                            color: selected
                                ? type.color
                                : type.bgColor,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(
                              color: selected
                                  ? type.color
                                  : type.color.withAlpha(40),
                              width: selected ? 0 : 1,
                            ),
                          ),
                          child: Column(
                            children: [
                              Icon(
                                type.icon,
                                color: selected
                                    ? AppColors.onPrimary
                                    : type.color,
                                size: 22,
                              ),
                              const SizedBox(height: 4),
                              Text(
                                type.label,
                                style: textTheme.labelSmall?.copyWith(
                                  color: selected
                                      ? AppColors.onPrimary
                                      : type.color,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    );
                  }).toList(),
                ),

                const SizedBox(height: 18),

                // Number field
                _SheetField(
                  controller: _numberCtrl,
                  label:      'Vehicle Number',
                  hint:       'e.g. DL 01 AB 1234',
                  icon:       Icons.pin_outlined,
                  caps:       TextCapitalization.characters,
                  validator: (v) {
                    if (v == null || v.trim().isEmpty) {
                      return 'Vehicle number is required.';
                    }
                    return null;
                  },
                ),

                const SizedBox(height: 14),

                // Nickname field
                _SheetField(
                  controller: _nicknameCtrl,
                  label:      'Nickname (optional)',
                  hint:       'e.g. My Swift, Office Bike',
                  icon:       Icons.label_outline_rounded,
                ),

                const SizedBox(height: 14),

                // Color field
                _SheetField(
                  controller: _colorCtrl,
                  label:      'Vehicle Colour (optional)',
                  hint:       'e.g. Pearl White',
                  icon:       Icons.palette_outlined,
                ),

                const SizedBox(height: 24),

                // Save button
                SizedBox(
                  height: 52,
                  child: FilledButton(
                    onPressed: _save,
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
                    ),
                    child: Text(isEdit ? 'Save Changes' : 'Add Vehicle'),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _SheetField extends StatelessWidget {
  final TextEditingController       controller;
  final String                      label;
  final String                      hint;
  final IconData                    icon;
  final String?                     Function(String?)? validator;
  final TextCapitalization          caps;

  const _SheetField({
    required this.controller,
    required this.label,
    required this.hint,
    required this.icon,
    this.validator,
    this.caps = TextCapitalization.words,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: textTheme.labelLarge?.copyWith(
            color:      AppColors.textPrimary,
            fontWeight: FontWeight.w600,
            fontSize:   13,
          ),
        ),
        const SizedBox(height: 8),
        TextFormField(
          controller:         controller,
          textCapitalization: caps,
          validator:          validator,
          style: textTheme.bodyMedium?.copyWith(color: AppColors.textPrimary),
          decoration: InputDecoration(
            hintText:   hint,
            prefixIcon: Icon(icon, color: AppColors.textSecondary, size: 20),
            hintStyle:  TextStyle(color: AppColors.textTertiary, fontSize: 14),
            filled:     true,
            fillColor:  AppColors.surfaceVariant,
            contentPadding: const EdgeInsets.symmetric(
              horizontal: 16,
              vertical:   14,
            ),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide:   const BorderSide(color: AppColors.divider, width: 1.5),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide:   const BorderSide(color: AppColors.divider, width: 1.5),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide:   const BorderSide(color: AppColors.primary, width: 2),
            ),
            errorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide:   const BorderSide(color: AppColors.error, width: 1.5),
            ),
            focusedErrorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide:   const BorderSide(color: AppColors.error, width: 2),
            ),
          ),
        ),
      ],
    );
  }
}