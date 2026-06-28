<?php

namespace App\Http\Requests\CMSPage;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

/**
 * StoreCMSPageRequest
 *
 * Validates the payload when an admin creates a new CMS page.
 *
 * SLUG BEHAVIOUR:
 *   - If `slug` is provided, it is sanitized (lowercase, hyphenated)
 *     in prepareForValidation() and validated for uniqueness.
 *   - If `slug` is omitted, the CMSPage model's boot() method
 *     auto-generates it from the title on insert.
 *
 * ADMIN ONLY:
 *   Role enforcement is done in CMSPageController, not here,
 *   to keep the request class focused purely on field validation.
 */
class StoreCMSPageRequest extends FormRequest
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
             * Page display title.
             * e.g. "Privacy Policy", "Terms & Conditions", "About Us"
             */
            'title' => ['required', 'string', 'max:255'],

            /**
             * URL-safe slug — optional on creation.
             * Auto-generated from title if not provided (model boot()).
             * Must be unique across all (non-deleted) CMS pages.
             * Only lowercase letters, numbers, and hyphens allowed.
             */
            'slug' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9\-]+$/',
                'unique:cms_pages,slug',
            ],

            /**
             * Full page body content.
             * Accepts HTML or Markdown — stored and delivered as-is.
             * No max length restriction — legal pages can be long.
             */
            'content' => ['required', 'string'],

            /**
             * Whether the page is publicly visible.
             * Defaults to true (published) in the controller if not sent.
             */
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'   => 'Please provide a page title.',
            'title.max'        => 'Page title cannot exceed 255 characters.',
            'slug.regex'       => 'Slug may only contain lowercase letters, numbers, and hyphens.',
            'slug.unique'      => 'This slug is already in use by another page.',
            'slug.max'         => 'Slug cannot exceed 255 characters.',
            'content.required' => 'Page content is required.',
        ];
    }

    /**
     * Normalize the slug before validation runs:
     * Converts "Privacy Policy" → "privacy-policy" automatically.
     */
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