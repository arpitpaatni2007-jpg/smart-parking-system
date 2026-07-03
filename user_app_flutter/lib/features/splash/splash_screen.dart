import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../config/routes/app_router.dart';
import '../../core/theme/app_colors.dart';

// ============================================================
// SplashScreen
// ============================================================
//
// The first screen shown on every cold start.
//
// WHAT IT DOES:
//   1. Locks the status bar to transparent / light icons so it
//      blends with the gradient background.
//   2. Runs a 3-part animation sequence:
//        a. Logo + icon fade-in + slide-up   (600 ms, ease-out)
//        b. Tagline fade-in                  (400 ms, 400 ms delay)
//        c. Loading indicator fade-in        (300 ms, 700 ms delay)
//   3. After 2.8 s total, navigates to the Login screen
//      (currently routes to AppRouter.login — wired to splash
//      until the login screen is built).
//
// DESIGN:
//   Full-bleed vertical gradient (primary dark → primary → primaryLight)
//   keeps the brand front-and-centre. The parking "P" icon inside a
//   rounded square reads immediately on any device size.
//
// NO STATE MANAGEMENT / NO API CALLS:
//   Pure StatefulWidget + AnimationController.
//   No Riverpod, no Bloc, no Provider.
// ============================================================

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen>
    with SingleTickerProviderStateMixin {
  // ── Animation Controller ──────────────────────────────────────
  // A single controller drives multiple staggered animations via
  // Interval curves — no need for multiple controllers.
  late final AnimationController _controller;

  // ── Logo: fade + slide-up ─────────────────────────────────────
  late final Animation<double> _logoOpacity;
  late final Animation<Offset> _logoSlide;

  // ── Tagline: fade only ────────────────────────────────────────
  late final Animation<double> _taglineOpacity;

  // ── Loading indicator: fade ───────────────────────────────────
  late final Animation<double> _loaderOpacity;

  @override
  void initState() {
    super.initState();

    // Force transparent status bar with light icons over the gradient.
    SystemChrome.setSystemUIOverlayStyle(
      const SystemUiOverlayStyle(
        statusBarColor: Colors.transparent,
        statusBarIconBrightness: Brightness.light,
        statusBarBrightness: Brightness.dark, // iOS
      ),
    );

    // Total animation duration — all staggered intervals fit within this.
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1400),
    );

    // ── Logo opacity: 0 → 1 in first 600 ms ──────────────────────
    _logoOpacity = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _controller,
        curve: const Interval(0.0, 0.43, curve: Curves.easeOut),
      ),
    );

    // ── Logo slide: 40 px below → natural position in first 600 ms ──
    _logoSlide = Tween<Offset>(
      begin: const Offset(0, 0.12),
      end: Offset.zero,
    ).animate(
      CurvedAnimation(
        parent: _controller,
        curve: const Interval(0.0, 0.43, curve: Curves.easeOutCubic),
      ),
    );

    // ── Tagline: appears at 400 ms, fades in over 400 ms ──────────
    _taglineOpacity = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _controller,
        curve: const Interval(0.29, 0.57, curve: Curves.easeOut),
      ),
    );

    // ── Loader: appears at 700 ms, fades in over 300 ms ───────────
    _loaderOpacity = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _controller,
        curve: const Interval(0.50, 0.71, curve: Curves.easeOut),
      ),
    );

    // Start animation immediately.
    _controller.forward();

    // Navigate after 2.8 s total (animation finishes at ~1.4 s,
    // giving the user time to read the screen before transitioning).
    Future.delayed(const Duration(milliseconds: 2800), _navigateNext);
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  // ── Navigation ─────────────────────────────────────────────────
  void _navigateNext() {
    if (!mounted) return;

    // Fade-transition to the next screen.
    Navigator.of(context).pushReplacementNamed(
      AppRouter.login, // → swap to AppRouter.home once auth is built
    );
  }

  // ── Build ───────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.sizeOf(context);

    return Scaffold(
      // Extend body behind status bar so gradient fills edge-to-edge.
      extendBodyBehindAppBar: true,
      body: Container(
        width: size.width,
        height: size.height,
        // ── Brand gradient background ────────────────────────────
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [
              AppColors.primaryDark,   // #082A3D — top (deep anchor)
              AppColors.primary,       // #0F3D56 — mid  (brand)
              AppColors.primaryLight,  // #1A5E80 — bottom (lighter lift)
            ],
            stops: [0.0, 0.55, 1.0],
          ),
        ),
        child: SafeArea(
          child: Column(
            children: [
              // ── Top spacer (⅓ of screen height) ───────────────
              SizedBox(height: size.height * 0.28),

              // ── Animated logo block ────────────────────────────
              FadeTransition(
                opacity: _logoOpacity,
                child: SlideTransition(
                  position: _logoSlide,
                  child: const _LogoBlock(),
                ),
              ),

              const SizedBox(height: 20),

              // ── Animated tagline ───────────────────────────────
              FadeTransition(
                opacity: _taglineOpacity,
                child: const _Tagline(),
              ),

              // ── Push loader to bottom ──────────────────────────
              const Spacer(),

              // ── Animated loading indicator ─────────────────────
              FadeTransition(
                opacity: _loaderOpacity,
                child: const _LoadingIndicator(),
              ),

              const SizedBox(height: 48),
            ],
          ),
        ),
      ),
    );
  }
}

