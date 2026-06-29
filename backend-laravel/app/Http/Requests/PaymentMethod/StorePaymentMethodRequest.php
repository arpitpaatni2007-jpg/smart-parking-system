<?php

namespace App\Http\Requests\PaymentMethod;

use App\Models\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;

/**
 * ============================================================
 * StorePaymentMethodRequest
 * ============================================================
 *
 * Validates incoming data when an admin creates a new payment
 * method (e.g. UPI, Card, Net Banking, Wallet).
 *
 * WHY A FORM REQUEST?
 * Keeps all field-level validation rules out of the controller.
 * The controller's store() method only runs after this request
 * confirms every field is valid — cleaner and testable.
 *
 * AUTHORIZATION:
 *   Only admin users should be able to create payment methods.
 *   Role enforcement is done in the controller (consistent with
 *   the pattern used in ParkingController) rather than here,
 *   so authorize() returns true and lets the controller decide.
 *
 * VALIDATION NOTES:
 *   - `name` is unique in the payment_methods table. Two methods
 *     cannot share the same slug (e.g. two "upi" entries).
 *   - `status` defaults to "active" in the controller if omitted,
 *     but if supplied it must be one of the two allowed values.
 *   - `icon` and `description` are optional — admin may add them
 *     later via the update endpoint.
 */
class StorePaymentMethodRequest extends FormRequest
{
    /**
     * All admin routes are guarded by auth middleware.
     * Role enforcement is handled in the controller.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a payment method.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // ── Required ──────────────────────────────────────────
            // Slug-style name: upi, card, net_banking, wallet
            // Must be unique across the payment_methods table.
            'name'        => ['required', 'string', 'max:100', 'unique:payment_methods,name'],

            // ── Optional ──────────────────────────────────────────
            // Icon URL or icon key for the Flutter payment screen.
            'icon'        => ['nullable', 'string', 'max:255'],

            // Human-readable label shown below the icon in the app.
            'description' => ['nullable', 'string', 'max:500'],

            // If not provided the controller defaults to "active".
            'status'      => ['nullable', 'string', 'in:' . PaymentMethod::STATUS_ACTIVE . ',' . PaymentMethod::STATUS_INACTIVE],
        ];
    }

    /**
     * Human-readable error messages for each rule.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'A payment method name is required.',
            'name.unique'   => 'A payment method with this name already exists.',
            'name.max'      => 'The payment method name may not exceed 100 characters.',
            'icon.max'      => 'The icon value may not exceed 255 characters.',
            'description.max' => 'The description may not exceed 500 characters.',
            'status.in'     => 'Status must be either "active" or "inactive".',
        ];
    }
}