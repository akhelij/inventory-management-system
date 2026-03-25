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

final class ProductsTable extends PowerGridComponent
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
        return Product::query()
            ->with(['category', 'unit']);
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('image')
            ->add('name')
            ->add('category_id', fn (Product $product) => $product->category_id)
            ->add('category_name', fn (Product $product) => $product->category->name)
            ->add('quantity')
            ->add('unit_id')
            ->add('unit_name', fn (Product $product) => $product->unit->short_code)
            ->add('selling_price');
    }

    public function columns(): array
    {
        return [
            Column::make(__('ID'), 'id')
                ->headerAttribute('text-center')
                ->bodyAttribute('text-center')
                ->searchable()
                ->sortable(),

            Column::make(__('Image'), 'image')
                ->headerAttribute('text-center')
                ->bodyAttribute('text-center'),

            Column::make(__('Name'), 'name')
                ->headerAttribute('text-center')
                ->bodyAttribute('text-center')
                ->searchable()
                ->sortable(),

            Column::add()
                ->title(__('Category'))
                ->field('category_name')
                ->headerAttribute('text-center')
                ->bodyAttribute('text-center'),

            Column::make(__('Quantity'), 'quantity')
                ->headerAttribute('text-center')
                ->bodyAttribute('text-center')
                ->sortable(),

            Column::make(__('Unit'), 'unit_name')
                ->headerAttribute('text-center')
                ->bodyAttribute('text-center'),

            Column::make(__('Selling Price'), 'selling_price')
                ->headerAttribute('align-middle text-center')
                ->bodyAttribute('align-middle text-center')
                ->sortable()
                ->searchable(),

            Column::action(__('Action'))
                ->headerAttribute('align-middle text-center', styleAttr: 'width: 150px;')
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
            Button::make('show', file_get_contents('assets/svg/eye.svg'))
                ->class('btn btn-outline-info btn-icon')
                ->tooltip(__('Show Product Details'))
                ->route('products.show', ['product' => $row])
                ->method('get'),

            Button::make('edit', file_get_contents('assets/svg/edit.svg'))
                ->class('btn btn-outline-warning btn-icon')
                ->route('products.edit', ['product' => $row])
                ->method('get')
                ->tooltip(__('Edit Product')),

            Button::add('delete')
                ->slot(file_get_contents('assets/svg/trash.svg'))
                ->class('btn btn-outline-danger btn-icon')
                ->tooltip(__('Delete Product'))
                ->route('products.destroy', ['product' => $row])
                ->method('delete'),
        ];
    }
}
