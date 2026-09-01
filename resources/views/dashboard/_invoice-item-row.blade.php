@php
    $oldItem = $oldItem ?? [];
    $selectedType = $oldItem['sale_type'] ?? 'general';
@endphp
<div class="sale-item-row">
    <label class="field product-field">
        <span>Product / ទំនិញ</span>
        <select class="sale-product" name="items[{{ $itemIndex }}][product_id]" required>
            <option value="">Select product</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock }}" @selected((string) ($oldItem['product_id'] ?? '') === (string) $product->id)>
                    {{ $product->name }} · ${{ number_format((float) $product->price, 2) }} · {{ $product->stock }} units
                </option>
            @endforeach
        </select>
    </label>
    <label class="field">
        <span>Type / ប្រភេទ</span>
        <select class="sale-type" name="items[{{ $itemIndex }}][sale_type]" required>
            <option value="general" @selected($selectedType === 'general')>General / ទូទៅ</option>
            <option value="retail" @selected($selectedType === 'retail')>Retail / រាយ</option>
            <option value="wholesale" @selected($selectedType === 'wholesale')>Wholesale / ដុំ</option>
        </select>
    </label>
    <label class="field">
        <span class="sale-quantity-label">Quantity / ចំនួន</span>
        <input class="sale-quantity" type="number" name="items[{{ $itemIndex }}][quantity]" value="{{ $oldItem['quantity'] ?? 1 }}" min="1" step="1" required>
    </label>
    <label class="field pack-size-field">
        <span>Units / pack</span>
        <input class="units-per-pack" type="number" name="items[{{ $itemIndex }}][units_per_pack]" value="{{ $oldItem['units_per_pack'] ?? 1 }}" min="1" step="1" required>
    </label>
    <label class="field">
        <span class="sale-price-label">Price ($)</span>
        <input class="sale-price" type="number" name="items[{{ $itemIndex }}][unit_price]" value="{{ $oldItem['unit_price'] ?? '' }}" min="0" step="0.01" placeholder="Auto price">
    </label>
    <label class="field">
        <span>Discount %</span>
        <input class="sale-discount" type="number" name="items[{{ $itemIndex }}][discount_rate]" value="{{ $oldItem['discount_rate'] ?? 0 }}" min="0" max="100" step="0.01" required>
    </label>
    <label class="field">
        <span>Line total</span>
        <output class="sale-line-total">$0.00</output>
    </label>
    <button class="remove-sale-item" type="button" aria-label="Remove product">×</button>
</div>
