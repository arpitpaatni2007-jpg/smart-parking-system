import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_colors.dart';

// ============================================================
// ProfileScreen
// ============================================================
//
// Premium profile screen for the Smart Parking System.
//
// SECTIONS:
//   1. Custom AppBar       — back button + edit action
//   2. Profile Hero Card   — avatar + name + email + phone
//                            + membership badge
//   3. Account Menu Group  — Edit Profile, My Vehicles,
//                            My Bookings, Payment History
//   4. Preferences Group   — Notifications, Settings
//   5. Support Group       — Help & Support, Privacy Policy
//   6. Danger Zone         — Logout (red tinted card)
//
// DESIGN LANGUAGE:
//   Matches home_screen.dart exactly —
//     • AppColors.background scaffold
//     • White cards with subtle shadow + divider border
//     • Primary teal-navy (#0F3D56) brand color
//     • Electric green secondary (#2ECC71) for badges
//     • Amber accent (#F59E0B) for membership star
//     • 16 px card radius, 20 px horizontal padding
//     • BouncingScrollPhysics on the scroll view
//
// ARCHITECTURE:
//   StatefulWidget shell only for controlling the bottom nav
//   indicator. All data is local dummy constants.
//   No Riverpod / Bloc / Provider / API.
// ============================================================

// ── Dummy user data ──────────────────────────────────────────

const _kUserName      = 'Rahul Sharma';
const _kUserEmail     = 'rahul.sharma@gmail.com';
const _kUserPhone     = '+91 98765 43210';
const _kMemberBadge   = 'Premium Member';
const _kAvatarInitials= 'RS';
const _kTotalBookings = 24;
const _kTotalSaved    = '₹1,240';

// ── Entry Point ───────────────────────────────────────────────

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  int _selectedNavIndex = 3; // Profile tab active

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
      resizeToAvoidBottomInset: false,
      body: SafeArea(
        child: _ProfileBody(
          onEditTap: () {},
        ),
      ),
      bottomNavigationBar: _BottomNavBar(
        selectedIndex: _selectedNavIndex,
        onTap: (i) => setState(() => _selectedNavIndex = i),
      ),
    );
  }
}

// ── Profile Body ──────────────────────────────────────────────

class _ProfileBody extends StatelessWidget {
  final VoidCallback onEditTap;
  const _ProfileBody({required this.onEditTap});

