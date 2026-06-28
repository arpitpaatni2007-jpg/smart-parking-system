<?php

namespace App\Http\Requests\CMSPage;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * UpdateCMSPageRequest
 *
 * Validates partial updates to an existing CMS page.
 * All fields are optional (sometimes) — only sent fields are validated.
 *
 * SLUG UNIQUENESS ON UPDATE:
 *   Rule::unique()->ignore($currentPageId) ensures the page can
 *   be re-saved with its own existing slug without a false conflict.
 *   The current page record is identified via the route {page} parameter.
 *
 * NOTE ON ROUTE MODEL BINDING:
 *   CMSPage uses slug as the route key (getRouteKeyName() returns 'slug').
 *   So $this->route('page') returns the resolved CMSPage model instance,
 *   from which we extract ->id for the unique ignore rule.
 */
class UpdateCMSPageRequest extends FormRequest
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
        // Resolve the current page's DB id for the unique ignore rule.
        // The {page} route param is resolved to a CMSPage model instance
        // because CMSPage::getRouteKeyName() returns 'slug'.
        $pageId = $this->route('page')?->id;

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],

            /**
             * When updating slug, ignore the current page's own row
             * so re-submitting the same slug does not trigger a conflict.
             * Still enforces uniqueness against all other pages.
             */
            'slug' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9\-]+$/',
                Rule::unique('cms_pages', 'slug')
                    ->ignore($pageId)
                    ->whereNull('deleted_at'),
            ],

            'content'   => ['sometimes', 'required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.max'     => 'Page title cannot exceed 255 characters.',
            'slug.regex'    => 'Slug may only contain lowercase letters, numbers, and hyphens.',
            'slug.unique'   => 'This slug is already used by another page.',
            'slug.max'      => 'Slug cannot exceed 255 characters.',
            'content.required' => 'Page content cannot be empty.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('slug')) {
            $this->merge(['slug' => Str::slug($this->slug)]);
        }
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