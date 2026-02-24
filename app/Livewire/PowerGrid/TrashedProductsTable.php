<?php

namespace App\Livewire\PowerGrid;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class TrashedProductsTable extends PowerGridComponent
{
    public function setUp(): array
    {
        return [
            PowerGrid::exportable('export')
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),

            PowerGrid::header()
                ->showSearchInput(),

            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Product::onlyTrashed()
            ->with(['category', 'unit']);
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('category_name', fn (Product $product) => $product->category->name ?? 'N/A')
            ->add('quantity')
            ->add('unit_name', fn (Product $product) => $product->unit->short_code ?? 'N/A')
            ->add('selling_price')
            ->add('deleted_at_formatted', fn (Product $product) => $product->deleted_at->format('Y-m-d H:i:s'));
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->headerAttribute('text-center')
                ->bodyAttribute('text-center')
                ->searchable()
                ->sortable(),

            Column::make('Name', 'name')
                ->headerAttribute('text-center')
                ->bodyAttribute('text-center')
                ->searchable()
                ->sortable(),

            Column::add()
                ->title('Category')
                ->field('category_name')
                ->headerAttribute('text-center')
                ->bodyAttribute('text-center'),

            Column::make('Quantity', 'quantity')
                ->headerAttribute('text-center')
                ->bodyAttribute('text-center')
                ->sortable(),

            Column::make('Unit', 'unit_name')
                ->headerAttribute('text-center')
                ->bodyAttribute('text-center'),

            Column::make('Selling Price', 'selling_price')
                ->headerAttribute('align-middle text-center')
                ->bodyAttribute('align-middle text-center')
                ->sortable(),

            Column::make('Deleted At', 'deleted_at_formatted')
                ->headerAttribute('text-center')
                ->bodyAttribute('text-center')
                ->sortable(),

            Column::action('Action')
                ->headerAttribute('align-middle text-center', styleAttr: 'width: 200px;')
                ->bodyAttribute('align-middle text-center d-flex justify-content-around'),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(Product $row): array
    {
        return [
            Button::make('restore', '<i class="fas fa-undo"></i>')
                ->class('btn btn-outline-success btn-sm')
                ->tooltip('Restore Product')
                ->route('products.restore', ['product' => $row->uuid])
                ->method('patch'),

            Button::make('force-delete', '<i class="fas fa-trash-alt"></i>')
                ->class('btn btn-outline-danger btn-sm')
                ->tooltip('Permanently Delete Product')
                ->route('products.force-delete', ['product' => $row->uuid])
                ->method('delete')
                ->confirm('Are you sure you want to permanently delete this product? This action cannot be undone.'),
        ];
    }
}
