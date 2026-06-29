<?php

namespace App\Http\Requests\PaymentMethod;

use App\Models\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ============================================================
 * UpdatePaymentMethodRequest
 * ============================================================
 *
 * Validates incoming data when an admin updates an existing
 * payment method record.
 *
 * KEY DIFFERENCE FROM StorePaymentMethodRequest:
 *   The `name` uniqueness rule must ignore the record being
 *   updated — otherwise saving a method without changing its
 *   name would fail with "already exists". We use Laravel's
 *   Rule::unique()->ignore() for this.
 *
 * PARTIAL UPDATES:
 *   All fields are optional (nullable or sometimes). The admin
 *   panel may send only the fields that changed. The controller
 *   uses $request->only([...]) to apply just what was sent.
 *
 * AUTHORIZATION:
 *   Role enforcement is handled in the controller, consistent
 *   with the pattern used across this project.
 */
class UpdatePaymentMethodRequest extends FormRequest
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
     * Validation rules for updating a payment method.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Route model binding provides the PaymentMethod instance.
        // We need its ID to exclude it from the unique check.
        $paymentMethodId = $this->route('payment_method')?->id;

        return [
            // ── All fields optional on update ─────────────────────
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                // Ignore the current record's own name when checking uniqueness.
                Rule::unique('payment_methods', 'name')->ignore($paymentMethodId),
            ],

            'icon'        => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'status'      => ['sometimes', 'required', 'string', 'in:' . PaymentMethod::STATUS_ACTIVE . ',' . PaymentMethod::STATUS_INACTIVE],
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
            'name.required'   => 'A payment method name is required.',
            'name.unique'     => 'A payment method with this name already exists.',
            'name.max'        => 'The payment method name may not exceed 100 characters.',
            'icon.max'        => 'The icon value may not exceed 255 characters.',
            'description.max' => 'The description may not exceed 500 characters.',
            'status.required' => 'Status is required when provided.',
            'status.in'       => 'Status must be either "active" or "inactive".',
        ];
    }
}