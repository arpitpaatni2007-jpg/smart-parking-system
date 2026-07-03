import 'package:flutter/material.dart';

import '../../features/splash/splash_screen.dart';

// ============================================================
// AppRouter
// ============================================================
//
// Centralised named-route registry for the Smart Parking app.
//
// ARCHITECTURE:
//   All route names are string constants on AppRouter.
//   AppRouter.routes is the map passed to MaterialApp.routes.
//   AppRouter.onGenerateRoute handles parameterised routes
//   that require arguments (e.g. booking detail with an id).
//
// HOW TO ADD A NEW ROUTE:
//   1. Add a route-name constant below.
//   2. If the screen needs no arguments → add it to `routes`.
//   3. If the screen needs arguments   → add a case in `onGenerateRoute`.
//   4. Navigate: Navigator.pushNamed(context, AppRouter.myScreen);
//
// CURRENT PHASE (Phase 1 — Splash only):
//   Only the splash route is active. All other constants are
//   defined now so the rest of the app can reference them
//   without ever having a compile error.
//
// FUTURE:
//   Replace this with go_router or auto_route when deep linking,
//   nested navigation, or web URL strategy is required.
// ============================================================

abstract final class AppRouter {
  // ── Route Name Constants ────────────────────────────────────────
  // Defined up-front so every feature file can import AppRouter
  // and reference a constant instead of a magic string.

  /// Initial screen — shown on every cold start.
  static const String splash = '/';

  /// Onboarding / walkthrough (future).
  static const String onboarding = '/onboarding';

  /// Login screen.
  static const String login = '/auth/login';

  /// Registration screen.
  static const String register = '/auth/register';

  /// OTP verification screen.
  static const String otp = '/auth/otp';

  /// Main shell — bottom-nav host after login.
  static const String home = '/home';

  /// Parking search / map screen.
  static const String parkingSearch = '/parking/search';

  /// Parking detail screen — requires `parking_id` argument.
  static const String parkingDetail = '/parking/detail';

  /// Slot selection screen.
  static const String slotSelection = '/parking/slots';

  /// Booking confirmation screen.
  static const String bookingConfirm = '/booking/confirm';

  /// Booking detail — requires `booking_id` argument.
  static const String bookingDetail = '/booking/detail';

  /// Active booking / QR code screen.
  static const String bookingActive = '/booking/active';

  /// Booking history list.
  static const String bookingHistory = '/booking/history';

  /// Payment screen.
  static const String payment = '/payment';

  /// Payment success screen.
  static const String paymentSuccess = '/payment/success';

  /// My vehicles list.
  static const String vehicles = '/profile/vehicles';

  /// Add / edit vehicle screen.
  static const String vehicleForm = '/profile/vehicles/form';

  /// Notifications inbox.
  static const String notifications = '/notifications';

  /// User profile screen.
  static const String profile = '/profile';

  /// Edit profile screen.
  static const String profileEdit = '/profile/edit';

  /// Change password screen.
  static const String changePassword = '/profile/change-password';

  // ── Route Table (no-argument screens) ──────────────────────────
  //
  // Every entry here is a screen that does NOT need dynamic
  // arguments passed at navigation time.
  // Screens that need arguments go in onGenerateRoute below.
  //
  static Map<String, WidgetBuilder> get routes => {
    splash: (_) => const SplashScreen(),

    // ── The entries below will be wired as their screens are built ─
    // login:          (_) => const LoginScreen(),
    // register:       (_) => const RegisterScreen(),
    // home:           (_) => const HomeScreen(),
    // bookingHistory: (_) => const BookingHistoryScreen(),
    // vehicles:       (_) => const VehiclesScreen(),
    // notifications:  (_) => const NotificationsScreen(),
    // profile:        (_) => const ProfileScreen(),
  };

  // ── Dynamic / Argument Routes ───────────────────────────────────
  //
  // Use onGenerateRoute for screens that receive typed arguments
  // via RouteSettings.arguments.
  //
  // Usage:
  //   Navigator.pushNamed(
  //     context,
  //     AppRouter.parkingDetail,
  //     arguments: {'parking_id': 42},
  //   );
  //
  static Route<dynamic>? onGenerateRoute(RouteSettings settings) {
    switch (settings.name) {
      // ── Parking Detail ─────────────────────────────────────────
      // case parkingDetail:
      //   final args = settings.arguments as Map<String, dynamic>;
      //   return MaterialPageRoute(
      //     builder: (_) => ParkingDetailScreen(parkingId: args['parking_id']),
      //   );

      // ── Booking Detail ─────────────────────────────────────────
      // case bookingDetail:
      //   final args = settings.arguments as Map<String, dynamic>;
      //   return MaterialPageRoute(
      //     builder: (_) => BookingDetailScreen(bookingId: args['booking_id']),
      //   );

      default:
        // Return null to fall back to MaterialApp.routes or onUnknownRoute.
        return null;
    }
  }

  // ── Unknown Route Fallback ──────────────────────────────────────
  //
  // Called when Navigator.pushNamed is used with a route name
  // that is not in `routes` and not handled by `onGenerateRoute`.
  // Shows a simple error screen so the app never crashes on a bad route.
  //
  static Route<dynamic> onUnknownRoute(RouteSettings settings) {
    return MaterialPageRoute(
      builder: (_) => _UnknownRouteScreen(routeName: settings.name ?? 'unknown'),
    );
  }
}

// ── Internal fallback screen ──────────────────────────────────────
// Not exported — only used by onUnknownRoute above.

class _UnknownRouteScreen extends StatelessWidget {
  final String routeName;

  const _UnknownRouteScreen({required this.routeName});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Page Not Found')),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.map_outlined, size: 64, color: Colors.grey),
              const SizedBox(height: 16),
              Text(
                'No route defined for "$routeName".',
                textAlign: TextAlign.center,
                style: Theme.of(context).textTheme.bodyLarge,
              ),
              const SizedBox(height: 24),
              FilledButton(
                onPressed: () => Navigator.of(context)
                    .pushNamedAndRemoveUntil(AppRouter.splash, (_) => false),
                child: const Text('Go Home'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}