import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_colors.dart';

// ============================================================
// SettingsScreen
// ============================================================
//
// Premium Settings screen for the Smart Parking app.
//
// SECTIONS:
//   1. AppBar          — "Settings" title
//   2. Profile header  — avatar, name, email, edit button
//   3. General         — Dark Mode / Notifications / Language / Location
//   4. Parking         — Default Vehicle / Payment / Auto-extend
//   5. Account         — Change Password / Privacy / Terms
//   6. Support         — Help / Contact / About
//   7. Logout button   — with confirmation dialog
//
// STATE:
//   _darkMode        — dark mode toggle
//   _notifications   — push notifications toggle
//   _autoExtend      — auto-extend booking toggle
//   _selectedLang    — current app language
//
// ARCHITECTURE:
//   Pure StatefulWidget — no Riverpod, Bloc, Provider.
// ============================================================

// ── Language options ──────────────────────────────────────────

const List<String> _languages = [
  'English',
  'Hindi',
  'Tamil',
  'Telugu',
  'Marathi',
  'Bengali',
];

// ── Screen ────────────────────────────────────────────────────

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  // ── Toggle states ─────────────────────────────────────────
  bool   _darkMode       = false;
  bool   _notifications  = true;
  bool   _autoExtend     = false;
  String _selectedLang   = 'English';

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

  // ── Actions ───────────────────────────────────────────────

  void _showLanguagePicker() {
    showModalBottomSheet(
      context:            context,
      backgroundColor:    Colors.transparent,
      isScrollControlled: true,
      builder: (_) => _LanguageSheet(
        selected:  _selectedLang,
        languages: _languages,
        onSelect:  (lang) => setState(() => _selectedLang = lang),
      ),
    );
  }

  void _showLogoutDialog() {
    showDialog(
      context: context,
      builder: (_) => _LogoutDialog(
        onConfirm: () {
          // TODO: clear session and navigate to login
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

  // ── Build ─────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.sizeOf(context).width;
    final hPad        = screenWidth > 600 ? screenWidth * 0.08 : 16.0;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar:          _buildAppBar(context),
      body: ListView(
        padding: EdgeInsets.fromLTRB(hPad, 16, hPad, 32),
        children: [

          // ── Profile header ─────────────────────────────
          _ProfileHeader(),

          const SizedBox(height: 24),

          // ── General ────────────────────────────────────
          _SectionCard(
            title: 'General',
            icon:  Icons.tune_rounded,
            children: [
              _SwitchTile(
                icon:     Icons.dark_mode_outlined,
                iconBg:   AppColors.primaryDark.withAlpha(20),
                iconColor: AppColors.primaryDark,
                title:    'Dark Mode',
                subtitle: 'Switch app appearance',
                value:    _darkMode,
                onChanged: (v) => setState(() => _darkMode = v),
              ),
              _DividerLine(),
              _SwitchTile(
                icon:     Icons.notifications_outlined,
                iconBg:   AppColors.infoLight,
                iconColor: AppColors.info,
                title:    'Push Notifications',
                subtitle: 'Bookings, payments & offers',
                value:    _notifications,
                onChanged: (v) => setState(() => _notifications = v),
              ),
              _DividerLine(),
              _NavTile(
                icon:     Icons.language_outlined,
                iconBg:   AppColors.successLight,
                iconColor: AppColors.secondaryDark,
                title:    'Language',
                trailing: _selectedLang,
                onTap:    _showLanguagePicker,
              ),
              _DividerLine(),
              _NavTile(
                icon:      Icons.location_on_outlined,
                iconBg:    AppColors.warningLight,
                iconColor: AppColors.accent,
                title:     'Location Permission',
                trailing:  'Allowed',
                trailingColor: AppColors.secondaryDark,
                onTap: () => _showSnack('Opening location settings…'),
              ),
            ],
          ),

          const SizedBox(height: 16),

          // ── Parking ─────────────────────────────────────
          _SectionCard(
            title: 'Parking',
            icon:  Icons.local_parking_rounded,
            children: [
              _NavTile(
                icon:     Icons.directions_car_outlined,
                iconBg:   AppColors.primary.withAlpha(14),
                iconColor: AppColors.primary,
                title:    'Default Vehicle',
                trailing: 'DL 01 AB 1234',
                onTap:    () => _showSnack('Opening vehicle settings…'),
              ),
              _DividerLine(),
              _NavTile(
                icon:     Icons.credit_card_rounded,
                iconBg:   AppColors.infoLight,
                iconColor: AppColors.info,
                title:    'Default Payment Method',
                trailing: 'GPay · UPI',
                onTap:    () => _showSnack('Opening payment settings…'),
              ),
              _DividerLine(),
              _SwitchTile(
                icon:     Icons.more_time_rounded,
                iconBg:   AppColors.primary.withAlpha(14),
                iconColor: AppColors.primaryLight,
                title:    'Auto-extend Booking',
                subtitle: 'Extend if slot is available',
                value:    _autoExtend,
                onChanged: (v) => setState(() => _autoExtend = v),
              ),
            ],
          ),

          const SizedBox(height: 16),

          // ── Account ─────────────────────────────────────
          _SectionCard(
            title: 'Account',
            icon:  Icons.manage_accounts_rounded,
            children: [
              _NavTile(
                icon:     Icons.lock_outline_rounded,
                iconBg:   AppColors.warningLight,
                iconColor: AppColors.accent,
                title:    'Change Password',
                onTap:    () => _showSnack('Opening change password…'),
              ),
              _DividerLine(),
              _NavTile(
                icon:     Icons.privacy_tip_outlined,
                iconBg:   AppColors.infoLight,
                iconColor: AppColors.info,
                title:    'Privacy Policy',
                onTap:    () => _showSnack('Opening privacy policy…'),
              ),
              _DividerLine(),
              _NavTile(
                icon:     Icons.description_outlined,
                iconBg:   AppColors.surfaceVariant,
                iconColor: AppColors.textSecondary,
                title:    'Terms & Conditions',
                onTap:    () => _showSnack('Opening terms…'),
              ),
            ],
          ),

          const SizedBox(height: 16),

          // ── Support ──────────────────────────────────────
          _SectionCard(
            title: 'Support',
            icon:  Icons.support_agent_rounded,
            children: [
              _NavTile(
                icon:     Icons.help_outline_rounded,
                iconBg:   AppColors.successLight,
                iconColor: AppColors.secondaryDark,
                title:    'Help Center',
                onTap:    () => _showSnack('Opening help center…'),
              ),
              _DividerLine(),
              _NavTile(
                icon:     Icons.chat_bubble_outline_rounded,
                iconBg:   AppColors.infoLight,
                iconColor: AppColors.info,
                title:    'Contact Us',
                onTap:    () => _showSnack('Opening contact…'),
              ),
              _DividerLine(),
              _NavTile(
                icon:     Icons.info_outline_rounded,
                iconBg:   AppColors.primary.withAlpha(14),
                iconColor: AppColors.primary,
                title:    'About App',
                trailing: 'v1.0.0',
                onTap:    () => _showAboutSheet(context),
              ),
            ],
          ),

          const SizedBox(height: 28),

          // ── Logout button ────────────────────────────────
          _LogoutButton(onTap: _showLogoutDialog),

          const SizedBox(height: 12),

          // ── App version note ─────────────────────────────
          Center(
            child: Text(
              'Smart Parking · Version 1.0.0',
              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                color: AppColors.textTertiary,
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
        'Settings',
        style: textTheme.titleLarge?.copyWith(
          color:      AppColors.textPrimary,
          fontWeight: FontWeight.w700,
        ),
      ),
      centerTitle: true,
      bottom: PreferredSize(
        preferredSize: const Size.fromHeight(1),
        child:         Divider(height: 1, color: AppColors.divider),
      ),
    );
  }

  void _showAboutSheet(BuildContext context) {
    showModalBottomSheet(
      context:         context,
      backgroundColor: Colors.transparent,
      builder: (_) => const _AboutSheet(),
    );
  }
}

// ── Profile Header ────────────────────────────────────────────

class _ProfileHeader extends StatelessWidget {
  const _ProfileHeader();

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

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
            color:       AppColors.primary.withAlpha(65),
            blurRadius:  18,
            spreadRadius: 0,
            offset:      const Offset(0, 6),
          ),
        ],
      ),
      child: Row(
        children: [

          // Avatar
          Container(
            width:  60,
            height: 60,
            decoration: BoxDecoration(
              color:        AppColors.onPrimary.withAlpha(25),
              shape:        BoxShape.circle,
              border: Border.all(
                color: AppColors.onPrimary.withAlpha(50),
                width: 2,
              ),
            ),
            child: const Center(
              child: Text(
                'A',
                style: TextStyle(
                  color:      AppColors.onPrimary,
                  fontSize:   26,
                  fontWeight: FontWeight.w800,
                  height:     1,
                ),
              ),
            ),
          ),

          const SizedBox(width: 14),

          // Name + email
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Arpit Sharma',
                  style: textTheme.titleMedium?.copyWith(
                    color:      AppColors.onPrimary,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  'arpit.sharma@email.com',
                  style: textTheme.bodySmall?.copyWith(
                    color: AppColors.onPrimary.withAlpha(190),
                  ),
                ),
                const SizedBox(height: 6),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical:   3,
                  ),
                  decoration: BoxDecoration(
                    color:        AppColors.onPrimary.withAlpha(25),
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(
                      color: AppColors.onPrimary.withAlpha(40),
                      width: 1,
                    ),
                  ),
                  child: Text(
                    'Premium Member',
                    style: textTheme.labelSmall?.copyWith(
                      color:      AppColors.onPrimary,
                      fontWeight: FontWeight.w700,
                      fontSize:   10,
                    ),
                  ),
                ),
              ],
            ),
          ),

          // Edit button
          GestureDetector(
            onTap: () {},
            child: Container(
              width:  38,
              height: 38,
              decoration: BoxDecoration(
                color:        AppColors.onPrimary.withAlpha(25),
                borderRadius: BorderRadius.circular(11),
                border: Border.all(
                  color: AppColors.onPrimary.withAlpha(40),
                  width: 1,
                ),
              ),
              child: const Icon(
                Icons.edit_outlined,
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

// ── Section Card ──────────────────────────────────────────────

class _SectionCard extends StatelessWidget {
  final String       title;
  final IconData     icon;
  final List<Widget> children;

  const _SectionCard({
    required this.title,
    required this.icon,
    required this.children,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [

        // Section label
        Padding(
          padding: const EdgeInsets.only(left: 4, bottom: 10),
          child: Row(
            children: [
              Icon(icon, color: AppColors.primary, size: 16),
              const SizedBox(width: 7),
              Text(
                title,
                style: textTheme.titleSmall?.copyWith(
                  color:      AppColors.textPrimary,
                  fontWeight: FontWeight.w700,
                  fontSize:   13,
                ),
              ),
            ],
          ),
        ),

        // Card
        Container(
          decoration: BoxDecoration(
            color:        AppColors.surface,
            borderRadius: BorderRadius.circular(18),
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
          child: Column(children: children),
        ),
      ],
    );
  }
}

// ── Divider ───────────────────────────────────────────────────

class _DividerLine extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Divider(
      height:    1,
      color:     AppColors.divider,
      indent:    62,
      endIndent: 0,
    );
  }
}

// ── Switch Tile ───────────────────────────────────────────────

class _SwitchTile extends StatelessWidget {
  final IconData           icon;
  final Color              iconBg;
  final Color              iconColor;
  final String             title;
  final String?            subtitle;
  final bool               value;
  final ValueChanged<bool> onChanged;

  const _SwitchTile({
    required this.icon,
    required this.iconBg,
    required this.iconColor,
    required this.title,
    this.subtitle,
    required this.value,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Row(
        children: [

          // Icon
          _TileIcon(icon: icon, bg: iconBg, color: iconColor),

          const SizedBox(width: 14),

          // Text
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: textTheme.bodyMedium?.copyWith(
                    color:      AppColors.textPrimary,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                if (subtitle != null)
                  Text(
                    subtitle!,
                    style: textTheme.bodySmall?.copyWith(
                      color: AppColors.textTertiary,
                    ),
                  ),
              ],
            ),
          ),

          // Switch
          Transform.scale(
            scale: 0.85,
            child: Switch(
              value:     value,
              onChanged: onChanged,
              activeColor:       AppColors.onPrimary,
              activeTrackColor:  AppColors.primary,
              inactiveThumbColor: AppColors.textDisabled,
              inactiveTrackColor: AppColors.surfaceVariant,
              trackOutlineColor: WidgetStateProperty.resolveWith((states) {
                if (states.contains(WidgetState.selected)) {
                  return Colors.transparent;
                }
                return AppColors.divider;
              }),
            ),
          ),
        ],
      ),
    );
  }
}

