@extends('layouts.tabler')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="row" style="min-height: calc(100vh - 160px);">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div x-data="productListEdit()" class="col-lg-6 d-flex flex-column">
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

                        <div class="table-responsive flex-grow-1" style="overflow-y: auto;">
                            <table class="table table-bordered card-table table-vcenter datatable m-0">
                                <thead class="thead-light">
                                <tr>
                                    <th scope="col" class="align-middle text-center small" style="width: 50%">
                                        <a href="#" @click.prevent="sortBy('name')" class="small" role="button">
                                            {{ __('Name') }}
                                            <span x-show="sortField === 'name' && sortDirection === 'asc'">▲</span>
                                            <span x-show="sortField === 'name' && sortDirection === 'desc'">▼</span>
                                        </a>
                                    </th>
                                    <th scope="col" class="align-middle text-center small" style="width: 20%">
                                        <a href="#" @click.prevent="sortBy('warehouse_id')" class="small" role="button">
                                            {{ __('Warehouse') }}
                                            <span x-show="sortField === 'warehouse_id' && sortDirection === 'asc'">▲</span>
                                            <span x-show="sortField === 'warehouse_id' && sortDirection === 'desc'">▼</span>
                                        </a>
                                    </th>
                                    <th scope="col" class="align-middle text-center small" style="width: 15%">
                                        <a href="#" @click.prevent="sortBy('quantity')" class="small" role="button">
                                            {{ __('Quantity') }}
                                            <span x-show="sortField === 'quantity' && sortDirection === 'asc'">▲</span>
                                            <span x-show="sortField === 'quantity' && sortDirection === 'desc'">▼</span>
                                        </a>
                                    </th>
                                    <th scope="col" class="align-middle text-center small" style="width: 15%">
                                        {{ __('Action') }}
                                    </th>
                                </tr>
                                </thead>
                                <tbody x-show="!isLoading">
                                    <template x-for="(product, index) in products" :key="product.id">
                                        <tr>
                                            <td class="text-truncate" style="max-width: 200px;">
                                                <div class="d-flex align-items-center gap-1">
                                                    <img style="width: 32px; height: 32px; object-fit: contain; flex-shrink: 0;"
                                                        :src="product.product_image ? '/storage/' + product.product_image : '/assets/img/products/default.webp'"
                                                        >
                                                    <span class="text-truncate fs-sm small" x-text="product.name"></span>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center fs-sm small" x-text="product.warehouse?.name || '--'"></td>
                                            <td class="align-middle text-center fs-sm small" x-text="product.quantity"></td>
                                            <td class="align-middle text-center small" style="width: 80px">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button
                                                        @click="addToOrder(product)"
                                                        :disabled="isAddingToOrder"
                                                        class="btn btn-icon btn-sm btn-outline-primary p-1" 
                                                        data-bs-toggle="tooltip">
                                                        <template x-if="!isAddingToOrder">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                                <path d="M12 5l0 14" />
                                                                <path d="M5 12l14 0" />
                                                            </svg>
                                                        </template>
                                                        <template x-if="isAddingToOrder">
                                                            <div class="spinner-border spinner-border-sm" style="width: 14px; height: 14px;">
                                                                <span class="visually-hidden">Loading...</span>
                                                            </div>
                                                        </template>
                                                    </button>
                                                    @if(auth()->user()->hasRole('admin'))
                                                    <button
                                                        @click="addFreeItem(product)"
                                                        :disabled="isAddingToOrder"
                                                        class="btn btn-icon btn-sm btn-outline-success p-1"
                                                        title="Add as free item"
                                                        data-bs-toggle="tooltip">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                            <path d="M12 8l0 13" />
                                                            <rect x="3" y="8" width="18" height="4" rx="1" />
                                                            <path d="M19 12v7a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-7" />
                                                            <path d="M7.5 8a2.5 2.5 0 1 0 0 -5a4.8 8 0 0 1 4.5 5a4.8 8 0 0 1 4.5 -5a2.5 2.5 0 1 0 0 5" />
                                                        </svg>
                                                    </button>
                                                    @endif
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
                            <ul class="pagination pagination-sm m-0 ms-auto">
                                <li class="page-item" :class="{ 'disabled': currentPage === 1 }">
                                    <a class="page-link" href="#" @click.prevent="prevPage">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 6l-6 6l6 6" /></svg>
                                    </a>
                                </li>
                                <!-- Display if totalPages is less than or equal to 5 -->
                                <template x-for="page in pageNumbers" :key="page"  x-if="totalPages <= 10">
                                    <li class="page-item" :class="{ 'active': page === currentPage }">
                                        <a class="page-link" href="#" @click.prevent="goToPage(page)" x-text="page"></a>
                                    </li>
                                </template>
                                <template x-if="totalPages > 10">
                                    <!-- Show first page -->
                                    <li class="page-item" :class="{ 'active': 1 === currentPage }">
                                        <a class="page-link" href="#" @click.prevent="goToPage(1)">1</a>
                                    </li>
                                    <!-- Show dots if needed -->
                                    <li class="page-item disabled" x-show="currentPage > 3">
                                        <span class="page-link">...</span>
                                    </li>
                                    <!-- Show page before current if needed -->
                                    <li class="page-item" x-show="currentPage > 2" :class="{ 'active': currentPage - 1 === currentPage }">
                                        <a class="page-link" href="#" @click.prevent="goToPage(currentPage - 1)" x-text="currentPage - 1"></a>
                                    </li>
                                    <!-- Show current page if not first or last -->
                                    <li class="page-item active" x-show="currentPage !== 1 && currentPage !== totalPages">
                                        <a class="page-link" href="#" x-text="currentPage"></a>
                                    </li>
                                    <!-- Show page after current if needed -->
                                    <li class="page-item" x-show="currentPage < totalPages - 1" :class="{ 'active': currentPage + 1 === currentPage }">
                                        <a class="page-link" href="#" @click.prevent="goToPage(currentPage + 1)" x-text="currentPage + 1"></a>
                                    </li>
                                    <!-- Show dots if needed -->
                                    <li class="page-item disabled" x-show="currentPage < totalPages - 2">
                                        <span class="page-link">...</span>
                                    </li>
                                    <!-- Show last page -->
                                    <li class="page-item" :class="{ 'active': totalPages === currentPage }">
                                        <a class="page-link" href="#" @click.prevent="goToPage(totalPages)" x-text="totalPages"></a>
                                    </li>
                                </template>
                                <li class="page-item" :class="{ 'disabled': currentPage === totalPages }">
                                    <a class="page-link" href="#" @click.prevent="nextPage">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l6 6l-6 6" /></svg>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 d-flex flex-column">
                    <div class="card flex-grow-1 d-flex flex-column">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">
                                    {{ __('Edit Order') }}
                                    <span 
                                        class="badge bg-primary text-white ms-2" 
                                        x-text="getTotalQuantity()"
                                        x-show="orderItems.length > 0">
                                    </span>
                                </h3>
                            </div>
                            <div class="card-actions btn-actions">
                                <x-action.close route="{{ route('orders.index') }}"/>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column flex-grow-1" style="overflow-y: auto;">
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
                                        <option value="Exchange" @selected($order->payment_type == "Exchange")>Lettre de change
                                        </option>
                                    </select>

                                    @error('payment_type')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                
                                @if(auth()->user()->hasRole('admin'))
                                <div class="col-md-4">
                                    <label class="small mb-1" for="author_id">
                                        {{ __('Author') }}
                                    </label>

                                    <select
                                        class="form-select form-control-solid @error('author_id') is-invalid @enderror"
                                        id="author_id" name="author_id">
                                        <option selected="" disabled="">
                                            Select a user:
                                        </option>
                                        @if(isset($users))
                                            @foreach ($users as $user)
                                                <option
                                                    value="{{ $user->id }}" @selected($order->user_id == $user->id)>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>

                                    @error('author_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                @endif
                                
                                <div class="col-md-4">
                                    <label class="small mb-1" for="tagged_user_id">
                                        {{ __('Tag') }}
                                    </label>

                                    <select
                                        class="form-select form-control-solid @error('tagged_user_id') is-invalid @enderror"
                                        id="tagged_user_id" name="tagged_user_id">
                                        <option selected="" disabled="">
                                            Select a user:
                                        </option>
                                        @if(isset($users))
                                            @foreach ($users as $user)
                                                <option
                                                    value="{{ $user->id }}" @selected($order->tagged_user_id == $user->id)>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>

                                    @error('tagged_user_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="table-responsive flex-grow-1" x-data="orderItemsManager({{ $order->id }})">
                                <table class="table table-striped table-bordered align-middle">
                                    <thead class="thead-light">
                                    <tr>
                                        <th scope="col" class="small">
                                            {{ __('Product') }}
                                        </th>
                                        <th scope="col" class="text-center small">{{ __('Quantity') }}</th>
                                        <th scope="col" class="text-center small">{{ __('Price') }}</th>
                                        <th scope="col" class="text-center small">{{ __('SubTotal') }}</th>
                                        <th scope="col" class="text-center small">
                                            {{ __('Action') }}
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(item, index) in orderItems" :key="item.id">
                                            <tr>
                                                <td>
                                                    <span class="small" x-text="item.product.name"></span>
                                                    <span x-show="item.unitcost == 0" class="badge bg-success ms-1 small">Gift</span>
                                                </td>
                                                <td style="width: 120px;">
                                                    <div class="input-group" style="width:110px">
                                                        <input type="number" class="form-control form-control-sm" 
                                                            x-model="item.quantity" 
                                                            @input="updateItemQuantity(item.id, $event.target.value)" 
                                                            min="1" required/>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="input-group" style="width:110px">
                                                        <input type="number" class="form-control form-control-sm" 
                                                            x-model="item.unitcost" 
                                                            @input="updateItemPrice(item.id, $event.target.value)" 
                                                            min="0" required/>
                                                    </div>
                                                </td>
                                                <td class="text-center small" x-text="formatCurrency(item.total)"></td>
                                                <td class="text-center">
                                                    <button 
                                                        type="button" 
                                                        @click="removeOrderItem(item.id)" 
                                                        class="btn btn-icon btn-sm btn-outline-danger p-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="16"
                                                            height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
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
                                            <td colspan="4" class="text-end small fw-bold">
                                                Total Product
                                            </td>
                                            <td class="text-center small fw-bold" x-text="getTotalQuantity()"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end small fw-bold">Subtotal</td>
                                            <td class="text-center small fw-bold" x-text="formatCurrency(getSubTotal())"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end small fw-bold">Total</td>
                                            <td class="text-center small fw-bold" x-text="formatCurrency(getTotal())"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                        <div class="card-footer text-end">
                            <a href="{{ route('orders.index') }}" class="btn btn-success mx-1">
                                <i class="fas fa-check me-1"></i> {{ __('Back to Orders') }}
                            </a>
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
                pageNumbers: [],
                currentPage: 1,
                totalPages: 1,
                perPage: 15,
                isLoading: true,
                isAddingToOrder: false,
                orderId: {{ $order->id }},
                
                init() {
                    this.fetchProducts();
                    this.initTooltips();
                },
                
                initTooltips() {
                    // Initialize tooltips when DOM is updated
                    this.$nextTick(() => {
                        if (typeof bootstrap !== 'undefined') {
                            const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                            tooltips.forEach(tooltip => {
                                new bootstrap.Tooltip(tooltip);
                            });
                        }
                    });
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
                            this.pageNumbers = Array.from({length: this.totalPages}, (_, i) => i + 1);
                            this.isLoading = false;
                            this.$nextTick(() => this.initTooltips());
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
                    
                    // Instead of directly querying the DOM, we'll use a more direct approach
                    // Create form data for the new item
                    const formData = new FormData();
                    formData.append('product_id', product.id);
                    formData.append('quantity', 1);
                    formData.append('unitcost', product.selling_price);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                    
                    // Send a direct API request to add the item
                    fetch(`/api/orders/${this.orderId}/items`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            if (response.status === 422) {
                                // Validation error - likely item already exists
                                return response.json().then(data => {
                                    window.showInfoToast(data.message || 'This item is already in the order');
                                    throw new Error('Item already exists');
                                });
                            }
                            throw new Error(`Error ${response.status}: ${response.statusText}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        window.showSuccessToast('Item added successfully');
                        
                        // Trigger an event to refresh the order items
                        // This will be handled by orderItemsManager.init() 
                        // which sets up listeners for this event
                        document.dispatchEvent(new CustomEvent('order-items-updated'));
                    })
                    .catch(error => {
                        if (error.message !== 'Item already exists') {
                            console.error('Error adding to order:', error);
                            window.showErrorToast('Error adding item to order');
                        }
                    })
                    .finally(() => {
                        this.isAddingToOrder = false;
                    });
                },
                
                addFreeItem(product) {
                    this.isAddingToOrder = true;
                    
                    // Create form data for the new free item
                    const formData = new FormData();
                    formData.append('product_id', product.id);
                    formData.append('quantity', 1);
                    formData.append('unitcost', 0); // Free item
                    formData.append('is_free', 1);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                    
                    // Send a direct API request to add the free item
                    fetch(`/api/orders/${this.orderId}/items`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            if (response.status === 422) {
                                // Validation error - likely item already exists
                                return response.json().then(data => {
                                    window.showInfoToast(data.message || 'This item is already in the order');
                                    throw new Error('Item already exists');
                                });
                            }
                            throw new Error(`Error ${response.status}: ${response.statusText}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        window.showSuccessToast('Free item added successfully');
                        
                        // Trigger an event to refresh the order items
                        document.dispatchEvent(new CustomEvent('order-items-updated'));
                    })
                    .catch(error => {
                        if (error.message !== 'Item already exists') {
                            console.error('Error adding free item to order:', error);
                            window.showErrorToast('Error adding free item to order');
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
                addTaxPercentage: 0,
                
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
                    return this.orderItems.reduce((total, item) => {
                        return total + (parseFloat(item.unitcost || 0) * parseInt(item.quantity || 0));
                    }, 0);
                },
                
                getTotal() {
                    const subtotal = this.getSubTotal();
                    const taxAmount = subtotal * (this.addTaxPercentage / 100);
                    return subtotal + taxAmount;
                },
                
                getTaxAmount() {
                    const subtotal = this.getSubTotal();
                    return subtotal * (this.addTaxPercentage / 100);
                },
                
                formatCurrency(value) {
                    return parseFloat(value).toFixed(2);
                },
                
                getTotalQuantity() {
                    return this.orderItems.reduce((total, item) => total + parseInt(item.quantity || 0), 0);
                }
            };
        }
    </script>
@endpushonce
