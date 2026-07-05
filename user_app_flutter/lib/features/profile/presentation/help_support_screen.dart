import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_colors.dart';

// ============================================================
// HelpSupportScreen
// ============================================================
//
// Premium Help & Support screen for the Smart Parking System.
//
// SECTIONS:
//   1. AppBar             — "Help & Support" title + back button
//   2. Search Bar         — search help articles
//   3. Quick Actions Row  — FAQs / Contact / Live Chat / Email Us
//   4. Help Categories    — 6 tappable category cards
//   5. Support Card       — phone, email, working hours
//   6. Bottom Button      — Contact Support CTA
//
// STATE:
//   _searchController  — search field text controller
//   _searchFocused     — whether the search field has focus
//   _expandedFaq       — index of expanded FAQ (-1 = none)
//
// DESIGN LANGUAGE:
//   Matches settings_screen.dart exactly —
//     • AppColors.background scaffold
//     • White cards with 0.8 px divider border + shadow
//     • 16 px card radius, 16 px horizontal padding
//     • Primary teal-navy brand color throughout
//     • BouncingScrollPhysics
//     • _SectionCard / _NavTile / _DividerLine pattern
//
// ARCHITECTURE:
//   Pure StatefulWidget — no Riverpod / Bloc / Provider.
//   All data is local dummy constants. No navigation calls.
// ============================================================

// ── Quick Action Data Model ───────────────────────────────────

class _QuickAction {
  final IconData icon;
  final Color    iconColor;
  final Color    iconBg;
  final String   label;

  const _QuickAction({
    required this.icon,
    required this.iconColor,
    required this.iconBg,
    required this.label,
  });
}

const List<_QuickAction> _quickActions = [
  _QuickAction(
    icon:      Icons.quiz_outlined,
    iconColor: AppColors.primary,
    iconBg:    Color(0xFFE8F1F7),
    label:     'FAQs',
  ),
  _QuickAction(
    icon:      Icons.support_agent_rounded,
    iconColor: Color(0xFF7B1FA2),
    iconBg:    Color(0xFFF3E5F5),
    label:     'Contact\nSupport',
  ),
  _QuickAction(
    icon:      Icons.chat_bubble_outline_rounded,
    iconColor: AppColors.secondary,
    iconBg:    Color(0xFFE8F8F1),
    label:     'Live Chat',
  ),
  _QuickAction(
    icon:      Icons.mail_outline_rounded,
    iconColor: AppColors.accent,
    iconBg:    Color(0xFFFEF3C7),
    label:     'Email Us',
  ),
];

// ── Help Category Data Model ──────────────────────────────────

class _HelpCategory {
  final IconData icon;
  final Color    iconColor;
  final Color    iconBg;
  final String   title;
  final String   subtitle;

  const _HelpCategory({
    required this.icon,
    required this.iconColor,
    required this.iconBg,
    required this.title,
    required this.subtitle,
  });
}

const List<_HelpCategory> _categories = [
  _HelpCategory(
    icon:      Icons.bookmark_outline_rounded,
    iconColor: AppColors.primary,
    iconBg:    Color(0xFFE8F1F7),
    title:     'Booking Issues',
    subtitle:  'Cancel, modify or failed bookings',
  ),
  _HelpCategory(
    icon:      Icons.credit_card_outlined,
    iconColor: Color(0xFF1565C0),
    iconBg:    Color(0xFFE3F0FF),
    title:     'Payment Issues',
    subtitle:  'Charges, failures & billing queries',
  ),
  _HelpCategory(
    icon:      Icons.manage_accounts_outlined,
    iconColor: Color(0xFF7B1FA2),
    iconBg:    Color(0xFFF3E5F5),
    title:     'Account & Login',
    subtitle:  'Sign in, password & profile help',
  ),
  _HelpCategory(
    icon:      Icons.local_parking_rounded,
    iconColor: AppColors.secondary,
    iconBg:    Color(0xFFE8F8F1),
    title:     'Parking Problems',
    subtitle:  'Slot unavailable, access & QR issues',
  ),
  _HelpCategory(
    icon:      Icons.currency_rupee_rounded,
    iconColor: AppColors.accent,
    iconBg:    Color(0xFFFEF3C7),
    title:     'Refund Requests',
    subtitle:  'Track & request refund status',
  ),
  _HelpCategory(
    icon:      Icons.bug_report_outlined,
    iconColor: AppColors.error,
    iconBg:    Color(0xFFFFEBEE),
    title:     'Report a Bug',
    subtitle:  'App glitches & technical issues',
  ),
];

