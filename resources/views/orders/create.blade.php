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
                <div x-data="productList()" class="col-lg-6">
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
                                                        @click="$dispatch('add-to-cart', { id: product.id, name: product.name, price: product.selling_price })"
                                                        class="btn btn-icon btn-outline-primary" style="width: 20px">
                                                        <x-icon.plus/>
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
                                        <!-- Download SVG icon from http://tabler-icons.io/i/chevron-left -->
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
                                        <!-- Download SVG icon from http://tabler-icons.io/i/chevron-right -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l6 6l-6 6" /></svg>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <form action="{{ route('orders.store') }}" method="POST" x-data="cartComponent()" @submit.prevent="submitOrder">
                            @csrf
                            <div class="card-header">
                                <div>
                                    <h3 class="card-title">
                                        {{ __('New Order') }}
                                    </h3>
                                </div>
                                <div class="card-actions btn-actions">
                                    <x-action.close route="{{ route('orders.index') }}"/>
                                </div>
                            </div>
                            <div class="card-body">
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
                                        <label class="small mb-1" for="customer_id">
                                            {{ __('Customer') }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            class="form-select form-control-solid @error('customer_id') is-invalid @enderror"
                                            id="customer_id" name="customer_id">
                                            @foreach ($customers as $customer)
                                                <option
                                                    value="{{ $customer->id }}" @selected( old('customer_id') == $customer->id)>
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

                                <div class="table-responsive">
                                    <input type="hidden" name="cart_data" x-bind:value="JSON.stringify(cart)">
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
                                            <template x-for="(item, index) in cart" :key="item.rowId">
                                                <tr>
                                                    <td x-text="item.name"></td>
                                                    <td style="width: 120px;">
                                                        <div class="input-group" style="width:110px">
                                                            <input type="number" class="form-control" 
                                                                x-model="item.qty" 
                                                                @input="updateQuantity(index, $event.target.value)" 
                                                                min="1" required/>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="input-group" style="width:110px">
                                                            <input type="number" class="form-control" 
                                                                x-model="item.price" 
                                                                @input="updatePrice(index, $event.target.value)" 
                                                                :min="item.basePrice" required/>
                                                        </div>
                                                    </td>
                                                    <td class="text-center" x-text="formatCurrency(item.subtotal)"></td>
                                                    <td class="text-center">
                                                        <button type="button" @click="removeItem(index)" class="btn btn-icon btn-outline-danger">
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
                                            <tr x-show="cart.length === 0">
                                                <td colspan="5" class="text-center">
                                                    {{ __('Add Products') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-end">
                                                    Total Product
                                                </td>
                                                <td class="text-center" x-text="getTotalQuantity()"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-end">Subtotal</td>
                                                <td class="text-center" x-text="formatCurrency(getSubTotal())"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-end">Total</td>
                                                <td class="text-center" x-text="formatCurrency(getTotal())"></td>
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
        function productList() {
            return {
                products: [],
                search: '',
                sortField: 'id',
                sortDirection: 'desc',
                isLoading: true,
                currentPage: 1,
                perPage: 15,
                totalPages: 1,
                
                init() {
                    this.fetchProducts();
                    
                    // Listen for add-to-cart events from other components
                    window.addEventListener('add-to-cart', (event) => {
                        this.addToCart(event.detail);
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
                            this.isLoading = false;
                        })
                        .catch(error => {
                            console.error('Error fetching products:', error);
                            this.isLoading = false;
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
                
                addToCart(product) {
                    // Dispatch event to be caught by cart component
                    window.dispatchEvent(new CustomEvent('cart-add-item', { 
                        detail: {
                            id: product.id,
                            name: product.name,
                            price: product.price,
                            basePrice: product.price
                        } 
                    }));
                }
            };
        }
        
        function cartComponent() {
            return {
                cart: [],
                
                init() {
                    // Retrieve cart from localStorage if available
                    const savedCart = localStorage.getItem('order_cart');
                    if (savedCart) {
                        this.cart = JSON.parse(savedCart);
                    }
                    
                    // Listen for add-to-cart events
                    window.addEventListener('cart-add-item', (event) => {
                        this.addItem(event.detail);
                    });
                },
                
                addItem(product) {
                    // Generate a unique row ID
                    const rowId = 'row_' + product.id + '_' + Date.now();
                    
                    // Check if product already exists in cart
                    const existingItemIndex = this.cart.findIndex(item => item.id === product.id);
                    
                    if (existingItemIndex !== -1) {
                        // Update existing item
                        this.cart[existingItemIndex].qty++;
                        this.updateSubtotal(existingItemIndex);
                    } else {
                        // Add new item
                        this.cart.push({
                            rowId: rowId,
                            id: product.id,
                            name: product.name,
                            qty: 1,
                            price: parseFloat(product.price),
                            basePrice: parseFloat(product.price),
                            subtotal: parseFloat(product.price)
                        });
                    }
                    
                    this.saveCart();
                },
                
                updateQuantity(index, quantity) {
                    quantity = parseInt(quantity);
                    if (quantity < 1) quantity = 1;
                    
                    this.cart[index].qty = quantity;
                    this.updateSubtotal(index);
                },
                
                updatePrice(index, price) {
                    price = parseFloat(price);
                    const basePrice = this.cart[index].basePrice;
                    
                    if (price < basePrice) {
                        price = basePrice;
                    }
                    
                    this.cart[index].price = price;
                    this.updateSubtotal(index);
                },
                
                updateSubtotal(index) {
                    const item = this.cart[index];
                    item.subtotal = item.price * item.qty;
                    this.saveCart();
                },
                
                removeItem(index) {
                    this.cart.splice(index, 1);
                    this.saveCart();
                },
                
                getTotalQuantity() {
                    return this.cart.reduce((total, item) => total + item.qty, 0);
                },
                
                getSubTotal() {
                    return this.cart.reduce((total, item) => total + item.subtotal, 0);
                },
                
                getTotal() {
                    return this.getSubTotal();
                },
                
                formatCurrency(value) {
                    return value.toFixed(2);
                },
                
                saveCart() {
                    localStorage.setItem('order_cart', JSON.stringify(this.cart));
                },
                
                clearCart() {
                    this.cart = [];
                    localStorage.removeItem('order_cart');
                },
                
                submitOrder(e) {
                    if (this.cart.length === 0) {
                        alert('Cannot create an order with an empty cart');
                        e.preventDefault();
                        return false;
                    }
                    
                    // Form will submit normally since we're just preventing when cart is empty
                    e.target.submit();
                }
            };
        }
    </script>
@endpushonce
