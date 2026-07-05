import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_colors.dart';

// ============================================================
// ChangePasswordScreen
// ============================================================
//
// Premium Change Password screen for the Smart Parking app.
//
// SECTIONS:
//   1. AppBar                — back + "Change Password" title
//   2. Security icon header  — visual anchor for the screen
//   3. Password fields card  — Current / New / Confirm password
//   4. Requirements card     — live-updating rule checklist
//   5. Action row            — Cancel (outlined) + Update (filled)
//
// DESIGN LANGUAGE:
//   Matches settings_screen.dart exactly:
//     • AppColors.background scaffold
//     • White surface cards, 16 px radius, subtle shadow + divider border
//     • Primary teal-navy (#0F3D56)
//     • Electric green (#2ECC71) for met requirements
//     • Amber (#F59E0B) for the security header accent
//     • 16–20 px horizontal padding, BouncingScrollPhysics
//
// ARCHITECTURE:
//   StatefulWidget — local form & visibility state only.
//   No Riverpod / Bloc / Provider / API.
// ============================================================

class ChangePasswordScreen extends StatefulWidget {
  const ChangePasswordScreen({super.key});

  @override
  State<ChangePasswordScreen> createState() => _ChangePasswordScreenState();
}

class _ChangePasswordScreenState extends State<ChangePasswordScreen> {
  // ── Form ─────────────────────────────────────────────────────
  final _formKey        = GlobalKey<FormState>();
  final _currentCtrl    = TextEditingController();
  final _newCtrl        = TextEditingController();
  final _confirmCtrl    = TextEditingController();
  final _currentFocus   = FocusNode();
  final _newFocus       = FocusNode();
  final _confirmFocus   = FocusNode();

  // ── Visibility toggles ────────────────────────────────────────
  bool _showCurrent = false;
  bool _showNew     = false;
  bool _showConfirm = false;

  // ── Loading ───────────────────────────────────────────────────
  bool _isLoading = false;

  // ── Requirements (live-evaluated on new password input) ───────
  bool get _hasMinLength    => _newCtrl.text.length >= 8;
  bool get _hasUppercase    => _newCtrl.text.contains(RegExp(r'[A-Z]'));
  bool get _hasLowercase    => _newCtrl.text.contains(RegExp(r'[a-z]'));
  bool get _hasNumber       => _newCtrl.text.contains(RegExp(r'[0-9]'));
  bool get _hasSpecial      => _newCtrl.text.contains(RegExp(r'[!@#\$%^&*(),.?":{}|<>_\-]'));
  bool get _allMet          => _hasMinLength && _hasUppercase && _hasLowercase && _hasNumber && _hasSpecial;

  @override
  void initState() {
    super.initState();
    // Rebuild the requirements card whenever new password changes.
    _newCtrl.addListener(() => setState(() {}));

    SystemChrome.setSystemUIOverlayStyle(
      const SystemUiOverlayStyle(
        statusBarColor:          Colors.transparent,
        statusBarIconBrightness: Brightness.dark,
        statusBarBrightness:     Brightness.light,
      ),
    );
  }

  @override
  void dispose() {
    _currentCtrl.dispose();
    _newCtrl.dispose();
    _confirmCtrl.dispose();
    _currentFocus.dispose();
    _newFocus.dispose();
    _confirmFocus.dispose();
    super.dispose();
  }