// ── Nav Tile ──────────────────────────────────────────────────

class _NavTile extends StatelessWidget {
  final IconData     icon;
  final Color        iconBg;
  final Color        iconColor;
  final String       title;
  final String?      subtitle;
  final String?      trailing;
  final Color?       trailingColor;
  final VoidCallback onTap;

  const _NavTile({
    required this.icon,
    required this.iconBg,
    required this.iconColor,
    required this.title,
    this.subtitle,
    this.trailing,
    this.trailingColor,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return InkWell(
      onTap:        onTap,
      borderRadius: BorderRadius.circular(18),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 13),
        child: Row(
          children: [

            // Icon
            _TileIcon(icon: icon, bg: iconBg, color: iconColor),

            const SizedBox(width: 14),

            // Text
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: textTheme.bodyMedium?.copyWith(
                      color:      AppColors.textPrimary,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  if (subtitle != null)
                    Text(
                      subtitle!,
                      style: textTheme.bodySmall?.copyWith(
                        color: AppColors.textTertiary,
                      ),
                    ),
                ],
              ),
            ),

            // Trailing
            if (trailing != null) ...[
              Text(
                trailing!,
                style: textTheme.bodySmall?.copyWith(
                  color:      trailingColor ?? AppColors.textTertiary,
                  fontWeight: FontWeight.w600,
                ),
              ),
              const SizedBox(width: 4),
            ],

            Icon(
              Icons.chevron_right_rounded,
              color: AppColors.textTertiary,
              size:  18,
            ),
          ],
        ),
      ),
    );
  }
}