// ── FAQ Data ─────────────────────────────────────────────────

class _FaqItem {
  final String question;
  final String answer;
  const _FaqItem({required this.question, required this.answer});
}

const List<_FaqItem> _faqs = [
  _FaqItem(
    question: 'How do I cancel a booking?',
    answer:
        'Go to My Bookings, select the active booking and tap "Cancel Booking". '
        'Cancellations made 2+ hours before start time are fully refunded.',
  ),
  _FaqItem(
    question: 'When will my refund be processed?',
    answer:
        'Refunds are typically processed within 5–7 business days to your '
        'original payment method after a successful cancellation.',
  ),
  _FaqItem(
    question: 'My QR code is not scanning. What should I do?',
    answer:
        'Ensure screen brightness is at maximum and the QR is fully visible. '
        'If the issue persists, tap "Refresh QR" in the booking details screen.',
  ),
  _FaqItem(
    question: 'Can I extend my parking duration?',
    answer:
        'Yes! Open your active booking and tap "Extend Time". Extension is '
        'subject to slot availability and charged at the standard hourly rate.',
  ),
];

// ════════════════════════════════════════════════════════════════
// Screen
// ════════════════════════════════════════════════════════════════

class HelpSupportScreen extends StatefulWidget {
  const HelpSupportScreen({super.key});

  @override
  State<HelpSupportScreen> createState() => _HelpSupportScreenState();
}

class _HelpSupportScreenState extends State<HelpSupportScreen> {
  final TextEditingController _searchController = TextEditingController();
  final FocusNode             _searchFocus      = FocusNode();
  bool _searchFocused  = false;
  int  _expandedFaq    = -1; // -1 = all collapsed

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

    _searchFocus.addListener(() {
      setState(() => _searchFocused = _searchFocus.hasFocus);
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    _searchFocus.dispose();
    super.dispose();
  }

  // ── Snack helper ────────────────────────────────────────────

  void _snack(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content:         Text(msg),
        behavior:        SnackBarBehavior.floating,
        duration:        const Duration(seconds: 2),
        backgroundColor: AppColors.textPrimary,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
      ),
    );
  }

  // ── Build ───────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final w    = MediaQuery.sizeOf(context).width;
    final hPad = w > 600 ? w * 0.08 : 16.0;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar:          _buildAppBar(context),
      body: ListView(
        physics:  const BouncingScrollPhysics(),
        padding:  EdgeInsets.fromLTRB(hPad, 16, hPad, 100),
        children: [

          // ── Search ─────────────────────────────────────────
          _SearchBar(
            controller: _searchController,
            focusNode:  _searchFocus,
            isFocused:  _searchFocused,
          ),

          const SizedBox(height: 24),

          // ── Quick Actions ───────────────────────────────────
          _QuickActionsRow(onTap: (action) => _snack('${action.label.replaceAll('\n', ' ')} tapped')),

          const SizedBox(height: 24),

          // ── Help Categories ─────────────────────────────────
          _SectionHeader(title: 'Help Categories', icon: Icons.category_outlined),
          const SizedBox(height: 12),
          _CategoriesGrid(onTap: (cat) => _snack('${cat.title} tapped')),

          const SizedBox(height: 24),

          // ── Popular FAQs ────────────────────────────────────
          _SectionHeader(title: 'Popular FAQs', icon: Icons.lightbulb_outline_rounded),
          const SizedBox(height: 12),
          _FaqSection(
            faqs:        _faqs,
            expandedIdx: _expandedFaq,
            onExpand:    (i) => setState(
              () => _expandedFaq = (_expandedFaq == i) ? -1 : i,
            ),
          ),

          const SizedBox(height: 24),

          // ── Support Card ────────────────────────────────────
          _SectionHeader(title: 'Contact Information', icon: Icons.headset_mic_outlined),
          const SizedBox(height: 12),
          const _SupportCard(),

          const SizedBox(height: 24),

          // ── Bottom CTA ──────────────────────────────────────
          _ContactSupportButton(onTap: () => _snack('Opening support…')),
        ],
      ),
    );
  }

  // ── AppBar ──────────────────────────────────────────────────

  PreferredSizeWidget _buildAppBar(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return AppBar(
      backgroundColor:     AppColors.background,
      elevation:           0,
      scrolledUnderElevation: 0,
      leading: GestureDetector(
        onTap: () => Navigator.maybePop(context),
        child: Container(
          margin: const EdgeInsets.all(10),
          decoration: BoxDecoration(
            color:        AppColors.surface,
            borderRadius: BorderRadius.circular(10),
            border:       Border.all(color: AppColors.divider),
            boxShadow: [
              BoxShadow(
                color:      AppColors.shadow,
                blurRadius: 6,
                offset:     const Offset(0, 2),
              ),
            ],
          ),
          child: const Icon(
            Icons.arrow_back_ios_new_rounded,
            color: AppColors.textPrimary,
            size:  18,
          ),
        ),
      ),
      title: Text(
        'Help & Support',
        style: textTheme.titleLarge?.copyWith(
          color:      AppColors.textPrimary,
          fontWeight: FontWeight.w700,
        ),
      ),
      actions: [
        Container(
          margin: const EdgeInsets.only(right: 16),
          child: IconButton(
            onPressed: () => _snack('Opening chat…'),
            style: IconButton.styleFrom(
              backgroundColor: AppColors.primary.withAlpha(12),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
              ),
            ),
            icon: const Icon(
              Icons.chat_bubble_outline_rounded,
              color: AppColors.primary,
              size:  20,
            ),
          ),
        ),
      ],
    );
  }
}

