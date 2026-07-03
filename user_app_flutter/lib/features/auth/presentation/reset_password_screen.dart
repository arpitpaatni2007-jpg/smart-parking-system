import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_colors.dart';

// ============================================================
// ResetPasswordScreen
// ============================================================
//
// Allows the user to set a new password after OTP verification.
//
// WHAT IT DOES:
//   1. Locks the status bar to dark icons over the light surface.
//   2. Validates both fields inline before submission:
//        - New Password     → required, min 6 chars
//        - Confirm Password → required, must match New Password
//   3. Manages independent visibility toggles for both fields.
//   4. Shows a loading spinner inside "Reset Password" while
//      the (future) auth call is in flight.
//   5. Transitions to a success state after submission —
//      the form is replaced with a confirmation card, consistent
//      with the pattern used in ForgotPasswordScreen.
//   6. "Back to Login" is UI-only — wired up when routing is added.
//
// DESIGN:
//   Mirrors LoginScreen, RegisterScreen, ForgotPasswordScreen,
//   and OtpVerificationScreen exactly — same spacing, typography,
//   border-radius, shadows, and button styles.
//
// ARCHITECTURE:
//   Pure StatefulWidget — no Riverpod, no Bloc, no Provider.
//   Replace _handleResetPassword() body with your auth
//   repository call when the data layer is ready.
// ============================================================

class ResetPasswordScreen extends StatefulWidget {
  const ResetPasswordScreen({super.key});

  @override
  State<ResetPasswordScreen> createState() => _ResetPasswordScreenState();
}

class _ResetPasswordScreenState extends State<ResetPasswordScreen> {
  // ── Form ──────────────────────────────────────────────────────
  final _formKey = GlobalKey<FormState>();

  // ── Controllers ───────────────────────────────────────────────
  final _passwordController        = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  // ── Focus Nodes ───────────────────────────────────────────────
  final _passwordFocus        = FocusNode();
  final _confirmPasswordFocus = FocusNode();

