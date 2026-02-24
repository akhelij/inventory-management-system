<?php

namespace App\Livewire\PowerGrid;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class SuppliersTable extends PowerGridComponent
{
    public int $perPage = 5;

    public array $perPageValues = [0, 5, 10, 20, 50];

    public function setUp(): array
    {
        return [
            PowerGrid::exportable('export')
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),

            PowerGrid::header()
                ->showSearchInput(),

            PowerGrid::footer()
                ->showPerPage($this->perPage, $this->perPageValues)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Supplier::query();
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('name_lower', fn (Supplier $model) => strtolower(e($model->name)))
            ->add('created_at')
            ->add('created_at_formatted', fn (Supplier $model) => Carbon::parse($model->created_at)->format('d/m/Y'));
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->headerAttribute('text-left')
                ->bodyAttribute('text-left')
                ->searchable()
                ->sortable(),

            Column::make('Name', 'name')
                ->headerAttribute('text-left')
                ->bodyAttribute('text-left')
                ->searchable()
                ->sortable(),

            Column::make('Created at', 'created_at')
                ->headerAttribute('text-center')
                ->bodyAttribute('text-center')
                ->hidden(),

            Column::make('Created at', 'created_at_formatted', 'created_at')
                ->headerAttribute('text-center')
                ->bodyAttribute('text-center')
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

    public function actions(Supplier $row): array
    {
        return [
            Button::make('show', file_get_contents('assets/svg/eye.svg'))
                ->class('btn btn-outline-info btn-icon w-100')
                ->tooltip('Show Supplier Details')
                ->route('suppliers.show', ['supplier' => $row])
                ->method('get'),

            Button::make('edit', file_get_contents('assets/svg/edit.svg'))
                ->class('btn btn-outline-warning btn-icon w-100')
                ->route('suppliers.edit', ['supplier' => $row])
                ->method('get')
                ->tooltip('Edit Supplier'),

            Button::add('delete')
                ->slot(file_get_contents('assets/svg/trash.svg'))
                ->class('btn btn-outline-danger btn-icon w-100')
                ->tooltip('Delete Supplier')
                ->route('suppliers.destroy', ['supplier' => $row])
                ->method('delete'),
        ];
    }
}