  @override
  Widget build(BuildContext context) {
    final w    = MediaQuery.sizeOf(context).width;
    final hPad = w > 600 ? w * 0.08 : 20.0;

    return CustomScrollView(
      physics: const BouncingScrollPhysics(),
      slivers: [

        // ── 1. Top Bar ──────────────────────────────────────
        SliverToBoxAdapter(
          child: _TopBar(hPad: hPad, onEditTap: onEditTap),
        ),

        // ── 2. Profile Hero Card ────────────────────────────
        SliverToBoxAdapter(
          child: Padding(
            padding: EdgeInsets.fromLTRB(hPad, 20, hPad, 0),
            child: const _ProfileHeroCard(),
          ),
        ),

        // ── 3. Stats Row ────────────────────────────────────
        SliverToBoxAdapter(
          child: Padding(
            padding: EdgeInsets.fromLTRB(hPad, 16, hPad, 0),
            child: const _StatsRow(),
          ),
        ),

        // ── 4. Account Group ────────────────────────────────
        SliverToBoxAdapter(
          child: _MenuGroup(
            hPad:  hPad,
            label: 'Account',
            items: [
              _MenuItem(
                icon:     Icons.person_outline_rounded,
                iconColor: AppColors.primary,
                iconBg:    AppColors.primary.withAlpha(15),
                title:    'Edit Profile',
                subtitle: 'Update your personal information',
                onTap:    () {},
              ),
              _MenuItem(
                icon:     Icons.directions_car_outlined,
                iconColor: const Color(0xFF1565C0),
                iconBg:    const Color(0xFF1565C0).withAlpha(15),
                title:    'My Vehicles',
                subtitle: 'Manage your registered vehicles',
                onTap:    () {},
              ),
              _MenuItem(
                icon:     Icons.bookmark_outline_rounded,
                iconColor: AppColors.secondary,
                iconBg:    AppColors.secondary.withAlpha(20),
                title:    'My Bookings',
                subtitle: 'View all parking reservations',
                onTap:    () {},
              ),
              _MenuItem(
                icon:     Icons.credit_card_rounded,
                iconColor: AppColors.accent,
                iconBg:    AppColors.accent.withAlpha(20),
                title:    'Payment History',
                subtitle: 'Transactions & receipts',
                onTap:    () {},
                isLast:   true,
              ),
            ],
          ),
        ),

        // ── 5. Preferences Group ────────────────────────────
        SliverToBoxAdapter(
          child: _MenuGroup(
            hPad:  hPad,
            label: 'Preferences',
            items: [
              _MenuItem(
                icon:     Icons.notifications_outlined,
                iconColor: const Color(0xFF7B1FA2),
                iconBg:    const Color(0xFF7B1FA2).withAlpha(15),
                title:    'Notifications',
                subtitle: 'Manage alerts and push notifications',
                onTap:    () {},
                trailing: _ToggleBadge(),
              ),
              _MenuItem(
                icon:     Icons.settings_outlined,
                iconColor: AppColors.textSecondary,
                iconBg:    AppColors.surfaceVariant,
                title:    'Settings',
                subtitle: 'App preferences and display',
                onTap:    () {},
                isLast:   true,
              ),
            ],
          ),
        ),

        // ── 6. Support Group ─────────────────────────────────
        SliverToBoxAdapter(
          child: _MenuGroup(
            hPad:  hPad,
            label: 'Support',
            items: [
              _MenuItem(
                icon:     Icons.help_outline_rounded,
                iconColor: AppColors.info,
                iconBg:    AppColors.infoLight,
                title:    'Help & Support',
                subtitle: 'FAQs, contact us and live chat',
                onTap:    () {},
              ),
              _MenuItem(
                icon:     Icons.privacy_tip_outlined,
                iconColor: const Color(0xFF00695C),
                iconBg:    const Color(0xFF00695C).withAlpha(15),
                title:    'Privacy Policy',
                subtitle: 'How we protect your data',
                onTap:    () {},
                isLast:   true,
              ),
            ],
          ),
        ),

        // ── 7. Logout ────────────────────────────────────────
        SliverToBoxAdapter(
          child: Padding(
            padding: EdgeInsets.fromLTRB(hPad, 8, hPad, 0),
            child: const _LogoutCard(),
          ),
        ),

        // ── App Version ──────────────────────────────────────
        const SliverToBoxAdapter(
          child: Padding(
            padding: EdgeInsets.only(top: 20, bottom: 8),
            child:   _AppVersionLabel(),
          ),
        ),

        const SliverToBoxAdapter(child: SizedBox(height: 24)),
      ],
    );
  }
}

// ── 1. Top Bar ────────────────────────────────────────────────

class _TopBar extends StatelessWidget {
  final double       hPad;
  final VoidCallback onEditTap;
  const _TopBar({required this.hPad, required this.onEditTap});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Padding(
      padding: EdgeInsets.fromLTRB(hPad, 16, hPad, 0),
      child: Row(
        children: [
          // Back button
          _CircleIconButton(
            icon:  Icons.arrow_back_ios_new_rounded,
            onTap: () => Navigator.maybePop(context),
          ),

          const SizedBox(width: 12),

          Expanded(
            child: Text(
              'My Profile',
              style: textTheme.titleLarge?.copyWith(
                color:      AppColors.textPrimary,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),

          // Edit icon button
          _CircleIconButton(
            icon:  Icons.edit_outlined,
            onTap: onEditTap,
          ),
        ],
      ),
    );
  }
}

// ── 2. Profile Hero Card ──────────────────────────────────────

class _ProfileHeroCard extends StatelessWidget {
  const _ProfileHeroCard();

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      padding:      const EdgeInsets.all(24),
      decoration:   _cardDecoration(),
      child: Column(
        children: [

          // ── Avatar ─────────────────────────────────────────
          Stack(
            alignment: Alignment.bottomRight,
            children: [
              // Outer ring
              Container(
                width:  96,
                height: 96,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: LinearGradient(
                    colors: [
                      AppColors.primary,
                      AppColors.primaryLighter,
                    ],
                    begin: Alignment.topLeft,
                    end:   Alignment.bottomRight,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color:      AppColors.primary.withAlpha(60),
                      blurRadius: 16,
                      offset:     const Offset(0, 6),
                    ),
                  ],
                ),
                child: Center(
                  child: Text(
                    _kAvatarInitials,
                    style: textTheme.headlineMedium?.copyWith(
                      color:      AppColors.onPrimary,
                      fontWeight: FontWeight.w800,
                      letterSpacing: 1,
                    ),
                  ),
                ),
              ),

              // Online indicator
              Container(
                width:  22,
                height: 22,
                margin: const EdgeInsets.only(bottom: 4, right: 4),
                decoration: BoxDecoration(
                  color:  AppColors.secondary,
                  shape:  BoxShape.circle,
                  border: Border.all(
                    color: AppColors.surface,
                    width: 3,
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 16),

          // ── Name ───────────────────────────────────────────
          Text(
            _kUserName,
            style: textTheme.headlineSmall?.copyWith(
              color:      AppColors.textPrimary,
              fontWeight: FontWeight.w800,
            ),
          ),

          const SizedBox(height: 6),

          // ── Email ──────────────────────────────────────────
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(
                Icons.mail_outline_rounded,
                color: AppColors.textSecondary,
                size:  14,
              ),
              const SizedBox(width: 5),
              Text(
                _kUserEmail,
                style: textTheme.bodySmall?.copyWith(
                  color: AppColors.textSecondary,
                ),
              ),
            ],
          ),

          const SizedBox(height: 4),

          // ── Phone ──────────────────────────────────────────
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(
                Icons.phone_outlined,
                color: AppColors.textSecondary,
                size:  14,
              ),
              const SizedBox(width: 5),
              Text(
                _kUserPhone,
                style: textTheme.bodySmall?.copyWith(
                  color: AppColors.textSecondary,
                ),
              ),
            ],
          ),

