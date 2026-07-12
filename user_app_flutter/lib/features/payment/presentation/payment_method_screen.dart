import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_colors.dart';

// ============================================================
// PaymentMethodScreen
// ============================================================
//
// Allows the user to select a payment method and apply a
// promo code before confirming a parking booking.
//
// SECTIONS:
//   1. AppBar              — "Select Payment Method"
//   2. Booking summary     — parking, slot, duration, total
//   3. Saved cards         — Visa & Mastercard cards
//   4. Payment methods     — Card / UPI / Net Banking / Wallet / Cash
//   5. Promo code          — text field + Apply button
//   6. Price breakdown     — subtotal, tax, discount, total
//   7. Sticky bottom bar   — "Proceed to Payment"
//
// STATE:
//   _selectedMethod   → active payment method
//   _selectedCard     → saved card id (null = no card)
//   _promoController  → promo code text field
//   _promoApplied     → whether a valid promo was applied
//   _isProcessing     → loading state on Pay button
//
// ARCHITECTURE:
//   Pure StatefulWidget — no Riverpod, Bloc, Provider.
// ============================================================

// ── Payment Method Enum ───────────────────────────────────────

enum _Method { card, upi, netBanking, wallet, cash }

extension _MethodX on _Method {
  String get title => switch (this) {
        _Method.card       => 'Credit / Debit Card',
        _Method.upi        => 'UPI',
        _Method.netBanking => 'Net Banking',
        _Method.wallet     => 'Wallet',
        _Method.cash       => 'Cash at Exit',
      };

  String get subtitle => switch (this) {
        _Method.card       => 'Visa, Mastercard, RuPay & more',
        _Method.upi        => 'GPay, PhonePe, Paytm UPI',
        _Method.netBanking => 'All major banks supported',
        _Method.wallet     => 'Paytm, Amazon Pay, Mobikwik',
        _Method.cash       => 'Pay at the exit booth on checkout',
      };

  IconData get icon => switch (this) {
        _Method.card       => Icons.credit_card_rounded,
        _Method.upi        => Icons.account_balance_rounded,
        _Method.netBanking => Icons.account_balance_wallet_rounded,
        _Method.wallet     => Icons.wallet_rounded,
        _Method.cash       => Icons.payments_outlined,
      };

  Color get color => switch (this) {
        _Method.card       => AppColors.primary,
        _Method.upi        => AppColors.secondaryDark,
        _Method.netBanking => AppColors.info,
        _Method.wallet     => AppColors.accent,
        _Method.cash       => AppColors.textSecondary,
      };

  Color get bgColor => switch (this) {
        _Method.card       => AppColors.primary.withAlpha(14),
        _Method.upi        => AppColors.successLight,
        _Method.netBanking => AppColors.infoLight,
        _Method.wallet     => AppColors.warningLight,
        _Method.cash       => AppColors.surfaceVariant,
      };
}

// ── Saved Card Model ──────────────────────────────────────────

class _SavedCard {
  final String  id;
  final String  brand;
  final String  last4;
  final String  expiry;
  final String  holder;
  final Color   gradientStart;
  final Color   gradientEnd;
  final IconData networkIcon;

  const _SavedCard({
    required this.id,
    required this.brand,
    required this.last4,
    required this.expiry,
    required this.holder,
    required this.gradientStart,
    required this.gradientEnd,
    required this.networkIcon,
  });
}

// ── Dummy Data ────────────────────────────────────────────────

const List<_SavedCard> _savedCards = [
  _SavedCard(
    id:             'visa_4589',
    brand:          'Visa',
    last4:          '4589',
    expiry:         '08/27',
    holder:         'ARPIT SHARMA',
    gradientStart:  AppColors.primary,
    gradientEnd:    AppColors.primaryLight,
    networkIcon:    Icons.credit_card_rounded,
  ),
  _SavedCard(
    id:             'mc_9012',
    brand:          'Mastercard',
    last4:          '9012',
    expiry:         '03/26',
    holder:         'ARPIT SHARMA',
    gradientStart:  Color(0xFF1A1A2E),
    gradientEnd:    Color(0xFF16213E),
    networkIcon:    Icons.credit_card_rounded,
  ),
];

