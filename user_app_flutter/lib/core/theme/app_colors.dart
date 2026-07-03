import 'package:flutter/material.dart';

/// ============================================================
/// AppColors
/// ============================================================
///
/// Central color palette for the Smart Parking System.
///
/// DESIGN PHILOSOPHY:
/// Built around a deep teal-navy primary that reads as
/// trustworthy and authoritative — right for a platform
/// handling real money and real-world parking. Paired with
/// an electric green accent that signals "available / go"
/// — the core parking metaphor — without tipping into
/// neon gimmickry. Neutral surfaces stay warm-white in
/// light mode and deep charcoal (not pure black) in dark,
/// keeping text comfortable at all hours.
///
/// PALETTE STRUCTURE:
///   primary   → Deep teal-navy  (#0F3D56) — trust, navigation
///   secondary → Electric green  (#2ECC71) — available, confirm
///   accent    → Amber           (#F59E0B) — caution, price
///   error     → Coral red       (#E53935) — cancel, warning
///   surface   → Warm off-white  (#F8F9FA) light / Charcoal (#1A1F2E) dark
///
/// USAGE:
///   Color bg = AppColors.background;          // light surface
///   Color brand = AppColors.primary;          // brand nav bar
///   Color ok = AppColors.success;             // slot available
/// ============================================================

abstract final class AppColors {
  // ── Brand Primary ────────────────────────────────────────────
  /// Deep teal-navy — used for the app bar, primary buttons,
  /// and navigation elements. Conveys trust and authority.
  static const Color primary = Color(0xFF0F3D56);

  /// Lighter tint of primary — used for selected states,
  /// chips, and subtle filled backgrounds.
  static const Color primaryLight = Color(0xFF1A5E80);

  /// Even lighter tint — used for hover/splash on primary.
  static const Color primaryLighter = Color(0xFF2980B2);

  /// Dark shade of primary — used for pressed states.
  static const Color primaryDark = Color(0xFF082A3D);

  /// On-primary text/icon color — always white for legibility.
  static const Color onPrimary = Color(0xFFFFFFFF);

  // ── Brand Secondary (Accent / Available) ─────────────────────
  /// Electric green — the "slot available" / "confirm" signal.
  /// Used for available slot indicators, success states,
  /// and the primary CTA on dark backgrounds.
  static const Color secondary = Color(0xFF2ECC71);

  /// Lighter secondary — used for success chips and badges.
  static const Color secondaryLight = Color(0xFF58D68D);

  /// Dark secondary — used for pressed success buttons.
  static const Color secondaryDark = Color(0xFF27AE60);

  /// On-secondary text color — dark for legibility on green.
  static const Color onSecondary = Color(0xFF0A2E1A);

  // ── Accent ────────────────────────────────────────────────────
  /// Amber — used for price display, overtime warnings,
  /// and "limited availability" slot counts.
  static const Color accent = Color(0xFFF59E0B);

  /// Light amber — for amber-tinted chip backgrounds.
  static const Color accentLight = Color(0xFFFCD34D);

  /// On-accent text color.
  static const Color onAccent = Color(0xFF1C1106);

  // ── Semantic ──────────────────────────────────────────────────
  /// Error / danger — cancelled bookings, full lots,
  /// payment failures.
  static const Color error = Color(0xFFE53935);

  /// Light error — error chip background.
  static const Color errorLight = Color(0xFFFFCDD2);

  /// Success — same as secondary, aliased for clarity in
  /// contexts that are explicitly about success states
  /// (e.g. payment confirmed toast).
  static const Color success = Color(0xFF2ECC71);

  /// Light success — success chip background.
  static const Color successLight = Color(0xFFC8F7DC);

  /// Warning — for expiring QR codes, low slots alerts.
  static const Color warning = Color(0xFFF59E0B);

  /// Light warning — warning chip background.
  static const Color warningLight = Color(0xFFFEF3C7);

  /// Info — for informational banners and tooltips.
  static const Color info = Color(0xFF0288D1);

  /// Light info background.
  static const Color infoLight = Color(0xFFE1F5FE);

  // ── Neutral / Surface (Light Mode) ────────────────────────────
  /// Warm off-white — the main app background in light mode.
  static const Color background = Color(0xFFF8F9FA);

