<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\UppercasesInput;
use App\Models\Structure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateStructureRequest extends FormRequest
{
    use UppercasesInput {
        prepareForValidation as prepareUppercaseFields;
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function uppercaseFields(): array
    {
        return [
            'structure_type',
            'name',
            'code',
            'description',
            'technical_attributes_json',
            'observations',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->prepareUppercaseFields();

        $dimensions = $this->input('dimensions_json');

        if (! is_array($dimensions)) {
            return;
        }

        if (! array_key_exists('area_total', $dimensions)
            && is_numeric($dimensions['largo'] ?? null)
            && is_numeric($dimensions['ancho'] ?? null)) {
            $dimensions['area_total'] = round((float) $dimensions['largo'] * (float) $dimensions['ancho'], 2);
            $this->merge(['dimensions_json' => $dimensions]);
        }
    }

    public function rules(): array
    {
        return [
            'farm_id' => ['sometimes', 'integer', 'exists:farms,id'],
            'parent_structure_id' => ['nullable', 'integer', 'exists:structures,id'],
            'structure_type' => ['sometimes', 'string', Rule::in(Structure::allowedTypes())],
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'under_construction', 'retired'])],
            'description' => ['nullable', 'string'],
            'dimensions_json' => ['nullable', 'array'],
            'dimensions_json.largo' => ['nullable', 'numeric', 'min:0'],
            'dimensions_json.ancho' => ['nullable', 'numeric', 'min:0'],
            'dimensions_json.alto' => ['nullable', 'numeric', 'min:0'],
            'dimensions_json.area_total' => ['nullable', 'numeric', 'min:0'],
            'technical_attributes_json' => ['nullable', 'array'],
            'observations' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Structure $structure */
            $structure = $this->route('structure');
            $structureType = $this->input('structure_type', $structure->structure_type);
            $parentId = $this->input('parent_structure_id', $structure->parent_structure_id);
            $farmId = (int) $this->input('farm_id', $structure->farm_id);

            if ($structureType === Structure::TYPE_GALPON && $parentId !== null) {
                $validator->errors()->add('parent_structure_id', 'A galpon cannot have a parent structure.');
            }

            if ($structureType === Structure::TYPE_SYSTEM && $parentId === null) {
                $validator->errors()->add('parent_structure_id', 'A system must belong to a galpon.');
            }

            if ($structureType !== Structure::TYPE_SYSTEM || $parentId === null) {
                return;
            }

            $parent = Structure::query()->find($parentId);

            if (! $parent) {
                return;
            }

            if ($parent->id === $structure->id) {
                $validator->errors()->add('parent_structure_id', 'A structure cannot be its own parent.');
            }

            if ($parent->structure_type !== Structure::TYPE_GALPON) {
                $validator->errors()->add('parent_structure_id', 'Systems can only be assigned to galpones.');
            }

            if ((int) $parent->farm_id !== $farmId) {
                $validator->errors()->add('parent_structure_id', 'The galpon parent must belong to the same farm.');
            }

            if ($structureType === Structure::TYPE_SYSTEM && $structure->systems()->exists()) {
                $validator->errors()->add('structure_type', 'A galpon with assigned systems cannot be converted into a system.');
            }
        });
    }
}
