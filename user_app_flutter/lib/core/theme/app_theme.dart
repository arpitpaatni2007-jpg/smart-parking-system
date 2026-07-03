import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import 'app_colors.dart';

/// ============================================================
/// AppTheme
/// ============================================================
///
/// Provides Material 3 light and dark themes for the
/// Smart Parking System.
///
/// ARCHITECTURE:
///   AppTheme.lightTheme → used in MaterialApp.theme
///   AppTheme.darkTheme  → used in MaterialApp.darkTheme
///
/// FONT STRATEGY:
///   Uses Flutter's default Roboto until a custom font is added.
///   To switch to a custom font (e.g. Inter), add the font to
///   pubspec.yaml and replace 'Roboto' with the font family name
///   in _textTheme() below.
///
/// MATERIAL 3:
///   useMaterial3: true is set on both themes.
///   ColorScheme.fromSeed() is used to generate the full M3
///   tonal palette from the brand primary seed color.
///   Individual component themes then override the generated
///   defaults with our exact AppColors values.
///
/// USAGE:
///   MaterialApp(
///     theme: AppTheme.lightTheme,
///     darkTheme: AppTheme.darkTheme,
///     themeMode: ThemeMode.system,
///   )
/// ============================================================

abstract final class AppTheme {
  // ── Shared Radius Constants ───────────────────────────────────
  /// Used consistently across cards, buttons, inputs, dialogs.
  static const double radiusXS = 4.0;
  static const double radiusSM = 8.0;
  static const double radiusMD = 12.0;
  static const double radiusLG = 16.0;
  static const double radiusXL = 24.0;
  static const double radiusFull = 999.0;

  // ── Shared Elevation Constants ────────────────────────────────
  static const double elevationNone = 0.0;
  static const double elevationSM = 1.0;
  static const double elevationMD = 2.0;
  static const double elevationLG = 4.0;

  // ────────────────────────────────────────────────────────────────
  // LIGHT THEME
  // ────────────────────────────────────────────────────────────────

  /// The default light theme — clean white surfaces with
  /// deep teal-navy primary and electric-green accents.
  static ThemeData get lightTheme {
    final colorScheme = _lightColorScheme;

    return ThemeData(
      useMaterial3: true,
      colorScheme: colorScheme,

      // ── Scaffold ─────────────────────────────────────────────
      scaffoldBackgroundColor: AppColors.background,

      // ── Typography ───────────────────────────────────────────
      textTheme: _textTheme(isLight: true),
      fontFamily: 'Roboto',

      // ── AppBar ───────────────────────────────────────────────
      appBarTheme: _appBarTheme(isLight: true),

      // ── Bottom Navigation Bar ─────────────────────────────────
      bottomNavigationBarTheme: _bottomNavBarTheme(isLight: true),

      // ── Navigation Bar (M3) ───────────────────────────────────
      navigationBarTheme: _navigationBarTheme(isLight: true),

      // ── Elevated Button ───────────────────────────────────────
      elevatedButtonTheme: _elevatedButtonTheme(),

      // ── Filled Button ─────────────────────────────────────────
      filledButtonTheme: _filledButtonTheme(),

      // ── Outlined Button ───────────────────────────────────────
      outlinedButtonTheme: _outlinedButtonTheme(isLight: true),

      // ── Text Button ───────────────────────────────────────────
      textButtonTheme: _textButtonTheme(),

      // ── Floating Action Button ────────────────────────────────
      floatingActionButtonTheme: _fabTheme(),

      // ── Card ─────────────────────────────────────────────────
      cardTheme: _cardTheme(isLight: true),

      // ── Input Decoration ─────────────────────────────────────
      inputDecorationTheme: _inputDecorationTheme(isLight: true),

      // ── Chip ─────────────────────────────────────────────────
      chipTheme: _chipTheme(isLight: true),

      // ── Dialog ───────────────────────────────────────────────
      dialogTheme: _dialogTheme(isLight: true),

      // ── Bottom Sheet ─────────────────────────────────────────
      bottomSheetTheme: _bottomSheetTheme(isLight: true),

      // ── Divider ───────────────────────────────────────────────
      dividerTheme: const DividerThemeData(
        color: AppColors.divider,
        thickness: 1.0,
        space: 1.0,
      ),

      // ── List Tile ─────────────────────────────────────────────
      listTileTheme: _listTileTheme(isLight: true),

      // ── Switch ───────────────────────────────────────────────
      switchTheme: _switchTheme(),

      // ── Checkbox ─────────────────────────────────────────────
      checkboxTheme: _checkboxTheme(),

      // ── Radio ─────────────────────────────────────────────────
      radioTheme: _radioTheme(),

      // ── Snack Bar ─────────────────────────────────────────────
      snackBarTheme: _snackBarTheme(),

      // ── Tab Bar ───────────────────────────────────────────────
      tabBarTheme: _tabBarTheme(isLight: true),

      // ── Progress Indicator ────────────────────────────────────
      progressIndicatorTheme: const ProgressIndicatorThemeData(
        color: AppColors.primary,
        linearTrackColor: AppColors.surfaceVariant,
      ),

      // ── Icon ─────────────────────────────────────────────────
      iconTheme: const IconThemeData(
        color: AppColors.textPrimary,
        size: 24.0,
      ),

      // ── Primary Icon (in AppBar, etc.) ────────────────────────
      primaryIconTheme: const IconThemeData(
        color: AppColors.onPrimary,
        size: 24.0,
      ),

      // ── Tooltip ───────────────────────────────────────────────
      tooltipTheme: TooltipThemeData(
        decoration: BoxDecoration(
          color: AppColors.textPrimary.withAlpha(230),
          borderRadius: BorderRadius.circular(radiusSM),
        ),
        textStyle: const TextStyle(
          color: AppColors.white,
          fontSize: 12.0,
        ),
      ),
    );
  }