          const SizedBox(height: 16),

          // ── Membership Badge ───────────────────────────────
          Container(
            padding: const EdgeInsets.symmetric(
              horizontal: 16,
              vertical:    7,
            ),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [
                  AppColors.accent,
                  const Color(0xFFD97706),
                ],
                begin: Alignment.topLeft,
                end:   Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(999),
              boxShadow: [
                BoxShadow(
                  color:      AppColors.accent.withAlpha(60),
                  blurRadius: 10,
                  offset:     const Offset(0, 3),
                ),
              ],
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(
                  Icons.workspace_premium_rounded,
                  color: Colors.white,
                  size:  15,
                ),
                const SizedBox(width: 6),
                Text(
                  _kMemberBadge,
                  style: textTheme.labelMedium?.copyWith(
                    color:       Colors.white,
                    fontWeight:  FontWeight.w700,
                    letterSpacing: 0.3,
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

// ── 3. Stats Row ──────────────────────────────────────────────

class _StatsRow extends StatelessWidget {
  const _StatsRow();

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: _StatCard(
            icon:    Icons.bookmark_rounded,
            iconBg:  AppColors.secondary.withAlpha(20),
            iconColor: AppColors.secondary,
            label:   'Total Bookings',
            value:   '$_kTotalBookings',
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _StatCard(
            icon:    Icons.savings_outlined,
            iconBg:  AppColors.accent.withAlpha(20),
            iconColor: AppColors.accent,
            label:   'Total Saved',
            value:   _kTotalSaved,
          ),
        ),
      ],
    );
  }
}

class _StatCard extends StatelessWidget {
  final IconData icon;
  final Color    iconBg;
  final Color    iconColor;
  final String   label;
  final String   value;