  // ── Submit ────────────────────────────────────────────────────
  Future<void> _onUpdate() async {
    if (!_formKey.currentState!.validate()) return;
    FocusScope.of(context).unfocus();

    setState(() => _isLoading = true);
    // Simulate network delay — replace with actual API call.
    await Future.delayed(const Duration(milliseconds: 1400));
    if (!mounted) return;
    setState(() => _isLoading = false);

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: const Row(
          children: [
            Icon(Icons.check_circle_rounded, color: AppColors.onSecondary, size: 18),
            SizedBox(width: 10),
            Text('Password updated successfully!'),
          ],
        ),
        backgroundColor: AppColors.secondary,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        margin: const EdgeInsets.all(16),
        duration: const Duration(seconds: 2),
      ),
    );
  }

  // ── Build ─────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final w    = MediaQuery.sizeOf(context).width;
    final hPad = w > 600 ? w * 0.08 : 16.0;

    return Scaffold(
      backgroundColor:          AppColors.background,
      resizeToAvoidBottomInset: true,
      appBar: _buildAppBar(context),
      body: Form(
        key: _formKey,
        child: ListView(
          physics:  const BouncingScrollPhysics(),
          padding:  EdgeInsets.fromLTRB(hPad, 20, hPad, 40),
          children: [

            // ── Security header ─────────────────────────────
            _SecurityHeader(),

            const SizedBox(height: 24),

            // ── Password fields card ────────────────────────
            _card(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _cardLabel('Password Fields'),
                  const SizedBox(height: 20),

                  // Current Password
                  _FieldLabel(label: 'Current Password'),
                  const SizedBox(height: 6),
                  _PasswordField(
                    controller:   _currentCtrl,
                    focusNode:    _currentFocus,
                    hint:         'Enter your current password',
                    showPassword: _showCurrent,
                    inputAction:  TextInputAction.next,
                    onToggle:     () => setState(() => _showCurrent = !_showCurrent),
                    onSubmitted:  (_) => _newFocus.requestFocus(),
                    validator: (v) {
                      if (v == null || v.isEmpty) return 'Current password is required';
                      if (v.length < 6) return 'Password too short';
                      return null;
                    },
                  ),

                  const SizedBox(height: 20),

                  // New Password
                  _FieldLabel(label: 'New Password'),
                  const SizedBox(height: 6),
                  _PasswordField(
                    controller:   _newCtrl,
                    focusNode:    _newFocus,
                    hint:         'Enter a strong new password',
                    showPassword: _showNew,
                    inputAction:  TextInputAction.next,
                    onToggle:     () => setState(() => _showNew = !_showNew),
                    onSubmitted:  (_) => _confirmFocus.requestFocus(),
                    validator: (v) {
                      if (v == null || v.isEmpty) return 'New password is required';
                      if (!_allMet) return 'Password does not meet all requirements';
                      if (v == _currentCtrl.text) return 'New password must differ from current';
                      return null;
                    },
                  ),

                  const SizedBox(height: 20),

                  // Confirm Password
                  _FieldLabel(label: 'Confirm New Password'),
                  const SizedBox(height: 6),
                  _PasswordField(
                    controller:   _confirmCtrl,
                    focusNode:    _confirmFocus,
                    hint:         'Re-enter your new password',
                    showPassword: _showConfirm,
                    inputAction:  TextInputAction.done,
                    onToggle:     () => setState(() => _showConfirm = !_showConfirm),
                    onSubmitted:  (_) => _onUpdate(),
                    validator: (v) {
                      if (v == null || v.isEmpty) return 'Please confirm your new password';
                      if (v != _newCtrl.text) return 'Passwords do not match';
                      return null;
                    },
                  ),
                ],
              ),
            ),

            const SizedBox(height: 16),

            // ── Requirements card ───────────────────────────
            _RequirementsCard(
              hasMinLength: _hasMinLength,
              hasUppercase: _hasUppercase,
              hasLowercase: _hasLowercase,
              hasNumber:    _hasNumber,
              hasSpecial:   _hasSpecial,
            ),

            const SizedBox(height: 28),

            // ── Action row ──────────────────────────────────
            _ActionRow(
              isLoading: _isLoading,
              onUpdate:  _onUpdate,
              onCancel:  () => FocusScope.of(context).unfocus(),
            ),
          ],
        ),
      ),
    );
  }

  // ── AppBar ────────────────────────────────────────────────────
  PreferredSizeWidget _buildAppBar(BuildContext context) {
    return AppBar(
      backgroundColor: AppColors.background,
      elevation: 0,
      scrolledUnderElevation: 0,
      leading: Padding(
        padding: const EdgeInsets.only(left: 8),
        child: _CircleIconButton(
          icon:  Icons.arrow_back_ios_new_rounded,
          onTap: () => Navigator.maybePop(context),
        ),
      ),
      title: Text(
        'Change Password',
        style: Theme.of(context).textTheme.titleLarge?.copyWith(
          color:      AppColors.textPrimary,
          fontWeight: FontWeight.w700,
          fontSize:   20,
        ),
      ),
      centerTitle: false,
      systemOverlayStyle: const SystemUiOverlayStyle(
        statusBarColor:          Colors.transparent,
        statusBarIconBrightness: Brightness.dark,
      ),
    );
  }
}

// ── Security Header ───────────────────────────────────────────

