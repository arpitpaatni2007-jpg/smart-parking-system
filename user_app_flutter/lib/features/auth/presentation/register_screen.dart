import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_colors.dart';

// ============================================================
// RegisterScreen
// ============================================================
//
// Production-quality registration screen for the Smart Parking app.
//
// WHAT IT DOES:
//   1. Locks the status bar to dark icons over the light surface.
//   2. Validates all five fields inline before submission:
//        - Full Name  → required, min 2 chars
//        - Email      → required, valid format
//        - Phone      → required, digits only, 10 chars min
//        - Password   → required, min 6 chars
//        - Confirm    → must match Password exactly
//   3. Manages independent visibility toggles for both
//      password fields via local state.
//   4. Shows a loading spinner inside the Register button while
//      the (future) auth call is in flight.
//   5. "Continue with Google" and "Login" are UI-only — wired
//      up when the auth/routing layer is added.
//
// DESIGN:
//   Mirrors LoginScreen exactly — same spacing, typography,
//   border-radius, shadows, and button styles. Warm off-white
//   background (AppColors.background), primary-coloured logo
//   block, full-width FilledButton CTA, outlined Google button.
//
// ARCHITECTURE:
//   Pure StatefulWidget — no Riverpod, no Bloc, no Provider.
//   All state is local. Replace _handleRegister() body with
//   your auth repository call when the data layer is ready.
// ============================================================

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  // ── Form ──────────────────────────────────────────────────────
  final _formKey = GlobalKey<FormState>();

  // ── Controllers ───────────────────────────────────────────────
  final _nameController            = TextEditingController();
  final _emailController           = TextEditingController();
  final _phoneController           = TextEditingController();
  final _passwordController        = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  // ── Focus Nodes ───────────────────────────────────────────────
  final _nameFocus            = FocusNode();
  final _emailFocus           = FocusNode();
  final _phoneFocus           = FocusNode();
  final _passwordFocus        = FocusNode();
  final _confirmPasswordFocus = FocusNode();

  // ── Local State ───────────────────────────────────────────────
  bool _obscurePassword        = true;
  bool _obscureConfirmPassword = true;
  bool _isLoading              = false;

  @override
  void initState() {
    super.initState();

    // Dark icons over the warm-white background.
    SystemChrome.setSystemUIOverlayStyle(
      const SystemUiOverlayStyle(
        statusBarColor:          Colors.transparent,
        statusBarIconBrightness: Brightness.dark,
        statusBarBrightness:     Brightness.light, // iOS
      ),
    );
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    _nameFocus.dispose();
    _emailFocus.dispose();
    _phoneFocus.dispose();
    _passwordFocus.dispose();
    _confirmPasswordFocus.dispose();
    super.dispose();
  }

  // ── Validation ────────────────────────────────────────────────

  String? _validateName(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Full name is required.';
    }
    if (value.trim().length < 2) {
      return 'Name must be at least 2 characters.';
    }
    return null;
  }

  String? _validateEmail(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Email address is required.';
    }
    final emailRegex = RegExp(r'^[\w.+-]+@[\w-]+\.[a-zA-Z]{2,}$');
    if (!emailRegex.hasMatch(value.trim())) {
      return 'Please enter a valid email address.';
    }
    return null;
  }

  String? _validatePhone(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Phone number is required.';
    }
    final digitsOnly = value.trim().replaceAll(RegExp(r'\D'), '');
    if (digitsOnly.length < 10) {
      return 'Please enter a valid phone number.';
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

  String? _validateConfirmPassword(String? value) {
    if (value == null || value.isEmpty) {
      return 'Please confirm your password.';
    }
    if (value != _passwordController.text) {
      return 'Passwords do not match.';
    }
    return null;
  }

  // ── Submit ────────────────────────────────────────────────────

  Future<void> _handleRegister() async {
    // Dismiss keyboard before validating.
    FocusScope.of(context).unfocus();

    if (!(_formKey.currentState?.validate() ?? false)) return;

    setState(() => _isLoading = true);

    // TODO: replace with your auth repository call.
    // e.g. await ref.read(authRepositoryProvider).register(name, email, phone, password);
    await Future.delayed(const Duration(seconds: 2));

    if (!mounted) return;
    setState(() => _isLoading = false);

    // TODO: navigate on success → AppRouter.home or AppRouter.login
  }

  void _handleGoogleSignUp() {
    // TODO: trigger Google OAuth flow
  }

  void _handleLogin() {
    // TODO: navigate → AppRouter.login
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
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [

                SizedBox(height: screenSize.height * 0.03),

                // ── Logo Block ─────────────────────────────────
                const _LogoBlock(),

                SizedBox(height: screenSize.height * 0.04),

                // ── Title ──────────────────────────────────────
                Text(
                  'Create Account',
                  style: textTheme.headlineMedium?.copyWith(
                    color:      AppColors.textPrimary,
                    fontWeight: FontWeight.w700,
                  ),
                ),

                const SizedBox(height: 6),

                // ── Subtitle ───────────────────────────────────
                Text(
                  'Sign up to get started',
                  style: textTheme.bodyMedium?.copyWith(
                    color: AppColors.textSecondary,
                  ),
                ),

                const SizedBox(height: 28),

                // ── Full Name ──────────────────────────────────
                const _FieldLabel(label: 'Full Name'),
                const SizedBox(height: 8),
                TextFormField(
                  controller:       _nameController,
                  focusNode:        _nameFocus,
                  keyboardType:     TextInputType.name,
                  textInputAction:  TextInputAction.next,
                  textCapitalization: TextCapitalization.words,
                  autocorrect:      false,
                  validator:        _validateName,
                  onFieldSubmitted: (_) =>
                      FocusScope.of(context).requestFocus(_emailFocus),
                  decoration: _inputDecoration(
                    hintText:   'John Doe',
                    prefixIcon: Icons.person_outline_rounded,
                  ),
                ),

                const SizedBox(height: 20),

                // ── Email ──────────────────────────────────────
                const _FieldLabel(label: 'Email Address'),
                const SizedBox(height: 8),
                TextFormField(
                  controller:       _emailController,
                  focusNode:        _emailFocus,
                  keyboardType:     TextInputType.emailAddress,
                  textInputAction:  TextInputAction.next,
                  autocorrect:      false,
                  validator:        _validateEmail,
                  onFieldSubmitted: (_) =>
                      FocusScope.of(context).requestFocus(_phoneFocus),
                  decoration: _inputDecoration(
                    hintText:   'you@example.com',
                    prefixIcon: Icons.email_outlined,
                  ),
                ),

                const SizedBox(height: 20),

                // ── Phone Number ───────────────────────────────
                const _FieldLabel(label: 'Phone Number'),
                const SizedBox(height: 8),
                TextFormField(
                  controller:       _phoneController,
                  focusNode:        _phoneFocus,
                  keyboardType:     TextInputType.phone,
                  textInputAction:  TextInputAction.next,
                  validator:        _validatePhone,
                  inputFormatters: [
                    FilteringTextInputFormatter.digitsOnly,
                    LengthLimitingTextInputFormatter(15),
                  ],
                  onFieldSubmitted: (_) =>
                      FocusScope.of(context).requestFocus(_passwordFocus),
                  decoration: _inputDecoration(
                    hintText:   '9876543210',
                    prefixIcon: Icons.phone_outlined,
                  ),
                ),

                const SizedBox(height: 20),

                // ── Password ───────────────────────────────────
                const _FieldLabel(label: 'Password'),
                const SizedBox(height: 8),
                TextFormField(
                  controller:       _passwordController,
                  focusNode:        _passwordFocus,
                  obscureText:      _obscurePassword,
                  textInputAction:  TextInputAction.next,
                  validator:        _validatePassword,
                  onFieldSubmitted: (_) =>
                      FocusScope.of(context).requestFocus(_confirmPasswordFocus),
                  decoration: _inputDecoration(
                    hintText:   'Minimum 6 characters',
                    prefixIcon: Icons.lock_outline_rounded,
                    suffixIcon: IconButton(
                      icon: Icon(
                        _obscurePassword
                            ? Icons.visibility_outlined
                            : Icons.visibility_off_outlined,
                        color: AppColors.textSecondary,
                        size:  22,
                      ),
                      onPressed: () =>
                          setState(() => _obscurePassword = !_obscurePassword),
                      tooltip: _obscurePassword
                          ? 'Show password'
                          : 'Hide password',
                    ),
                  ),
                ),

                const SizedBox(height: 20),

                // ── Confirm Password ───────────────────────────
                const _FieldLabel(label: 'Confirm Password'),
                const SizedBox(height: 8),
                TextFormField(
                  controller:       _confirmPasswordController,
                  focusNode:        _confirmPasswordFocus,
                  obscureText:      _obscureConfirmPassword,
                  textInputAction:  TextInputAction.done,
                  validator:        _validateConfirmPassword,
                  onFieldSubmitted: (_) => _handleRegister(),
                  decoration: _inputDecoration(
                    hintText:   'Re-enter your password',
                    prefixIcon: Icons.lock_outline_rounded,
                    suffixIcon: IconButton(
                      icon: Icon(
                        _obscureConfirmPassword
                            ? Icons.visibility_outlined
                            : Icons.visibility_off_outlined,
                        color: AppColors.textSecondary,
                        size:  22,
                      ),
                      onPressed: () => setState(
                        () => _obscureConfirmPassword = !_obscureConfirmPassword,
                      ),
                      tooltip: _obscureConfirmPassword
                          ? 'Show password'
                          : 'Hide password',
                    ),
                  ),
                ),

                const SizedBox(height: 32),

                // ── Register Button ────────────────────────────
                SizedBox(
                  height: 52,
                  child: FilledButton(
                    onPressed: _isLoading ? null : _handleRegister,
                    style: FilledButton.styleFrom(
                      backgroundColor:         AppColors.primary,
                      disabledBackgroundColor: AppColors.primaryLighter,
                      foregroundColor:         AppColors.onPrimary,
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
                        : const Text('Create Account'),
                  ),
                ),

                const SizedBox(height: 32),

                // ── OR Divider ─────────────────────────────────
                const _OrDivider(),

                const SizedBox(height: 24),

                // ── Google Button ──────────────────────────────
                SizedBox(
                  height: 52,
                  child: OutlinedButton(
                    onPressed: _handleGoogleSignUp,
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

                // ── Login Row ──────────────────────────────────
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      'Already have an account? ',
                      style: textTheme.bodyMedium?.copyWith(
                        color: AppColors.textSecondary,
                      ),
                    ),
                    GestureDetector(
                      onTap: _handleLogin,
                      child: Text(
                        'Login',
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

  // ── Input Decoration Helper ────────────────────────────────────

  InputDecoration _inputDecoration({
    required String   hintText,
    required IconData prefixIcon,
    Widget?           suffixIcon,
  }) {
    return InputDecoration(
      hintText:   hintText,
      prefixIcon: Icon(prefixIcon, color: AppColors.textSecondary, size: 20),
      suffixIcon: suffixIcon,
      filled:     true,
      fillColor:  AppColors.surface,
      hintStyle: const TextStyle(
        color:    AppColors.textTertiary,
        fontSize: 14,
      ),
      contentPadding: const EdgeInsets.symmetric(
        horizontal: 16,
        vertical:   16,
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
// Mirrors LoginScreen and SplashScreen — primary-coloured
// rounded square with the parking "P" icon for consistent
// brand identity across all auth screens.
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