  const _StatCard({
    required this.icon,
    required this.iconBg,
    required this.iconColor,
    required this.label,
    required this.value,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      padding:    const EdgeInsets.all(16),
      decoration: _cardDecoration(),
      child: Row(
        children: [
          Container(
            width:  42,
            height: 42,
            decoration: BoxDecoration(
              color:        iconBg,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: iconColor, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  value,
                  style: textTheme.titleMedium?.copyWith(
                    color:      AppColors.textPrimary,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                Text(
                  label,
                  style: textTheme.bodySmall?.copyWith(
                    color:    AppColors.textSecondary,
                    fontSize: 11,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ── 4. Menu Group ─────────────────────────────────────────────

class _MenuGroup extends StatelessWidget {
  final double          hPad;
  final String          label;
  final List<_MenuItem> items;

  const _MenuGroup({
    required this.hPad,
    required this.label,
    required this.items,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Padding(
      padding: EdgeInsets.fromLTRB(hPad, 20, hPad, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Section label
          Padding(
            padding: const EdgeInsets.only(left: 4, bottom: 10),
            child: Text(
              label.toUpperCase(),
              style: textTheme.labelSmall?.copyWith(
                color:         AppColors.textTertiary,
                fontWeight:    FontWeight.w700,
                letterSpacing: 1.2,
                fontSize:      10.5,
              ),
            ),
          ),

          // Card container holding all items
          Container(
            decoration: _cardDecoration(),
            clipBehavior: Clip.antiAlias,
            child: Column(
              children: items,
            ),
          ),
        ],
      ),
    );
  }
}

// ── Menu Item ────────────────────────────────────────────────

class _MenuItem extends StatelessWidget {
  final IconData    icon;
  final Color       iconColor;
  final Color       iconBg;
  final String      title;
  final String      subtitle;
  final VoidCallback onTap;
  final bool        isLast;
  final Widget?     trailing;

  const _MenuItem({
    required this.icon,
    required this.iconColor,
    required this.iconBg,
    required this.title,
    required this.subtitle,
    required this.onTap,
    this.isLast  = false,
    this.trailing,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        InkWell(
          onTap:       onTap,
          splashColor: AppColors.primary.withAlpha(8),
          highlightColor: AppColors.primary.withAlpha(5),
          child: Padding(
            padding: const EdgeInsets.symmetric(
              horizontal: 16,
              vertical:   14,
            ),
            child: Row(
              children: [

                // ── Leading Icon ────────────────────────────
                Container(
                  width:  46,
                  height: 46,
                  decoration: BoxDecoration(
                    color:        iconBg,
                    borderRadius: BorderRadius.circular(13),
                  ),
                  child: Icon(icon, color: iconColor, size: 22),
                ),

                const SizedBox(width: 14),

                // ── Title + Subtitle ─────────────────────────
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: textTheme.titleSmall?.copyWith(
                          color:      AppColors.textPrimary,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        subtitle,
                        style: textTheme.bodySmall?.copyWith(
                          color:    AppColors.textSecondary,
                          fontSize: 12,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),

                const SizedBox(width: 8),

                // ── Trailing ────────────────────────────────
                trailing ??
                    Icon(
                      Icons.chevron_right_rounded,
                      color: AppColors.textTertiary,
                      size:  20,
                    ),
              ],
            ),
          ),
        ),

        // Divider between items (hidden on last item)
        if (!isLast)
          Padding(
            padding: const EdgeInsets.only(left: 76),
            child:   Divider(
              height:    1,
              thickness: 1,
              color:     AppColors.divider,
            ),
          ),
      ],
    );
  }
}

// ── Toggle Badge (for Notifications row) ─────────────────────

class _ToggleBadge extends StatefulWidget {
  @override
  State<_ToggleBadge> createState() => _ToggleBadgeState();
}

class _ToggleBadgeState extends State<_ToggleBadge> {
  bool _enabled = true;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => setState(() => _enabled = !_enabled),
      child: AnimatedContainer(
        duration:    const Duration(milliseconds: 220),
        curve:       Curves.easeInOut,
        width:       46,
        height:      26,
        padding:     const EdgeInsets.all(3),
        decoration:  BoxDecoration(
          color:        _enabled ? AppColors.secondary : AppColors.divider,
          borderRadius: BorderRadius.circular(999),
        ),
        child: AnimatedAlign(
          duration:  const Duration(milliseconds: 220),
          curve:     Curves.easeInOut,
          alignment: _enabled
              ? Alignment.centerRight
              : Alignment.centerLeft,
          child: Container(
            width:      20,
            height:     20,
            decoration: const BoxDecoration(
              color: Colors.white,
              shape: BoxShape.circle,
            ),
          ),
        ),
      ),
    );
  }
}

// ── 7. Logout Card ────────────────────────────────────────────

class _LogoutCard extends StatelessWidget {
  const _LogoutCard();

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return InkWell(
      onTap: () => _showLogoutDialog(context),
      borderRadius: BorderRadius.circular(16),
      splashColor: AppColors.error.withAlpha(10),
      child: Container(
        padding:    const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color:        AppColors.error.withAlpha(8),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: AppColors.error.withAlpha(40),
            width: 1,
          ),
        ),
        child: Row(
          children: [
            // Icon box
            Container(
              width:  46,
              height: 46,
              decoration: BoxDecoration(
                color:        AppColors.error.withAlpha(15),
                borderRadius: BorderRadius.circular(13),
              ),
              child: const Icon(
                Icons.logout_rounded,
                color: AppColors.error,
                size:  22,
              ),
            ),

            const SizedBox(width: 14),

            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Logout',
                    style: textTheme.titleSmall?.copyWith(
                      color:      AppColors.error,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    'Sign out of your account',
                    style: textTheme.bodySmall?.copyWith(
                      color:    AppColors.error.withAlpha(160),
                      fontSize: 12,
                    ),
                  ),
                ],
              ),
            ),

            Icon(
              Icons.chevron_right_rounded,
              color: AppColors.error.withAlpha(150),
              size:  20,
            ),
          ],
        ),
      ),
    );
  }

  void _showLogoutDialog(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    showDialog<void>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(20),
        ),
        backgroundColor: AppColors.surface,
        title: Row(
          children: [
            Container(
              width:  40,
              height: 40,
              decoration: BoxDecoration(
                color:        AppColors.error.withAlpha(15),
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Icon(
                Icons.logout_rounded,
                color: AppColors.error,
                size:  20,
              ),
            ),
            const SizedBox(width: 12),
            Text(
              'Logout',
              style: textTheme.titleLarge?.copyWith(
                color:      AppColors.textPrimary,
                fontWeight: FontWeight.w700,
              ),
            ),
          ],
        ),
        content: Text(
          'Are you sure you want to logout from your account?',
          style: textTheme.bodyMedium?.copyWith(
            color: AppColors.textSecondary,
            height: 1.5,
          ),
        ),
        actionsPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
        actions: [
          // Cancel
          OutlinedButton(
            onPressed: () => Navigator.pop(ctx),
            style: OutlinedButton.styleFrom(
              foregroundColor: AppColors.textSecondary,
              side: const BorderSide(color: AppColors.divider),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              minimumSize: const Size(0, 46),
              padding: const EdgeInsets.symmetric(horizontal: 20),
            ),
            child: const Text('Cancel'),
          ),
          const SizedBox(width: 8),
          // Confirm Logout
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.error,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              minimumSize: const Size(0, 46),
              elevation:    0,
              padding: const EdgeInsets.symmetric(horizontal: 24),
            ),
            child: const Text(
              'Logout',
              style: TextStyle(fontWeight: FontWeight.w700),
            ),
          ),
        ],
      ),
    );
  }
}

