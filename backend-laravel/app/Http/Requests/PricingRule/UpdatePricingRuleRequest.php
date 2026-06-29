<?php

namespace App\Http\Requests\PricingRule;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * UpdatePricingRuleRequest
 *
 * Validates partial updates to an existing pricing rule.
 * All fields are optional (sometimes) — only sent fields are validated.
 *
 * PARTIAL UPDATE SUPPORT:
 *   Admin can update just the status (activate/deactivate) without
 *   resending pricing values, or update only the base_price without
 *   changing the pricing_type.
 *
 * CROSS-FIELD VALIDATION:
 *   The extra_hour_price vs base_price check is applied only
 *   when both values are present in the same request — handled
 *   in withValidator() using the merged current + incoming values.
 */
class UpdatePricingRuleRequest extends FormRequest
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
            'vehicle_type_id'  => ['sometimes', 'required', 'integer', 'exists:vehicle_types,id'],
            'pricing_type'     => ['sometimes', 'required', 'string', 'in:hourly,daily,monthly'],
            'base_price'       => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'extra_hour_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'status'           => ['sometimes', 'required', 'string', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_type_id.exists'  => 'The selected vehicle type does not exist.',
            'pricing_type.in'         => 'Pricing type must be: hourly, daily, or monthly.',
            'base_price.min'          => 'Base price must be greater than zero.',
            'extra_hour_price.min'    => 'Extra hour price cannot be negative.',
            'status.in'               => 'Status must be: active or inactive.',
        ];
    }

    /**
     * Cross-field validation after primary rules pass.
     *
     * Resolve the effective pricing_type and base_price by merging
     * the incoming request values with the existing model values,
     * so partial updates are validated correctly.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            // Resolve the current rule model from the route
            $rule = $this->route('pricing_rule');

            if (! $rule) {
                return;
            }

            // Use the incoming value if provided, otherwise fall back to existing model value
            $effectivePricingType    = $this->input('pricing_type',     $rule->pricing_type);
            $effectiveBasePrice      = $this->input('base_price',       $rule->base_price);
            $effectiveExtraHourPrice = $this->input('extra_hour_price', $rule->extra_hour_price);

            // extra_hour_price must be present for hourly and not exceed base_price
            if ($effectivePricingType === 'hourly') {
                if ($this->has('extra_hour_price') && is_null($this->extra_hour_price)) {
                    $v->errors()->add(
                        'extra_hour_price',
                        'Extra hour price cannot be null for hourly pricing.'
                    );
                    return;
                }

                if (
                    ! is_null($effectiveExtraHourPrice) &&
                    (float) $effectiveExtraHourPrice > (float) $effectiveBasePrice
                ) {
                    $v->errors()->add(
                        'extra_hour_price',
                        'Extra hour price cannot exceed the base price.'
                    );
                }
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