// ── Logo Block ──────────────────────────────────────────────────
//
// Parking "P" icon inside a frosted rounded-square container,
// followed by the app name in white.
//
class _LogoBlock extends StatelessWidget {
  const _LogoBlock();

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        // ── Icon container ────────────────────────────────────────
        Container(
          width: 100,
          height: 100,
          decoration: BoxDecoration(
            // White at 15% opacity gives a frosted-glass feel
            // without a heavy white block on the gradient.
            color: AppColors.white.withAlpha(38),
            borderRadius: BorderRadius.circular(28),
            border: Border.all(
              color: AppColors.white.withAlpha(51),
              width: 1.5,
            ),
            // Subtle glow that reinforces the brand without needing
            // a BackdropFilter (which has performance implications).
            boxShadow: [
              BoxShadow(
                color: AppColors.primaryDark.withAlpha(102),
                blurRadius: 32,
                spreadRadius: 0,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: const Center(
            child: Icon(
              Icons.local_parking_rounded,
              size: 56,
              color: AppColors.white,
            ),
          ),
        ),

        const SizedBox(height: 24),

        // ── App name ──────────────────────────────────────────────
        const Text(
          'Smart Parking',
          style: TextStyle(
            color: AppColors.white,
            fontSize: 30,
            fontWeight: FontWeight.w700,
            letterSpacing: 0.5,
            height: 1.2,
          ),
        ),

        const SizedBox(height: 6),

        // ── Subtle divider line ───────────────────────────────────
        Container(
          width: 40,
          height: 2,
          decoration: BoxDecoration(
            color: AppColors.secondary,
            borderRadius: BorderRadius.circular(1),
          ),
        ),
      ],
    );
  }
}

// ── Tagline ─────────────────────────────────────────────────────
class _Tagline extends StatelessWidget {
  const _Tagline();

  @override
  Widget build(BuildContext context) {
    return const Text(
      'Park Smart. Save Time.',
      style: TextStyle(
        color: AppColors.white,
        fontSize: 15,
        fontWeight: FontWeight.w400,
        letterSpacing: 1.2,
        height: 1.4,
      ),
    );
  }
}

// ── Loading Indicator ────────────────────────────────────────────
//
// A narrow linear progress bar at the bottom, tinted white at
// 40% — unobtrusive but confirms the app is alive.
//
class _LoadingIndicator extends StatelessWidget {
  const _LoadingIndicator();

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 80),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          ClipRRect(
            borderRadius: BorderRadius.circular(2),
            child: LinearProgressIndicator(
              minHeight: 2.5,
              backgroundColor: AppColors.white.withAlpha(51),
              valueColor: const AlwaysStoppedAnimation<Color>(
                AppColors.secondary,
              ),
            ),
          ),
          const SizedBox(height: 12),
          Text(
            'Loading…',
            style: TextStyle(
              color: AppColors.white.withAlpha(153),
              fontSize: 12,
              fontWeight: FontWeight.w400,
              letterSpacing: 0.5,
            ),
          ),
        ],
      ),
    );
  }
}