import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_colors.dart';

// ============================================================
// AddVehicleScreen
// ============================================================
//
// A premium form screen to register a new vehicle in the
// Smart Parking System.
//
// SECTIONS:
//   1. AppBar            — "Add Vehicle" title + back button
//   2. Vehicle type      — horizontal type selector cards
//   3. Form card         — all text fields grouped in a card
//   4. Default checkbox  — set as default vehicle toggle
//   5. Save button       — full-width FilledButton with loader
//   6. Helper note       — "You can edit these details later."
//
// FIELDS:
//   - Vehicle Number (required, uppercase)
//   - Owner Name     (required)
//   - Vehicle Type   (required, tap selector)
//   - Brand          (required, dropdown)
//   - Model          (required, text)
//   - Vehicle Colour (optional, text)
//   - Set as Default (checkbox)
//
// ARCHITECTURE:
//   Pure StatefulWidget — no Riverpod, Bloc, Provider.
//   Form validated on submit. Loading state mocked with delay.
// ============================================================

// ── Vehicle Type ──────────────────────────────────────────────

enum _VehicleType { car, bike, ev, suv }

extension _VehicleTypeX on _VehicleType {
  String get label => switch (this) {
        _VehicleType.car  => 'Car',
        _VehicleType.bike => 'Bike',
        _VehicleType.ev   => 'EV',
        _VehicleType.suv  => 'SUV',
      };

  IconData get icon => switch (this) {
        _VehicleType.car  => Icons.directions_car_rounded,
        _VehicleType.bike => Icons.two_wheeler_rounded,
        _VehicleType.ev   => Icons.electric_car_rounded,
        _VehicleType.suv  => Icons.airport_shuttle_rounded,
      };

  Color get activeColor => switch (this) {
        _VehicleType.car  => AppColors.primary,
        _VehicleType.bike => AppColors.accent,
        _VehicleType.ev   => AppColors.info,
        _VehicleType.suv  => AppColors.secondaryDark,
      };

  Color get activeBg => switch (this) {
        _VehicleType.car  => AppColors.primary.withAlpha(14),
        _VehicleType.bike => AppColors.warningLight,
        _VehicleType.ev   => AppColors.infoLight,
        _VehicleType.suv  => AppColors.successLight,
      };
}

// ── Brand options (by type) ───────────────────────────────────

const Map<_VehicleType, List<String>> _brandsByType = {
  _VehicleType.car:  ['Maruti Suzuki', 'Hyundai', 'Tata', 'Honda', 'Toyota', 'Kia', 'Other'],
  _VehicleType.bike: ['Hero', 'Honda', 'Bajaj', 'TVS', 'Royal Enfield', 'Yamaha', 'Other'],
  _VehicleType.ev:   ['Tata', 'MG', 'Tesla', 'Ather', 'Ola Electric', 'Other'],
  _VehicleType.suv:  ['Tata', 'Hyundai', 'Mahindra', 'Jeep', 'Toyota', 'Kia', 'Other'],
};

// ── Screen ────────────────────────────────────────────────────

class AddVehicleScreen extends StatefulWidget {
  const AddVehicleScreen({super.key});

  @override
  State<AddVehicleScreen> createState() => _AddVehicleScreenState();
}

class _AddVehicleScreenState extends State<AddVehicleScreen> {
  // ── Form ──────────────────────────────────────────────────
  final _formKey = GlobalKey<FormState>();

  // ── Controllers ───────────────────────────────────────────
  final _numberCtrl  = TextEditingController();
  final _ownerCtrl   = TextEditingController();
  final _modelCtrl   = TextEditingController();
  final _colorCtrl   = TextEditingController();

  // ── Focus Nodes ───────────────────────────────────────────
  final _numberFocus = FocusNode();
  final _ownerFocus  = FocusNode();
  final _modelFocus  = FocusNode();
  final _colorFocus  = FocusNode();

  // ── State ─────────────────────────────────────────────────
  _VehicleType  _selectedType      = _VehicleType.car;
  String?       _selectedBrand;
  bool          _setAsDefault      = false;
  bool          _isLoading         = false;