class _SecurityHeader extends StatelessWidget {
  const _SecurityHeader();

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      width:   double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin:  Alignment.topLeft,
          end:    Alignment.bottomRight,
          colors: [AppColors.primaryDark, AppColors.primary],
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color:      AppColors.primary.withAlpha(70),
            blurRadius: 16,
            offset:     const Offset(0, 6),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            width:  52,
            height: 52,
            decoration: BoxDecoration(
              color:        AppColors.white.withAlpha(25),
              borderRadius: BorderRadius.circular(14),
            ),
            child: const Icon(
              Icons.lock_outline_rounded,
              color: AppColors.onPrimary,
              size:  26,
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Secure Your Account',
                  style: textTheme.titleMedium?.copyWith(
                    color:      AppColors.onPrimary,
                    fontWeight: FontWeight.w700,
                    fontSize:   15,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Choose a strong password to protect your account and bookings.',
                  style: textTheme.bodySmall?.copyWith(
                    color:  AppColors.onPrimary.withAlpha(180),
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ── Requirements Card ─────────────────────────────────────────

class _RequirementsCard extends StatelessWidget {
  final bool hasMinLength;
  final bool hasUppercase;
  final bool hasLowercase;
  final bool hasNumber;
  final bool hasSpecial;

  const _RequirementsCard({
    required this.hasMinLength,
    required this.hasUppercase,
    required this.hasLowercase,
    required this.hasNumber,
    required this.hasSpecial,
  });

  @override
  Widget build(BuildContext context) {
    final allMet = hasMinLength && hasUppercase && hasLowercase && hasNumber && hasSpecial;

    return _card(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              _cardLabel('Password Requirements'),
              const Spacer(),
              if (allMet)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color:        AppColors.successLight,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    'Strong',
                    style: Theme.of(context).textTheme.labelSmall?.copyWith(
                      color:      AppColors.secondaryDark,
                      fontWeight: FontWeight.w700,
                      fontSize:   11,
                    ),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 16),
          _Requirement(met: hasMinLength, label: 'Minimum 8 characters'),
          const SizedBox(height: 10),
          _Requirement(met: hasUppercase, label: 'One uppercase letter (A–Z)'),
          const SizedBox(height: 10),
          _Requirement(met: hasLowercase, label: 'One lowercase letter (a–z)'),
          const SizedBox(height: 10),
          _Requirement(met: hasNumber,    label: 'One number (0–9)'),
          const SizedBox(height: 10),
          _Requirement(met: hasSpecial,   label: 'One special character (!@#\$%^&*)'),
        ],
      ),
    );
  }
}

class _Requirement extends StatelessWidget {
  final bool   met;
  final String label;
  const _Requirement({required this.met, required this.label});

  @override
  Widget build(BuildContext context) {
    final color = met ? AppColors.secondary : AppColors.textTertiary;

    return Row(
      children: [
        AnimatedContainer(
          duration: const Duration(milliseconds: 250),
          width:  22,
          height: 22,
          decoration: BoxDecoration(
            color:        met ? AppColors.successLight : AppColors.surfaceVariant,
            shape:        BoxShape.circle,
            border: Border.all(
              color: met ? AppColors.secondary : AppColors.divider,
              width: 1.5,
            ),
          ),
          child: Center(
            child: Icon(
              met ? Icons.check_rounded : Icons.remove_rounded,
              size:  12,
              color: color,
            ),
          ),
        ),
        const SizedBox(width: 10),
        AnimatedDefaultTextStyle(
          duration: const Duration(milliseconds: 200),
          style: Theme.of(context).textTheme.bodySmall!.copyWith(
            color:      color,
            fontWeight: met ? FontWeight.w600 : FontWeight.w400,
            fontSize:   13,
          ),
          child: Text(label),
        ),
      ],
    );
  }
}

// ── Action Row ────────────────────────────────────────────────

class _ActionRow extends StatelessWidget {
  final bool         isLoading;
  final VoidCallback onUpdate;
  final VoidCallback onCancel;

  const _ActionRow({
    required this.isLoading,
    required this.onUpdate,
    required this.onCancel,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        // Cancel
        Expanded(
          child: OutlinedButton(
            onPressed: isLoading ? null : onCancel,
            style: OutlinedButton.styleFrom(
              foregroundColor: AppColors.textSecondary,
              side: const BorderSide(color: AppColors.divider, width: 1.2),
              minimumSize: const Size(0, 52),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
              ),
              textStyle: const TextStyle(
                fontSize:   15,
                fontWeight: FontWeight.w600,
              ),
            ),
            child: const Text('Cancel'),
          ),
        ),

        const SizedBox(width: 12),

        // Update Password
        Expanded(
          flex: 2,
          child: FilledButton(
            onPressed: isLoading ? null : onUpdate,
            style: FilledButton.styleFrom(
              backgroundColor:        AppColors.primary,
              foregroundColor:        AppColors.onPrimary,
              disabledBackgroundColor: AppColors.textDisabled,
              minimumSize: const Size(0, 52),
              elevation:   0,
              shadowColor: Colors.transparent,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
              ),
              textStyle: const TextStyle(
                fontSize:      15,
                fontWeight:    FontWeight.w700,
                letterSpacing: 0.3,
              ),
            ),
            child: isLoading
                ? const SizedBox(
                    width:  20,
                    height: 20,
                    child:  CircularProgressIndicator(
                      color:       AppColors.onPrimary,
                      strokeWidth: 2.2,
                    ),
                  )
                : const Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.lock_reset_rounded, size: 18),
                      SizedBox(width: 8),
                      Text('Update Password'),
                    ],
                  ),
          ),
        ),
      ],
    );
  }
}

// ── Password Field ────────────────────────────────────────────

