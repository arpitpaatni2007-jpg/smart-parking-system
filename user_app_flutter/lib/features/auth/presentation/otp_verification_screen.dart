import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_colors.dart';

// ============================================================
// OtpVerificationScreen
// ============================================================
//
// Presents a 6-digit OTP input flow for email verification.
//
// WHAT IT DOES:
//   1. Locks the status bar to dark icons over the light surface.
//   2. Renders six individual OTP boxes — each accepts exactly
//      one digit, auto-advances focus on entry, and auto-moves
//      back on backspace when the current box is empty.
//   3. Runs a 60-second countdown timer. "Resend OTP" is
//      disabled and shows the remaining seconds until it
//      reaches zero, then becomes tappable.
//   4. Validates that all six boxes are filled before allowing
//      submission.
//   5. Shows a loading spinner inside the Verify button while
//      the (future) network call is in flight.
//   6. Resets the countdown and clears boxes on Resend.
//
// ARCHITECTURE:
//   Pure StatefulWidget — no Riverpod, no Bloc, no Provider.
//   Replace _handleVerify() and _handleResend() bodies with
//   your auth repository calls when the data layer is ready.
// ============================================================

class OtpVerificationScreen extends StatefulWidget {
  const OtpVerificationScreen({super.key});

  @override
  State<OtpVerificationScreen> createState() => _OtpVerificationScreenState();
}

class _OtpVerificationScreenState extends State<OtpVerificationScreen> {
  // ── OTP Config ────────────────────────────────────────────────
  static const int _otpLength       = 6;
  static const int _countdownSeconds = 60;

  // ── Controllers & Focus Nodes ─────────────────────────────────
  // One controller and one focus node per OTP box.
  late final List<TextEditingController> _controllers;
  late final List<FocusNode>             _focusNodes;

  // ── Timer ─────────────────────────────────────────────────────
  Timer?  _countdownTimer;
  int     _secondsRemaining = _countdownSeconds;
  bool    get _canResend    => _secondsRemaining == 0;

  // ── Local State ───────────────────────────────────────────────
  bool _isLoading = false;
  // Tracks whether every box has a digit — gates the Verify button.
  bool get _isOtpComplete =>
      _controllers.every((c) => c.text.isNotEmpty);

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

    _controllers = List.generate(
      _otpLength,
      (_) => TextEditingController(),
    );
    _focusNodes = List.generate(
      _otpLength,
      (_) => FocusNode(),
    );

