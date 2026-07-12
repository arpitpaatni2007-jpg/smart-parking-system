import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_colors.dart';

// ============================================================
// ForgotPasswordScreen
// ============================================================
//
// Allows the user to request a password reset link via email.
//
// WHAT IT DOES:
//   1. Locks the status bar to dark icons over the light surface.
//   2. Validates the email field inline before submission.
//   3. Shows a loading spinner inside the "Send Reset Link"
//      button while the (future) network call is in flight.
//   4. Shows a success state after submission — the form is
//      replaced with a confirmation message and a resend option,
//      matching common real-world UX for password reset flows.
//   5. "Back to Login" is UI-only — wired up when routing is added.
//
// DESIGN:
//   Mirrors LoginScreen and RegisterScreen exactly — same
//   spacing, typography, border-radius, shadows, and button
//   styles. Warm off-white background, primary-coloured logo
//   block, full-width FilledButton CTA.
//
// ARCHITECTURE:
//   Pure StatefulWidget — no Riverpod, no Bloc, no Provider.
//   Replace _handleSendResetLink() body with your auth
//   repository call when the data layer is ready.
// ============================================================

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  // ── Form ──────────────────────────────────────────────────────
  final _formKey = GlobalKey<FormState>();

  // ── Controllers ───────────────────────────────────────────────
  final _emailController = TextEditingController();

  // ── Focus Nodes ───────────────────────────────────────────────
  final _emailFocus = FocusNode();

  // ── Local State ───────────────────────────────────────────────
  bool _isLoading  = false;
  bool _isSuccess  = false;

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
    _emailController.dispose();
    _emailFocus.dispose();
    super.dispose();
  }

  // ── Validation ────────────────────────────────────────────────

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

  // ── Submit ────────────────────────────────────────────────────

  Future<void> _handleSendResetLink() async {
    FocusScope.of(context).unfocus();

    if (!(_formKey.currentState?.validate() ?? false)) return;

    setState(() => _isLoading = true);

    // TODO: replace with your auth repository call.
    // e.g. await ref.read(authRepositoryProvider).sendPasswordResetEmail(email);
    await Future.delayed(const Duration(seconds: 2));

    if (!mounted) return;
    setState(() {
      _isLoading = false;
      _isSuccess  = true;
    });
  }

  Future<void> _handleResend() async {
    setState(() => _isLoading = true);

    // TODO: replace with your auth repository call.
    await Future.delayed(const Duration(seconds: 2));

    if (!mounted) return;
    setState(() => _isLoading = false);
  }

void _handleBackToLogin() {
  Navigator.of(context).pop();
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

              // ── Logo Block ───────────────────────────────────
              const _LogoBlock(),

              SizedBox(height: screenSize.height * 0.05),

              // ── Title ────────────────────────────────────────
              Text(
                'Forgot Password',
                style: textTheme.headlineMedium?.copyWith(
                  color:      AppColors.textPrimary,
                  fontWeight: FontWeight.w700,
                ),
              ),

              const SizedBox(height: 8),

              // ── Subtitle ─────────────────────────────────────
              Text(
                "Enter your email address and we'll send you a password reset link.",
                style: textTheme.bodyMedium?.copyWith(
                  color:  AppColors.textSecondary,
                  height: 1.5,
                ),
              ),

              const SizedBox(height: 36),

              // ── Form / Success — switches on _isSuccess ──────
              _isSuccess
                  ? _SuccessCard(
                      email:         _emailController.text.trim(),
                      isLoading:     _isLoading,
                      onResend:      _handleResend,
                      onBackToLogin: _handleBackToLogin,
                    )
                  : _EmailForm(
                      formKey:         _formKey,
                      emailController: _emailController,
                      emailFocus:      _emailFocus,
                      isLoading:       _isLoading,
                      validateEmail:   _validateEmail,
                      onSubmit:        _handleSendResetLink,
                      onBackToLogin:   _handleBackToLogin,
                    ),
            ],
          ),
        ),
      ),
    );
  }
}

// ── Email Form ────────────────────────────────────────────────
//
// The default state — email field + Send Reset Link button.
//
class _EmailForm extends StatelessWidget {
  final GlobalKey<FormState>  formKey;
  final TextEditingController emailController;
  final FocusNode             emailFocus;
  final bool                  isLoading;
  final String?               Function(String?) validateEmail;
  final VoidCallback          onSubmit;
  final VoidCallback          onBackToLogin;