  // ── Local State ───────────────────────────────────────────────
  bool _obscurePassword        = true;
  bool _obscureConfirmPassword = true;
  bool _isLoading              = false;
  bool _isSuccess              = false;

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
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    _passwordFocus.dispose();
    _confirmPasswordFocus.dispose();
    super.dispose();
  }

  // ── Validation ────────────────────────────────────────────────

  String? _validatePassword(String? value) {
    if (value == null || value.isEmpty) {
      return 'New password is required.';
    }
    if (value.length < 6) {
      return 'Password must be at least 6 characters.';
    }
    return null;
  }

  String? _validateConfirmPassword(String? value) {
    if (value == null || value.isEmpty) {
      return 'Please confirm your new password.';
    }
    if (value != _passwordController.text) {
      return 'Passwords do not match.';
    }
    return null;
  }

  // ── Password Strength ─────────────────────────────────────────

  _PasswordStrength _getStrength(String password) {
    if (password.isEmpty)  return _PasswordStrength.none;
    if (password.length < 6) return _PasswordStrength.weak;

    final hasUpper   = password.contains(RegExp(r'[A-Z]'));
    final hasLower   = password.contains(RegExp(r'[a-z]'));
    final hasDigit   = password.contains(RegExp(r'[0-9]'));
    final hasSpecial = password.contains(RegExp(r'[!@#\$&*~^%]'));
    final score      = [hasUpper, hasLower, hasDigit, hasSpecial]
        .where((v) => v)
        .length;

    if (score <= 1) return _PasswordStrength.weak;
    if (score == 2) return _PasswordStrength.fair;
    if (score == 3) return _PasswordStrength.good;
    return _PasswordStrength.strong;
  }

  // ── Submit ────────────────────────────────────────────────────

  Future<void> _handleResetPassword() async {
    FocusScope.of(context).unfocus();

    if (!(_formKey.currentState?.validate() ?? false)) return;

    setState(() => _isLoading = true);

    // TODO: replace with your auth repository call.
    // e.g. await ref.read(authRepositoryProvider).resetPassword(password);
    await Future.delayed(const Duration(seconds: 2));

    if (!mounted) return;
    setState(() {
      _isLoading = false;
      _isSuccess  = true;
    });
  }

  void _handleBackToLogin() {
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
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [

              SizedBox(height: screenSize.height * 0.04),

              // ── Logo Block ─────────────────────────────────
              const _LogoBlock(),

              SizedBox(height: screenSize.height * 0.05),

              // ── Title ──────────────────────────────────────
              Text(
                'Create New Password',
                style: textTheme.headlineMedium?.copyWith(
                  color:      AppColors.textPrimary,
                  fontWeight: FontWeight.w700,
                ),
              ),

              const SizedBox(height: 8),

              // ── Subtitle ───────────────────────────────────
              Text(
                'Your new password must be different from previously used passwords.',
                style: textTheme.bodyMedium?.copyWith(
                  color:  AppColors.textSecondary,
                  height: 1.5,
                ),
              ),

              const SizedBox(height: 36),

              // ── Form / Success ─────────────────────────────
              _isSuccess
                  ? _SuccessCard(onBackToLogin: _handleBackToLogin)
                  : _ResetForm(
                      formKey:                 _formKey,
                      passwordController:      _passwordController,
                      confirmController:       _confirmPasswordController,
                      passwordFocus:           _passwordFocus,
                      confirmFocus:            _confirmPasswordFocus,
                      obscurePassword:         _obscurePassword,
                      obscureConfirm:          _obscureConfirmPassword,
                      isLoading:               _isLoading,
                      getStrength:             _getStrength,
                      validatePassword:        _validatePassword,
                      validateConfirmPassword: _validateConfirmPassword,
                      onTogglePassword: () => setState(
                        () => _obscurePassword = !_obscurePassword,
                      ),
                      onToggleConfirm: () => setState(
                        () => _obscureConfirmPassword = !_obscureConfirmPassword,
                      ),
                      onSubmit:       _handleResetPassword,
                      onBackToLogin:  _handleBackToLogin,
                      onPasswordChanged: (_) => setState(() {}),
                    ),
            ],
          ),
        ),
      ),
    );
  }
}

// ── Reset Form ────────────────────────────────────────────────
//
// The default state — two password fields + Reset Password button.
//
class _ResetForm extends StatelessWidget {
  final GlobalKey<FormState>        formKey;
  final TextEditingController       passwordController;
  final TextEditingController       confirmController;
  final FocusNode                   passwordFocus;
  final FocusNode                   confirmFocus;
  final bool                        obscurePassword;
  final bool                        obscureConfirm;
  final bool                        isLoading;
  final _PasswordStrength Function(String) getStrength;
  final String? Function(String?)   validatePassword;
  final String? Function(String?)   validateConfirmPassword;
  final VoidCallback                onTogglePassword;
  final VoidCallback                onToggleConfirm;
  final VoidCallback                onSubmit;
  final VoidCallback                onBackToLogin;
  final ValueChanged<String>        onPasswordChanged;