    _startCountdown();
  }

  @override
  void dispose() {
    _countdownTimer?.cancel();
    for (final c in _controllers) {
      c.dispose();
    }
    for (final f in _focusNodes) {
      f.dispose();
    }
    super.dispose();
  }

  // ── Timer Logic ───────────────────────────────────────────────

  void _startCountdown() {
    _countdownTimer?.cancel();
    setState(() => _secondsRemaining = _countdownSeconds);

    _countdownTimer = Timer.periodic(
      const Duration(seconds: 1),
      (timer) {
        if (_secondsRemaining == 0) {
          timer.cancel();
        } else {
          if (mounted) {
            setState(() => _secondsRemaining--);
          }
        }
      },
    );
  }

  String get _timerLabel {
    final mins = (_secondsRemaining ~/ 60).toString().padLeft(2, '0');
    final secs = (_secondsRemaining % 60).toString().padLeft(2, '0');
    return '$mins:$secs';
  }

  // ── OTP Input Logic ───────────────────────────────────────────

  /// Called on every keystroke inside an OTP box.
  /// Handles digit entry (advance) and backspace (retreat).
  void _onOtpChanged(String value, int index) {
    if (value.length == 1) {
      // Digit entered — move to next box, or dismiss keyboard on last.
      if (index < _otpLength - 1) {
        FocusScope.of(context).requestFocus(_focusNodes[index + 1]);
      } else {
        _focusNodes[index].unfocus();
      }
    }
    // Rebuild so the Verify button enable/disable state updates.
    setState(() {});
  }

  /// Called on key events so we can intercept Backspace when
  /// the current box is already empty and retreat focus.
  void _onKeyEvent(KeyEvent event, int index) {
    if (event is KeyDownEvent &&
        event.logicalKey == LogicalKeyboardKey.backspace &&
        _controllers[index].text.isEmpty &&
        index > 0) {
      // Move focus back and clear the previous box.
      FocusScope.of(context).requestFocus(_focusNodes[index - 1]);
      _controllers[index - 1].clear();
      setState(() {});
    }
  }

  String _currentOtp() =>
      _controllers.map((c) => c.text).join();

  void _clearOtp() {
    for (final c in _controllers) {
      c.clear();
    }
    setState(() {});
    // Return focus to the first box.
    FocusScope.of(context).requestFocus(_focusNodes[0]);
  }

  // ── Actions ───────────────────────────────────────────────────

  Future<void> _handleVerify() async {
    if (!_isOtpComplete) return;
    FocusScope.of(context).unfocus();

    setState(() => _isLoading = true);

    // TODO: replace with your auth repository call.
    // e.g. await ref.read(authRepositoryProvider).verifyOtp(_currentOtp());
    await Future.delayed(const Duration(seconds: 2));

    if (!mounted) return;
    setState(() => _isLoading = false);

    // TODO: navigate on success → AppRouter.home
  }

  Future<void> _handleResend() async {
    if (!_canResend) return;

    _clearOtp();
    _startCountdown();

    // TODO: replace with your auth repository call.
    // e.g. await ref.read(authRepositoryProvider).resendOtp();
  }

  // ── Build ─────────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final theme      = Theme.of(context);
    final textTheme  = theme.textTheme;
    final screenSize = MediaQuery.sizeOf(context);

    return Scaffold(
      backgroundColor:          AppColors.background,
      resizeToAvoidBottomInset: true,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: EdgeInsets.symmetric(
            horizontal: screenSize.width > 600
                ? screenSize.width * 0.15
                : 24.0,
            vertical: 24.0,
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [

              SizedBox(height: screenSize.height * 0.04),

              // ── Logo Block ─────────────────────────────────
              const _LogoBlock(),

              SizedBox(height: screenSize.height * 0.05),

              // ── Title ──────────────────────────────────────
              Text(
                'Verify OTP',
                style: textTheme.headlineMedium?.copyWith(
                  color:      AppColors.textPrimary,
                  fontWeight: FontWeight.w700,
                ),
              ),

              const SizedBox(height: 8),

              // ── Subtitle ───────────────────────────────────
              Text(
                'Enter the 6-digit code sent to your email.',
                style: textTheme.bodyMedium?.copyWith(
                  color:  AppColors.textSecondary,
                  height: 1.5,
                ),
              ),

              SizedBox(height: screenSize.height * 0.05),

              // ── OTP Boxes ──────────────────────────────────
              _OtpInputRow(
                controllers: _controllers,
                focusNodes:  _focusNodes,
                onChanged:   _onOtpChanged,
                onKeyEvent:  _onKeyEvent,
              ),

              const SizedBox(height: 12),

              // ── Countdown / Resend Row ─────────────────────
              _ResendRow(
                canResend:        _canResend,
                timerLabel:       _timerLabel,
                secondsRemaining: _secondsRemaining,
                onResend:         _handleResend,
              ),

              SizedBox(height: screenSize.height * 0.06),

              // ── Verify Button ──────────────────────────────
              SizedBox(
                height: 52,
                child: FilledButton(
                  onPressed: (_isOtpComplete && !_isLoading)
                      ? _handleVerify
                      : null,
                  style: FilledButton.styleFrom(
                    backgroundColor:         AppColors.primary,
                    disabledBackgroundColor: AppColors.divider,
                    foregroundColor:         AppColors.onPrimary,
                    disabledForegroundColor: AppColors.textTertiary,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    textStyle: textTheme.labelLarge?.copyWith(
                      fontSize:      16,
                      fontWeight:    FontWeight.w600,
                      letterSpacing: 0.5,
                    ),
                  ),
                  child: _isLoading
                      ? const SizedBox(
                          width:  22,
                          height: 22,
                          child:  CircularProgressIndicator(
                            strokeWidth: 2.5,
                            color:       AppColors.onPrimary,
                          ),
                        )
                      : const Text('Verify OTP'),
                ),
              ),

              const SizedBox(height: 24),
            ],
          ),
        ),
      ),
    );
  }
}

// ── OTP Input Row ─────────────────────────────────────────────
//
// Renders six evenly spaced, fixed-size text boxes.
// Each box is a single-digit TextFormField styled to match
// the input fields in LoginScreen and RegisterScreen.
//
class _OtpInputRow extends StatelessWidget {
  final List<TextEditingController> controllers;
  final List<FocusNode>             focusNodes;
  final void Function(String, int)  onChanged;
  final void Function(KeyEvent, int) onKeyEvent;

  const _OtpInputRow({
    required this.controllers,
    required this.focusNodes,
    required this.onChanged,
    required this.onKeyEvent,
  });

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.sizeOf(context).width;

    // Scale box size relative to screen — never too small on narrow phones,
    // never oversized on tablets.
    final boxSize = ((screenWidth - 48) / 6).clamp(44.0, 60.0);

    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: List.generate(
        controllers.length,
        (index) => _OtpBox(
          controller: controllers[index],
          focusNode:  focusNodes[index],
          boxSize:    boxSize,
          onChanged:  (v) => onChanged(v, index),
          onKeyEvent: (e) => onKeyEvent(e, index),
        ),
      ),
    );
  }
}

