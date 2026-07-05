import 'package:flutter/material.dart';
import 'features/auth/presentation/login_screen.dart';
import 'features/auth/presentation/register_screen.dart';
import 'core/theme/app_theme.dart';
import 'features/splash/splash_screen.dart';
import 'features/auth/presentation/forgot_password_screen.dart';
import 'features/auth/presentation/otp_verification_screen.dart';
import 'features/auth/presentation/reset_password_screen.dart';
import 'features/home/presentation/home_screen.dart';
import 'features/parking/presentation/parking_list_screen.dart';
import 'features/parking/presentation/parking_details_screen.dart';
import 'features/parking/presentation/slot_selection_screen.dart';
import 'features/booking/presentation/booking_confirmation_screen.dart';
import 'features/vehicle/presentation/vehicle_list_screen.dart';
import 'features/vehicle/presentation/add_vehicle_screen.dart';
import 'features/booking/presentation/my_bookings_screen.dart';
import 'features/profile/presentation/profile_screen.dart';
import 'features/profile/presentation/edit_profile_screen.dart';
import 'features/payment/presentation/payment_history_screen.dart';
import 'features/notification/presentation/notification_screen.dart';

class App extends StatelessWidget {
  const App({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Smart Parking',
      theme: AppTheme.lightTheme,
      home: const NotificationScreen(),
    );
  }
}