// ── App Version Label ─────────────────────────────────────────

class _AppVersionLabel extends StatelessWidget {
  const _AppVersionLabel();

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;
    return Center(
      child: Text(
        'Smart Parking v1.0.0',
        style: textTheme.bodySmall?.copyWith(
          color:    AppColors.textTertiary,
          fontSize: 11,
        ),
      ),
    );
  }
}

// ── Bottom Navigation Bar ────────────────────────────────────
// Exact copy of home_screen.dart's _BottomNavBar to maintain
// design consistency across all screens.

class _BottomNavBar extends StatelessWidget {
  final int               selectedIndex;
  final ValueChanged<int> onTap;

  const _BottomNavBar({
    required this.selectedIndex,
    required this.onTap,
  });

  static const _items = [
    (icon: Icons.home_outlined,          activeIcon: Icons.home_rounded,         label: 'Home'),
    (icon: Icons.bookmark_outline,       activeIcon: Icons.bookmark_rounded,      label: 'Bookings'),
    (icon: Icons.notifications_outlined, activeIcon: Icons.notifications_rounded, label: 'Alerts'),
    (icon: Icons.person_outline_rounded, activeIcon: Icons.person_rounded,        label: 'Profile'),
  ];

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        boxShadow: [
          BoxShadow(
            color:      AppColors.shadow,
            blurRadius: 16,
            offset:     const Offset(0, -4),
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
                onTap:    () => onTap(index),
                behavior: HitTestBehavior.opaque,
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  padding:  const EdgeInsets.symmetric(
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

// ── Shared Helpers ────────────────────────────────────────────

/// Circular icon button used in the top bar.
class _CircleIconButton extends StatelessWidget {
  final IconData     icon;
  final VoidCallback onTap;

  const _CircleIconButton({
    required this.icon,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width:  40,
        height: 40,
        decoration: BoxDecoration(
          color:        AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.divider, width: 1),
          boxShadow: [
            BoxShadow(
              color:      AppColors.shadow,
              blurRadius: 6,
              offset:     const Offset(0, 2),
            ),
          ],
        ),
        child: Icon(icon, color: AppColors.textPrimary, size: 18),
      ),
    );
  }
}

/// Shared card decoration — white surface, 16 px radius,
/// subtle shadow and divider border. Mirrors home_screen.dart.
BoxDecoration _cardDecoration() {
  return BoxDecoration(
    color:        AppColors.surface,
    borderRadius: BorderRadius.circular(16),
    border: Border.all(color: AppColors.divider, width: 0.8),
    boxShadow: [
      BoxShadow(
        color:      AppColors.shadow,
        blurRadius: 10,
        spreadRadius: 0,
        offset:     const Offset(0, 3),
      ),
    ],
  );
}