  // ────────────────────────────────────────────────────────────────
  // DARK THEME
  // ────────────────────────────────────────────────────────────────

  /// The dark theme — deep charcoal surfaces with lighter
  /// teal-navy primary and the same electric-green accents.
  static ThemeData get darkTheme {
    final colorScheme = _darkColorScheme;

    return ThemeData(
      useMaterial3: true,
      colorScheme: colorScheme,

      scaffoldBackgroundColor: AppColors.backgroundDark,

      textTheme: _textTheme(isLight: false),
      fontFamily: 'Roboto',

      appBarTheme: _appBarTheme(isLight: false),
      bottomNavigationBarTheme: _bottomNavBarTheme(isLight: false),
      navigationBarTheme: _navigationBarTheme(isLight: false),
      elevatedButtonTheme: _elevatedButtonTheme(),
      filledButtonTheme: _filledButtonTheme(),
      outlinedButtonTheme: _outlinedButtonTheme(isLight: false),
      textButtonTheme: _textButtonTheme(),
      floatingActionButtonTheme: _fabTheme(),
      cardTheme: _cardTheme(isLight: false),
      inputDecorationTheme: _inputDecorationTheme(isLight: false),
      chipTheme: _chipTheme(isLight: false),
      dialogTheme: _dialogTheme(isLight: false),
      bottomSheetTheme: _bottomSheetTheme(isLight: false),

      dividerTheme: const DividerThemeData(
        color: AppColors.dividerDark,
        thickness: 1.0,
        space: 1.0,
      ),

      listTileTheme: _listTileTheme(isLight: false),
      switchTheme: _switchTheme(),
      checkboxTheme: _checkboxTheme(),
      radioTheme: _radioTheme(),
      snackBarTheme: _snackBarTheme(),
      tabBarTheme: _tabBarTheme(isLight: false),

      progressIndicatorTheme: const ProgressIndicatorThemeData(
        color: AppColors.secondary,
        linearTrackColor: AppColors.surfaceVariantDark,
      ),

      iconTheme: const IconThemeData(
        color: AppColors.textPrimaryDark,
        size: 24.0,
      ),

      primaryIconTheme: const IconThemeData(
        color: AppColors.onPrimary,
        size: 24.0,
      ),

      tooltipTheme: TooltipThemeData(
        decoration: BoxDecoration(
          color: AppColors.surfaceVariantDark,
          borderRadius: BorderRadius.circular(radiusSM),
        ),
        textStyle: const TextStyle(
          color: AppColors.textPrimaryDark,
          fontSize: 12.0,
        ),
      ),
    );
  }

  // ────────────────────────────────────────────────────────────────
  // COLOR SCHEMES
  // ────────────────────────────────────────────────────────────────

