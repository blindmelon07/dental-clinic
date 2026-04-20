<div>
    <div class="mb-8">
        <h1 class="font-heading text-3xl font-bold text-slate-900">My Invoices</h1>
        <p class="text-slate-500 mt-1.5">View your billing history and outstanding balances</p>
    </div>

    @if($this->patient)
        <div class="space-y-3" aria-live="polite" aria-label="Invoices list">
            @forelse($this->invoices as $invoice)
                <article class="card overflow-hidden" x-data="{ open: false }">
                    {{-- Invoice Header --}}
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2.5 flex-wrap">
                                    <h2 class="font-heading font-semibold text-slate-900 font-mono">{{ $invoice->invoice_number }}</h2>
                                    @php
                                        $statusColor = match($invoice->status->value) {
                                            'paid' => 'bg-green-100 text-green-700',
                                            'partially_paid' => 'bg-amber-100 text-amber-700',
                                            'overdue' => 'bg-red-100 text-red-700',
                                            'sent' => 'bg-cyan-100 text-cyan-700',
                                            default => 'bg-slate-100 text-slate-600',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusColor }}">{{ $invoice->status->label() }}</span>
                                </div>
                                <p class="text-sm text-slate-500 mt-1">
                                    Issued: <time datetime="{{ $invoice->invoice_date->toDateString() }}">{{ $invoice->invoice_date->format('M d, Y') }}</time>
                                    @if($invoice->due_date)
                                        <span class="text-slate-300 mx-1" aria-hidden="true">·</span>
                                        Due: <time datetime="{{ $invoice->due_date->toDateString() }}">{{ $invoice->due_date->format('M d, Y') }}</time>
                                    @endif
                                </p>
                            </div>

                            <div class="flex items-center gap-3 flex-shrink-0">
                                <div class="text-right">
                                    <p class="font-heading text-lg font-bold text-slate-900 tabular-nums">₱{{ number_format($invoice->total, 2) }}</p>
                                    @if($invoice->balance_due > 0)
                                        <p class="text-sm text-red-600 font-semibold tabular-nums">₱{{ number_format($invoice->balance_due, 2) }} due</p>
                                    @else
                                        <p class="text-sm text-green-600 font-medium">Fully paid</p>
                                    @endif
                                </div>

                                <button @click="open = !open"
                                        :aria-expanded="open"
                                        aria-label="Toggle invoice details for {{ $invoice->invoice_number }}"
                                        class="p-2.5 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500">
                                    <svg class="w-5 h-5 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Invoice Line Items (expandable) --}}
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="border-t border-slate-100 bg-slate-50/50 px-5 py-4"
                         style="display: none;">

                        <div class="overflow-x-auto -mx-1 px-1">
                            <table class="w-full text-sm" aria-label="Invoice line items">
                                <thead>
                                    <tr class="text-left">
                                        <th class="pb-3 text-xs font-semibold text-slate-500 uppercase tracking-widest">Service / Item</th>
                                        <th class="pb-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-widest">Qty</th>
                                        <th class="pb-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-widest">Unit Price</th>
                                        <th class="pb-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-widest">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($invoice->items as $item)
                                        <tr>
                                            <td class="py-2.5 text-slate-800 font-medium">{{ $item->description }}</td>
                                            <td class="py-2.5 text-center text-slate-500 tabular-nums">{{ $item->quantity }}</td>
                                            <td class="py-2.5 text-right text-slate-500 tabular-nums">₱{{ number_format($item->unit_price, 2) }}</td>
                                            <td class="py-2.5 text-right font-semibold text-slate-800 tabular-nums">₱{{ number_format($item->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="border-t border-slate-200">
                                        <td colspan="3" class="pt-3 text-right text-slate-500 text-xs font-medium uppercase tracking-wide">Subtotal</td>
                                        <td class="pt-3 text-right tabular-nums font-medium">₱{{ number_format($invoice->subtotal, 2) }}</td>
                                    </tr>
                                    @if($invoice->discount_amount > 0)
                                    <tr>
                                        <td colspan="3" class="pt-1.5 text-right text-slate-500 text-xs font-medium uppercase tracking-wide">Discount</td>
                                        <td class="pt-1.5 text-right text-red-600 tabular-nums font-medium">−₱{{ number_format($invoice->discount_amount, 2) }}</td>
                                    </tr>
                                    @endif
                                    @if($invoice->tax_amount > 0)
                                    <tr>
                                        <td colspan="3" class="pt-1.5 text-right text-slate-500 text-xs font-medium uppercase tracking-wide">Tax ({{ $invoice->tax_rate }}%)</td>
                                        <td class="pt-1.5 text-right tabular-nums font-medium">₱{{ number_format($invoice->tax_amount, 2) }}</td>
                                    </tr>
                                    @endif
                                    <tr class="border-t border-slate-200">
                                        <td colspan="3" class="pt-3 text-right font-heading font-bold text-slate-800">Total</td>
                                        <td class="pt-3 text-right font-heading font-bold text-slate-900 tabular-nums text-base">₱{{ number_format($invoice->total, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="pt-1.5 text-right text-green-700 font-medium text-xs uppercase tracking-wide">Amount Paid</td>
                                        <td class="pt-1.5 text-right text-green-700 font-semibold tabular-nums">₱{{ number_format($invoice->amount_paid, 2) }}</td>
                                    </tr>
                                    @if($invoice->balance_due > 0)
                                    <tr>
                                        <td colspan="3" class="pt-1.5 text-right text-red-600 font-bold text-xs uppercase tracking-wide">Balance Due</td>
                                        <td class="pt-1.5 text-right text-red-600 font-bold tabular-nums">₱{{ number_format($invoice->balance_due, 2) }}</td>
                                    </tr>
                                    @endif
                                </tfoot>
                            </table>
                        </div>

                        @if($invoice->notes)
                            <div class="mt-4 pt-4 border-t border-slate-200">
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Notes</p>
                                <p class="text-sm text-slate-600">{{ $invoice->notes }}</p>
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <div class="text-center py-16 card p-8">
                    <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-heading font-semibold text-slate-700">No invoices yet</h3>
                    <p class="text-slate-500 text-sm mt-1">Your billing history will appear here after clinic visits.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $this->invoices->links() }}
        </div>
    @endif
</div>