// ── Single OTP Box ────────────────────────────────────────────
class _OtpBox extends StatelessWidget {
  final TextEditingController           controller;
  final FocusNode                       focusNode;
  final double                          boxSize;
  final ValueChanged<String>            onChanged;
  final void Function(KeyEvent)         onKeyEvent;

  const _OtpBox({
    required this.controller,
    required this.focusNode,
    required this.boxSize,
    required this.onChanged,
    required this.onKeyEvent,
  });

  @override
  Widget build(BuildContext context) {
    final bool isFilled = controller.text.isNotEmpty;

    return KeyboardListener(
      focusNode: FocusNode(), // Separate node for key events only
      onKeyEvent: onKeyEvent,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        width:  boxSize,
        height: boxSize + 4,
        decoration: BoxDecoration(
          color:        isFilled ? AppColors.primary.withAlpha(10) : AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: focusNode.hasFocus
                ? AppColors.primary
                : isFilled
                    ? AppColors.primaryLight
                    : AppColors.divider,
            width: focusNode.hasFocus || isFilled ? 2.0 : 1.5,
          ),
          boxShadow: focusNode.hasFocus
              ? [
                  BoxShadow(
                    color:       AppColors.primary.withAlpha(30),
                    blurRadius:  8,
                    spreadRadius: 0,
                    offset:      const Offset(0, 2),
                  ),
                ]
              : null,
        ),
        child: Center(
          child: TextField(
            controller:      controller,
            focusNode:       focusNode,
            onChanged:       onChanged,
            keyboardType:    TextInputType.number,
            textAlign:       TextAlign.center,
            maxLength:       1,
            obscureText:     false,
            style: TextStyle(
              color:      AppColors.primary,
              fontSize:   boxSize * 0.40,
              fontWeight: FontWeight.w700,
              height:     1,
            ),
            inputFormatters: [
              FilteringTextInputFormatter.digitsOnly,
            ],
            decoration: const InputDecoration(
              counterText:    '',
              border:         InputBorder.none,
              enabledBorder:  InputBorder.none,
              focusedBorder:  InputBorder.none,
              contentPadding: EdgeInsets.zero,
              isDense:        true,
            ),
          ),
        ),
      ),
    );
  }
}

// ── Resend Row ────────────────────────────────────────────────
//
// Shows the countdown timer while it is running.
// Once the countdown hits zero the "Resend OTP" link becomes
// tappable and the timer label is hidden.
//
class _ResendRow extends StatelessWidget {
  final bool         canResend;
  final String       timerLabel;
  final int          secondsRemaining;
  final VoidCallback onResend;

  const _ResendRow({
    required this.canResend,
    required this.timerLabel,
    required this.secondsRemaining,
    required this.onResend,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Text(
          "Didn't receive the code? ",
          style: textTheme.bodyMedium?.copyWith(
            color: AppColors.textSecondary,
          ),
        ),
        canResend
            // ── Active resend link ──────────────────────────
            ? GestureDetector(
                onTap: onResend,
                child: Text(
                  'Resend OTP',
                  style: textTheme.bodyMedium?.copyWith(
                    color:      AppColors.primary,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              )
            // ── Countdown label ─────────────────────────────
            : Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    'Resend in ',
                    style: textTheme.bodyMedium?.copyWith(
                      color: AppColors.textTertiary,
                    ),
                  ),
                  // Countdown badge
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical:   2,
                    ),
                    decoration: BoxDecoration(
                      color:        AppColors.primaryLighter.withAlpha(20),
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(
                        color: AppColors.primaryLighter.withAlpha(60),
                        width: 1,
                      ),
                    ),
                    child: Text(
                      timerLabel,
                      style: textTheme.labelMedium?.copyWith(
                        color:      AppColors.primary,
                        fontWeight: FontWeight.w700,
                        fontFeatures: const [FontFeature.tabularFigures()],
                      ),
                    ),
                  ),
                ],
              ),
      ],
    );
  }
}

// ── Logo Block ────────────────────────────────────────────────
//
// Mirrors LoginScreen, RegisterScreen, ForgotPasswordScreen —
// primary-coloured rounded square with the parking "P" icon.
//
class _LogoBlock extends StatelessWidget {
  const _LogoBlock();

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Container(
        width:  72,
        height: 72,
        decoration: BoxDecoration(
          color:        AppColors.primary,
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(
              color:        AppColors.shadow,
              blurRadius:   24,
              spreadRadius: 0,
              offset:       const Offset(0, 6),
            ),
          ],
        ),
        child: const Center(
          child: Icon(
            Icons.local_parking_rounded,
            size:  38,
            color: AppColors.onPrimary,
          ),
        ),
      ),
    );
  }
}