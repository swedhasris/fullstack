@if($errors->any())
<div class="bg-red-900/30 border border-red-500/30 text-red-300 rounded-xl px-4 py-3 text-sm">
    <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Asset Name *</label>
        <input type="text" name="name" value="{{ old('name', $asset?->name) }}" required
               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
               placeholder="e.g. Dell Laptop #001">
    </div>
    <div>
        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Type *</label>
        <select name="type" required class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
            @foreach(['Hardware', 'Software', 'Network', 'Virtual', 'Mobile', 'Printer', 'Server', 'Storage', 'Other'] as $t)
            <option value="{{ $t }}" {{ old('type', $asset?->type) === $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Status *</label>
        <select name="status" required class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
            @foreach(['Operational', 'Maintenance', 'Retired', 'Disposed', 'In Stock'] as $s)
            <option value="{{ $s }}" {{ old('status', $asset?->status ?? 'Operational') === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Owner</label>
        <select name="owner" class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
            <option value="">No Owner</option>
            @foreach($users as $u)
            <option value="{{ $u->uid }}" data-name="{{ $u->name }}" {{ old('owner', $asset?->owner) === $u->uid ? 'selected' : '' }}>{{ $u->name }}</option>
            @endforeach
        </select>
        <input type="hidden" name="owner_name" id="ownerName" value="{{ old('owner_name', $asset?->owner_name) }}">
    </div>
    <div>
        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Manufacturer</label>
        <input type="text" name="manufacturer" value="{{ old('manufacturer', $asset?->manufacturer) }}"
               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
               placeholder="Dell, HP, Cisco...">
    </div>
    <div>
        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Model</label>
        <input type="text" name="model" value="{{ old('model', $asset?->model) }}"
               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
               placeholder="Latitude 5520, ProBook 450...">
    </div>
    <div>
        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Serial Number</label>
        <input type="text" name="serial_number" value="{{ old('serial_number', $asset?->serial_number) }}"
               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600 font-mono"
               placeholder="SN-XXXXXXXX">
    </div>
    <div>
        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">IP Address</label>
        <input type="text" name="ip_address" value="{{ old('ip_address', $asset?->ip_address) }}"
               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600 font-mono"
               placeholder="192.168.1.100">
    </div>
    <div>
        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Location</label>
        <input type="text" name="location" value="{{ old('location', $asset?->location) }}"
               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
               placeholder="Office, Floor 2, Rack A...">
    </div>
    <div>
        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Purchase Date</label>
        <input type="date" name="purchase_date" value="{{ old('purchase_date', $asset?->purchase_date?->format('Y-m-d')) }}"
               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
    </div>
    <div>
        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Warranty Expiry</label>
        <input type="date" name="warranty_expiry" value="{{ old('warranty_expiry', $asset?->warranty_expiry?->format('Y-m-d')) }}"
               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
    </div>
</div>

<div>
    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Description</label>
    <textarea name="description" rows="3"
              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600 resize-none"
              placeholder="Additional details about this asset...">{{ old('description', $asset?->description) }}</textarea>
</div>

<script>
document.querySelector('[name="owner"]')?.addEventListener('change', function() {
    const opt = this.selectedOptions[0];
    document.getElementById('ownerName').value = opt?.dataset?.name || '';
});
</script>