  const _EmailForm({
    required this.formKey,
    required this.emailController,
    required this.emailFocus,
    required this.isLoading,
    required this.validateEmail,
    required this.onSubmit,
    required this.onBackToLogin,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Form(
      key: formKey,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [

          // ── Email Field ────────────────────────────────────
          const _FieldLabel(label: 'Email Address'),
          const SizedBox(height: 8),
          TextFormField(
            controller:       emailController,
            focusNode:        emailFocus,
            keyboardType:     TextInputType.emailAddress,
            textInputAction:  TextInputAction.done,
            autocorrect:      false,
            validator:        validateEmail,
            onFieldSubmitted: (_) => onSubmit(),
            decoration: _inputDecoration(
              hintText:   'you@example.com',
              prefixIcon: Icons.email_outlined,
            ),
          ),

          const SizedBox(height: 32),

          // ── Send Reset Link Button ─────────────────────────
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
                  : const Text('Send Reset Link'),
            ),
          ),

          const SizedBox(height: 24),

          // ── Back to Login ──────────────────────────────────
          _BackToLoginButton(onTap: onBackToLogin),

          const SizedBox(height: 24),
        ],
      ),
    );
  }
}

// ── Success Card ──────────────────────────────────────────────
//
// Replaces the form after a successful submission.
// Shows a confirmation message, the email it was sent to,
// a Resend option, and the Back to Login button.
//
class _SuccessCard extends StatelessWidget {
  final String      email;
  final bool        isLoading;
  final VoidCallback onResend;
  final VoidCallback onBackToLogin;

  const _SuccessCard({
    required this.email,
    required this.isLoading,
    required this.onResend,
    required this.onBackToLogin,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [

        // ── Success Icon Banner ────────────────────────────
        Container(
          padding:      const EdgeInsets.all(20),
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
              Container(
                width:  56,
                height: 56,
                decoration: BoxDecoration(
                  color:        AppColors.secondary,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color:       AppColors.secondary.withAlpha(51),
                      blurRadius:  16,
                      spreadRadius: 0,
                      offset:      const Offset(0, 4),
                    ),
                  ],
                ),
                child: const Center(
                  child: Icon(
                    Icons.mark_email_read_outlined,
                    size:  28,
                    color: AppColors.onSecondary,
                  ),
                ),
              ),

              const SizedBox(height: 16),

              Text(
                'Check your inbox',
                style: textTheme.titleMedium?.copyWith(
                  color:      AppColors.textPrimary,
                  fontWeight: FontWeight.w700,
                ),
              ),

              const SizedBox(height: 8),

              RichText(
                textAlign: TextAlign.center,
                text: TextSpan(
                  style: textTheme.bodyMedium?.copyWith(
                    color:  AppColors.textSecondary,
                    height: 1.5,
                  ),
                  children: [
                    const TextSpan(text: 'We sent a password reset link to\n'),
                    TextSpan(
                      text: email,
                      style: textTheme.bodyMedium?.copyWith(
                        color:      AppColors.primary,
                        fontWeight: FontWeight.w600,
                        height:     1.5,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),

        const SizedBox(height: 32),

        // ── Resend Button ──────────────────────────────────
        SizedBox(
          height: 52,
          child: FilledButton(
            onPressed: isLoading ? null : onResend,
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
                : const Text('Resend Reset Link'),
          ),
        ),

        const SizedBox(height: 16),

        // ── Hint text ──────────────────────────────────────
        Text(
          "Didn't receive the email? Check your spam folder or resend.",
          textAlign: TextAlign.center,
          style: textTheme.bodySmall?.copyWith(
            color:  AppColors.textTertiary,
            height: 1.5,
          ),
        ),

        const SizedBox(height: 24),

        // ── Back to Login ──────────────────────────────────
        _BackToLoginButton(onTap: onBackToLogin),

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

// ── Private Widgets ───────────────────────────────────────────

// ── Logo Block ────────────────────────────────────────────────
//
// Mirrors LoginScreen and RegisterScreen — primary-coloured
// rounded square with the parking "P" icon.
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
}) {
  return InputDecoration(
    hintText:   hintText,
    prefixIcon: Icon(prefixIcon, color: AppColors.textSecondary, size: 20),
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