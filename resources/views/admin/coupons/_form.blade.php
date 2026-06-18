@csrf

<div class="grid sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
        <input name="code" value="{{ old('code', $coupon->code) }}" class="w-full border rounded-lg px-3 py-2 uppercase" required>
        @error('code') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
        <input name="name" value="{{ old('name', $coupon->name) }}" class="w-full border rounded-lg px-3 py-2">
        @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
        <select name="type" class="w-full border rounded-lg px-3 py-2">
            @foreach($types as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $coupon->type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('type') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Value</label>
        <input type="number" step="0.01" min="0" name="value" value="{{ old('value', $coupon->value) }}" class="w-full border rounded-lg px-3 py-2" required>
        @error('value') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Minimum order total</label>
        <input type="number" step="0.01" min="0" name="minimum_order_total" value="{{ old('minimum_order_total', $coupon->minimum_order_total ?? 0) }}" class="w-full border rounded-lg px-3 py-2">
        @error('minimum_order_total') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Usage limit</label>
        <input type="number" min="1" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}" class="w-full border rounded-lg px-3 py-2">
        @error('usage_limit') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Starts at</label>
        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($coupon->starts_at)->format('Y-m-d\TH:i')) }}" class="w-full border rounded-lg px-3 py-2">
        @error('starts_at') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Expires at</label>
        <input type="datetime-local" name="expires_at" value="{{ old('expires_at', optional($coupon->expires_at)->format('Y-m-d\TH:i')) }}" class="w-full border rounded-lg px-3 py-2">
        @error('expires_at') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>
</div>

<label class="inline-flex items-center gap-2 mt-4">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon->is_active))>
    <span class="text-sm text-gray-700">Active</span>
</label>

<div class="flex justify-end gap-3 mt-6">
    <a href="{{ route('admin.coupons.index') }}" class="px-4 py-2 border rounded-lg">Cancel</a>
    <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Save coupon</button>
</div>
