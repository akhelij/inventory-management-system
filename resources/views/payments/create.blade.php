@extends('layouts.tabler')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">
                            {{ __('Add payment') }}
                        </h3>
                    </div>

                    <div class="card-actions">
                        <a class="btn btn-info" href="{{ route('customers.index') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left"
                                 width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                 fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M5 12l14 0"/>
                                <path d="M5 12l6 6"/>
                                <path d="M5 12l6 -6"/>
                            </svg>
                            {{ __('Back') }}
                        </a>
                    </div>
                </div>

                <form action="{{ route('payments.store', $customer->id) }}" method="POST">
                    @csrf
                    <input name="customer_id" value="{{ $customer->id }}" hidden/>
                    <div class="card-body">
                        <div class="row gx-3 mb-3">
                            <div class="col-6">
                                <label for="nature" class="small my-1">
                                    {{ __('Nature') }}
                                </label>
                                <input name="nature" id="nature" type="text"
                                       class="form-control example-date-input @error('nature') is-invalid @enderror"
                                       value="{{ old('nature') }}"
                                       required
                                >
                            </div>
                            <div class="col-6">
                                <label for="bank" class="small my-1">
                                    {{ __('Bank') }}
                                </label>
                                <select name="bank" id="bank"
                                        class="form-select @error('bank') is-invalid @enderror">
                                    <option value="">{{ __('Select a bank:') }}</option>
                                    @foreach ($banks as $bank)
                                        <option value="{{ $bank->value }}" @selected(old('bank', $customer->bank_name) === $bank->value)>
                                            {{ $bank->value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6">
                                <label for="date" class="small my-1">
                                    {{ __('Date') }}
                                </label>
                                <input name="date" id="date" type="text"
                                       class="form-control datepicker @error('date') is-invalid @enderror"
                                       value="{{ old('date') ?: now()->format('d/m/Y') }}"
                                       placeholder="dd/mm/yyyy"
                                       required
                                       autocomplete="off"
                                >
                            </div>

                            <div class="col-6">
                                <label for="echeance" class="small my-1">
                                    {{ __('Echeance') }}
                                </label>
                                <input name="echeance" id="echeance" type="text"
                                       class="form-control datepicker @error('echeance') is-invalid @enderror"
                                       value="{{ old('echeance') ?: '' }}"
                                       placeholder="dd/mm/yyyy"
                                       required
                                       autocomplete="off"
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="small mb-1" for="customer_id">
                                    {{ __('Payment type') }}
                                </label>

                                <select class="form-control @error('payment_type') is-invalid @enderror" id="payment_type" name="payment_type">
                                    <option value="HandCash">Cash</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="Exchange">Lettre de change</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="amount" class="small my-1">
                                    {{ __('Amount') }}
                                </label>
                                <input name="amount" id="amount" type="text"
                                       class="form-control example-date-input @error('amount') is-invalid @enderror"
                                       value="{{ old('amount') }}"
                                       required
                                >
                            </div>
                            <div class="col-12">
                                <label for="description" class="small my-1">
                                    {{ __('Description') }}
                                </label>
                                <textarea name="description" id="description"
                                          class="form-control @error('description') is-invalid @enderror"
                                          rows="3">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <x-button type="submit">
                            {{ __('Add') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const dateInputs = document.querySelectorAll('.datepicker');
        
        dateInputs.forEach(function(input) {
            // Format input as user types
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
                
                if (value.length >= 2) {
                    value = value.substring(0,2) + '/' + value.substring(2);
                }
                if (value.length >= 5) {
                    value = value.substring(0,5) + '/' + value.substring(5,9);
                }
                
                e.target.value = value;
            });
            
            // Validate date format and actual date
            input.addEventListener('blur', function(e) {
                const dateValue = e.target.value;
                if (dateValue) {
                    const dateRegex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
                    const match = dateValue.match(dateRegex);
                    
                    if (!match) {
                        e.target.setCustomValidity('Please enter date in dd/mm/yyyy format');
                        return;
                    }
                    
                    const day = parseInt(match[1]);
                    const month = parseInt(match[2]);
                    const year = parseInt(match[3]);
                    
                    // Validate actual date
                    const date = new Date(year, month - 1, day);
                    if (date.getFullYear() !== year || 
                        date.getMonth() !== month - 1 || 
                        date.getDate() !== day) {
                        e.target.setCustomValidity('Please enter a valid date');
                        return;
                    }
                    
                    e.target.setCustomValidity('');
                }
            });
            
            // Clear validation message when user starts typing
            input.addEventListener('input', function(e) {
                e.target.setCustomValidity('');
            });
        });
    });
    </script>
@endsection