// ── Tile Icon ─────────────────────────────────────────────────

class _TileIcon extends StatelessWidget {
  final IconData icon;
  final Color    bg;
  final Color    color;

  const _TileIcon({
    required this.icon,
    required this.bg,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width:  38,
      height: 38,
      decoration: BoxDecoration(
        color:        bg,
        borderRadius: BorderRadius.circular(11),
      ),
      child: Icon(icon, color: color, size: 19),
    );
  }
}

// ── Logout Button ─────────────────────────────────────────────

class _LogoutButton extends StatelessWidget {
  final VoidCallback onTap;
  const _LogoutButton({required this.onTap});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return SizedBox(
      height: 54,
      child: OutlinedButton(
        onPressed: onTap,
        style: OutlinedButton.styleFrom(
          foregroundColor: AppColors.error,
          side: const BorderSide(color: AppColors.error, width: 1.5),
          backgroundColor: AppColors.errorLight,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          textStyle: textTheme.labelLarge?.copyWith(
            fontSize:      16,
            fontWeight:    FontWeight.w700,
            letterSpacing: 0.3,
          ),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.logout_rounded, size: 20),
            const SizedBox(width: 10),
            const Text('Logout'),
          ],
        ),
      ),
    );
  }
}

// ── Language Bottom Sheet ─────────────────────────────────────