  List<String> get _brands => _brandsByType[_selectedType]!;

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
  }

  @override
  void dispose() {
    _numberCtrl.dispose();
    _ownerCtrl.dispose();
    _modelCtrl.dispose();
    _colorCtrl.dispose();
    _numberFocus.dispose();
    _ownerFocus.dispose();
    _modelFocus.dispose();
    _colorFocus.dispose();
    super.dispose();
  }

  // ── Validation ────────────────────────────────────────────

  String? _required(String? v, String field) {
    if (v == null || v.trim().isEmpty) return '$field is required.';
    return null;
  }

  String? _validateNumber(String? v) {
    if (v == null || v.trim().isEmpty) return 'Vehicle number is required.';
    if (v.trim().length < 5) return 'Enter a valid vehicle number.';
    return null;
  }

  // ── Submit ─────────────────────────────────────────────────

  Future<void> _handleSave() async {
    FocusScope.of(context).unfocus();
    if (!(_formKey.currentState?.validate() ?? false)) return;
    if (_selectedBrand == null) {
      _showSnack('Please select a brand.');
      return;
    }

    setState(() => _isLoading = true);
    // TODO: replace with your repository call.
    await Future.delayed(const Duration(seconds: 2));
    if (!mounted) return;
    setState(() => _isLoading = false);

    _showSnack('Vehicle saved successfully!');
    // TODO: navigate back or to vehicle list.
  }

  void _showSnack(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content:         Text(message),
        behavior:        SnackBarBehavior.floating,
        duration:        const Duration(seconds: 2),
        backgroundColor: AppColors.textPrimary,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
      ),
    );
  }

  // ── Build ──────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.sizeOf(context).width;
    final hPad        = screenWidth > 600 ? screenWidth * 0.1 : 20.0;

    return Scaffold(
      backgroundColor:          AppColors.background,
      appBar:                   _buildAppBar(context),
      resizeToAvoidBottomInset: true,
      body: GestureDetector(
        onTap: () => FocusScope.of(context).unfocus(),
        behavior: HitTestBehavior.opaque,
        child: Form(
          key: _formKey,
          child: CustomScrollView(
            physics: const BouncingScrollPhysics(),
            slivers: [
              SliverToBoxAdapter(
                child: Padding(
                  padding: EdgeInsets.fromLTRB(hPad, 20, hPad, 32),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [

                      // ── Intro banner ─────────────────────
                      _IntroBanner(),

                      const SizedBox(height: 24),

                      // ── Vehicle Type selector ─────────────
                      _SectionHeader(title: 'Vehicle Type'),
                      const SizedBox(height: 12),
                      _VehicleTypeSelector(
                        selected: _selectedType,
                        onChanged: (t) => setState(() {
                          _selectedType  = t;
                          _selectedBrand = null; // reset brand
                        }),
                      ),

                      const SizedBox(height: 24),

                      // ── Vehicle details card ──────────────
                      _SectionHeader(title: 'Vehicle Details'),
                      const SizedBox(height: 12),
                      _FormCard(
                        children: [

                          // Vehicle Number
                          _FieldLabel(label: 'Vehicle Number *'),
                          const SizedBox(height: 8),
                          _FormField(
                            controller:  _numberCtrl,
                            focusNode:   _numberFocus,
                            nextFocus:   _ownerFocus,
                            hint:        'e.g. DL 01 AB 1234',
                            icon:        Icons.pin_outlined,
                            caps:        TextCapitalization.characters,
                            inputFormatters: [
                              UpperCaseTextFormatter(),
                            ],
                            validator:   _validateNumber,
                          ),

                          const SizedBox(height: 18),

                          // Owner Name
                          _FieldLabel(label: 'Owner Name *'),
                          const SizedBox(height: 8),
                          _FormField(
                            controller: _ownerCtrl,
                            focusNode:  _ownerFocus,
                            nextFocus:  _modelFocus,
                            hint:       'e.g. Arpit Sharma',
                            icon:       Icons.person_outline_rounded,
                            caps:       TextCapitalization.words,
                            validator:  (v) => _required(v, 'Owner name'),
                          ),

                          const SizedBox(height: 18),

                          // Brand dropdown
                          _FieldLabel(label: 'Brand *'),
                          const SizedBox(height: 8),
                          _BrandDropdown(
                            value:    _selectedBrand,
                            brands:   _brands,
                            onChanged: (v) =>
                                setState(() => _selectedBrand = v),
                          ),

                          const SizedBox(height: 18),

                          // Model
                          _FieldLabel(label: 'Model *'),
                          const SizedBox(height: 8),
                          _FormField(
                            controller: _modelCtrl,
                            focusNode:  _modelFocus,
                            nextFocus:  _colorFocus,
                            hint:       'e.g. Swift Dzire, Activa 6G',
                            icon:       Icons.directions_car_outlined,
                            caps:       TextCapitalization.words,
                            validator:  (v) => _required(v, 'Model'),
                          ),

                          const SizedBox(height: 18),

                          // Vehicle Colour
                          _FieldLabel(label: 'Vehicle Colour (optional)'),
                          const SizedBox(height: 8),
                          _FormField(
                            controller:      _colorCtrl,
                            focusNode:       _colorFocus,
                            hint:            'e.g. Pearl White, Midnight Blue',
                            icon:            Icons.palette_outlined,
                            caps:            TextCapitalization.words,
                            textInputAction: TextInputAction.done,
                          ),
                        ],
                      ),

                      const SizedBox(height: 20),

                      // ── Default checkbox card ──────────────
                      _DefaultCheckboxCard(
                        value:     _setAsDefault,
                        onChanged: (v) =>
                            setState(() => _setAsDefault = v ?? false),
                      ),

                      const SizedBox(height: 28),

                      // ── Save button ────────────────────────
                      _SaveButton(
                        isLoading: _isLoading,
                        onTap:     _handleSave,
                      ),

                      const SizedBox(height: 16),

                      // ── Helper note ────────────────────────
                      _HelperNote(),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  PreferredSizeWidget _buildAppBar(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return AppBar(
      backgroundColor:  AppColors.surface,
      elevation:        0,
      surfaceTintColor: Colors.transparent,
      leading: IconButton(
        icon:  const Icon(Icons.arrow_back_ios_rounded, size: 20),
        color: AppColors.textPrimary,
        onPressed: () => Navigator.of(context).maybePop(),
      ),
      title: Text(
        'Add Vehicle',
        style: textTheme.titleLarge?.copyWith(
          color:      AppColors.textPrimary,
          fontWeight: FontWeight.w700,
        ),
      ),
      centerTitle: true,
      bottom: PreferredSize(
        preferredSize: const Size.fromHeight(1),
        child:         Divider(height: 1, color: AppColors.divider),
      ),
    );
  }
}

// ── Intro Banner ──────────────────────────────────────────────

class _IntroBanner extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin:  Alignment.topLeft,
          end:    Alignment.bottomRight,
          colors: [AppColors.primary, AppColors.primaryLight],
        ),
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color:       AppColors.primary.withAlpha(60),
            blurRadius:  16,
            spreadRadius: 0,
            offset:      const Offset(0, 6),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            width:  52,
            height: 52,
            decoration: BoxDecoration(
              color:        AppColors.onPrimary.withAlpha(25),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(
                color: AppColors.onPrimary.withAlpha(40),
                width: 1,
              ),
            ),
            child: const Icon(
              Icons.directions_car_rounded,
              color: AppColors.onPrimary,
              size:  26,
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Register Your Vehicle',
                  style: textTheme.titleSmall?.copyWith(
                    color:      AppColors.onPrimary,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Add a vehicle to book parking slots instantly on every visit.',
                  style: textTheme.bodySmall?.copyWith(
                    color:  AppColors.onPrimary.withAlpha(190),
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

// ── Vehicle Type Selector ─────────────────────────────────────

class _VehicleTypeSelector extends StatelessWidget {
  final _VehicleType          selected;
  final ValueChanged<_VehicleType> onChanged;

  const _VehicleTypeSelector({
    required this.selected,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    final types = _VehicleType.values;

    return Row(
      children: List.generate(types.length, (index) {
        final type       = types[index];
        final isSelected = type == selected;

        return Expanded(
          child: GestureDetector(
            onTap: () => onChanged(type),
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 180),
              margin: EdgeInsets.only(
                right: index < types.length - 1 ? 10 : 0,
              ),
              padding: const EdgeInsets.symmetric(vertical: 14),
              decoration: BoxDecoration(
                color: isSelected ? type.activeColor : AppColors.surface,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: isSelected
                      ? type.activeColor
                      : AppColors.divider,
                  width: isSelected ? 0 : 1,
                ),
                boxShadow: isSelected
                    ? [
                        BoxShadow(
                          color:       type.activeColor.withAlpha(55),
                          blurRadius:  12,
                          spreadRadius: 0,
                          offset:      const Offset(0, 4),
                        ),
                      ]
                    : [
                        BoxShadow(
                          color:       AppColors.shadow,
                          blurRadius:  6,
                          offset:      const Offset(0, 2),
                        ),
                      ],
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    type.icon,
                    size:  24,
                    color: isSelected
                        ? AppColors.onPrimary
                        : type.activeColor,
                  ),
                  const SizedBox(height: 6),
                  Text(
                    type.label,
                    style: TextStyle(
                      fontSize:   11,
                      fontWeight: FontWeight.w700,
                      color:      isSelected
                          ? AppColors.onPrimary
                          : AppColors.textSecondary,
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      }),
    );
  }
}

// ── Form Card ─────────────────────────────────────────────────

class _FormCard extends StatelessWidget {
  final List<Widget> children;
  const _FormCard({required this.children});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color:        AppColors.surface,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppColors.divider, width: 1),
        boxShadow: [
          BoxShadow(
            color:        AppColors.shadow,
            blurRadius:   12,
            spreadRadius: 0,
            offset:       const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: children,
      ),
    );
  }
}

// ── Section Header ────────────────────────────────────────────

class _SectionHeader extends StatelessWidget {
  final String title;
  const _SectionHeader({required this.title});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Row(
      children: [
        Container(
          width:  4,
          height: 18,
          decoration: BoxDecoration(
            color:        AppColors.primary,
            borderRadius: BorderRadius.circular(2),
          ),
        ),
        const SizedBox(width: 8),
        Text(
          title,
          style: textTheme.titleSmall?.copyWith(
            color:      AppColors.textPrimary,
            fontWeight: FontWeight.w700,
            fontSize:   15,
          ),
        ),
      ],
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

// ── Form Field ────────────────────────────────────────────────

class _FormField extends StatelessWidget {
  final TextEditingController       controller;
  final FocusNode                   focusNode;
  final FocusNode?                  nextFocus;
  final String                      hint;
  final IconData                    icon;
  final TextCapitalization          caps;
  final String?    Function(String?)? validator;
  final TextInputAction              textInputAction;
  final List<TextInputFormatter>?   inputFormatters;

  const _FormField({
    required this.controller,
    required this.focusNode,
    this.nextFocus,
    required this.hint,
    required this.icon,
    this.caps           = TextCapitalization.sentences,
    this.validator,
    this.textInputAction = TextInputAction.next,
    this.inputFormatters,
  });

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller:          controller,
      focusNode:           focusNode,
      textCapitalization:  caps,
      textInputAction:     textInputAction,
      inputFormatters:     inputFormatters,
      validator:           validator,
      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
        color: AppColors.textPrimary,
      ),
      onFieldSubmitted: (_) {
        if (nextFocus != null) {
          FocusScope.of(context).requestFocus(nextFocus);
        } else {
          focusNode.unfocus();
        }
      },
      decoration: _buildDecoration(hint, icon),
    );
  }

  InputDecoration _buildDecoration(String hint, IconData icon) {
    return InputDecoration(
      hintText:   hint,
      prefixIcon: Icon(icon, color: AppColors.textSecondary, size: 20),
      hintStyle:  const TextStyle(
        color:    AppColors.textTertiary,
        fontSize: 14,
      ),
      filled:     true,
      fillColor:  AppColors.surfaceVariant,
      contentPadding: const EdgeInsets.symmetric(
        horizontal: 16,
        vertical:   14,
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

// ── Brand Dropdown ────────────────────────────────────────────

class _BrandDropdown extends StatelessWidget {
  final String?                 value;
  final List<String>            brands;
  final ValueChanged<String?>   onChanged;

  const _BrandDropdown({
    required this.value,
    required this.brands,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return DropdownButtonFormField<String>(
      value:       value,
      onChanged:   onChanged,
      isExpanded:  true,
      icon: const Icon(
        Icons.keyboard_arrow_down_rounded,
        color: AppColors.textSecondary,
      ),
      dropdownColor: AppColors.surface,
      borderRadius:  BorderRadius.circular(14),
      style: textTheme.bodyMedium?.copyWith(
        color: AppColors.textPrimary,
      ),
      hint: Text(
        'Select brand',
        style: textTheme.bodyMedium?.copyWith(
          color: AppColors.textTertiary,
        ),
      ),
      validator: (v) {
        if (v == null || v.isEmpty) return 'Brand is required.';
        return null;
      },
      decoration: InputDecoration(
        prefixIcon: const Icon(
          Icons.business_outlined,
          color: AppColors.textSecondary,
          size:  20,
        ),
        filled:     true,
        fillColor:  AppColors.surfaceVariant,
        contentPadding: const EdgeInsets.symmetric(
          horizontal: 16,
          vertical:   14,
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
      ),
      items: brands.map((brand) {
        return DropdownMenuItem<String>(
          value: brand,
          child: Text(
            brand,
            style: textTheme.bodyMedium?.copyWith(
              color: AppColors.textPrimary,
            ),
          ),
        );
      }).toList(),
    );
  }
}

// ── Default Checkbox Card ─────────────────────────────────────

class _DefaultCheckboxCard extends StatelessWidget {
  final bool              value;
  final ValueChanged<bool?> onChanged;

  const _DefaultCheckboxCard({
    required this.value,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return GestureDetector(
      onTap: () => onChanged(!value),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: value
              ? AppColors.primary.withAlpha(10)
              : AppColors.surface,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: value
                ? AppColors.primary.withAlpha(70)
                : AppColors.divider,
            width: value ? 1.5 : 1,
          ),
          boxShadow: [
            BoxShadow(
              color:       value
                  ? AppColors.primary.withAlpha(18)
                  : AppColors.shadow,
              blurRadius:  10,
              spreadRadius: 0,
              offset:      const Offset(0, 3),
            ),
          ],
        ),
        child: Row(
          children: [
            // ── Custom checkbox ──────────────────────
            AnimatedContainer(
              duration: const Duration(milliseconds: 180),
              width:  24,
              height: 24,
              decoration: BoxDecoration(
                color: value ? AppColors.primary : Colors.transparent,
                borderRadius: BorderRadius.circular(7),
                border: Border.all(
                  color: value ? AppColors.primary : AppColors.textSecondary,
                  width: 1.5,
                ),
              ),
              child: value
                  ? const Icon(
                      Icons.check_rounded,
                      color: AppColors.onPrimary,
                      size:  16,
                    )
                  : null,
            ),

            const SizedBox(width: 14),

            // ── Text ─────────────────────────────────
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Set as Default Vehicle',
                    style: textTheme.titleSmall?.copyWith(
                      color:      AppColors.textPrimary,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    'This vehicle will be pre-selected on the booking screen.',
                    style: textTheme.bodySmall?.copyWith(
                      color:  AppColors.textSecondary,
                      height: 1.4,
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(width: 10),

            // ── Star icon ─────────────────────────────
            Icon(
              value ? Icons.star_rounded : Icons.star_outline_rounded,
              color: value ? AppColors.accent : AppColors.textTertiary,
              size:  22,
            ),
          ],
        ),
      ),
    );
  }
}

// ── Save Button ───────────────────────────────────────────────

class _SaveButton extends StatelessWidget {
  final bool         isLoading;
  final VoidCallback onTap;

  const _SaveButton({required this.isLoading, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return SizedBox(
      height: 56,
      child: FilledButton(
        onPressed: isLoading ? null : onTap,
        style: FilledButton.styleFrom(
          backgroundColor:         AppColors.primary,
          disabledBackgroundColor: AppColors.primaryLighter,
          foregroundColor:         AppColors.onPrimary,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          textStyle: textTheme.labelLarge?.copyWith(
            fontSize:      17,
            fontWeight:    FontWeight.w700,
            letterSpacing: 0.4,
          ),
          elevation:   0,
          shadowColor: Colors.transparent,
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
            : Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.check_circle_outline_rounded, size: 20),
                  const SizedBox(width: 10),
                  const Text('Save Vehicle'),
                ],
              ),
      ),
    );
  }
}

// ── Helper Note ───────────────────────────────────────────────

class _HelperNote extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        const Icon(
          Icons.edit_note_rounded,
          color: AppColors.textTertiary,
          size:  16,
        ),
        const SizedBox(width: 6),
        Text(
          'You can edit these details later.',
          style: textTheme.bodySmall?.copyWith(
            color: AppColors.textTertiary,
          ),
        ),
      ],
    );
  }
}

// ── Uppercase Text Formatter ──────────────────────────────────

class UpperCaseTextFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(
    TextEditingValue oldValue,
    TextEditingValue newValue,
  ) {
    return newValue.copyWith(
      text:              newValue.text.toUpperCase(),
      selection:         newValue.selection,
      composing:         newValue.composing,
    );
  }
}