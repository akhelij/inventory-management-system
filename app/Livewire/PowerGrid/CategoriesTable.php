<?php

namespace App\Livewire\PowerGrid;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class CategoriesTable extends PowerGridComponent
{
    public int $perPage = 5;

    public array $perPageValues = [0, 5, 10, 20, 50];

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage($this->perPage, $this->perPageValues)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Category::query();
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('name_lower', fn (Category $model) => strtolower(e($model->name)))
            ->add('slug')
            ->add('created_at')
            ->add('created_at_formatted', fn (Category $model) => Carbon::parse($model->created_at)->format('d/m/Y'));
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->headerAttribute('align-middle text-center')
                ->bodyAttribute('align-middle text-center')
                ->searchable()
                ->sortable(),

            Column::make('Name', 'name')
                ->headerAttribute('align-middle text-center')
                ->bodyAttribute('align-middle text-center')
                ->searchable()
                ->sortable(),

            Column::make('Slug', 'slug')
                ->headerAttribute('align-middle text-center')
                ->bodyAttribute('align-middle text-center')
                ->searchable(),

            Column::make('Created at', 'created_at')
                ->headerAttribute('align-middle text-center')
                ->bodyAttribute('align-middle text-center')
                ->hidden(),

            Column::make('Created at', 'created_at_formatted', 'created_at')
                ->headerAttribute('align-middle text-center')
                ->bodyAttribute('align-middle text-center')
                ->searchable(),

            Column::action('Action')
                ->headerAttribute('text-center', styleAttr: 'width: 150px;')
                ->bodyAttribute('text-center d-flex justify-content-around'),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(Category $row): array
    {
        return [
            Button::make('show', file_get_contents('assets/svg/eye.svg'))
                ->class('btn btn-outline-info btn-icon w-100')
                ->tooltip('Show Category Details')
                ->route('categories.show', ['category' => $row])
                ->method('get'),

            Button::make('edit', file_get_contents('assets/svg/edit.svg'))
                ->class('btn btn-outline-warning btn-icon w-100')
                ->route('categories.edit', ['category' => $row])
                ->method('get')
                ->tooltip('Edit Category'),

            Button::add('delete')
                ->slot(file_get_contents('assets/svg/trash.svg'))
                ->class('btn btn-outline-danger btn-icon w-100')
                ->tooltip('Delete Category')
                ->route('categories.destroy', ['category' => $row])
                ->method('delete'),
        ];
    }
}