  static ColorScheme get _lightColorScheme => ColorScheme.fromSeed(
        seedColor: AppColors.primary,
        brightness: Brightness.light,
        primary: AppColors.primary,
        onPrimary: AppColors.onPrimary,
        primaryContainer: AppColors.primaryLighter.withAlpha(30),
        secondary: AppColors.secondary,
        onSecondary: AppColors.onSecondary,
        secondaryContainer: AppColors.successLight,
        tertiary: AppColors.accent,
        onTertiary: AppColors.onAccent,
        tertiaryContainer: AppColors.warningLight,
        error: AppColors.error,
        onError: AppColors.white,
        errorContainer: AppColors.errorLight,
        surface: AppColors.surface,
        onSurface: AppColors.textPrimary,
        surfaceContainerHighest: AppColors.surfaceVariant,
        outline: AppColors.divider,
        outlineVariant: AppColors.textDisabled,
        shadow: AppColors.shadow,
        scrim: AppColors.scrim,
      );

  static ColorScheme get _darkColorScheme => ColorScheme.fromSeed(
        seedColor: AppColors.primary,
        brightness: Brightness.dark,
        primary: AppColors.primaryLighter,
        onPrimary: AppColors.white,
        primaryContainer: AppColors.primaryDark,
        secondary: AppColors.secondary,
        onSecondary: AppColors.onSecondary,
        secondaryContainer: AppColors.secondaryDark.withAlpha(50),
        tertiary: AppColors.accent,
        onTertiary: AppColors.onAccent,
        tertiaryContainer: AppColors.accent.withAlpha(40),
        error: const Color(0xFFEF9A9A),
        onError: const Color(0xFF4A0000),
        errorContainer: const Color(0xFFB71C1C),
        surface: AppColors.surfaceDark,
        onSurface: AppColors.textPrimaryDark,
        surfaceContainerHighest: AppColors.surfaceVariantDark,
        outline: AppColors.dividerDark,
        outlineVariant: AppColors.textTertiaryDark,
        shadow: Colors.black26,
        scrim: Colors.black54,
      );

  // ────────────────────────────────────────────────────────────────
  // COMPONENT THEMES
  // ────────────────────────────────────────────────────────────────

  // ── AppBar ─────────────────────────────────────────────────────
  static AppBarTheme _appBarTheme({required bool isLight}) {
    return AppBarTheme(
      // Solid primary in light, elevated dark surface in dark.
      backgroundColor:
          isLight ? AppColors.primary : AppColors.surfaceDark,
      foregroundColor: AppColors.onPrimary,
      elevation: elevationNone,
      scrolledUnderElevation: elevationSM,
      shadowColor: AppColors.shadow,
      centerTitle: false,
      titleTextStyle: const TextStyle(
        color: AppColors.onPrimary,
        fontSize: 18.0,
        fontWeight: FontWeight.w600,
        letterSpacing: 0.15,
      ),
      iconTheme: const IconThemeData(
        color: AppColors.onPrimary,
        size: 24.0,
      ),
      actionsIconTheme: const IconThemeData(
        color: AppColors.onPrimary,
        size: 24.0,
      ),
      // Status bar icons: light icons on dark app bar.
      systemOverlayStyle: SystemUiOverlayStyle.light.copyWith(
        statusBarColor: Colors.transparent,
      ),
    );
  }

  // ── Bottom Navigation Bar ──────────────────────────────────────
  static BottomNavigationBarThemeData _bottomNavBarTheme(
      {required bool isLight}) {
    return BottomNavigationBarThemeData(
      backgroundColor: isLight ? AppColors.surface : AppColors.surfaceDark,
      selectedItemColor: AppColors.primary,
      unselectedItemColor: AppColors.navInactive,
      selectedLabelStyle: const TextStyle(
        fontSize: 11.0,
        fontWeight: FontWeight.w600,
        letterSpacing: 0.4,
      ),
      unselectedLabelStyle: const TextStyle(
        fontSize: 11.0,
        fontWeight: FontWeight.w400,
      ),
      showSelectedLabels: true,
      showUnselectedLabels: true,
      type: BottomNavigationBarType.fixed,
      elevation: elevationLG,
    );
  }

