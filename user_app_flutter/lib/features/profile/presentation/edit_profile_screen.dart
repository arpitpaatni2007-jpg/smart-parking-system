import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_colors.dart';

// ============================================================
// EditProfileScreen
// ============================================================
//
// Premium edit profile screen for the Smart Parking System.
//
// SECTIONS:
//   1. Custom AppBar  — back + title "Edit Profile" + save icon
//   2. Avatar Block   — large editable avatar with camera badge
//   3. Form Card      — Full Name, Email, Phone, DOB, Gender
//   4. Action Row     — Cancel (outlined) + Save Changes (filled)
//
// DESIGN LANGUAGE:
//   Matches profile_screen.dart exactly:
//     • AppColors.background scaffold
//     • White cards with 16 px radius + shadow + divider border
//     • Primary teal-navy (#0F3D56) brand color
//     • Electric green (#2ECC71) for avatar badge
//     • Consistent 20 px horizontal padding
//     • BouncingScrollPhysics
//
// ARCHITECTURE:
//   StatefulWidget — local form state only.
//   No Riverpod / Bloc / Provider / API calls.
// ============================================================

// ── Dummy pre-filled data ────────────────────────────────────
const _kInitialName   = 'Rahul Sharma';
const _kInitialEmail  = 'rahul.sharma@gmail.com';
const _kInitialPhone  = '+91 98765 43210';
const _kInitialDob    = '15 Aug 1995';
const _kInitialGender = 'Male';
const _kAvatarInitials = 'RS';

class EditProfileScreen extends StatefulWidget {
  const EditProfileScreen({super.key});