class _LanguageSheet extends StatelessWidget {
  final String                selected;
  final List<String>          languages;
  final ValueChanged<String>  onSelect;

  const _LanguageSheet({
    required this.selected,
    required this.languages,
    required this.onSelect,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 12),
      decoration: const BoxDecoration(
        color:        AppColors.surface,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
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
              Row(
                children: [
                  const _TileIcon(
                    icon:  Icons.language_outlined,
                    bg:    AppColors.successLight,
                    color: AppColors.secondaryDark,
                  ),
                  const SizedBox(width: 12),
                  Text(
                    'Select Language',
                    style: textTheme.titleMedium?.copyWith(
                      color:      AppColors.textPrimary,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 16),

              // Language list
              ...languages.map((lang) {
                final isSelected = lang == selected;
                return InkWell(
                  onTap: () {
                    onSelect(lang);
                    Navigator.of(context).pop();
                  },
                  borderRadius: BorderRadius.circular(12),
                  child: Container(
                    margin: const EdgeInsets.only(bottom: 8),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical:   13,
                    ),
                    decoration: BoxDecoration(
                      color: isSelected
                          ? AppColors.primary.withAlpha(10)
                          : AppColors.surface,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: isSelected
                            ? AppColors.primary.withAlpha(60)
                            : AppColors.divider,
                        width: isSelected ? 1.5 : 1,
                      ),
                    ),
                    child: Row(
                      children: [
                        Text(
                          lang,
                          style: textTheme.bodyMedium?.copyWith(
                            color:      isSelected
                                ? AppColors.primary
                                : AppColors.textPrimary,
                            fontWeight: isSelected
                                ? FontWeight.w700
                                : FontWeight.w500,
                          ),
                        ),
                        const Spacer(),
                        if (isSelected)
                          const Icon(
                            Icons.check_circle_rounded,
                            color: AppColors.primary,
                            size:  20,
                          ),
                      ],
                    ),
                  ),
                );
              }),
            ],
          ),
        ),
      ),
    );
  }
}