  const _ResetForm({
    required this.formKey,
    required this.passwordController,
    required this.confirmController,
    required this.passwordFocus,
    required this.confirmFocus,
    required this.obscurePassword,
    required this.obscureConfirm,
    required this.isLoading,
    required this.getStrength,
    required this.validatePassword,
    required this.validateConfirmPassword,
    required this.onTogglePassword,
    required this.onToggleConfirm,
    required this.onSubmit,
    required this.onBackToLogin,
    required this.onPasswordChanged,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;
    final strength  = getStrength(passwordController.text);

    return Form(
      key: formKey,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [

          // ── New Password ─────────────────────────────────
          const _FieldLabel(label: 'New Password'),
          const SizedBox(height: 8),
          TextFormField(
            controller:       passwordController,
            focusNode:        passwordFocus,
            obscureText:      obscurePassword,
            textInputAction:  TextInputAction.next,
            validator:        validatePassword,
            onChanged:        onPasswordChanged,
            onFieldSubmitted: (_) =>
                FocusScope.of(context).requestFocus(confirmFocus),
            decoration: _inputDecoration(
              hintText:   'Minimum 6 characters',
              prefixIcon: Icons.lock_outline_rounded,
              suffixIcon: IconButton(
                icon: Icon(
                  obscurePassword
                      ? Icons.visibility_outlined
                      : Icons.visibility_off_outlined,
                  color: AppColors.textSecondary,
                  size:  22,
                ),
                onPressed: onTogglePassword,
                tooltip: obscurePassword ? 'Show password' : 'Hide password',
              ),
            ),
          ),

          // ── Password Strength Indicator ──────────────────
          if (passwordController.text.isNotEmpty) ...[
            const SizedBox(height: 10),
            _PasswordStrengthBar(strength: strength),
          ],

          const SizedBox(height: 20),

          // ── Confirm New Password ─────────────────────────
          const _FieldLabel(label: 'Confirm New Password'),
          const SizedBox(height: 8),
          TextFormField(
            controller:       confirmController,
            focusNode:        confirmFocus,
            obscureText:      obscureConfirm,
            textInputAction:  TextInputAction.done,
            validator:        validateConfirmPassword,
            onFieldSubmitted: (_) => onSubmit(),
            decoration: _inputDecoration(
              hintText:   'Re-enter your new password',
              prefixIcon: Icons.lock_outline_rounded,
              suffixIcon: IconButton(
                icon: Icon(
                  obscureConfirm
                      ? Icons.visibility_outlined
                      : Icons.visibility_off_outlined,
                  color: AppColors.textSecondary,
                  size:  22,
                ),
                onPressed: onToggleConfirm,
                tooltip: obscureConfirm ? 'Show password' : 'Hide password',
              ),
            ),
          ),

          const SizedBox(height: 32),

          // ── Reset Password Button ────────────────────────
          SizedBox(
            height: 52,
            child: FilledButton(
              onPressed: isLoading ? null : onSubmit,
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
              child: isLoading
                  ? const SizedBox(
                      width:  22,
                      height: 22,
                      child:  CircularProgressIndicator(
                        strokeWidth: 2.5,
                        color:       AppColors.onPrimary,
                      ),
                    )
                  : const Text('Reset Password'),
            ),
          ),

          const SizedBox(height: 24),

          // ── Back to Login ────────────────────────────────
          _BackToLoginButton(onTap: onBackToLogin),

          const SizedBox(height: 24),
        ],
      ),
    );
  }
}

// ── Password Strength Bar ─────────────────────────────────────
//
// Four segmented bars that fill progressively as password
// complexity increases. Consistent with the parking-system
// color palette — error → accent → info → success.
//
class _PasswordStrengthBar extends StatelessWidget {
  final _PasswordStrength strength;

  const _PasswordStrengthBar({required this.strength});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // ── Four segmented bars ──────────────────────────
        Row(
          children: List.generate(4, (index) {
            final filled = index < strength.filledSegments;
            return Expanded(
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 250),
                height:       4,
                margin: EdgeInsets.only(right: index < 3 ? 6 : 0),
                decoration: BoxDecoration(
                  color:        filled ? strength.color : AppColors.divider,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            );
          }),
        ),

        const SizedBox(height: 6),

        // ── Strength label ────────────────────────────────
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              'Password strength',
              style: textTheme.bodySmall?.copyWith(
                color: AppColors.textTertiary,
              ),
            ),
            Text(
              strength.label,
              style: textTheme.bodySmall?.copyWith(
                color:      strength.color,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
      ],
    );
  }
}

// ── Success Card ──────────────────────────────────────────────
//
// Replaces the form after a successful password reset.
// Mirrors the success card pattern from ForgotPasswordScreen.
//
class _SuccessCard extends StatelessWidget {
  final VoidCallback onBackToLogin;

