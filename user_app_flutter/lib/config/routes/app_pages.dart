import 'package:flutter/material.dart';

import '../../features/splash/splash_screen.dart';
import '../../features/auth/presentation/login_screen.dart';
import '../../features/auth/presentation/register_screen.dart';
import '../../features/auth/presentation/forgot_password_screen.dart';
import '../../features/auth/presentation/otp_verification_screen.dart';
import '../../features/auth/presentation/reset_password_screen.dart';
import '../../features/home/presentation/home_screen.dart';
import '../../features/parking/presentation/parking_list_screen.dart';
import '../../features/parking/presentation/parking_details_screen.dart';
import '../../features/parking/presentation/slot_selection_screen.dart';
import '../../features/booking/presentation/booking_confirmation_screen.dart';
import '../../features/booking/presentation/my_bookings_screen.dart';
import '../../features/booking/presentation/booking_details_screen.dart';
import '../../features/vehicle/presentation/vehicle_list_screen.dart';
import '../../features/vehicle/presentation/add_vehicle_screen.dart';
import '../../features/payment/presentation/payment_method_screen.dart';
import '../../features/payment/presentation/payment_history_screen.dart';
import '../../features/profile/presentation/profile_screen.dart';
import '../../features/profile/presentation/edit_profile_screen.dart';
import '../../features/profile/presentation/settings_screen.dart';
import '../../features/profile/presentation/change_password_screen.dart';
import '../../features/profile/presentation/help_support_screen.dart';
import '../../features/notification/presentation/notification_screen.dart';

import 'app_routes.dart';

// ============================================================
// AppPages
// ============================================================
//
// Maps every AppRoutes constant to its widget using Flutter's
// built-in onGenerateRoute. A single MaterialPageRoute factory
// keeps transitions consistent across the whole app.
//
// HOW TO ADD A NEW SCREEN:
//   1. Add a constant to AppRoutes.
//   2. Add a case here.
//   Done. No third-party router package needed.
// ============================================================

abstract final class AppPages {
  /// Pass this map to [MaterialApp.routes] for simple push navigation.
  static Map<String, WidgetBuilder> get routes => {
        AppRoutes.splash:          (_) => const SplashScreen(),
        AppRoutes.login:           (_) => const LoginScreen(),
        AppRoutes.register:        (_) => const RegisterScreen(),
        AppRoutes.forgotPassword:  (_) => const ForgotPasswordScreen(),
        AppRoutes.otpVerification: (_) => const OtpVerificationScreen(),
        AppRoutes.resetPassword:   (_) => const ResetPasswordScreen(),
        AppRoutes.home:            (_) => const HomeScreen(),
        AppRoutes.parkingList:     (_) => const ParkingListScreen(),
        AppRoutes.parkingDetails:  (_) => const ParkingDetailsScreen(),
        AppRoutes.slotSelection:   (_) => const SlotSelectionScreen(),
        AppRoutes.bookingConfirmation: (_) => const BookingConfirmationScreen(),
        AppRoutes.myBookings:      (_) => const MyBookingsScreen(),
        AppRoutes.bookingDetails:  (_) => const BookingDetailsScreen(),
        AppRoutes.vehicleList:     (_) => const VehicleListScreen(),
        AppRoutes.addVehicle:      (_) => const AddVehicleScreen(),
        AppRoutes.paymentMethod:   (_) => const PaymentMethodScreen(),
        AppRoutes.paymentHistory:  (_) => const PaymentHistoryScreen(),
        AppRoutes.profile:         (_) => const ProfileScreen(),
        AppRoutes.editProfile:     (_) => const EditProfileScreen(),
        AppRoutes.settings:        (_) => const SettingsScreen(),
        AppRoutes.changePassword:  (_) => const ChangePasswordScreen(),
        AppRoutes.helpSupport:     (_) => const HelpSupportScreen(),
        AppRoutes.notifications:   (_) => const NotificationScreen(),
      };
}