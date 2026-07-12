import 'package:flutter/material.dart';

import 'config/routes/app_pages.dart';
import 'config/routes/app_routes.dart';
import 'core/theme/app_theme.dart';

class App extends StatelessWidget {
  const App({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title:        'Smart Parking',
      theme:        AppTheme.lightTheme,
      initialRoute: AppRoutes.splash,   // ← start at splash
      routes:       AppPages.routes,    // ← all named routes registered
    );
  }
}