  const _SuccessCard({required this.onBackToLogin});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [

        // ── Success Banner ────────────────────────────────
        Container(
          padding:      const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color:        AppColors.successLight,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: AppColors.secondaryDark.withAlpha(51),
              width: 1,
            ),
          ),
          child: Column(
            children: [
              // ── Success icon ─────────────────────────────
              Container(
                width:  56,
                height: 56,
                decoration: BoxDecoration(
                  color:        AppColors.secondary,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color:        AppColors.secondary.withAlpha(51),
                      blurRadius:   16,
                      spreadRadius: 0,
                      offset:       const Offset(0, 4),
                    ),
                  ],
                ),
                child: const Center(
                  child: Icon(
                    Icons.check_rounded,
                    size:  30,
                    color: AppColors.onSecondary,
                  ),
                ),
              ),

              const SizedBox(height: 16),

              Text(
                'Password Reset!',
                style: textTheme.titleMedium?.copyWith(
                  color:      AppColors.textPrimary,
                  fontWeight: FontWeight.w700,
                ),
              ),

              const SizedBox(height: 8),

              Text(
                'Your password has been reset successfully. '
                'You can now log in with your new password.',
                textAlign: TextAlign.center,
                style: textTheme.bodyMedium?.copyWith(
                  color:  AppColors.textSecondary,
                  height: 1.5,
                ),
              ),
            ],
          ),
        ),

        const SizedBox(height: 32),

        // ── Back to Login Button ──────────────────────────
        SizedBox(
          height: 52,
          child: FilledButton(
            onPressed: onBackToLogin,
            style: FilledButton.styleFrom(
              backgroundColor: AppColors.primary,
              foregroundColor: AppColors.onPrimary,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              textStyle: textTheme.labelLarge?.copyWith(
                fontSize:      16,
                fontWeight:    FontWeight.w600,
                letterSpacing: 0.5,
              ),
            ),
            child: const Text('Back to Login'),
          ),
        ),

        const SizedBox(height: 24),
      ],
    );
  }
}

// ── Back to Login Button ──────────────────────────────────────
class _BackToLoginButton extends StatelessWidget {
  final VoidCallback onTap;
  const _BackToLoginButton({required this.onTap});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Center(
      child: TextButton.icon(
        onPressed: onTap,
        style: TextButton.styleFrom(
          foregroundColor: AppColors.primary,
          padding: const EdgeInsets.symmetric(
            horizontal: 8,
            vertical:   4,
          ),
          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
        ),
        icon: const Icon(
          Icons.arrow_back_rounded,
          size:  18,
          color: AppColors.primary,
        ),
        label: Text(
          'Back to Login',
          style: textTheme.bodyMedium?.copyWith(
            color:      AppColors.primary,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
    );
  }
}

// ── Logo Block ────────────────────────────────────────────────
//
// Mirrors all auth screens — primary-coloured rounded square
// with the parking "P" icon for consistent brand identity.
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

// ── Password Strength Enum ─────────────────────────────────────
enum _PasswordStrength {
  none,
  weak,
  fair,
  good,
  strong;

  int get filledSegments => switch (this) {
    _PasswordStrength.none   => 0,
    _PasswordStrength.weak   => 1,
    _PasswordStrength.fair   => 2,
    _PasswordStrength.good   => 3,
    _PasswordStrength.strong => 4,
  };

  Color get color => switch (this) {
    _PasswordStrength.none   => AppColors.divider,
    _PasswordStrength.weak   => AppColors.error,
    _PasswordStrength.fair   => AppColors.accent,
    _PasswordStrength.good   => AppColors.info,
    _PasswordStrength.strong => AppColors.success,
  };

  String get label => switch (this) {
    _PasswordStrength.none   => '',
    _PasswordStrength.weak   => 'Weak',
    _PasswordStrength.fair   => 'Fair',
    _PasswordStrength.good   => 'Good',
    _PasswordStrength.strong => 'Strong',
  };
}