<?php

namespace App\Http\Requests\PricingRule;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * StorePricingRuleRequest
 *
 * Validates the payload when an admin creates a new pricing rule.
 *
 * BUSINESS RULES ENFORCED HERE:
 *   1. extra_hour_price is required only for 'hourly' pricing_type.
 *      For daily/monthly, it is irrelevant and defaults to 0.
 *
 *   2. There should be at most ONE active rule per (vehicle_type_id, pricing_type)
 *      combination. This is enforced in the controller (not here) because
 *      the unique check is a business rule, not a simple field validation.
 *
 * ADMIN ONLY:
 *   Role enforcement is done in the controller.
 */
class StorePricingRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /**
             * The vehicle category this rule applies to.
             * Must exist in the vehicle_types master table.
             */
            'vehicle_type_id' => ['required', 'integer', 'exists:vehicle_types,id'],

            /**
             * Billing cycle.
             * Must match the ENUM values in the pricing_rules migration.
             */
            'pricing_type' => ['required', 'string', 'in:hourly,daily,monthly'],

            /**
             * Charge for the first unit (first hour, or flat day/month rate).
             * Must be a positive monetary value.
             * DECIMAL(10,2) in DB — validated as numeric here.
             */
            'base_price' => ['required', 'numeric', 'min:0.01'],

            /**
             * Charge per additional hour beyond the first (hourly plans only).
             * - Required when pricing_type = 'hourly'
             * - Should be 0.00 (or omitted) for daily/monthly
             * - Cannot exceed base_price (extra hours should not cost more than the first)
             *
             * 'required_if' makes it required only when hourly is selected.
             * 'nullable' allows it to be omitted for non-hourly types.
             */
            'extra_hour_price' => [
                'nullable',
                'numeric',
                'min:0',
                'required_if:pricing_type,hourly',
            ],

            /**
             * Active rules are applied during booking price calculation.
             * Inactive rules are preserved for history but not used.
             * Defaults to 'active' in the controller if not provided.
             */
            'status' => ['sometimes', 'string', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_type_id.required'       => 'Please select a vehicle type.',
            'vehicle_type_id.exists'         => 'The selected vehicle type does not exist.',
            'pricing_type.required'          => 'Please select a pricing type (hourly, daily, or monthly).',
            'pricing_type.in'               => 'Pricing type must be: hourly, daily, or monthly.',
            'base_price.required'            => 'Base price is required.',
            'base_price.min'                 => 'Base price must be greater than zero.',
            'extra_hour_price.required_if'   => 'Extra hour price is required for hourly pricing.',
            'extra_hour_price.min'           => 'Extra hour price cannot be negative.',
            'status.in'                      => 'Status must be: active or inactive.',
        ];
    }

    /**
     * After base validation passes, ensure extra_hour_price
     * does not exceed base_price for hourly plans.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if (
                $this->pricing_type === 'hourly' &&
                $this->filled('extra_hour_price') &&
                $this->filled('base_price') &&
                (float) $this->extra_hour_price > (float) $this->base_price
            ) {
                $v->errors()->add(
                    'extra_hour_price',
                    'Extra hour price cannot exceed the base price.'
                );
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}