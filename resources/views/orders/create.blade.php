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
                <div x-data="productList()" class="col-lg-6 d-flex flex-column">
                    <div class="card flex-grow-1 d-flex flex-column">
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
                                                        @click="addToCart(product)"
                                                        :disabled="isAddingToCart"
                                                        class="btn btn-icon btn-sm btn-outline-primary p-1" 
                                                        data-bs-toggle="tooltip">
                                                        <template x-if="!isAddingToCart">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                                <path d="M12 5l0 14" />
                                                                <path d="M5 12l14 0" />
                                                            </svg>
                                                        </template>
                                                        <template x-if="isAddingToCart">
                                                            <div class="spinner-border spinner-border-sm" style="width: 14px; height: 14px;">
                                                                <span class="visually-hidden">Loading...</span>
                                                            </div>
                                                        </template>
                                                    </button>
                                                    @if(auth()->user()->hasRole('admin'))
                                                    <button
                                                        @click="addFreeItem(product)"
                                                        :disabled="isAddingToCart"
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
                        <form action="{{ route('orders.store') }}" method="POST" x-data="cartComponent()" @submit.prevent="submitOrder">
                            @csrf
                            <div class="card-header">
                                <div>
                                    <h3 class="card-title">
                                        {{ __('New Order') }}
                                        <span 
                                            class="badge bg-primary text-white ms-2" 
                                            x-text="getTotalQuantity()" 
                                            x-show="cart.length > 0">
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
                                        <label class="small mb-1" for="payment_type">
                                            {{ __('Payment') }}
                                        </label>

                                        <select class="form-control @error('payment_type') is-invalid @enderror" id="payment_type" name="payment_type">
                                            <option value="HandCash">Cash</option>
                                            <option value="Cheque">Cheque</option>
                                            <option value="Exchange">Lettre de change</option>
                                        </select>
                                    </div>

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
                                        <x-tom-select
                                            name="customer_id"
                                            label="{{ __('Customer') }}"
                                            :data="$customers"
                                            :value="old('customer_id')"
                                            :required="true"
                                            placeholder="{{ __('Search customer...') }}"
                                        />
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
                                            @foreach ($users as $user)
                                                <option
                                                    value="{{ $user->id }}" @selected( old('author_id') == $user->id)>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('author_id')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                    @endif

                                    <div class="col-md-4" x-data="{ showInstallments: false }">
                                        <label class="small mb-1">{{ __('Payment Mode') }}</label>
                                        <select name="payment_mode" class="form-select" @change="showInstallments = $event.target.value === 'installment'">
                                            <option value="full">{{ __('Full Payment') }}</option>
                                            <option value="installment">{{ __('Installments') }}</option>
                                        </select>

                                        <template x-if="showInstallments">
                                            <div class="row mt-2">
                                                <div class="col-6">
                                                    <label class="small mb-1">{{ __('Number of installments') }}</label>
                                                    <input type="number" name="installment_count" class="form-control form-control-sm" value="4" min="2" max="24">
                                                </div>
                                                <div class="col-6">
                                                    <label class="small mb-1">{{ __('Period (days)') }}</label>
                                                    <input type="number" name="installment_period_days" class="form-control form-control-sm" value="30" min="7" max="365">
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    
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
                                            @foreach ($users as $user)
                                                <option
                                                    value="{{ $user->id }}" @selected( old('tagged_user_id') == $user->id)>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('tagged_user_id')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="table-responsive flex-grow-1">
                                    <input type="hidden" name="cart_data" x-bind:value="JSON.stringify(cart)">
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
                                            <template x-for="(item, index) in cart" :key="item.uuid">
                                                <tr>
                                                    <td>
                                                        <span class="small" x-text="item.name"></span>
                                                        <span x-show="item.is_free" class="badge bg-success ms-1 small">Gift</span>
                                                    </td>
                                                    <td style="width: 120px;">
                                                        <div class="input-group" style="width:110px">
                                                            <input type="number" class="form-control form-control-sm" 
                                                                x-model="item.qty" 
                                                                @input="updateCartItem(item.uuid, 'quantity', $event.target.value)" 
                                                                :max="item.max_qty"
                                                                min="1" required/>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="input-group" style="width:110px">
                                                            <input type="number" class="form-control form-control-sm" 
                                                                x-model="item.price" 
                                                                @change="updateCartItem(item.uuid, 'price', $event.target.value)" 
                                                                :min="item.basePrice" required/>
                                                        </div>
                                                    </td>
                                                    <td class="text-center small" x-text="formatCurrency(item.subtotal)"></td>
                                                    <td class="text-center">
                                                        <button 
                                                            type="button" 
                                                            @click="removeCartItem(item.uuid)" 
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
                                            <tr x-show="cart.length === 0">
                                                <td colspan="5" class="text-center">
                                                    {{ __('Add Products') }}
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
                                <button 
                                    type="submit" 
                                    class="btn btn-success add-list mx-1"
                                    :disabled="cart.length === 0">
                                    {{ __('Create Order') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@pushonce('page-scripts')
    <script>
        // Check if Bootstrap is loaded
        if (typeof bootstrap === 'undefined') {
            console.warn('Bootstrap JS is not loaded. Using fallback toast implementation.');
        }
        
        function productList() {
            return {
                products: [],
                search: '',
                sortField: 'id',
                sortDirection: 'desc',
                isLoading: true,
                isAddingToCart: false,
                currentPage: 1,
                perPage: 25,
                totalPages: 1,
                pageNumbers: [],

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
                
                get pageNumbers() {
                    // Dynamically generate page numbers based on totalPages
                    return Array.from({length: this.totalPages}, (_, i) => i + 1);
                },
                
                goToPage(page) {
                    if (page < 1 || page > this.totalPages) return;
                    this.currentPage = page;
                    this.fetchProducts();
                },
                
                addToCart(product) {
                    this.isAddingToCart = true;
                    
                    const productData = {
                        id: product.id,
                        name: product.name,
                        price: product.selling_price
                    };
                    
                    fetch('/cart', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(productData)
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(data => {
                                if (response.status === 400) {
                                    if (data.message.includes('already in your cart')) {
                                        window.showErrorToast('This product already exists in your order');
                                        throw new Error('Item already in cart');
                                    } else if (data.message.includes('exceeds available stock')) {
                                        window.showErrorToast(data.message);
                                        throw new Error('Stock limit exceeded');
                                    }
                                }
                                throw new Error(`Error ${response.status}: ${data.message || response.statusText}`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        document.dispatchEvent(new CustomEvent('cart-updated', { detail: data.cart }));
                    })
                    .catch(error => {
                        if (error.message !== 'Item already in cart' && error.message !== 'Stock limit exceeded') {
                            console.error('Error adding to cart:', error);
                        }
                    })
                    .finally(() => {
                        this.isAddingToCart = false;
                    });
                },
                
                addFreeItem(product) {
                    this.isAddingToCart = true;
                    
                    const productData = {
                        id: product.id,
                        name: product.name,
                        price: product.selling_price,
                        is_free: true
                    };
                    
                    fetch('/cart', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(productData)
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(data => {
                                if (response.status === 400) {
                                    if (data.message.includes('already in your cart')) {
                                        window.showErrorToast('This product already exists in your order');
                                        throw new Error('Item already in cart');
                                    } else if (data.message.includes('exceeds available stock')) {
                                        window.showErrorToast(data.message);
                                        throw new Error('Stock limit exceeded');
                                    }
                                }
                                throw new Error(`Error ${response.status}: ${data.message || response.statusText}`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        document.dispatchEvent(new CustomEvent('cart-updated', { detail: data.cart }));
                    })
                    .catch(error => {
                        if (error.message !== 'Item already in cart' && error.message !== 'Stock limit exceeded') {
                            console.error('Error adding free item:', error);
                        }
                    })
                    .finally(() => {
                        this.isAddingToCart = false;
                    });
                }
            };
        }     

        function cartComponent() {
            return {
                cart: [],
                isLoading: true,
                
                init() {
                    this.fetchCart();
                    document.addEventListener('cart-updated', event => {
                        this.cart = event.detail;
                    });
                },
                
                fetchCart() {
                    this.isLoading = true;
                    fetch('/cart')
                        .then(response => response.json())
                        .then(data => {
                            this.cart = data;
                            this.isLoading = false;
                        })
                        .catch(error => {
                            console.error('Error fetching cart:', error);
                            this.isLoading = false;
                            window.showErrorToast('Error loading cart');
                        });
                },
                
                updateCartItem(uuid, field, value) {
                    const updateData = {};
                    
                    if (field === 'quantity') {
                        updateData.quantity = parseInt(value);
                    } else if (field === 'price') {
                        updateData.price = parseFloat(value);
                    }
                    
                    fetch(`/cart/${uuid}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(updateData)
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(data => {
                                if (response.status === 400) {
                                    console.error('Validation error:', data.message);
                                    window.showErrorToast(data.message);
                                }
                                throw new Error(data.message || 'Error updating cart');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            this.cart = data.cart;
                        } else {
                            console.error('Error from server:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error updating cart:', error);
                    });
                },
                
                removeCartItem(uuid) {
                    console.log('Deleting item with UUID:', uuid);
                    
                    fetch(`/cart/${uuid}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            this.cart = data.cart;
                        } else {
                            console.error('Failed to remove item:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error removing cart item:', error);
                    });
                },
                
                getTotalQuantity() {
                    return this.cart.reduce((total, item) => total + parseInt(item.qty), 0);
                },
                
                getSubTotal() {
                    return this.cart.reduce((total, item) => total + parseFloat(item.subtotal), 0);
                },
                
                getTotal() {
                    return this.getSubTotal();
                },
                
                formatCurrency(value) {
                    return parseFloat(value).toFixed(2);
                },
                
                submitOrder(e) {
                    if (this.cart.length === 0) {
                        window.showErrorToast('Cannot create an order with an empty cart');
                        e.preventDefault();
                        return false;
                    }
                    e.target.submit();
                }
            };
        }
    </script>
@endpushonce
