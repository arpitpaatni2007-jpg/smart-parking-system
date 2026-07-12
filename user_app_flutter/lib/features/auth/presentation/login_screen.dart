import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_colors.dart';
import '../../../config/routes/app_routes.dart';


// ============================================================
// LoginScreen
// ============================================================
//
// Production-quality login screen for the Smart Parking app.
//
// WHAT IT DOES:
//   1. Locks the status bar to dark icons over the light surface.
//   2. Validates email format and password length inline before
//      submission — no external validation package needed.
//   3. Manages password visibility toggle via local state.
//   4. Shows a loading spinner inside the Login button while
//      the (future) auth call is in flight.
//   5. "Continue with Google" is UI-only — wired up when the
//      auth layer is added.
//   6. "Forgot Password" and "Sign Up" are no-ops until routed.
//
// DESIGN:
//   Single-scroll column on a warm off-white background (AppColors.background).
//   Top logo block mirrors the SplashScreen icon container style —
//   primary-coloured rounded square with the parking "P" icon.
//   Inputs follow the InputDecorationTheme from AppTheme exactly.
//   Primary CTA is a full-width FilledButton in AppColors.primary.
//   Google button is an OutlinedButton with the "G" monogram.
//
// ARCHITECTURE:
//   Pure StatefulWidget — no Riverpod, no Bloc, no Provider.
//   All state is local. Replace _handleLogin() body with your
//   auth repository call when the data layer is ready.
// ============================================================

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  // ── Form ─────────────────────────────────────────────────────
  final _formKey = GlobalKey<FormState>();

  // ── Controllers ──────────────────────────────────────────────
  final _emailController    = TextEditingController();
  final _passwordController = TextEditingController();

  // ── Focus Nodes ──────────────────────────────────────────────
  final _emailFocus    = FocusNode();
  final _passwordFocus = FocusNode();

  // ── Local State ───────────────────────────────────────────────
  bool _obscurePassword = true;
  bool _isLoading       = false;

  @override
  void initState() {
    super.initState();

    // Dark icons over the warm-white background.
    SystemChrome.setSystemUIOverlayStyle(
      const SystemUiOverlayStyle(
        statusBarColor:           Colors.transparent,
        statusBarIconBrightness:  Brightness.dark,
        statusBarBrightness:      Brightness.light, // iOS
      ),
    );
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    _emailFocus.dispose();
    _passwordFocus.dispose();
    super.dispose();
  }

  // ── Validation ────────────────────────────────────────────────

  String? _validateEmail(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Email address is required.';
    }
    // Simple RFC-friendly pattern — good enough for field validation.
    final emailRegex = RegExp(r'^[\w.+-]+@[\w-]+\.[a-zA-Z]{2,}$');
    if (!emailRegex.hasMatch(value.trim())) {
      return 'Please enter a valid email address.';
    }
    return null;
  }

  String? _validatePassword(String? value) {
    if (value == null || value.isEmpty) {
      return 'Password is required.';
    }
    if (value.length < 6) {
      return 'Password must be at least 6 characters.';
    }
    return null;
  }

  // ── Submit ────────────────────────────────────────────────────

  Future<void> _handleLogin() async {
  FocusScope.of(context).unfocus();
  if (!(_formKey.currentState?.validate() ?? false)) return;
  setState(() => _isLoading = true);
  // TODO: replace with your auth repository call.
  await Future.delayed(const Duration(seconds: 2));
  if (!mounted) return;
  setState(() => _isLoading = false);
  Navigator.of(context).pushReplacementNamed(AppRoutes.home);
}

  void _handleForgotPassword() {
  Navigator.of(context).pushNamed(AppRoutes.forgotPassword);
}

  void _handleGoogleSignIn() {
    // TODO: trigger Google OAuth flow
  }

  void _handleSignUp() {
  Navigator.of(context).pushNamed(AppRoutes.register);
}
  // ── Build ─────────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final theme      = Theme.of(context);
    final textTheme  = theme.textTheme;
    final screenSize = MediaQuery.sizeOf(context);

    return Scaffold(
      backgroundColor: AppColors.background,
      // Resize when keyboard appears so fields stay visible.
      resizeToAvoidBottomInset: true,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: EdgeInsets.symmetric(
            horizontal: screenSize.width > 600 ? screenSize.width * 0.15 : 24.0,
            vertical: 24.0,
          ),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [

                SizedBox(height: screenSize.height * 0.04),

                // ── Logo Block ───────────────────────────────────
                const _LogoBlock(),

                SizedBox(height: screenSize.height * 0.05),

                // ── Welcome Title ────────────────────────────────
                Text(
                  'Welcome Back',
                  style: textTheme.headlineMedium?.copyWith(
                    color:      AppColors.textPrimary,
                    fontWeight: FontWeight.w700,
                  ),
                ),

                const SizedBox(height: 6),

                // ── Subtitle ─────────────────────────────────────
                Text(
                  'Login to continue',
                  style: textTheme.bodyMedium?.copyWith(
                    color: AppColors.textSecondary,
                  ),
                ),

                const SizedBox(height: 32),

                // ── Email Field ───────────────────────────────────
                _FieldLabel(label: 'Email Address'),
                const SizedBox(height: 8),
                TextFormField(
                  controller:      _emailController,
                  focusNode:       _emailFocus,
                  keyboardType:    TextInputType.emailAddress,
                  textInputAction: TextInputAction.next,
                  autocorrect:     false,
                  validator:       _validateEmail,
                  onFieldSubmitted: (_) =>
                      FocusScope.of(context).requestFocus(_passwordFocus),
                  decoration: _inputDecoration(
                    hintText:    'you@example.com',
                    prefixIcon:  Icons.email_outlined,
                  ),
                ),

                const SizedBox(height: 20),

                // ── Password Field ────────────────────────────────
                _FieldLabel(label: 'Password'),
                const SizedBox(height: 8),
                TextFormField(
                  controller:      _passwordController,
                  focusNode:       _passwordFocus,
                  obscureText:     _obscurePassword,
                  textInputAction: TextInputAction.done,
                  validator:       _validatePassword,
                  onFieldSubmitted: (_) => _handleLogin(),
                  decoration: _inputDecoration(
                    hintText:   'Enter your password',
                    prefixIcon: Icons.lock_outline_rounded,
                    suffixIcon: IconButton(
                      icon: Icon(
                        _obscurePassword
                            ? Icons.visibility_outlined
                            : Icons.visibility_off_outlined,
                        color: AppColors.textSecondary,
                        size: 22,
                      ),
                      onPressed: () =>
                          setState(() => _obscurePassword = !_obscurePassword),
                      tooltip: _obscurePassword
                          ? 'Show password'
                          : 'Hide password',
                    ),
                  ),
                ),

                const SizedBox(height: 12),

                // ── Forgot Password ───────────────────────────────
                Align(
                  alignment: Alignment.centerRight,
                  child: TextButton(
                    onPressed: _handleForgotPassword,
                    style: TextButton.styleFrom(
                      foregroundColor: AppColors.primary,
                      padding: const EdgeInsets.symmetric(
                        horizontal: 4, vertical: 4,
                      ),
                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                    ),
                    child: Text(
                      'Forgot Password?',
                      style: textTheme.bodyMedium?.copyWith(
                        color:      AppColors.primary,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ),

                const SizedBox(height: 28),

                // ── Login Button ──────────────────────────────────
                SizedBox(
                  height: 52,
                  child: FilledButton(
                    onPressed: _isLoading ? null : _handleLogin,
                    style: FilledButton.styleFrom(
                      backgroundColor:          AppColors.primary,
                      disabledBackgroundColor:  AppColors.primaryLighter,
                      foregroundColor:          AppColors.onPrimary,
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
                        : const Text('Login'),
                  ),
                ),

                const SizedBox(height: 32),

                // ── OR Divider ────────────────────────────────────
                const _OrDivider(),

                const SizedBox(height: 24),

                // ── Google Button ─────────────────────────────────
                SizedBox(
                  height: 52,
                  child: OutlinedButton(
                    onPressed: _handleGoogleSignIn,
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.textPrimary,
                      side: const BorderSide(
                        color: AppColors.divider,
                        width: 1.5,
                      ),
                      backgroundColor: AppColors.surface,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      textStyle: textTheme.labelLarge?.copyWith(
                        fontSize:   15,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        // Google "G" monogram — no asset needed.
                        Container(
                          width:  26,
                          height: 26,
                          decoration: BoxDecoration(
                            color:        AppColors.primaryLighter,
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: const Center(
                            child: Text(
                              'G',
                              style: TextStyle(
                                color:      AppColors.white,
                                fontSize:   14,
                                fontWeight: FontWeight.w700,
                                height:     1,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        const Text('Continue with Google'),
                      ],
                    ),
                  ),
                ),

                const SizedBox(height: 40),

                // ── Sign Up Row ───────────────────────────────────
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      "Don't have an account? ",
                      style: textTheme.bodyMedium?.copyWith(
                        color: AppColors.textSecondary,
                      ),
                    ),
                    GestureDetector(
                      onTap: _handleSignUp,
                      child: Text(
                        'Sign Up',
                        style: textTheme.bodyMedium?.copyWith(
                          color:      AppColors.primary,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 24),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // ── Input Decoration Helper ───────────────────────────────────

  InputDecoration _inputDecoration({
    required String   hintText,
    required IconData prefixIcon,
    Widget?           suffixIcon,
  }) {
    return InputDecoration(
      hintText:  hintText,
      prefixIcon: Icon(prefixIcon, color: AppColors.textSecondary, size: 20),
      suffixIcon: suffixIcon,
      filled:     true,
      fillColor:  AppColors.surface,
      hintStyle: const TextStyle(
        color:    AppColors.textTertiary,
        fontSize: 14,
      ),
      contentPadding: const EdgeInsets.symmetric(
        horizontal: 16, vertical: 16,
      ),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide:   const BorderSide(color: AppColors.divider, width: 1.5),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide:   const BorderSide(color: AppColors.divider, width: 1.5),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide:   const BorderSide(color: AppColors.primary, width: 2),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide:   const BorderSide(color: AppColors.error, width: 1.5),
      ),
      focusedErrorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide:   const BorderSide(color: AppColors.error, width: 2),
      ),
    );
  }
}

// ── Private Widgets ───────────────────────────────────────────

// ── Logo Block ────────────────────────────────────────────────
//
// Mirrors the SplashScreen icon container — primary-coloured
// rounded square with the parking "P" icon — so brand identity
// is consistent across both screens.
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
              color:       AppColors.shadow,
              blurRadius:  24,
              spreadRadius: 0,
              offset:      const Offset(0, 6),
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

// ── Field Label ───────────────────────────────────────────────
class _FieldLabel extends StatelessWidget {
  final String label;
  const _FieldLabel({required this.label});

  @override
  Widget build(BuildContext context) {
    return Text(
      label,
      style: Theme.of(context).textTheme.labelLarge?.copyWith(
        color:      AppColors.textPrimary,
        fontWeight: FontWeight.w600,
        fontSize:   13,
      ),
    );
  }
}

// ── OR Divider ────────────────────────────────────────────────
class _OrDivider extends StatelessWidget {
  const _OrDivider();

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        const Expanded(
          child: Divider(color: AppColors.divider, thickness: 1),
        ),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: Text(
            'OR',
            style: Theme.of(context).textTheme.bodySmall?.copyWith(
              color:         AppColors.textTertiary,
              fontWeight:    FontWeight.w600,
              letterSpacing: 1.2,
            ),
          ),
        ),
        const Expanded(
          child: Divider(color: AppColors.divider, thickness: 1),
        ),
      ],
    );
  }
}