  /// Slightly elevated surface — cards, bottom sheets,
  /// dialogs in light mode.
  static const Color surface = Color(0xFFFFFFFF);

  /// Secondary surface — used for input field fills,
  /// section backgrounds.
  static const Color surfaceVariant = Color(0xFFEEF2F5);

  /// Divider / subtle borders in light mode.
  static const Color divider = Color(0xFFE2E8EE);

  // ── Neutral / Surface (Dark Mode) ────────────────────────────
  /// Deep charcoal — the main background in dark mode.
  /// Not pure black — keeps it comfortable for long sessions.
  static const Color backgroundDark = Color(0xFF1A1F2E);

  /// Elevated surface in dark mode — cards and sheets.
  static const Color surfaceDark = Color(0xFF242A3A);

  /// Secondary surface in dark mode.
  static const Color surfaceVariantDark = Color(0xFF2E3547);

  /// Divider in dark mode.
  static const Color dividerDark = Color(0xFF3C4459);

  // ── Text (Light Mode) ─────────────────────────────────────────
  /// Primary text — headings, labels, main content.
  static const Color textPrimary = Color(0xFF0D1B2A);

  /// Secondary text — supporting copy, placeholders, captions.
  static const Color textSecondary = Color(0xFF5A6A7A);

  /// Tertiary text — hints, timestamps, disabled labels.
  static const Color textTertiary = Color(0xFF8FA3B4);

  /// Disabled text — non-interactive labels.
  static const Color textDisabled = Color(0xFFB8C7D4);

  // ── Text (Dark Mode) ─────────────────────────────────────────
  /// Primary text on dark backgrounds.
  static const Color textPrimaryDark = Color(0xFFE8EDF2);

  /// Secondary text on dark backgrounds.
  static const Color textSecondaryDark = Color(0xFF8FA3B4);

  /// Tertiary text on dark backgrounds.
  static const Color textTertiaryDark = Color(0xFF5A6A7A);

  // ── Parking-Specific Semantics ─────────────────────────────────
  /// Slot is free and bookable.
  static const Color slotAvailable = Color(0xFF2ECC71);

  /// Slot is currently occupied.
  static const Color slotOccupied = Color(0xFFE53935);

  /// Slot is reserved (booking confirmed, not yet arrived).
  static const Color slotReserved = Color(0xFFF59E0B);

  /// Slot is under maintenance (not bookable).
  static const Color slotMaintenance = Color(0xFF8FA3B4);

  // ── Booking Status Colors ─────────────────────────────────────
  /// Booking is pending payment.
  static const Color statusPending = Color(0xFFF59E0B);

  /// Booking is confirmed / paid.
  static const Color statusConfirmed = Color(0xFF2ECC71);

  /// Customer is currently checked in.
  static const Color statusCheckedIn = Color(0xFF0288D1);

  /// Booking completed successfully.
  static const Color statusCompleted = Color(0xFF27AE60);

  /// Booking was cancelled.
  static const Color statusCancelled = Color(0xFFE53935);

  /// Booking was a no-show.
  static const Color statusNoShow = Color(0xFF8FA3B4);

  // ── Utility ───────────────────────────────────────────────────
  /// Pure white — use for explicit white overlays.
  static const Color white = Color(0xFFFFFFFF);

  /// Pure black — use sparingly (prefer textPrimary).
  static const Color black = Color(0xFF000000);

  /// Transparent — for clear backgrounds, invisible hitboxes.
  static const Color transparent = Colors.transparent;

  /// Scrim / overlay — semi-transparent dark used behind
  /// modals, bottom sheets, and drawers.
  static const Color scrim = Color(0x80000000); // 50% black

  /// Shadow color — for box shadows on cards.
  static const Color shadow = Color(0x1A0F3D56); // 10% primary

  // ── Map Pin Colors ─────────────────────────────────────────────
  /// Free parking pin on map.
  static const Color mapPinFree = Color(0xFF2ECC71);

  /// Paid parking pin on map.
  static const Color mapPinPaid = Color(0xFF0F3D56);

  /// Selected / active map pin.
  static const Color mapPinSelected = Color(0xFFF59E0B);

  // ── Bottom Navigation ─────────────────────────────────────────
  /// Active nav item color.
  static const Color navActive = Color(0xFF0F3D56);

  /// Inactive nav item color.
  static const Color navInactive = Color(0xFF8FA3B4);
}