  // ── Navigation Bar (M3 component) ─────────────────────────────
  static NavigationBarThemeData _navigationBarTheme(
      {required bool isLight}) {
    return NavigationBarThemeData(
      backgroundColor: isLight ? AppColors.surface : AppColors.surfaceDark,
      indicatorColor: AppColors.primary.withAlpha(20),
      labelTextStyle: WidgetStateProperty.resolveWith((states) {
        final selected = states.contains(WidgetState.selected);
        return TextStyle(
          fontSize: 11.0,
          fontWeight: selected ? FontWeight.w600 : FontWeight.w400,
          color: selected
              ? AppColors.primary
              : (isLight ? AppColors.navInactive : AppColors.textSecondaryDark),
        );
      }),
      iconTheme: WidgetStateProperty.resolveWith((states) {
        final selected = states.contains(WidgetState.selected);
        return IconThemeData(
          color: selected
              ? AppColors.primary
              : (isLight ? AppColors.navInactive : AppColors.textSecondaryDark),
          size: 24.0,
        );
      }),
      elevation: elevationLG,
      height: 64.0,
    );
  }

  // ── Elevated Button ────────────────────────────────────────────
  static ElevatedButtonThemeData _elevatedButtonTheme() {
    return ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: AppColors.primary,
        foregroundColor: AppColors.onPrimary,
        disabledBackgroundColor: AppColors.textDisabled,
        disabledForegroundColor: AppColors.white,
        elevation: elevationMD,
        shadowColor: AppColors.shadow,
        minimumSize: const Size(double.infinity, 52.0),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(radiusMD),
        ),
        textStyle: const TextStyle(
          fontSize: 16.0,
          fontWeight: FontWeight.w600,
          letterSpacing: 0.5,
        ),
        padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 14.0),
      ),
    );
  }

  // ── Filled Button ─────────────────────────────────────────────
  static FilledButtonThemeData _filledButtonTheme() {
    return FilledButtonThemeData(
      style: FilledButton.styleFrom(
        backgroundColor: AppColors.primary,
        foregroundColor: AppColors.onPrimary,
        minimumSize: const Size(double.infinity, 52.0),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(radiusMD),
        ),
        textStyle: const TextStyle(
          fontSize: 16.0,
          fontWeight: FontWeight.w600,
          letterSpacing: 0.5,
        ),
        padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 14.0),
      ),
    );
  }

  // ── Outlined Button ───────────────────────────────────────────
  static OutlinedButtonThemeData _outlinedButtonTheme(
      {required bool isLight}) {
    return OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: AppColors.primary,
        side: const BorderSide(color: AppColors.primary, width: 1.5),
        minimumSize: const Size(double.infinity, 52.0),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(radiusMD),
        ),
        textStyle: const TextStyle(
          fontSize: 16.0,
          fontWeight: FontWeight.w600,
          letterSpacing: 0.5,
        ),
        padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 14.0),
      ),
    );
  }

  // ── Text Button ───────────────────────────────────────────────
  static TextButtonThemeData _textButtonTheme() {
    return TextButtonThemeData(
      style: TextButton.styleFrom(
        foregroundColor: AppColors.primary,
        textStyle: const TextStyle(
          fontSize: 14.0,
          fontWeight: FontWeight.w600,
          letterSpacing: 0.25,
        ),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(radiusSM),
        ),
        padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
      ),
    );
  }

  // ── FAB ───────────────────────────────────────────────────────
  static FloatingActionButtonThemeData _fabTheme() {
    return FloatingActionButtonThemeData(
      backgroundColor: AppColors.secondary,
      foregroundColor: AppColors.onSecondary,
      elevation: elevationLG,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(radiusLG),
      ),
    );
  }

  // ── Card ──────────────────────────────────────────────────────
  // FIX: return type changed from CardTheme to CardThemeData.
  static CardThemeData _cardTheme({required bool isLight}) {
    return CardThemeData(
      color: isLight ? AppColors.surface : AppColors.surfaceDark,
      surfaceTintColor: Colors.transparent,
      elevation: elevationSM,
      shadowColor: AppColors.shadow,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(radiusLG),
        side: BorderSide(
          color: isLight ? AppColors.divider : AppColors.dividerDark,
          width: 0.8,
        ),
      ),
      margin: EdgeInsets.zero,
      clipBehavior: Clip.antiAlias,
    );
  }

  // ── Input Decoration ──────────────────────────────────────────
  // FIX: prefixIconColor / suffixIconColor changed from
  //      WidgetStateProperty<Color> to plain Color.
  //      InputDecorationTheme does not accept WidgetStateProperty
  //      for those two fields in Flutter 3.44.
  static InputDecorationTheme _inputDecorationTheme(
      {required bool isLight}) {
    final fillColor =
        isLight ? AppColors.surfaceVariant : AppColors.surfaceVariantDark;
    final borderColor = isLight ? AppColors.divider : AppColors.dividerDark;
    final labelColor =
        isLight ? AppColors.textSecondary : AppColors.textSecondaryDark;
    final hintColor =
        isLight ? AppColors.textTertiary : AppColors.textTertiaryDark;
    final iconColor =
        isLight ? AppColors.textSecondary : AppColors.textSecondaryDark;

    final defaultBorder = OutlineInputBorder(
      borderRadius: BorderRadius.circular(radiusMD),
      borderSide: BorderSide(color: borderColor, width: 1.0),
    );

    final focusedBorder = OutlineInputBorder(
      borderRadius: BorderRadius.circular(radiusMD),
      borderSide: const BorderSide(color: AppColors.primary, width: 1.8),
    );

    final errorBorder = OutlineInputBorder(
      borderRadius: BorderRadius.circular(radiusMD),
      borderSide: const BorderSide(color: AppColors.error, width: 1.5),
    );

    return InputDecorationTheme(
      filled: true,
      fillColor: fillColor,
      contentPadding:
          const EdgeInsets.symmetric(horizontal: 16.0, vertical: 16.0),
      border: defaultBorder,
      enabledBorder: defaultBorder,
      focusedBorder: focusedBorder,
      errorBorder: errorBorder,
      focusedErrorBorder: errorBorder,
      disabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(radiusMD),
        borderSide: BorderSide(
          color: borderColor.withAlpha(100),
          width: 1.0,
        ),
      ),
      labelStyle: TextStyle(color: labelColor, fontSize: 14.0),
      hintStyle: TextStyle(color: hintColor, fontSize: 14.0),
      floatingLabelStyle: const TextStyle(
        color: AppColors.primary,
        fontSize: 13.0,
        fontWeight: FontWeight.w500,
      ),
      errorStyle: const TextStyle(
        color: AppColors.error,
        fontSize: 12.0,
      ),
      // Plain Color — WidgetStateProperty<Color> not supported here.
      prefixIconColor: iconColor,
      suffixIconColor: iconColor,
    );
  }

  // ── Chip ──────────────────────────────────────────────────────
  static ChipThemeData _chipTheme({required bool isLight}) {
    return ChipThemeData(
      backgroundColor:
          isLight ? AppColors.surfaceVariant : AppColors.surfaceVariantDark,
      selectedColor: AppColors.primary.withAlpha(20),
      labelStyle: TextStyle(
        fontSize: 13.0,
        fontWeight: FontWeight.w500,
        color: isLight ? AppColors.textPrimary : AppColors.textPrimaryDark,
      ),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(radiusFull),
        side: BorderSide(
          color: isLight ? AppColors.divider : AppColors.dividerDark,
          width: 0.8,
        ),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 12.0, vertical: 6.0),
      elevation: elevationNone,
    );
  }

  // ── Dialog ────────────────────────────────────────────────────
  // FIX: return type changed from DialogTheme to DialogThemeData.
  static DialogThemeData _dialogTheme({required bool isLight}) {
    return DialogThemeData(
      backgroundColor:
          isLight ? AppColors.surface : AppColors.surfaceDark,
      surfaceTintColor: Colors.transparent,
      elevation: elevationLG,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(radiusXL),
      ),
      titleTextStyle: TextStyle(
        color: isLight ? AppColors.textPrimary : AppColors.textPrimaryDark,
        fontSize: 18.0,
        fontWeight: FontWeight.w700,
        letterSpacing: 0.15,
      ),
      contentTextStyle: TextStyle(
        color:
            isLight ? AppColors.textSecondary : AppColors.textSecondaryDark,
        fontSize: 14.0,
        height: 1.5,
      ),
    );
  }

  // ── Bottom Sheet ──────────────────────────────────────────────
  static BottomSheetThemeData _bottomSheetTheme({required bool isLight}) {
    return BottomSheetThemeData(
      backgroundColor:
          isLight ? AppColors.surface : AppColors.surfaceDark,
      surfaceTintColor: Colors.transparent,
      elevation: elevationLG,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.only(
          topLeft: Radius.circular(24.0),
          topRight: Radius.circular(24.0),
        ),
      ),
      dragHandleColor:
          isLight ? AppColors.textDisabled : AppColors.dividerDark,
      dragHandleSize: const Size(36.0, 4.0),
      showDragHandle: true,
      clipBehavior: Clip.antiAlias,
    );
  }

  // ── List Tile ─────────────────────────────────────────────────
  static ListTileThemeData _listTileTheme({required bool isLight}) {
    return ListTileThemeData(
      contentPadding:
          const EdgeInsets.symmetric(horizontal: 16.0, vertical: 4.0),
      iconColor:
          isLight ? AppColors.textSecondary : AppColors.textSecondaryDark,
      titleTextStyle: TextStyle(
        color: isLight ? AppColors.textPrimary : AppColors.textPrimaryDark,
        fontSize: 15.0,
        fontWeight: FontWeight.w500,
      ),
      subtitleTextStyle: TextStyle(
        color:
            isLight ? AppColors.textSecondary : AppColors.textSecondaryDark,
        fontSize: 13.0,
      ),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(radiusMD),
      ),
    );
  }

  // ── Switch ────────────────────────────────────────────────────
  static SwitchThemeData _switchTheme() {
    return SwitchThemeData(
      thumbColor: WidgetStateProperty.resolveWith((states) {
        if (states.contains(WidgetState.selected)) {
          return AppColors.onPrimary;
        }
        return AppColors.textDisabled;
      }),
      trackColor: WidgetStateProperty.resolveWith((states) {
        if (states.contains(WidgetState.selected)) {
          return AppColors.primary;
        }
        return AppColors.surfaceVariant;
      }),
      trackOutlineColor: WidgetStateProperty.resolveWith((states) {
        if (states.contains(WidgetState.selected)) {
          return Colors.transparent;
        }
        return AppColors.divider;
      }),
    );
  }

  // ── Checkbox ──────────────────────────────────────────────────
  static CheckboxThemeData _checkboxTheme() {
    return CheckboxThemeData(
      fillColor: WidgetStateProperty.resolveWith((states) {
        if (states.contains(WidgetState.selected)) {
          return AppColors.primary;
        }
        return Colors.transparent;
      }),
      checkColor: WidgetStateProperty.all(AppColors.onPrimary),
      side: const BorderSide(color: AppColors.textSecondary, width: 1.5),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(radiusXS),
      ),
    );
  }

  // ── Radio ─────────────────────────────────────────────────────
  static RadioThemeData _radioTheme() {
    return RadioThemeData(
      fillColor: WidgetStateProperty.resolveWith((states) {
        if (states.contains(WidgetState.selected)) {
          return AppColors.primary;
        }
        return AppColors.textSecondary;
      }),
    );
  }

  // ── SnackBar ──────────────────────────────────────────────────
  static SnackBarThemeData _snackBarTheme() {
    return SnackBarThemeData(
      backgroundColor: AppColors.textPrimary,
      contentTextStyle: const TextStyle(
        color: AppColors.white,
        fontSize: 14.0,
        fontWeight: FontWeight.w400,
      ),
      actionTextColor: AppColors.secondary,
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(radiusMD),
      ),
      elevation: elevationLG,
    );
  }

  // ── Tab Bar ───────────────────────────────────────────────────
  // FIX: return type changed from TabBarTheme to TabBarThemeData.
  static TabBarThemeData _tabBarTheme({required bool isLight}) {
    return TabBarThemeData(
      labelColor: AppColors.primary,
      unselectedLabelColor:
          isLight ? AppColors.textSecondary : AppColors.textSecondaryDark,
      labelStyle: const TextStyle(
        fontSize: 14.0,
        fontWeight: FontWeight.w600,
        letterSpacing: 0.25,
      ),
      unselectedLabelStyle: const TextStyle(
        fontSize: 14.0,
        fontWeight: FontWeight.w400,
      ),
      indicator: const UnderlineTabIndicator(
        borderSide: BorderSide(
          color: AppColors.primary,
          width: 2.5,
        ),
      ),
      indicatorSize: TabBarIndicatorSize.label,
      dividerColor: Colors.transparent,
    );
  }

  // ────────────────────────────────────────────────────────────────
  // TEXT THEME
  // ────────────────────────────────────────────────────────────────

  /// Builds the full M3 TextTheme with our type scale.
  ///
  /// TYPE SCALE:
  ///   displayLarge  → 57px / hero titles (rare in app)
  ///   displayMedium → 45px / section hero
  ///   displaySmall  → 36px / large numbers (price, stat)
  ///   headlineLarge → 32px / screen titles
  ///   headlineMedium→ 28px / section headers
  ///   headlineSmall → 24px / card titles
  ///   titleLarge    → 22px / dialog titles, app bar
  ///   titleMedium   → 16px / list tile titles, tab labels
  ///   titleSmall    → 14px / sub-headers, chip labels
  ///   bodyLarge     → 16px / primary body copy
  ///   bodyMedium    → 14px / secondary body, descriptions
  ///   bodySmall     → 12px / captions, helper text
  ///   labelLarge    → 14px / button labels
  ///   labelMedium   → 12px / badge text, small labels
  ///   labelSmall    → 11px / bottom nav labels, timestamps
  static TextTheme _textTheme({required bool isLight}) {
    final Color primary =
        isLight ? AppColors.textPrimary : AppColors.textPrimaryDark;
    final Color secondary =
        isLight ? AppColors.textSecondary : AppColors.textSecondaryDark;
    final Color tertiary =
        isLight ? AppColors.textTertiary : AppColors.textTertiaryDark;

    return TextTheme(
      // ── Display ──────────────────────────────────────────────
      displayLarge: TextStyle(
        fontSize: 57.0, fontWeight: FontWeight.w300,
        color: primary, letterSpacing: -0.25, height: 1.12,
      ),
      displayMedium: TextStyle(
        fontSize: 45.0, fontWeight: FontWeight.w300,
        color: primary, letterSpacing: 0, height: 1.16,
      ),
      displaySmall: TextStyle(
        fontSize: 36.0, fontWeight: FontWeight.w400,
        color: primary, letterSpacing: 0, height: 1.22,
      ),
      // ── Headline ─────────────────────────────────────────────
      headlineLarge: TextStyle(
        fontSize: 32.0, fontWeight: FontWeight.w700,
        color: primary, letterSpacing: 0, height: 1.25,
      ),
      headlineMedium: TextStyle(
        fontSize: 28.0, fontWeight: FontWeight.w700,
        color: primary, letterSpacing: 0, height: 1.29,
      ),
      headlineSmall: TextStyle(
        fontSize: 24.0, fontWeight: FontWeight.w600,
        color: primary, letterSpacing: 0, height: 1.33,
      ),
      // ── Title ─────────────────────────────────────────────────
      titleLarge: TextStyle(
        fontSize: 22.0, fontWeight: FontWeight.w600,
        color: primary, letterSpacing: 0, height: 1.27,
      ),
      titleMedium: TextStyle(
        fontSize: 16.0, fontWeight: FontWeight.w600,
        color: primary, letterSpacing: 0.15, height: 1.5,
      ),
      titleSmall: TextStyle(
        fontSize: 14.0, fontWeight: FontWeight.w600,
        color: primary, letterSpacing: 0.1, height: 1.43,
      ),
      // ── Body ──────────────────────────────────────────────────
      bodyLarge: TextStyle(
        fontSize: 16.0, fontWeight: FontWeight.w400,
        color: primary, letterSpacing: 0.5, height: 1.5,
      ),
      bodyMedium: TextStyle(
        fontSize: 14.0, fontWeight: FontWeight.w400,
        color: secondary, letterSpacing: 0.25, height: 1.43,
      ),
      bodySmall: TextStyle(
        fontSize: 12.0, fontWeight: FontWeight.w400,
        color: tertiary, letterSpacing: 0.4, height: 1.33,
      ),
      // ── Label ─────────────────────────────────────────────────
      labelLarge: TextStyle(
        fontSize: 14.0, fontWeight: FontWeight.w600,
        color: primary, letterSpacing: 0.1, height: 1.43,
      ),
      labelMedium: TextStyle(
        fontSize: 12.0, fontWeight: FontWeight.w500,
        color: secondary, letterSpacing: 0.5, height: 1.33,
      ),
      labelSmall: TextStyle(
        fontSize: 11.0, fontWeight: FontWeight.w500,
        color: tertiary, letterSpacing: 0.5, height: 1.45,
      ),
    );
  }
}