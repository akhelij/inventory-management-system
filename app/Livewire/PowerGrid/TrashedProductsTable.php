<?php

namespace App\Livewire\PowerGrid;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Exportable;
use PowerComponents\LivewirePowerGrid\Footer;
use PowerComponents\LivewirePowerGrid\Header;
use PowerComponents\LivewirePowerGrid\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridColumns;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class TrashedProductsTable extends PowerGridComponent
{
    public function setUp(): array
    {
        return [
            Exportable::make('export')
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),

            Header::make()->showSearchInput(),

            Footer::make()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Product::onlyTrashed()
            ->with(['category', 'unit']);
    }

    public function addColumns(): PowerGridColumns
    {
        return PowerGrid::columns()
            ->addColumn('id')
            ->addColumn('name')
            ->addColumn('category_name', function (Product $product) {
                return $product->category->name ?? 'N/A';
            })
            ->addColumn('quantity')
            ->addColumn('unit_name', function (Product $product) {
                return $product->unit->short_code ?? 'N/A';
            })
            ->addColumn('selling_price')
            ->addColumn('deleted_at_formatted', function (Product $product) {
                return $product->deleted_at->format('Y-m-d H:i:s');
            });
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
        return [
            //
        ];
    }

    public function actions(\App\Models\Product $row): array
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