class _PasswordField extends StatelessWidget {
  final TextEditingController       controller;
  final FocusNode                   focusNode;
  final String                      hint;
  final bool                        showPassword;
  final TextInputAction              inputAction;
  final VoidCallback                 onToggle;
  final void Function(String)?      onSubmitted;
  final String? Function(String?)?  validator;

  const _PasswordField({
    required this.controller,
    required this.focusNode,
    required this.hint,
    required this.showPassword,
    required this.inputAction,
    required this.onToggle,
    this.onSubmitted,
    this.validator,
  });

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller:      controller,
      focusNode:       focusNode,
      obscureText:     !showPassword,
      textInputAction: inputAction,
      onFieldSubmitted: onSubmitted,
      validator:       validator,
      style: const TextStyle(
        color:      AppColors.textPrimary,
        fontSize:   15,
        fontWeight: FontWeight.w500,
        letterSpacing: 0.5,
      ),
      decoration: InputDecoration(
        hintText:  hint,
        hintStyle: const TextStyle(
          color:      AppColors.textTertiary,
          fontSize:   14,
          fontWeight: FontWeight.w400,
          letterSpacing: 0,
        ),
        filled:    true,
        fillColor: AppColors.surfaceVariant,
        prefixIcon: Padding(
          padding: const EdgeInsets.only(left: 14, right: 10),
          child: Icon(
            Icons.lock_outline_rounded,
            color: AppColors.textSecondary,
            size:  20,
          ),
        ),
        prefixIconConstraints: const BoxConstraints(minWidth: 0, minHeight: 0),
        suffixIcon: GestureDetector(
          onTap: onToggle,
          child: Padding(
            padding: const EdgeInsets.only(right: 14),
            child: Icon(
              showPassword
                  ? Icons.visibility_outlined
                  : Icons.visibility_off_outlined,
              color: AppColors.textSecondary,
              size:  20,
            ),
          ),
        ),
        suffixIconConstraints: const BoxConstraints(minWidth: 0, minHeight: 0),
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide:   const BorderSide(color: AppColors.divider, width: 1),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide:   const BorderSide(color: AppColors.divider, width: 1),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide:   const BorderSide(color: AppColors.primary, width: 1.8),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide:   const BorderSide(color: AppColors.error, width: 1.5),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide:   const BorderSide(color: AppColors.error, width: 1.8),
        ),
        errorStyle: const TextStyle(color: AppColors.error, fontSize: 12),
      ),
    );
  }
}

// ── Reusable Helpers ──────────────────────────────────────────

class _FieldLabel extends StatelessWidget {
  final String label;
  const _FieldLabel({required this.label});

  @override
  Widget build(BuildContext context) {
    return Text(
      label,
      style: Theme.of(context).textTheme.labelMedium?.copyWith(
        color:      AppColors.textSecondary,
        fontWeight: FontWeight.w600,
        fontSize:   12,
        letterSpacing: 0.4,
      ),
    );
  }
}

/// Circular / rounded-square icon button — mirrors settings_screen.dart.
class _CircleIconButton extends StatelessWidget {
  final IconData     icon;
  final VoidCallback onTap;
  const _CircleIconButton({required this.icon, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width:  40,
        height: 40,
        decoration: BoxDecoration(
          color:        AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.divider, width: 1),
          boxShadow: [
            BoxShadow(
              color:      AppColors.shadow,
              blurRadius: 6,
              offset:     const Offset(0, 2),
            ),
          ],
        ),
        child: Icon(icon, color: AppColors.textPrimary, size: 18),
      ),
    );
  }
}

/// Shared card container — white surface, 16 px radius, subtle shadow.
/// Mirrors the exact card style used in settings_screen.dart.
Widget _card({required Widget child}) {
  return Container(
    width:   double.infinity,
    padding: const EdgeInsets.all(20),
    decoration: BoxDecoration(
      color:        AppColors.surface,
      borderRadius: BorderRadius.circular(16),
      border: Border.all(color: AppColors.divider, width: 0.8),
      boxShadow: [
        BoxShadow(
          color:      AppColors.shadow,
          blurRadius: 10,
          spreadRadius: 0,
          offset:     const Offset(0, 3),
        ),
      ],
    ),
    child: child,
  );
}

/// Card section label with left accent bar — same pattern as settings_screen.dart.
Widget _cardLabel(String label) {
  return Builder(
    builder: (context) => Row(
      children: [
        Container(
          width:  3,
          height: 18,
          decoration: BoxDecoration(
            color:        AppColors.primary,
            borderRadius: BorderRadius.circular(2),
          ),
        ),
        const SizedBox(width: 10),
        Text(
          label,
          style: Theme.of(context).textTheme.titleSmall?.copyWith(
            color:      AppColors.textPrimary,
            fontWeight: FontWeight.w700,
            fontSize:   14,
          ),
        ),
      ],
    ),
  );
}