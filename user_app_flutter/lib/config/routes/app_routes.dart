// ============================================================
// AppRoutes
// ============================================================
//
// Single source of truth for every named route string in the
// Smart Parking app.
//
// USAGE:
//   Navigator.pushNamed(context, AppRoutes.login);
//   Navigator.pushReplacementNamed(context, AppRoutes.home);
//   Navigator.pop(context);
// ============================================================

abstract final class AppRoutes {
  // ── Auth ──────────────────────────────────────────────────
  static const String splash          = '/';
  static const String login           = '/login';
  static const String register        = '/register';
  static const String forgotPassword  = '/forgot-password';
  static const String otpVerification = '/otp-verification';
  static const String resetPassword   = '/reset-password';

  // ── Main ──────────────────────────────────────────────────
  static const String home            = '/home';

  // ── Parking ───────────────────────────────────────────────
  static const String parkingList     = '/parking-list';
  static const String parkingDetails  = '/parking-details';
  static const String slotSelection   = '/slot-selection';

  // ── Booking ───────────────────────────────────────────────
  static const String bookingConfirmation = '/booking-confirmation';
  static const String myBookings          = '/my-bookings';
  static const String bookingDetails      = '/booking-details';

  // ── Vehicle ───────────────────────────────────────────────
  static const String vehicleList   = '/vehicle-list';
  static const String addVehicle    = '/add-vehicle';

  // ── Payment ───────────────────────────────────────────────
  static const String paymentMethod  = '/payment-method';
  static const String paymentHistory = '/payment-history';

  // ── Profile ───────────────────────────────────────────────
  static const String profile        = '/profile';
  static const String editProfile    = '/edit-profile';
  static const String settings       = '/settings';
  static const String changePassword = '/change-password';
  static const String helpSupport    = '/help-support';

  // ── Notifications ─────────────────────────────────────────
  static const String notifications  = '/notifications';
}