// ════════════════════════════════════════════════════════════════
// Search Bar
// ════════════════════════════════════════════════════════════════

class _SearchBar extends StatelessWidget {
  final TextEditingController controller;
  final FocusNode             focusNode;
  final bool                  isFocused;

  const _SearchBar({
    required this.controller,
    required this.focusNode,
    required this.isFocused,
  });

  @override
  Widget build(BuildContext context) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 200),
      decoration: BoxDecoration(
        color:        AppColors.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: isFocused ? AppColors.primary : AppColors.divider,
          width: isFocused ? 1.8 : 0.8,
        ),
        boxShadow: [
          BoxShadow(
            color:      isFocused
                ? AppColors.primary.withAlpha(20)
                : AppColors.shadow,
            blurRadius: isFocused ? 12 : 6,
            offset:     const Offset(0, 3),
          ),
        ],
      ),
      child: TextField(
        controller:  controller,
        focusNode:   focusNode,
        style: Theme.of(context).textTheme.bodyMedium?.copyWith(
          color: AppColors.textPrimary,
        ),
        decoration: InputDecoration(
          hintText:        'Search help articles…',
          hintStyle: Theme.of(context).textTheme.bodyMedium?.copyWith(
            color: AppColors.textTertiary,
          ),
          prefixIcon: const Icon(
            Icons.search_rounded,
            color: AppColors.textSecondary,
            size:  22,
          ),
          suffixIcon: ValueListenableBuilder<TextEditingValue>(
            valueListenable: controller,
            builder: (_, val, __) => val.text.isNotEmpty
                ? GestureDetector(
                    onTap: controller.clear,
                    child: const Icon(
                      Icons.close_rounded,
                      color: AppColors.textTertiary,
                      size:  18,
                    ),
                  )
                : const SizedBox.shrink(),
          ),
          filled:           false,
          border:           InputBorder.none,
          enabledBorder:    InputBorder.none,
          focusedBorder:    InputBorder.none,
          contentPadding:   const EdgeInsets.symmetric(
            horizontal: 16,
            vertical:   16,
          ),
        ),
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════
// Quick Actions Row
// ════════════════════════════════════════════════════════════════

class _QuickActionsRow extends StatelessWidget {
  final void Function(_QuickAction) onTap;
  const _QuickActionsRow({required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: _quickActions
          .map(
            (action) => Expanded(
              child: Padding(
                padding: EdgeInsets.only(
                  right: action == _quickActions.last ? 0 : 10,
                ),
                child: _QuickActionCard(action: action, onTap: () => onTap(action)),
              ),
            ),
          )
          .toList(),
    );
  }
}

class _QuickActionCard extends StatelessWidget {
  final _QuickAction action;
  final VoidCallback onTap;
  const _QuickActionCard({required this.action, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding:    const EdgeInsets.symmetric(vertical: 16, horizontal: 8),
        decoration: BoxDecoration(
          color:        AppColors.surface,
          borderRadius: BorderRadius.circular(14),
          border:       Border.all(color: AppColors.divider, width: 0.8),
          boxShadow: [
            BoxShadow(
              color:      AppColors.shadow,
              blurRadius: 8,
              offset:     const Offset(0, 3),
            ),
          ],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Icon container
            Container(
              width:  46,
              height: 46,
              decoration: BoxDecoration(
                color:        action.iconBg,
                borderRadius: BorderRadius.circular(13),
              ),
              child: Icon(action.icon, color: action.iconColor, size: 22),
            ),
            const SizedBox(height: 10),
            Text(
              action.label,
              textAlign: TextAlign.center,
              maxLines:  2,
              style: textTheme.labelMedium?.copyWith(
                color:      AppColors.textPrimary,
                fontWeight: FontWeight.w600,
                height:     1.3,
                fontSize:   11,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════
// Section Header
// ════════════════════════════════════════════════════════════════

class _SectionHeader extends StatelessWidget {
  final String   title;
  final IconData icon;
  const _SectionHeader({required this.title, required this.icon});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;
    return Row(
      children: [
        Container(
          width:  32,
          height: 32,
          decoration: BoxDecoration(
            color:        AppColors.primary.withAlpha(14),
            borderRadius: BorderRadius.circular(9),
          ),
          child: Icon(icon, color: AppColors.primary, size: 17),
        ),
        const SizedBox(width: 10),
        Text(
          title,
          style: textTheme.titleMedium?.copyWith(
            color:      AppColors.textPrimary,
            fontWeight: FontWeight.w700,
          ),
        ),
      ],
    );
  }
}

// ════════════════════════════════════════════════════════════════
// Help Categories Grid (2-column)
// ════════════════════════════════════════════════════════════════

class _CategoriesGrid extends StatelessWidget {
  final void Function(_HelpCategory) onTap;
  const _CategoriesGrid({required this.onTap});

  @override
  Widget build(BuildContext context) {
    // Build rows of two
    final rows = <Widget>[];
    for (int i = 0; i < _categories.length; i += 2) {
      final left  = _categories[i];
      final right = i + 1 < _categories.length ? _categories[i + 1] : null;

      rows.add(
        Row(
          children: [
            Expanded(child: _CategoryCard(cat: left, onTap: () => onTap(left))),
            const SizedBox(width: 12),
            Expanded(
              child: right != null
                  ? _CategoryCard(cat: right, onTap: () => onTap(right))
                  : const SizedBox.shrink(),
            ),
          ],
        ),
      );
      if (i + 2 < _categories.length) rows.add(const SizedBox(height: 12));
    }

    return Column(children: rows);
  }
}

class _CategoryCard extends StatelessWidget {
  final _HelpCategory cat;
  final VoidCallback  onTap;
  const _CategoryCard({required this.cat, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding:    const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color:        AppColors.surface,
          borderRadius: BorderRadius.circular(14),
          border:       Border.all(color: AppColors.divider, width: 0.8),
          boxShadow: [
            BoxShadow(
              color:      AppColors.shadow,
              blurRadius: 8,
              offset:     const Offset(0, 3),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Icon
            Container(
              width:  42,
              height: 42,
              decoration: BoxDecoration(
                color:        cat.iconBg,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(cat.icon, color: cat.iconColor, size: 20),
            ),
            const SizedBox(height: 12),
            // Title
            Text(
              cat.title,
              style: textTheme.titleSmall?.copyWith(
                color:      AppColors.textPrimary,
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 4),
            // Subtitle
            Text(
              cat.subtitle,
              style: textTheme.bodySmall?.copyWith(
                color:    AppColors.textSecondary,
                fontSize: 11,
                height:   1.4,
              ),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
            const SizedBox(height: 10),
            // Arrow chip
            Row(
              children: [
                const Spacer(),
                Container(
                  width:  28,
                  height: 28,
                  decoration: BoxDecoration(
                    color:        AppColors.primary.withAlpha(12),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(
                    Icons.arrow_forward_rounded,
                    color: AppColors.primary,
                    size:  14,
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

// ════════════════════════════════════════════════════════════════
// FAQ Section (expandable)
// ════════════════════════════════════════════════════════════════

class _FaqSection extends StatelessWidget {
  final List<_FaqItem>      faqs;
  final int                 expandedIdx;
  final void Function(int)  onExpand;

  const _FaqSection({
    required this.faqs,
    required this.expandedIdx,
    required this.onExpand,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color:        AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        border:       Border.all(color: AppColors.divider, width: 0.8),
        boxShadow: [
          BoxShadow(
            color:      AppColors.shadow,
            blurRadius: 10,
            offset:     const Offset(0, 3),
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        children: List.generate(faqs.length, (i) {
          final isLast     = i == faqs.length - 1;
          final isExpanded = expandedIdx == i;
          return _FaqTile(
            faq:        faqs[i],
            isExpanded: isExpanded,
            isLast:     isLast,
            onTap:      () => onExpand(i),
          );
        }),
      ),
    );
  }
}

class _FaqTile extends StatelessWidget {
  final _FaqItem  faq;
  final bool      isExpanded;
  final bool      isLast;
  final VoidCallback onTap;

  const _FaqTile({
    required this.faq,
    required this.isExpanded,
    required this.isLast,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        InkWell(
          onTap:         onTap,
          splashColor:   AppColors.primary.withAlpha(8),
          highlightColor: AppColors.primary.withAlpha(5),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            child: Row(
              children: [
                // Question number badge
                Container(
                  width:  28,
                  height: 28,
                  decoration: BoxDecoration(
                    color:        isExpanded
                        ? AppColors.primary
                        : AppColors.primary.withAlpha(14),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Center(
                    child: Text(
                      'Q',
                      style: textTheme.labelSmall?.copyWith(
                        color:      isExpanded
                            ? AppColors.onPrimary
                            : AppColors.primary,
                        fontWeight: FontWeight.w800,
                        fontSize:   12,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                // Question text
                Expanded(
                  child: Text(
                    faq.question,
                    style: textTheme.bodyMedium?.copyWith(
                      color:      AppColors.textPrimary,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                // Chevron
                AnimatedRotation(
                  turns:    isExpanded ? 0.5 : 0.0,
                  duration: const Duration(milliseconds: 220),
                  child: Icon(
                    Icons.keyboard_arrow_down_rounded,
                    color: isExpanded ? AppColors.primary : AppColors.textTertiary,
                    size:  22,
                  ),
                ),
              ],
            ),
          ),
        ),
        // Answer panel
        AnimatedCrossFade(
          duration:       const Duration(milliseconds: 220),
          crossFadeState: isExpanded
              ? CrossFadeState.showSecond
              : CrossFadeState.showFirst,
          firstChild:  const SizedBox(width: double.infinity),
          secondChild: Container(
            width: double.infinity,
            margin: const EdgeInsets.only(bottom: 4),
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
            child: Container(
              padding:    const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color:        AppColors.primary.withAlpha(8),
                borderRadius: BorderRadius.circular(12),
                border:       Border.all(
                  color: AppColors.primary.withAlpha(25),
                  width: 1,
                ),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Padding(
                    padding: EdgeInsets.only(top: 1),
                    child: Icon(
                      Icons.info_outline_rounded,
                      color: AppColors.primary,
                      size:  15,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      faq.answer,
                      style: textTheme.bodySmall?.copyWith(
                        color:  AppColors.textSecondary,
                        height: 1.55,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
        // Divider
        if (!isLast)
          Padding(
            padding: const EdgeInsets.only(left: 56),
            child:   Divider(height: 1, thickness: 1, color: AppColors.divider),
          ),
      ],
    );
  }
}

// ════════════════════════════════════════════════════════════════
// Support Card
// ════════════════════════════════════════════════════════════════

class _SupportCard extends StatelessWidget {
  const _SupportCard();

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [AppColors.primary, AppColors.primaryLight],
          begin:  Alignment.topLeft,
          end:    Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color:      AppColors.primary.withAlpha(80),
            blurRadius: 18,
            offset:     const Offset(0, 6),
          ),
        ],
      ),
      child: Stack(
        children: [
          // Decorative circle top-right
          Positioned(
            top:   -20,
            right: -20,
            child: Container(
              width:  100,
              height: 100,
              decoration: BoxDecoration(
                color:  Colors.white.withAlpha(15),
                shape:  BoxShape.circle,
              ),
            ),
          ),
          // Decorative circle bottom-left
          Positioned(
            bottom: -30,
            left:   -20,
            child: Container(
              width:  90,
              height: 90,
              decoration: BoxDecoration(
                color:  Colors.white.withAlpha(10),
                shape:  BoxShape.circle,
              ),
            ),
          ),
          // Content
          Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header row
                Row(
                  children: [
                    Container(
                      width:  48,
                      height: 48,
                      decoration: BoxDecoration(
                        color:        Colors.white.withAlpha(25),
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: const Icon(
                        Icons.headset_mic_rounded,
                        color: Colors.white,
                        size:  24,
                      ),
                    ),
                    const SizedBox(width: 14),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Customer Support',
                          style: textTheme.titleMedium?.copyWith(
                            color:      Colors.white,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        const SizedBox(height: 2),
                        // Online indicator
                        Row(
                          children: [
                            Container(
                              width:  7,
                              height: 7,
                              decoration: BoxDecoration(
                                color:  AppColors.secondary,
                                shape:  BoxShape.circle,
                                boxShadow: [
                                  BoxShadow(
                                    color:      AppColors.secondary.withAlpha(120),
                                    blurRadius: 4,
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(width: 5),
                            Text(
                              'Online Now',
                              style: textTheme.bodySmall?.copyWith(
                                color:      Colors.white.withAlpha(200),
                                fontSize:   11,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ],
                ),

                const SizedBox(height: 20),

                // Divider
                Container(
                  height: 1,
                  color: Colors.white.withAlpha(30),
                ),

                const SizedBox(height: 18),

                // Contact details
                _SupportInfoRow(
                  icon:  Icons.phone_rounded,
                  label: 'Phone Number',
                  value: '+91 1800 123 4567',
                ),
                const SizedBox(height: 14),
                _SupportInfoRow(
                  icon:  Icons.mail_rounded,
                  label: 'Email Address',
                  value: 'support@smartpark.in',
                ),
                const SizedBox(height: 14),
                _SupportInfoRow(
                  icon:  Icons.access_time_rounded,
                  label: 'Working Hours',
                  value: 'Mon – Sat, 9 AM – 8 PM',
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _SupportInfoRow extends StatelessWidget {
  final IconData icon;
  final String   label;
  final String   value;

  const _SupportInfoRow({
    required this.icon,
    required this.label,
    required this.value,
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
            color:        Colors.white.withAlpha(20),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(icon, color: Colors.white, size: 17),
        ),
        const SizedBox(width: 14),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              label,
              style: textTheme.bodySmall?.copyWith(
                color:   Colors.white.withAlpha(160),
                fontSize: 11,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              value,
              style: textTheme.bodyMedium?.copyWith(
                color:      Colors.white,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
      ],
    );
  }
}

// ════════════════════════════════════════════════════════════════
// Bottom CTA Button
// ════════════════════════════════════════════════════════════════

class _ContactSupportButton extends StatelessWidget {
  final VoidCallback onTap;
  const _ContactSupportButton({required this.onTap});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return GestureDetector(
      onTap: onTap,
      child: Container(
        width:   double.infinity,
        height:  56,
        decoration: BoxDecoration(
          color:        AppColors.secondary,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color:      AppColors.secondary.withAlpha(80),
              blurRadius: 14,
              offset:     const Offset(0, 5),
            ),
          ],
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(
              Icons.support_agent_rounded,
              color: AppColors.onSecondary,
              size:  22,
            ),
            const SizedBox(width: 10),
            Text(
              'Contact Support',
              style: textTheme.titleSmall?.copyWith(
                color:         AppColors.onSecondary,
                fontWeight:    FontWeight.w700,
                letterSpacing: 0.3,
              ),
            ),
          ],
        ),
      ),
    );
  }
}