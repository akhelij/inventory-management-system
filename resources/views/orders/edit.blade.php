@extends('layouts.tabler')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="col-lg-6" x-data="productListEdit()">
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">
                                    {{ __('Products') }}
                                </h3>
                            </div>
                            <div class="ms-auto text-secondary">
                                {{ __('Search') }}
                                <div class="ms-2 d-inline-block">
                                    <input type="text" x-model="search" x-on:input.debounce.300ms="searchProducts()" class="form-control form-control-sm"
                                        aria-label="{{ __('Search') }}">
                                </div>
                            </div>
                        </div>

                        <div x-show="isLoading" class="text-center p-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>

                        <div class="table">
                            <table class="table table-bordered card-table table-vcenter text-nowrap datatable">
                                <thead class="thead-light">
                                <tr>
                                    <th scope="col" class="align-middle text-center">
                                        <a href="#" @click.prevent="sortBy('name')" role="button">
                                            {{ __('Name') }}
                                            <span x-show="sortField === 'name' && sortDirection === 'asc'">▲</span>
                                            <span x-show="sortField === 'name' && sortDirection === 'desc'">▼</span>
                                        </a>
                                    </th>
                                    <th scope="col" class="align-middle text-center">
                                        <a href="#" @click.prevent="sortBy('warehouse_id')" role="button">
                                            {{ __('Warehouse') }}
                                            <span x-show="sortField === 'warehouse_id' && sortDirection === 'asc'">▲</span>
                                            <span x-show="sortField === 'warehouse_id' && sortDirection === 'desc'">▼</span>
                                        </a>
                                    </th>
                                    <th scope="col" class="align-middle text-center">
                                        <a href="#" @click.prevent="sortBy('quantity')" role="button">
                                            {{ __('Quantity') }}
                                            <span x-show="sortField === 'quantity' && sortDirection === 'asc'">▲</span>
                                            <span x-show="sortField === 'quantity' && sortDirection === 'desc'">▼</span>
                                        </a>
                                    </th>
                                    <th scope="col" class="align-middle text-center">
                                        {{ __('Action') }}
                                    </th>
                                </tr>
                                </thead>
                                <tbody x-show="!isLoading">
                                    <template x-for="(product, index) in products" :key="product.id">
                                        <tr>
                                            <td class="text-truncate" style="max-width: 300px;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <img style="width: 50px; flex-shrink: 0;"
                                                        :src="product.product_image ? '/storage/' + product.product_image : '/assets/img/products/default.webp'"
                                                        :alt="product.name">
                                                    <span class="text-truncate" :title="product.name" x-text="product.name"></span>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center" x-text="product.warehouse?.name || '--'"></td>
                                            <td class="align-middle text-center" x-text="product.quantity"></td>
                                            <td class="align-middle text-center" style="width: 10%">
                                                <div class="d-flex">
                                                    <button
                                                        @click="addToOrder(product)"
                                                        :disabled="isAddingToOrder"
                                                        class="btn btn-icon btn-outline-primary" style="width: 20px">
                                                        <template x-if="!isAddingToOrder">
                                                            <x-icon.plus/>
                                                        </template>
                                                        <template x-if="isAddingToOrder">
                                                            <div class="spinner-border spinner-border-sm" role="status">
                                                                <span class="visually-hidden">Loading...</span>
                                                            </div>
                                                        </template>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="products.length === 0 && !isLoading">
                                        <td class="align-middle text-center" colspan="4">
                                            {{ __('No results found') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="card-footer d-flex align-items-center">
                            <ul class="pagination m-0 ms-auto">
                                <li class="page-item" :class="{ 'disabled': currentPage === 1 }">
                                    <a class="page-link" href="#" @click.prevent="prevPage">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 6l-6 6l6 6" /></svg>
                                        prev
                                    </a>
                                </li>
                                <template x-for="page in totalPages" :key="page">
                                    <li class="page-item" :class="{ 'active': page === currentPage }">
                                        <a class="page-link" href="#" @click.prevent="goToPage(page)" x-text="page"></a>
                                    </li>
                                </template>
                                <li class="page-item" :class="{ 'disabled': currentPage === totalPages }">
                                    <a class="page-link" href="#" @click.prevent="nextPage">
                                        next
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l6 6l-6 6" /></svg>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">
                                    {{ __('Edit Order') }}
                                </h3>
                            </div>
                            <div class="card-actions btn-actions">
                                <x-action.close route="{{ route('orders.index') }}"/>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row gx-3 mb-3">
                                <div class="col-md-4">
                                    <label for="purchase_date" class="small my-1">
                                        {{ __('Date') }}
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input name="purchase_date" id="purchase_date" type="date"
                                           class="form-control example-date-input @error('purchase_date') is-invalid @enderror"
                                           value="{{ old('purchase_date') ?? now()->format('Y-m-d') }}"
                                           required
                                    >

                                    @error('purchase_date')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="small mb-1" for="customer_id">
                                        {{ __('Customer') }}
                                        <span class="text-danger">*</span>
                                    </label>

                                    <select
                                        class="form-select form-control-solid @error('customer_id') is-invalid @enderror"
                                        id="customer_id" name="customer_id">
                                        <option selected="" disabled="">
                                            Select a customer:
                                        </option>
                                        @foreach ($customers as $customer)
                                            <option
                                                value="{{ $customer->id }}" @selected($order->customer_id == $customer->id)>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('customer_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="small mb-1" for="payment_type">
                                        {{ __('Payment') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('payment_type') is-invalid @enderror"
                                            id="payment_type" name="payment_type">
                                        <option value="HandCash" @selected($order->payment_type == "HandCash")>
                                            Cash
                                        </option>
                                        <option value="Cheque" @selected($order->payment_type == "Cheque")>Cheque
                                        </option>
                                    </select>

                                    @error('payment_type')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="table-responsive" x-data="orderItemsManager({{ $order->id }})">
                                <table class="table table-striped table-bordered align-middle">
                                    <thead class="thead-light">
                                    <tr>
                                        <th scope="col">
                                            {{ __('Product') }}
                                        </th>
                                        <th scope="col" class="text-center">{{ __('Quantity') }}</th>
                                        <th scope="col" class="text-center">{{ __('Price') }}</th>
                                        <th scope="col" class="text-center">{{ __('SubTotal') }}</th>
                                        <th scope="col" class="text-center">
                                            {{ __('Action') }}
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(item, index) in orderItems" :key="item.id">
                                            <tr>
                                                <td>
                                                    <span x-text="item.product.name"></span>
                                                    <span x-show="item.unitcost == 0" class="badge bg-success ms-1 small">Gift</span>
                                                </td>
                                                <td style="width: 120px;">
                                                    <div class="input-group" style="width:110px">
                                                        <input type="number" class="form-control" 
                                                            x-model="item.quantity" 
                                                            @input="updateItemQuantity(item.id, $event.target.value)" 
                                                            min="1" required/>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="input-group" style="width:110px">
                                                        <input type="number" class="form-control" 
                                                            x-model="item.unitcost" 
                                                            @input="updateItemPrice(item.id, $event.target.value)" 
                                                            min="0" required/>
                                                    </div>
                                                </td>
                                                <td class="text-center" x-text="formatCurrency(item.total)"></td>
                                                <td class="text-center">
                                                    <button 
                                                        type="button" 
                                                        @click="removeOrderItem(item.id)" 
                                                        class="btn btn-icon btn-outline-danger delete-item-btn">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="24"
                                                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                            <path d="M4 7l16 0"/>
                                                            <path d="M10 11l0 6"/>
                                                            <path d="M14 11l0 6"/>
                                                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/>
                                                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/>
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="orderItems.length === 0">
                                            <td colspan="5" class="text-center">
                                                {{ __('No items in this order') }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end">Subtotal</td>
                                            <td class="text-center" x-text="formatCurrency(getSubTotal())"></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end">Total</td>
                                            <td class="text-center" x-text="formatCurrency(getTotal())"></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                        <div class="card-footer text-end">
                            <form action="{{ route('orders.update', $order->id) }}" method="POST" x-data="{
                                submitForm(e) {
                                    // Get the order items manager component
                                    const orderItemsComponent = document.querySelector('[x-data=\"orderItemsManager(' + {{ $order->id }} + ')\"]').__x.$data;
                                    
                                    if (orderItemsComponent.orderItems.length === 0) {
                                        window.showErrorToast('Cannot update an order with no items');
                                        e.preventDefault();
                                        return false;
                                    }
                                    
                                    // Form will submit normally
                                    return true;
                                }
                            }" @submit.prevent="submitForm($event)">
                                @method('PUT')
                                @csrf
                                <button 
                                    type="submit" 
                                    class="btn btn-success add-list mx-1"
                                    :disabled="$root.closest('.container-xl').querySelector('[x-data^=\"orderItemsManager\"]').__x.$data.orderItems.length === 0">
                                    {{ __('Done') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@pushonce('page-scripts')
    <script src="{{ asset('js/toast.js') }}"></script>
    <script>
        function productListEdit() {
            return {
                products: [],
                search: '',
                sortField: 'id',
                sortDirection: 'desc',
                isLoading: true,
                isAddingToOrder: false,
                currentPage: 1,
                perPage: 15,
                totalPages: 1,
                orderId: {{ $order->id }},
                
                init() {
                    this.fetchProducts();
                },
                
                fetchProducts() {
                    this.isLoading = true;
                    const params = new URLSearchParams({
                        page: this.currentPage,
                        per_page: this.perPage,
                        search: this.search,
                        sort_field: this.sortField,
                        sort_direction: this.sortDirection
                    });
                    
                    fetch(`/api/products?${params}`)
                        .then(response => response.json())
                        .then(data => {
                            this.products = data.data;
                            this.totalPages = data.last_page;
                            this.currentPage = data.current_page;
                            this.isLoading = false;
                        })
                        .catch(error => {
                            console.error('Error fetching products:', error);
                            this.isLoading = false;
                            window.showErrorToast('Error loading products');
                        });
                },
                
                searchProducts() {
                    this.currentPage = 1;
                    this.fetchProducts();
                },
                
                sortBy(field) {
                    if (this.sortField === field) {
                        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.sortField = field;
                        this.sortDirection = 'asc';
                    }
                    this.fetchProducts();
                },
                
                prevPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                        this.fetchProducts();
                    }
                },
                
                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.currentPage++;
                        this.fetchProducts();
                    }
                },
                
                goToPage(page) {
                    this.currentPage = page;
                    this.fetchProducts();
                },
                
                addToOrder(product) {
                    this.isAddingToOrder = true;
                    
                    fetch(`/api/orders/${this.orderId}/items`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            product_id: product.id,
                            quantity: 1,
                            unitcost: product.selling_price
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            if (response.status === 400) {
                                return response.json().then(data => {
                                    window.showInfoToast(data.message || 'This item is already in the order');
                                    throw new Error('Item already in order');
                                });
                            }
                            throw new Error(`Error ${response.status}: ${response.statusText}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        document.dispatchEvent(new CustomEvent('order-items-updated'));
                    })
                    .catch(error => {
                        if (error.message !== 'Item already in order') {
                            console.error('Error adding to order:', error);
                            window.showErrorToast('Error adding item to order');
                        }
                    })
                    .finally(() => {
                        this.isAddingToOrder = false;
                    });
                }
            };
        }
        
        function orderItemsManager(orderId) {
            return {
                orderItems: [],
                isLoading: true,
                orderId: orderId,
                
                init() {
                    this.fetchOrderItems();
                    
                    document.addEventListener('order-items-updated', () => {
                        this.fetchOrderItems();
                    });
                },
                
                fetchOrderItems() {
                    this.isLoading = true;
                    fetch(`/api/orders/${this.orderId}/items`)
                        .then(response => response.json())
                        .then(data => {
                            this.orderItems = data;
                            this.isLoading = false;
                        })
                        .catch(error => {
                            console.error('Error fetching order items:', error);
                            this.isLoading = false;
                            window.showErrorToast('Error loading order items');
                        });
                },
                
                updateItemQuantity(itemId, quantity) {
                    console.log(`Updating quantity for item ${itemId} to ${quantity}`);
                    quantity = parseInt(quantity);
                    if (quantity < 1) quantity = 1;
                    
                    fetch(`/api/orders/${this.orderId}/items/${itemId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ quantity })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(data => {
                                window.showErrorToast(data.message || 'Failed to update item');
                                throw new Error(data.message || 'Error updating item');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            this.fetchOrderItems();
                        } else {
                            window.showErrorToast(data.message || 'Failed to update item');
                        }
                    })
                    .catch(error => {
                        console.error('Error updating item:', error);
                    });
                },
                
                updateItemPrice(itemId, price) {
                    console.log(`Updating price for item ${itemId} to ${price}`);
                    price = parseFloat(price);
                    if (price < 0) price = 0;
                    
                    fetch(`/api/orders/${this.orderId}/items/${itemId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ unitcost: price })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            this.fetchOrderItems();
                        } else {
                            window.showErrorToast(data.message || 'Failed to update item');
                        }
                    })
                    .catch(error => {
                        console.error('Error updating item:', error);
                        window.showErrorToast('Error updating item');
                    });
                },
                
                removeOrderItem(itemId) {
                    console.log('Removing item with ID:', itemId);
                    
                    fetch(`/api/orders/${this.orderId}/items/${itemId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            this.fetchOrderItems();
                        } else {
                            window.showErrorToast(data.message || 'Failed to remove item');
                        }
                    })
                    .catch(error => {
                        console.error('Error removing item:', error);
                        window.showErrorToast('Error removing item from order');
                    });
                },
                
                getSubTotal() {
                    return this.orderItems.reduce((total, item) => total + parseFloat(item.total), 0);
                },
                
                getTotal() {
                    return this.getSubTotal();
                },
                
                formatCurrency(value) {
                    return parseFloat(value).toFixed(2);
                }
            };
        }
    </script>
@endpushonce