// ── Logout Dialog ─────────────────────────────────────────────

class _LogoutDialog extends StatelessWidget {
  final VoidCallback onConfirm;
  const _LogoutDialog({required this.onConfirm});

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
          Icons.logout_rounded,
          color: AppColors.error,
          size:  28,
        ),
      ),
      title: Text(
        'Logout?',
        style: textTheme.titleLarge?.copyWith(
          color:      AppColors.textPrimary,
          fontWeight: FontWeight.w700,
        ),
        textAlign: TextAlign.center,
      ),
      content: Text(
        'Are you sure you want to log out of your Smart Parking account?',
        style: textTheme.bodyMedium?.copyWith(
          color:  AppColors.textSecondary,
          height: 1.5,
        ),
        textAlign: TextAlign.center,
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
                  padding:     const EdgeInsets.symmetric(vertical: 14),
                  elevation:   0,
                  shadowColor: Colors.transparent,
                ),
                child: const Text('Logout'),
              ),
            ),
          ],
        ),
      ],
    );
  }
}

// ── About Sheet ───────────────────────────────────────────────

class _AboutSheet extends StatelessWidget {
  const _AboutSheet();

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

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
                  margin: const EdgeInsets.only(top: 12, bottom: 24),
                  width:  40,
                  height: 4,
                  decoration: BoxDecoration(
                    color:        AppColors.divider,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),

              // App icon
              Container(
                width:  72,
                height: 72,
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    begin:  Alignment.topLeft,
                    end:    Alignment.bottomRight,
                    colors: [AppColors.primary, AppColors.primaryLight],
                  ),
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: [
                    BoxShadow(
                      color:       AppColors.primary.withAlpha(60),
                      blurRadius:  16,
                      offset:      const Offset(0, 6),
                    ),
                  ],
                ),
                child: const Icon(
                  Icons.local_parking_rounded,
                  color: AppColors.onPrimary,
                  size:  36,
                ),
              ),

              const SizedBox(height: 16),

              Text(
                'Smart Parking',
                style: textTheme.titleLarge?.copyWith(
                  color:      AppColors.textPrimary,
                  fontWeight: FontWeight.w800,
                ),
              ),

              const SizedBox(height: 4),

              Text(
                'Version 1.0.0 (Build 100)',
                style: textTheme.bodySmall?.copyWith(
                  color: AppColors.textTertiary,
                ),
              ),

              const SizedBox(height: 20),

              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color:        AppColors.surfaceVariant,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: AppColors.divider, width: 1),
                ),
                child: Column(
                  children: [
                    _AboutRow(label: 'Developer',  value: 'Smart Parking Inc.'),
                    const SizedBox(height: 10),
                    _AboutRow(label: 'Platform',   value: 'Flutter 3.44'),
                    const SizedBox(height: 10),
                    _AboutRow(label: 'Released',   value: 'July 2025'),
                    const SizedBox(height: 10),
                    _AboutRow(label: 'Support',    value: 'support@smartpark.in'),
                  ],
                ),
              ),

              const SizedBox(height: 20),

              Text(
                '© 2025 Smart Parking Inc. All rights reserved.',
                style: textTheme.bodySmall?.copyWith(
                  color: AppColors.textTertiary,
                ),
                textAlign: TextAlign.center,
              ),

              const SizedBox(height: 16),

              SizedBox(
                width:  double.infinity,
                height: 48,
                child: FilledButton(
                  onPressed: () => Navigator.of(context).pop(),
                  style: FilledButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    foregroundColor: AppColors.onPrimary,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                    elevation:   0,
                    shadowColor: Colors.transparent,
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

class _AboutRow extends StatelessWidget {
  final String label;
  final String value;
  const _AboutRow({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Row(
      children: [
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
            color:      AppColors.textPrimary,
            fontWeight: FontWeight.w600,
          ),
        ),
      ],
    );
  }
}