  @override
  State<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends State<EditProfileScreen> {
  final _formKey    = GlobalKey<FormState>();
  final _nameFocus  = FocusNode();
  final _emailFocus = FocusNode();
  final _phoneFocus = FocusNode();
  final _dobFocus   = FocusNode();

  late final TextEditingController _nameCtrl;
  late final TextEditingController _emailCtrl;
  late final TextEditingController _phoneCtrl;
  late final TextEditingController _dobCtrl;

  String _selectedGender = _kInitialGender;
  bool   _isSaving       = false;

  static const _genders = ['Male', 'Female', 'Non-binary', 'Prefer not to say'];

  @override
  void initState() {
    super.initState();
    _nameCtrl  = TextEditingController(text: _kInitialName);
    _emailCtrl = TextEditingController(text: _kInitialEmail);
    _phoneCtrl = TextEditingController(text: _kInitialPhone);
    _dobCtrl   = TextEditingController(text: _kInitialDob);

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
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    _phoneCtrl.dispose();
    _dobCtrl.dispose();
    _nameFocus.dispose();
    _emailFocus.dispose();
    _phoneFocus.dispose();
    _dobFocus.dispose();
    super.dispose();
  }

  // ── Save handler ─────────────────────────────────────────────
  Future<void> _onSave() async {
    if (!_formKey.currentState!.validate()) return;
    _formKey.currentState!.save();
    FocusScope.of(context).unfocus();

    setState(() => _isSaving = true);
    // Simulate network delay — replace with actual API call.
    await Future.delayed(const Duration(milliseconds: 1200));
    if (!mounted) return;
    setState(() => _isSaving = false);

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: const Text('Profile updated successfully!'),
        backgroundColor: AppColors.secondary,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
        margin: const EdgeInsets.all(16),
        duration: const Duration(seconds: 2),
      ),
    );
  }

  // ── Date picker ──────────────────────────────────────────────
  Future<void> _pickDate() async {
    FocusScope.of(context).unfocus();
    final now       = DateTime.now();
    final initial   = DateTime(1995, 8, 15);
    final firstDate = DateTime(1940);
    final lastDate  = DateTime(now.year - 10, now.month, now.day);

    final picked = await showDatePicker(
      context:   context,
      initialDate: initial,
      firstDate:   firstDate,
      lastDate:    lastDate,
      builder: (ctx, child) => Theme(
        data: Theme.of(ctx).copyWith(
          colorScheme: Theme.of(ctx).colorScheme.copyWith(
            primary:    AppColors.primary,
            onPrimary:  AppColors.onPrimary,
            surface:    AppColors.surface,
            onSurface:  AppColors.textPrimary,
          ),
          dialogTheme: DialogThemeData(
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(20),
            ),
          ),
        ),
        child: child!,
      ),
    );

    if (picked != null && mounted) {
      _dobCtrl.text =
          '${picked.day.toString().padLeft(2, '0')} '
          '${_monthName(picked.month)} '
          '${picked.year}';
    }
  }

  static String _monthName(int m) => const [
    '', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
  ][m];

  // ── Build ────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final w    = MediaQuery.sizeOf(context).width;
    final hPad = w > 600 ? w * 0.08 : 20.0;

    return Scaffold(
      backgroundColor:          AppColors.background,
      resizeToAvoidBottomInset: true,
      body: SafeArea(
        child: Column(
          children: [
            // ── AppBar ────────────────────────────────────────
            _AppBar(
              hPad:      hPad,
              isSaving:  _isSaving,
              onSave:    _onSave,
            ),

            // ── Scrollable body ───────────────────────────────
            Expanded(
              child: Form(
                key: _formKey,
                child: CustomScrollView(
                  physics:   const BouncingScrollPhysics(),
                  slivers: [

                    // ── Avatar ─────────────────────────────────
                    SliverToBoxAdapter(
                      child: _AvatarBlock(hPad: hPad),
                    ),

                    // ── Form Card ──────────────────────────────
                    SliverToBoxAdapter(
                      child: Padding(
                        padding: EdgeInsets.fromLTRB(hPad, 24, hPad, 0),
                        child: _FormCard(
                          nameCtrl:    _nameCtrl,
                          emailCtrl:   _emailCtrl,
                          phoneCtrl:   _phoneCtrl,
                          dobCtrl:     _dobCtrl,
                          nameFocus:   _nameFocus,
                          emailFocus:  _emailFocus,
                          phoneFocus:  _phoneFocus,
                          dobFocus:    _dobFocus,
                          gender:      _selectedGender,
                          onGenderChanged: (v) =>
                              setState(() => _selectedGender = v ?? _selectedGender),
                          onDobTap:    _pickDate,
                        ),
                      ),
                    ),

                    // ── Action Buttons ─────────────────────────
                    SliverToBoxAdapter(
                      child: Padding(
                        padding: EdgeInsets.fromLTRB(hPad, 28, hPad, 0),
                        child: _ActionRow(
                          isSaving: _isSaving,
                          onSave:   _onSave,
                          onCancel: () => FocusScope.of(context).unfocus(),
                        ),
                      ),
                    ),

                    const SliverToBoxAdapter(child: SizedBox(height: 40)),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── 1. AppBar ─────────────────────────────────────────────────

class _AppBar extends StatelessWidget {
  final double       hPad;
  final bool         isSaving;
  final VoidCallback onSave;

  const _AppBar({
    required this.hPad,
    required this.isSaving,
    required this.onSave,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      color: AppColors.background,
      padding: EdgeInsets.fromLTRB(hPad, 8, hPad, 8),
      child: Row(
        children: [
          // Back button
          _CircleIconButton(
            icon:  Icons.arrow_back_ios_new_rounded,
            onTap: () => Navigator.maybePop(context),
          ),

          const SizedBox(width: 14),

          // Title
          Expanded(
            child: Text(
              'Edit Profile',
              style: textTheme.titleLarge?.copyWith(
                color:      AppColors.textPrimary,
                fontWeight: FontWeight.w700,
                fontSize:   20,
              ),
            ),
          ),

          // Quick-save icon button (top-right shortcut)
          GestureDetector(
            onTap: isSaving ? null : onSave,
            child: Container(
              width:  40,
              height: 40,
              decoration: BoxDecoration(
                color:        AppColors.primary,
                borderRadius: BorderRadius.circular(12),
                boxShadow: [
                  BoxShadow(
                    color:      AppColors.primary.withAlpha(60),
                    blurRadius: 8,
                    offset:     const Offset(0, 3),
                  ),
                ],
              ),
              child: isSaving
                  ? const Padding(
                      padding: EdgeInsets.all(10),
                      child:   CircularProgressIndicator(
                        color:       AppColors.onPrimary,
                        strokeWidth: 2,
                      ),
                    )
                  : const Icon(
                      Icons.check_rounded,
                      color: AppColors.onPrimary,
                      size:  20,
                    ),
            ),
          ),
        ],
      ),
    );
  }
}

// ── 2. Avatar Block ───────────────────────────────────────────

class _AvatarBlock extends StatelessWidget {
  final double hPad;
  const _AvatarBlock({required this.hPad});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.fromLTRB(hPad, 24, hPad, 0),
      child: Center(
        child: Stack(
          clipBehavior: Clip.none,
          children: [
            // Avatar circle
            Container(
              width:  96,
              height: 96,
              decoration: BoxDecoration(
                shape:   BoxShape.circle,
                gradient: const LinearGradient(
                  begin:  Alignment.topLeft,
                  end:    Alignment.bottomRight,
                  colors: [AppColors.primaryLight, AppColors.primary],
                ),
                border: Border.all(
                  color: AppColors.surface,
                  width: 3,
                ),
                boxShadow: [
                  BoxShadow(
                    color:      AppColors.primary.withAlpha(50),
                    blurRadius: 20,
                    offset:     const Offset(0, 6),
                  ),
                ],
              ),
              child: const Center(
                child: Text(
                  _kAvatarInitials,
                  style: TextStyle(
                    color:      AppColors.onPrimary,
                    fontSize:   34,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 1,
                  ),
                ),
              ),
            ),

            // Camera badge — bottom-right
            Positioned(
              bottom: 0,
              right:  0,
              child: GestureDetector(
                onTap: () {
                  // TODO: launch image picker
                },
                child: Container(
                  width:  30,
                  height: 30,
                  decoration: BoxDecoration(
                    color:  AppColors.secondary,
                    shape:  BoxShape.circle,
                    border: Border.all(color: AppColors.surface, width: 2),
                    boxShadow: [
                      BoxShadow(
                        color:      AppColors.secondary.withAlpha(60),
                        blurRadius: 6,
                        offset:     const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: const Icon(
                    Icons.camera_alt_rounded,
                    color: AppColors.onSecondary,
                    size:  15,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── 3. Form Card ──────────────────────────────────────────────

class _FormCard extends StatelessWidget {
  final TextEditingController nameCtrl;
  final TextEditingController emailCtrl;
  final TextEditingController phoneCtrl;
  final TextEditingController dobCtrl;
  final FocusNode             nameFocus;
  final FocusNode             emailFocus;
  final FocusNode             phoneFocus;
  final FocusNode             dobFocus;
  final String                gender;
  final ValueChanged<String?> onGenderChanged;
  final VoidCallback          onDobTap;

  const _FormCard({
    required this.nameCtrl,
    required this.emailCtrl,
    required this.phoneCtrl,
    required this.dobCtrl,
    required this.nameFocus,
    required this.emailFocus,
    required this.phoneFocus,
    required this.dobFocus,
    required this.gender,
    required this.onGenderChanged,
    required this.onDobTap,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: _cardDecoration(),
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 24, 20, 24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Section label
            _SectionLabel(label: 'Personal Information'),

            const SizedBox(height: 20),

            // Full Name
            _FieldLabel(label: 'Full Name'),
            const SizedBox(height: 6),
            _ProfileTextField(
              controller:  nameCtrl,
              focusNode:   nameFocus,
              hint:        'Enter your full name',
              icon:        Icons.person_outline_rounded,
              inputAction: TextInputAction.next,
              keyboardType: TextInputType.name,
              textCapitalization: TextCapitalization.words,
              onFieldSubmitted: (_) => emailFocus.requestFocus(),
              validator: (v) {
                if (v == null || v.trim().isEmpty) return 'Name is required';
                if (v.trim().length < 2) return 'Name is too short';
                return null;
              },
            ),

            const SizedBox(height: 20),

            // Email
            _FieldLabel(label: 'Email Address'),
            const SizedBox(height: 6),
            _ProfileTextField(
              controller:   emailCtrl,
              focusNode:    emailFocus,
              hint:         'Enter your email address',
              icon:         Icons.email_outlined,
              inputAction:  TextInputAction.next,
              keyboardType: TextInputType.emailAddress,
              onFieldSubmitted: (_) => phoneFocus.requestFocus(),
              validator: (v) {
                if (v == null || v.trim().isEmpty) return 'Email is required';
                final re = RegExp(r'^[\w.+-]+@[\w-]+\.[a-zA-Z]{2,}$');
                if (!re.hasMatch(v.trim())) return 'Enter a valid email address';
                return null;
              },
            ),

            const SizedBox(height: 20),

            // Phone Number
            _FieldLabel(label: 'Phone Number'),
            const SizedBox(height: 6),
            _ProfileTextField(
              controller:   phoneCtrl,
              focusNode:    phoneFocus,
              hint:         '+91 98765 43210',
              icon:         Icons.phone_outlined,
              inputAction:  TextInputAction.done,
              keyboardType: TextInputType.phone,
              inputFormatters: [
                FilteringTextInputFormatter.allow(RegExp(r'[\d\s+\-()]')),
              ],
              validator: (v) {
                if (v == null || v.trim().isEmpty) return 'Phone number is required';
                final digits = v.replaceAll(RegExp(r'\D'), '');
                if (digits.length < 10) return 'Enter a valid phone number';
                return null;
              },
            ),

            const SizedBox(height: 20),

            // Date of Birth
            _FieldLabel(label: 'Date of Birth'),
            const SizedBox(height: 6),
            _ProfileTextField(
              controller:   dobCtrl,
              focusNode:    dobFocus,
              hint:         'DD MMM YYYY',
              icon:         Icons.calendar_today_outlined,
              inputAction:  TextInputAction.done,
              keyboardType: TextInputType.none,
              readOnly:     true,
              onTap:        onDobTap,
              trailingIcon: Icons.edit_calendar_outlined,
              validator: (v) {
                if (v == null || v.trim().isEmpty) return 'Date of birth is required';
                return null;
              },
            ),

            const SizedBox(height: 20),

            // Gender
            _FieldLabel(label: 'Gender'),
            const SizedBox(height: 6),
            _GenderDropdown(
  gender: gender,
  onChanged: onGenderChanged,
),
          ],
        ),
      ),
    );
  }
}

// ── 4. Action Row ─────────────────────────────────────────────

class _ActionRow extends StatelessWidget {
  final bool         isSaving;
  final VoidCallback onSave;
  final VoidCallback onCancel;

  const _ActionRow({
    required this.isSaving,
    required this.onSave,
    required this.onCancel,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        // Cancel
        Expanded(
          child: OutlinedButton(
            onPressed: isSaving ? null : onCancel,
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

        const SizedBox(width: 14),

        // Save Changes
        Expanded(
          flex: 2,
          child: FilledButton(
            onPressed: isSaving ? null : onSave,
            style: FilledButton.styleFrom(
              backgroundColor: AppColors.primary,
              foregroundColor: AppColors.onPrimary,
              disabledBackgroundColor: AppColors.textDisabled,
              minimumSize: const Size(0, 52),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
              ),
              textStyle: const TextStyle(
                fontSize:   15,
                fontWeight: FontWeight.w700,
                letterSpacing: 0.3,
              ),
              elevation: 0,
            ),
            child: isSaving
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
                      Icon(Icons.check_circle_outline_rounded, size: 18),
                      SizedBox(width: 8),
                      Text('Save Changes'),
                    ],
                  ),
          ),
        ),
      ],
    );
  }
}

// ── Reusable Widgets ──────────────────────────────────────────

class _SectionLabel extends StatelessWidget {
  final String label;
  const _SectionLabel({required this.label});

  @override
  Widget build(BuildContext context) {
    return Row(
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
    );
  }
}

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

// ── Profile Text Field ─────────────────────────────────────────

class _ProfileTextField extends StatelessWidget {
  final TextEditingController controller;
  final FocusNode             focusNode;
  final String                hint;
  final IconData              icon;
  final TextInputAction       inputAction;
  final TextInputType         keyboardType;
  final bool                  readOnly;
  final VoidCallback?         onTap;
  final IconData?             trailingIcon;
  final TextCapitalization    textCapitalization;
  final List<TextInputFormatter>? inputFormatters;
  final void Function(String)?    onFieldSubmitted;
  final String? Function(String?)? validator;

  const _ProfileTextField({
    required this.controller,
    required this.focusNode,
    required this.hint,
    required this.icon,
    required this.inputAction,
    required this.keyboardType,
    this.readOnly          = false,
    this.onTap,
    this.trailingIcon,
    this.textCapitalization = TextCapitalization.none,
    this.inputFormatters,
    this.onFieldSubmitted,
    this.validator,
  });

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller:          controller,
      focusNode:           focusNode,
      readOnly:            readOnly,
      onTap:               onTap,
      keyboardType:        keyboardType,
      textInputAction:     inputAction,
      textCapitalization:  textCapitalization,
      inputFormatters:     inputFormatters,
      onFieldSubmitted:    onFieldSubmitted,
      validator:           validator,
      style: const TextStyle(
        color:      AppColors.textPrimary,
        fontSize:   15,
        fontWeight: FontWeight.w500,
      ),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: const TextStyle(
          color:    AppColors.textTertiary,
          fontSize: 14,
          fontWeight: FontWeight.w400,
        ),
        filled:      true,
        fillColor:   AppColors.surfaceVariant,
        prefixIcon: Padding(
          padding: const EdgeInsets.only(left: 14, right: 10),
          child: Icon(icon, color: AppColors.textSecondary, size: 20),
        ),
        prefixIconConstraints: const BoxConstraints(minWidth: 0, minHeight: 0),
        suffixIcon: trailingIcon != null
            ? Padding(
                padding: const EdgeInsets.only(right: 14),
                child:   Icon(trailingIcon, color: AppColors.textTertiary, size: 18),
              )
            : null,
        suffixIconConstraints: const BoxConstraints(minWidth: 0, minHeight: 0),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppColors.divider, width: 1),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppColors.divider, width: 1),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppColors.primary, width: 1.8),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppColors.error, width: 1.5),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppColors.error, width: 1.8),
        ),
        errorStyle: const TextStyle(
          color:    AppColors.error,
          fontSize: 12,
        ),
      ),
    );
  }
}

// ── Gender Dropdown ───────────────────────────────────────────

class _GenderDropdown extends StatelessWidget {
  final String                gender;
  final ValueChanged<String?> onChanged;

  static const _items = ['Male', 'Female', 'Non-binary', 'Prefer not to say'];

  const _GenderDropdown({required this.gender, required this.onChanged});

  @override
  Widget build(BuildContext context) {
    return DropdownButtonFormField<String>(
      value:       gender,
      onChanged:   onChanged,
      icon: const Icon(
        Icons.keyboard_arrow_down_rounded,
        color: AppColors.textSecondary,
        size:  22,
      ),
      style: const TextStyle(
        color:      AppColors.textPrimary,
        fontSize:   15,
        fontWeight: FontWeight.w500,
      ),
      dropdownColor: AppColors.surface,
      borderRadius:  BorderRadius.circular(14),
      elevation:     2,
      decoration: InputDecoration(
        filled:      true,
        fillColor:   AppColors.surfaceVariant,
        prefixIcon: const Padding(
          padding: EdgeInsets.only(left: 14, right: 10),
          child:   Icon(
            Icons.wc_outlined,
            color: AppColors.textSecondary,
            size:  20,
          ),
        ),
        prefixIconConstraints: const BoxConstraints(minWidth: 0, minHeight: 0),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppColors.divider, width: 1),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppColors.divider, width: 1),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppColors.primary, width: 1.8),
        ),
      ),
      items: _items.map((g) => DropdownMenuItem(
        value: g,
        child: Text(g),
      )).toList(),
    );
  }
}

// ── Shared Helpers ─────────────────────────────────────────────

/// Circular / rounded-square icon button — mirrors profile_screen.dart.
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

/// Shared card decoration — mirrors profile_screen.dart exactly.
BoxDecoration _cardDecoration() {
  return BoxDecoration(
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
  );
}