const _parkingName   = 'Cyber Hub Parking Complex';
const _slotNumber    = 'G09';
const _duration      = '2 hours';
const _subtotal      = 80.0;
const _tax           = 4.0;
const _discount      = 10.0;
const _total         = 74.0;
const _validPromo    = 'PARK10';

// ── Screen ────────────────────────────────────────────────────

class PaymentMethodScreen extends StatefulWidget {
  const PaymentMethodScreen({super.key});

  @override
  State<PaymentMethodScreen> createState() => _PaymentMethodScreenState();
}

class _PaymentMethodScreenState extends State<PaymentMethodScreen> {
  // ── Selection state ───────────────────────────────────────
  _Method  _selectedMethod = _Method.upi;
  String?  _selectedCardId;

  // ── Promo state ───────────────────────────────────────────
  final _promoCtrl    = TextEditingController();
  final _promoFocus   = FocusNode();
  bool  _promoApplied = false;
  bool  _promoError   = false;

  // ── Processing state ──────────────────────────────────────
  bool _isProcessing = false;

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
    _promoCtrl.dispose();
    _promoFocus.dispose();
    super.dispose();
  }

  // ── Computed ──────────────────────────────────────────────

  double get _effectiveDiscount => _promoApplied ? _discount : 0.0;
  double get _effectiveTotal    => _subtotal + _tax - _effectiveDiscount;

  // ── Actions ───────────────────────────────────────────────

  void _applyPromo() {
    FocusScope.of(context).unfocus();
    final code = _promoCtrl.text.trim().toUpperCase();
    if (code == _validPromo) {
      setState(() {
        _promoApplied = true;
        _promoError   = false;
      });
      _showSnack('Promo code applied! ₹${_discount.toStringAsFixed(0)} off.');
    } else {
      setState(() {
        _promoApplied = false;
        _promoError   = true;
      });
    }
  }

  void _removePromo() {
    setState(() {
      _promoApplied = false;
      _promoError   = false;
      _promoCtrl.clear();
    });
  }

  Future<void> _handlePay() async {
    FocusScope.of(context).unfocus();
    setState(() => _isProcessing = true);
    // TODO: replace with real payment gateway call
    await Future.delayed(const Duration(seconds: 2));
    if (!mounted) return;
    setState(() => _isProcessing = false);
    _showSnack('Payment of ₹${_effectiveTotal.toStringAsFixed(0)} successful!');
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

  // ── Build ─────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.sizeOf(context).width;
    final hPad        = screenWidth > 600 ? screenWidth * 0.08 : 16.0;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar:          _buildAppBar(context),
      body: Stack(
        children: [

          // ── Scrollable content ───────────────────────
          CustomScrollView(
            physics: const BouncingScrollPhysics(),
            slivers: [
              SliverToBoxAdapter(
                child: Padding(
                  padding: EdgeInsets.fromLTRB(hPad, 16, hPad, 130),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [

                      // ── Booking summary ─────────────────
                      _BookingSummaryCard(),

                      const SizedBox(height: 20),

                      // ── Saved cards ─────────────────────
                      _SectionLabel(
                        icon:  Icons.credit_card_rounded,
                        title: 'Saved Cards',
                      ),
                      const SizedBox(height: 12),
                      _SavedCardsRow(
                        cards:         _savedCards,
                        selectedCardId: _selectedCardId,
                        onSelect: (id) => setState(() {
                          _selectedCardId = id;
                          _selectedMethod = _Method.card;
                        }),
                      ),

                      const SizedBox(height: 22),

                      // ── Payment methods ─────────────────
                      _SectionLabel(
                        icon:  Icons.payment_rounded,
                        title: 'Payment Methods',
                      ),
                      const SizedBox(height: 12),
                      _PaymentMethodsCard(
                        selected:      _selectedMethod,
                        onChanged: (m) => setState(() {
                          _selectedMethod = m;
                          if (m != _Method.card) _selectedCardId = null;
                        }),
                      ),

                      const SizedBox(height: 22),

                      // ── Promo code ──────────────────────
                      _SectionLabel(
                        icon:  Icons.local_offer_rounded,
                        title: 'Promo Code',
                      ),
                      const SizedBox(height: 12),
                      _PromoCodeCard(
                        controller:   _promoCtrl,
                        focusNode:    _promoFocus,
                        isApplied:    _promoApplied,
                        hasError:     _promoError,
                        discount:     _discount,
                        onApply:      _applyPromo,
                        onRemove:     _removePromo,
                      ),

                      const SizedBox(height: 22),

                      // ── Price breakdown ─────────────────
                      _SectionLabel(
                        icon:  Icons.receipt_long_rounded,
                        title: 'Price Breakdown',
                      ),
                      const SizedBox(height: 12),
                      _PriceBreakdownCard(
                        subtotal:         _subtotal,
                        tax:              _tax,
                        discount:         _effectiveDiscount,
                        total:            _effectiveTotal,
                        promoApplied:     _promoApplied,
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),

          // ── Sticky bottom bar ────────────────────────
          Positioned(
            bottom: 0,
            left:   0,
            right:  0,
            child: _StickyBottomBar(
              total:        _effectiveTotal,
              isProcessing: _isProcessing,
              method:       _selectedMethod,
              onPay:        _handlePay,
            ),
          ),
        ],
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
        'Select Payment Method',
        style: textTheme.titleLarge?.copyWith(
          color:      AppColors.textPrimary,
          fontWeight: FontWeight.w700,
          fontSize:   18,
        ),
      ),
      centerTitle: true,
      bottom: PreferredSize(
        preferredSize: const Size.fromHeight(1),
        child: Divider(height: 1, color: AppColors.divider),
      ),
    );
  }
}

// ── Booking Summary Card ──────────────────────────────────────

class _BookingSummaryCard extends StatelessWidget {
  const _BookingSummaryCard();

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin:  Alignment.topLeft,
          end:    Alignment.bottomRight,
          colors: [AppColors.primary, AppColors.primaryLight],
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color:       AppColors.primary.withAlpha(65),
            blurRadius:  18,
            offset:      const Offset(0, 6),
          ),
        ],
      ),
      child: Stack(
        children: [
          Positioned(
            right:  -20,
            bottom: -20,
            child:  Icon(
              Icons.local_parking_rounded,
              size:  120,
              color: AppColors.onPrimary.withAlpha(12),
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(18),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [

                // Parking name
                Text(
                  _parkingName,
                  style: textTheme.titleSmall?.copyWith(
                    color:      AppColors.onPrimary,
                    fontWeight: FontWeight.w700,
                  ),
                ),

                const SizedBox(height: 14),

                // Info row
                Row(
                  children: [
                    _SummaryChip(
                      icon:  Icons.local_parking_rounded,
                      label: 'Slot $_slotNumber',
                    ),
                    const SizedBox(width: 10),
                    _SummaryChip(
                      icon:  Icons.timelapse_rounded,
                      label: _duration,
                    ),
                  ],
                ),

                const SizedBox(height: 14),
                Divider(height: 1, color: AppColors.onPrimary.withAlpha(40)),
                const SizedBox(height: 14),

                // Total
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Total Amount',
                      style: textTheme.bodySmall?.copyWith(
                        color: AppColors.onPrimary.withAlpha(190),
                      ),
                    ),
                    Text(
                      '₹${_total.toStringAsFixed(0)}',
                      style: textTheme.headlineSmall?.copyWith(
                        color:      AppColors.onPrimary,
                        fontWeight: FontWeight.w800,
                        height:     1,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _SummaryChip extends StatelessWidget {
  final IconData icon;
  final String   label;
  const _SummaryChip({required this.icon, required this.label});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color:        AppColors.onPrimary.withAlpha(20),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(
          color: AppColors.onPrimary.withAlpha(35),
          width: 1,
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, color: AppColors.onPrimary, size: 13),
          const SizedBox(width: 5),
          Text(
            label,
            style: const TextStyle(
              color:      AppColors.onPrimary,
              fontSize:   12,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}

// ── Section Label ─────────────────────────────────────────────

class _SectionLabel extends StatelessWidget {
  final IconData icon;
  final String   title;
  const _SectionLabel({required this.icon, required this.title});

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
        Icon(icon, color: AppColors.primary, size: 16),
        const SizedBox(width: 6),
        Text(
          title,
          style: textTheme.titleSmall?.copyWith(
            color:      AppColors.textPrimary,
            fontWeight: FontWeight.w700,
            fontSize:   14,
          ),
        ),
      ],
    );
  }
}

// ── Saved Cards Row ───────────────────────────────────────────

class _SavedCardsRow extends StatelessWidget {
  final List<_SavedCard>  cards;
  final String?           selectedCardId;
  final ValueChanged<String> onSelect;

  const _SavedCardsRow({
    required this.cards,
    required this.selectedCardId,
    required this.onSelect,
  });

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 160,
      child: ListView.separated(
        scrollDirection:  Axis.horizontal,
        itemCount:        cards.length,
        separatorBuilder: (_, __) => const SizedBox(width: 12),
        itemBuilder: (ctx, i) => _SavedCardTile(
          card:       cards[i],
          isSelected: cards[i].id == selectedCardId,
          onTap:      () => onSelect(cards[i].id),
        ),
      ),
    );
  }
}

class _SavedCardTile extends StatelessWidget {
  final _SavedCard   card;
  final bool         isSelected;
  final VoidCallback onTap;

  const _SavedCardTile({
    required this.card,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        width:    220,
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin:  Alignment.topLeft,
            end:    Alignment.bottomRight,
            colors: [card.gradientStart, card.gradientEnd],
          ),
          borderRadius: BorderRadius.circular(18),
          border: Border.all(
            color: isSelected
                ? AppColors.secondary
                : Colors.transparent,
            width: 2.5,
          ),
          boxShadow: [
            BoxShadow(
              color:       card.gradientStart.withAlpha(isSelected ? 80 : 50),
              blurRadius:  isSelected ? 18 : 10,
              offset:      const Offset(0, 6),
            ),
          ],
        ),
        padding: const EdgeInsets.all(18),
        child: Stack(
          children: [

            // Background chip pattern
            Positioned(
              right:  -14,
              top:    -14,
              child:  Icon(
                Icons.credit_card_rounded,
                size:  80,
                color: AppColors.onPrimary.withAlpha(12),
              ),
            ),

            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [

                // Brand + selected tick
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      card.brand,
                      style: textTheme.titleSmall?.copyWith(
                        color:      AppColors.onPrimary,
                        fontWeight: FontWeight.w700,
                        letterSpacing: 0.5,
                      ),
                    ),
                    if (isSelected)
                      Container(
                        width:  22,
                        height: 22,
                        decoration: BoxDecoration(
                          color: AppColors.secondary,
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(
                          Icons.check_rounded,
                          color: AppColors.onSecondary,
                          size:  13,
                        ),
                      ),
                  ],
                ),

                const Spacer(),

                // Last 4
                Text(
                  '•••• •••• •••• ${card.last4}',
                  style: textTheme.bodyMedium?.copyWith(
                    color:         AppColors.onPrimary,
                    fontWeight:    FontWeight.w600,
                    letterSpacing: 1.5,
                    fontFeatures:  const [FontFeature.tabularFigures()],
                  ),
                ),

                const SizedBox(height: 10),

                // Holder + expiry
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      card.holder,
                      style: textTheme.bodySmall?.copyWith(
                        color:         AppColors.onPrimary.withAlpha(200),
                        letterSpacing: 0.5,
                        fontSize:      11,
                      ),
                    ),
                    Text(
                      card.expiry,
                      style: textTheme.bodySmall?.copyWith(
                        color:      AppColors.onPrimary.withAlpha(200),
                        fontSize:   11,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

// ── Payment Methods Card ──────────────────────────────────────

class _PaymentMethodsCard extends StatelessWidget {
  final _Method              selected;
  final ValueChanged<_Method> onChanged;

  const _PaymentMethodsCard({
    required this.selected,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    final methods = _Method.values;

    return Container(
      decoration: BoxDecoration(
        color:        AppColors.surface,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppColors.divider, width: 1),
        boxShadow: [
          BoxShadow(
            color:       AppColors.shadow,
            blurRadius:  10,
            offset:      const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: List.generate(methods.length, (i) {
          final method  = methods[i];
          final isLast  = i == methods.length - 1;
          final isSelected = method == selected;

          return Column(
            children: [
              _MethodTile(
                method:     method,
                isSelected: isSelected,
                onTap:      () => onChanged(method),
              ),
              if (!isLast)
                Divider(
                  height:    1,
                  color:     AppColors.divider,
                  indent:    68,
                  endIndent: 0,
                ),
            ],
          );
        }),
      ),
    );
  }
}

class _MethodTile extends StatelessWidget {
  final _Method      method;
  final bool         isSelected;
  final VoidCallback onTap;

  const _MethodTile({
    required this.method,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return InkWell(
      onTap:        onTap,
      borderRadius: BorderRadius.circular(20),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 160),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 13),
        decoration: BoxDecoration(
          color: isSelected
              ? method.color.withAlpha(7)
              : Colors.transparent,
          borderRadius: BorderRadius.circular(20),
        ),
        child: Row(
          children: [

            // Method icon
            AnimatedContainer(
              duration: const Duration(milliseconds: 160),
              width:  44,
              height: 44,
              decoration: BoxDecoration(
                color:        isSelected ? method.color : method.bgColor,
                borderRadius: BorderRadius.circular(13),
                boxShadow: isSelected
                    ? [
                        BoxShadow(
                          color:       method.color.withAlpha(55),
                          blurRadius:  10,
                          offset:      const Offset(0, 4),
                        ),
                      ]
                    : null,
              ),
              child: Icon(
                method.icon,
                color: isSelected ? AppColors.onPrimary : method.color,
                size:  22,
              ),
            ),

            const SizedBox(width: 14),

            // Title + subtitle
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    method.title,
                    style: textTheme.bodyMedium?.copyWith(
                      color:      AppColors.textPrimary,
                      fontWeight: isSelected
                          ? FontWeight.w700
                          : FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    method.subtitle,
                    style: textTheme.bodySmall?.copyWith(
                      color: AppColors.textTertiary,
                    ),
                  ),
                ],
              ),
            ),

            // Radio
            AnimatedContainer(
              duration: const Duration(milliseconds: 160),
              width:  22,
              height: 22,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(
                  color: isSelected ? method.color : AppColors.divider,
                  width: isSelected ? 0 : 1.5,
                ),
                color: isSelected ? method.color : Colors.transparent,
              ),
              child: isSelected
                  ? const Icon(
                      Icons.check_rounded,
                      color: AppColors.onPrimary,
                      size:  14,
                    )
                  : null,
            ),
          ],
        ),
      ),
    );
  }
}

// ── Promo Code Card ───────────────────────────────────────────

class _PromoCodeCard extends StatelessWidget {
  final TextEditingController controller;
  final FocusNode             focusNode;
  final bool                  isApplied;
  final bool                  hasError;
  final double                discount;
  final VoidCallback          onApply;
  final VoidCallback          onRemove;

  const _PromoCodeCard({
    required this.controller,
    required this.focusNode,
    required this.isApplied,
    required this.hasError,
    required this.discount,
    required this.onApply,
    required this.onRemove,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color:        AppColors.surface,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: isApplied
              ? AppColors.secondary.withAlpha(60)
              : hasError
                  ? AppColors.error.withAlpha(50)
                  : AppColors.divider,
          width: (isApplied || hasError) ? 1.5 : 1,
        ),
        boxShadow: [
          BoxShadow(
            color:       isApplied
                ? AppColors.secondary.withAlpha(20)
                : AppColors.shadow,
            blurRadius:  10,
            offset:      const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [

          // Input row
          Row(
            children: [

              // Text field
              Expanded(
                child: Container(
                  height: 50,
                  decoration: BoxDecoration(
                    color:        isApplied
                        ? AppColors.successLight
                        : AppColors.surfaceVariant,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: isApplied
                          ? AppColors.secondary.withAlpha(50)
                          : hasError
                              ? AppColors.error
                              : AppColors.divider,
                      width: (isApplied || hasError) ? 1.5 : 1,
                    ),
                  ),
                  child: Row(
                    children: [
                      const SizedBox(width: 12),
                      Icon(
                        isApplied
                            ? Icons.check_circle_rounded
                            : Icons.local_offer_outlined,
                        color: isApplied
                            ? AppColors.secondaryDark
                            : AppColors.textTertiary,
                        size: 18,
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: TextField(
                          controller:    controller,
                          focusNode:     focusNode,
                          enabled:       !isApplied,
                          textCapitalization: TextCapitalization.characters,
                          style: textTheme.bodyMedium?.copyWith(
                            color:         isApplied
                                ? AppColors.secondaryDark
                                : AppColors.textPrimary,
                            fontWeight:    FontWeight.w700,
                            letterSpacing: 1.2,
                          ),
                          decoration: InputDecoration(
                            hintText:       'Enter promo code',
                            hintStyle: TextStyle(
                              color:         AppColors.textTertiary,
                              fontWeight:    FontWeight.w400,
                              letterSpacing: 0,
                            ),
                            border:         InputBorder.none,
                            isDense:        true,
                            contentPadding: EdgeInsets.zero,
                          ),
                        ),
                      ),
                      if (isApplied)
                        GestureDetector(
                          onTap: onRemove,
                          child: Padding(
                            padding: const EdgeInsets.only(right: 10),
                            child: Icon(
                              Icons.close_rounded,
                              color: AppColors.textSecondary,
                              size:  18,
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              ),

              const SizedBox(width: 10),

              // Apply button
              SizedBox(
                height: 50,
                child: FilledButton(
                  onPressed: isApplied ? null : onApply,
                  style: FilledButton.styleFrom(
                    backgroundColor:         AppColors.primary,
                    disabledBackgroundColor: AppColors.secondary,
                    foregroundColor:         AppColors.onPrimary,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    padding: const EdgeInsets.symmetric(horizontal: 18),
                    textStyle: textTheme.labelLarge?.copyWith(
                      fontWeight: FontWeight.w700,
                      fontSize:   14,
                    ),
                    elevation:   0,
                    shadowColor: Colors.transparent,
                  ),
                  child: Text(isApplied ? 'Applied' : 'Apply'),
                ),
              ),
            ],
          ),

          // Status message
          if (isApplied) ...[
            const SizedBox(height: 10),
            Row(
              children: [
                const Icon(
                  Icons.local_offer_rounded,
                  color: AppColors.secondaryDark,
                  size:  14,
                ),
                const SizedBox(width: 6),
                Text(
                  'Promo applied! You save ₹${discount.toStringAsFixed(0)}',
                  style: textTheme.bodySmall?.copyWith(
                    color:      AppColors.secondaryDark,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
          ] else if (hasError) ...[
            const SizedBox(height: 10),
            Row(
              children: [
                const Icon(
                  Icons.error_outline_rounded,
                  color: AppColors.error,
                  size:  14,
                ),
                const SizedBox(width: 6),
                Text(
                  'Invalid promo code. Please try again.',
                  style: textTheme.bodySmall?.copyWith(
                    color:      AppColors.error,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ] else ...[
            const SizedBox(height: 8),
            Text(
              'Try PARK10 for ₹10 off',
              style: textTheme.bodySmall?.copyWith(
                color: AppColors.textTertiary,
              ),
            ),
          ],
        ],
      ),
    );
  }
}

// ── Price Breakdown Card ──────────────────────────────────────

class _PriceBreakdownCard extends StatelessWidget {
  final double subtotal;
  final double tax;
  final double discount;
  final double total;
  final bool   promoApplied;

  const _PriceBreakdownCard({
    required this.subtotal,
    required this.tax,
    required this.discount,
    required this.total,
    required this.promoApplied,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color:        AppColors.surface,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppColors.divider, width: 1),
        boxShadow: [
          BoxShadow(
            color:       AppColors.shadow,
            blurRadius:  10,
            offset:      const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: [

          _BreakdownRow(
            label:  'Parking Charges',
            value:  '₹${subtotal.toStringAsFixed(0)}',
            icon:   Icons.local_parking_rounded,
          ),

          const SizedBox(height: 12),

          _BreakdownRow(
            label:  'GST & Taxes',
            value:  '₹${tax.toStringAsFixed(2)}',
            icon:   Icons.account_balance_outlined,
          ),

          if (promoApplied) ...[
            const SizedBox(height: 12),
            _BreakdownRow(
              label:      'Promo Discount',
              value:      '− ₹${discount.toStringAsFixed(0)}',
              icon:       Icons.local_offer_rounded,
              valueColor: AppColors.secondaryDark,
            ),
          ],

          const SizedBox(height: 14),

          Container(height: 1, color: AppColors.divider),

          const SizedBox(height: 14),

          // Total row
          Row(
            children: [
              Container(
                width:  36,
                height: 36,
                decoration: BoxDecoration(
                  color:        AppColors.primary,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(
                  Icons.currency_rupee_rounded,
                  color: AppColors.onPrimary,
                  size:  18,
                ),
              ),
              const SizedBox(width: 12),
              Text(
                'Total Amount',
                style: textTheme.titleSmall?.copyWith(
                  color:      AppColors.textPrimary,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const Spacer(),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  if (promoApplied)
                    Text(
                      '₹${(subtotal + tax).toStringAsFixed(0)}',
                      style: textTheme.bodySmall?.copyWith(
                        color:      AppColors.textTertiary,
                        decoration: TextDecoration.lineThrough,
                      ),
                    ),
                  Text(
                    '₹${total.toStringAsFixed(0)}',
                    style: textTheme.titleLarge?.copyWith(
                      color:      AppColors.primary,
                      fontWeight: FontWeight.w800,
                      fontSize:   24,
                      height:     1,
                    ),
                  ),
                ],
              ),
            ],
          ),

          if (promoApplied) ...[
            const SizedBox(height: 10),
            Container(
              width:   double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 8),
              decoration: BoxDecoration(
                color:        AppColors.successLight,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(
                    Icons.savings_rounded,
                    color: AppColors.secondaryDark,
                    size:  14,
                  ),
                  const SizedBox(width: 6),
                  Text(
                    'You save ₹${discount.toStringAsFixed(0)} with promo PARK10',
                    style: textTheme.bodySmall?.copyWith(
                      color:      AppColors.secondaryDark,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _BreakdownRow extends StatelessWidget {
  final String  label;
  final String  value;
  final IconData icon;
  final Color?  valueColor;

  const _BreakdownRow({
    required this.label,
    required this.value,
    required this.icon,
    this.valueColor,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Row(
      children: [
        Icon(icon, color: AppColors.textTertiary, size: 15),
        const SizedBox(width: 10),
        Text(
          label,
          style: textTheme.bodyMedium?.copyWith(
            color: AppColors.textSecondary,
          ),
        ),
        const Spacer(),
        Text(
          value,
          style: textTheme.bodyMedium?.copyWith(
            color:      valueColor ?? AppColors.textPrimary,
            fontWeight: FontWeight.w700,
          ),
        ),
      ],
    );
  }
}

// ── Sticky Bottom Bar ─────────────────────────────────────────

class _StickyBottomBar extends StatelessWidget {
  final double       total;
  final bool         isProcessing;
  final _Method      method;
  final VoidCallback onPay;

  const _StickyBottomBar({
    required this.total,
    required this.isProcessing,
    required this.method,
    required this.onPay,
  });

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        boxShadow: [
          BoxShadow(
            color:       AppColors.shadow,
            blurRadius:  20,
            spreadRadius: 0,
            offset:      const Offset(0, -6),
          ),
        ],
        border: const Border(
          top: BorderSide(color: AppColors.divider, width: 1),
        ),
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
          child: Row(
            children: [

              // Method icon + total
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize:       MainAxisSize.min,
                children: [
                  Row(
                    children: [
                      Icon(method.icon, color: method.color, size: 14),
                      const SizedBox(width: 5),
                      Text(
                        method.title,
                        style: textTheme.bodySmall?.copyWith(
                          color: AppColors.textTertiary,
                        ),
                      ),
                    ],
                  ),
                  Text(
                    '₹${total.toStringAsFixed(0)}',
                    style: textTheme.headlineSmall?.copyWith(
                      color:      AppColors.primary,
                      fontWeight: FontWeight.w800,
                      height:     1.1,
                    ),
                  ),
                ],
              ),

              const SizedBox(width: 16),

              // Pay button
              Expanded(
                child: SizedBox(
                  height: 54,
                  child: FilledButton(
                    onPressed: isProcessing ? null : onPay,
                    style: FilledButton.styleFrom(
                      backgroundColor:         AppColors.primary,
                      disabledBackgroundColor: AppColors.primaryLighter,
                      foregroundColor:         AppColors.onPrimary,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                      textStyle: textTheme.labelLarge?.copyWith(
                        fontSize:      16,
                        fontWeight:    FontWeight.w700,
                        letterSpacing: 0.3,
                      ),
                      elevation:   0,
                      shadowColor: Colors.transparent,
                    ),
                    child: isProcessing
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
                              const Icon(Icons.lock_rounded, size: 18),
                              const SizedBox(width: 8),
                              const Text('Proceed to Payment'),
                            ],
                          ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}