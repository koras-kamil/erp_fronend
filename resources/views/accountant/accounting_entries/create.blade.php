<x-app-layout>
    <div x-data="accountingEntryForm()" class="py-8 w-full max-w-4xl mx-auto px-4" dir="{{ app()->getLocale() == 'ku' ? 'rtl' : 'ltr' }}">
        
        {{-- Header Section --}}
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">{{ __('accounting_entry.new_entry') }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ __('accounting_entry.new_entry_desc') }}</p>
            </div>
            <a href="{{ route('accountant.accounting_entries.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-50 flex items-center gap-2 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('accounting_entry.back') }}
            </a>
        </div>

        <form action="{{ route('accountant.accounting_entries.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 space-y-8">
            @csrf
            
            {{-- 1. Visual Status Indicators --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 rounded-xl border-2 transition-all flex items-center gap-4" 
                     :class="form.type === 'cash_in' ? 'bg-emerald-50 border-emerald-500 shadow-sm' : 'bg-slate-50 border-slate-100 opacity-50'">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center bg-white text-emerald-500 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                    </div>
                    <span class="font-black text-emerald-800 text-sm uppercase">{{ __('accounting_entry.cash_in') }}</span>
                </div>

                <div class="p-4 rounded-xl border-2 transition-all flex items-center gap-4" 
                     :class="form.type === 'cash_out' ? 'bg-rose-50 border-rose-500 shadow-sm' : 'bg-slate-50 border-slate-100 opacity-50'">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center bg-white text-rose-500 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7-7m-7-7v18"></path></svg>
                    </div>
                    <span class="font-black text-rose-800 text-sm uppercase">{{ __('accounting_entry.cash_out') }}</span>
                </div>
            </div>

            {{-- Hidden Type Input for Backend --}}
            <input type="hidden" name="type" :value="form.type">

            {{-- 2. Smart Amount & Currency Row --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-1">
                    <label class="block text-xs font-bold text-slate-500 mb-2 italic">بڕی پارە (بۆ دەرهێنان - دابنێ)</label>
                    <input type="text" 
                           x-model="amountRaw" 
                           @input="handleAmountInput"
                           class="format-number w-full text-center font-black text-3xl rounded-2xl transition-all duration-300 py-4 shadow-sm border-2 focus:ring-0 outline-none" 
                           :class="form.type === 'cash_out' ? 'border-rose-500 bg-rose-50/30 text-rose-700' : 'border-emerald-500 bg-emerald-50/30 text-emerald-700'"
                           placeholder="0.00">
                    
                    {{-- داتای ڕاستەقینە بۆ سێرڤەر بەبێ فاریزە --}}
                    <input type="hidden" name="amount" :value="form.amount">
                </div>
                
                <div class="md:col-span-1">
                    <label class="block text-xs font-bold text-slate-500 mb-2">{{ __('accounting_entry.currency_name') }} *</label>
                    <select name="currency_id" x-model="form.currency_id" class="w-full border-slate-300 rounded-2xl font-bold py-4 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        @foreach($currencies as $cur)
                            <option value="{{ $cur->id }}">{{ $cur->currency_type }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-1">
                    <label class="block text-xs font-bold text-slate-500 mb-2">{{ __('accounting_entry.exchange_rate') }} *</label>
                    <input type="number" step="0.000001" name="exchange_rate" x-model="form.exchange_rate" class="w-full text-center rounded-2xl border-slate-300 font-bold py-4 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
            </div>

            {{-- 3. Cashbox & Date Details --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-50 p-6 rounded-3xl border border-slate-100 shadow-inner">
                <div class="space-y-3">
                    <label class="block text-xs font-bold text-slate-500">{{ __('accounting_entry.cashbox_name') }} *</label>
                    <select name="cashbox_id" x-model="form.cashbox_id" class="w-full border-slate-300 rounded-xl font-bold py-3 shadow-sm" required>
                        <option value="">{{ __('accounting_entry.select') }}</option>
                        @foreach($cashboxes as $box)
                            <option value="{{ $box['id'] }}">{{ $box['name'] }}</option>
                        @endforeach
                    </select>

                    {{-- Live Real Balance --}}
                    <div x-show="form.cashbox_id" x-transition class="bg-white p-3 rounded-xl border border-slate-200 flex justify-between items-center shadow-sm">
                        <span class="text-[10px] font-black text-slate-400 uppercase">باڵانسی ڕاستەقینە:</span>
                        <span class="text-xs font-black" :class="selectedBalance < 0 ? 'text-rose-600' : 'text-indigo-700'" dir="ltr" x-text="window.formatNumber(selectedBalance) + ' ' + (currenciesData[form.currency_id]?.type || '')"></span>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="block text-xs font-bold text-slate-500">{{ __('accounting_entry.entry_datetime') }} *</label>
                    <input type="datetime-local" name="entry_datetime" x-model="form.datetime" class="w-full text-center rounded-xl border-slate-300 font-bold py-3 shadow-sm" required>
                </div>
                
                <div class="space-y-3">
                    <label class="block text-xs font-bold text-slate-500">{{ __('accounting_entry.manual_voucher') }}</label>
                    <input type="text" name="manual_voucher" class="w-full text-center rounded-xl border-slate-300 font-bold py-3 shadow-sm">
                </div>
            </div>

            {{-- 4. Note & Attachment --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 italic">{{ __('accounting_entry.note') }}</label>
                    <input type="text" name="note" class="w-full rounded-xl border-slate-300 py-3.5 px-4 shadow-sm focus:ring-indigo-500" placeholder="...">
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 italic">{{ __('accounting_entry.attachment') }}</label>
                    <input type="file" name="attachment" class="w-full text-sm border border-slate-300 rounded-xl bg-white h-[52px] leading-[48px] px-3 shadow-sm cursor-pointer">
                </div>
            </div>

            {{-- Submit --}}
            <div class="pt-6 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-14 py-4 bg-indigo-600 text-white font-black rounded-2xl hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all transform hover:-translate-y-1 active:scale-95 flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ __('accounting_entry.save') }}
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('accountingEntryForm', () => ({
                currenciesData: {!! json_encode($currencies->keyBy('id')->map(fn($c) => ['type' => $c->currency_type, 'rate' => $c->price_single ?? 1])) !!},
                cashboxesData: {!! json_encode($cashboxes) !!},
                
                amountRaw: '',
                form: {
                    type: 'cash_in',
                    amount: 0,
                    currency_id: '{{ $currencies->first()->id ?? "" }}',
                    cashbox_id: '',
                    exchange_rate: 1,
                    datetime: new Date(new Date().getTime() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16)
                },

                // لۆجیکی نیشانەی ماینەس و ئەپدەیتکردنی بڕەکە بۆ باڵانس
                handleAmountInput() {
                    let val = this.amountRaw.replace(/,/g, '').trim();
                    
                    if (val === '-') {
                        this.form.type = 'cash_out';
                        this.form.amount = 0;
                        return;
                    }

                    if (val.startsWith('-')) {
                        this.form.type = 'cash_out';
                        let num = val.substring(1);
                        this.form.amount = parseFloat(num) || 0;
                    } else {
                        this.form.type = 'cash_in';
                        this.form.amount = parseFloat(val) || 0;
                    }
                },

                // وەرگرتنی باڵانسی ڕاستەقینە لە ڕاپۆرتی قاسەوە
                get selectedBalance() {
                    if(!this.form.cashbox_id) return 0;
                    let box = this.cashboxesData.find(b => b.id == this.form.cashbox_id);
                    return box ? (box.balances[this.form.currency_id] || 0) : 0;
                },

                init() {
                    // نوێکردنەوەی نرخی دراو یەکسەر لە کاتی گۆڕینی جۆری پارە
                    this.$watch('form.currency_id', id => {
                        this.form.exchange_rate = this.currenciesData[id]?.rate || 1;
                    });
                }
            }));
        });
    </script>
</x-app-layout>