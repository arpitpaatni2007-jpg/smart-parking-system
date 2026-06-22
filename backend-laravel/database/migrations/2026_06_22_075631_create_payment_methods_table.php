<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * Migration: Create Payment Methods Table
 * ============================================================
 *
 * WHY THIS TABLE EXISTS:
 * The `payment_methods` table is the master list of payment
 * options available on the platform — UPI, Credit/Debit Card,
 * Net Banking, Wallets, etc.
 *
 * Storing these in the database (instead of hardcoding them)
 * means an admin can enable or disable payment methods through
 * the Admin Panel without requiring a code deployment or a
 * new mobile app release. This is a critical real-world need.
 *
 * HOW IT CONNECTS TO OTHER TABLES:
 *   - `payments` table (Payments module, built later) will store
 *     the payment method used for each transaction. It can
 *     reference `payment_methods.id` as a FK, or store the
 *     method name as a string — decided in that module.
 *   - The API returns only active methods to the User App for
 *     display on the Payment screen.
 *
 * TABLES THAT WILL REFERENCE THIS:
 *   - `payments` → payment_method_id (optional FK, decided later)
 *
 * FUTURE SCALABILITY:
 *   - New method = new row. No schema changes.
 *   - A `sort_order` int column can be added to let admin
 *     control the display order in the app.
 *   - A `gateway` varchar column can route different methods
 *     to different payment providers if we ever use more than
 *     just Razorpay.
 */
return new class extends Migration
{
    /**
     * Run the migration — creates the `payment_methods` table.
     */
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            // Auto-incrementing primary key.
            $table->id();

            // Payment method name, e.g. "UPI", "Credit / Debit Card",
            // "Net Banking", "Wallet".
            // Unique so the same method isn't added twice.
            $table->string('name', 100)->unique();

            // Icon identifier for the payment screen in the User App.
            // From the Payment Screen screenshot, each method has
            // a small icon next to it (UPI logo, card icon, etc.)
            // This field stores whatever the mobile team needs —
            // an asset name, S3 URL, or icon library class.
            $table->string('icon', 255)->nullable();

            // Short description of this payment method.
            // Shown in Admin Panel when managing methods.
            // Example: "Pay using any UPI app — GPay, PhonePe, Paytm."
            $table->text('description')->nullable();

            // Controls whether this method appears in the User App
            // payment screen and is accepted by the payment API.
            // "active"   → method is live and available to users.
            // "inactive" → method is hidden from users and rejected
            //              by the API even if somehow submitted.
            $table->string('status', 20)->default('active');

            // Managed automatically by Laravel.
            $table->timestamps();

            // Index on status — every payment screen load queries
            // WHERE status = 'active', so this index keeps it fast.
            $table->index('status');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};