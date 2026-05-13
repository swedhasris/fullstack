@if($errors->any())
<div class="bg-red-900/30 border border-red-500/30 text-red-300 rounded-xl px-4 py-3 text-sm">
    <ul class="space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Full Name *</label>
        <input type="text" name="name" value="{{ old('name', $user?->name) }}" required
               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
               placeholder="John Smith">
    </div>
    <div>
        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Email Address *</label>
        <input type="email" name="email" value="{{ old('email', $user?->email) }}" required
               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
               placeholder="john@company.com">
    </div>
    <div>
        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Role *</label>
        <select name="role" required class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
            @foreach($roles as $role)
            <option value="{{ $role->value }}" {{ old('role', $user?->role?->value) === $role->value ? 'selected' : '' }}>{{ $role->label() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $user?->phone) }}"
               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
               placeholder="+91 98765 43210">
    </div>
    <div>
        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Department</label>
        <input type="text" name="department" value="{{ old('department', $user?->department) }}"
               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
               placeholder="IT, HR, Finance...">
    </div>
    <div>
        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Password {{ $user ? '(leave blank to keep)' : '*' }}</label>
        <input type="password" name="password" {{ $user ? '' : 'required' }} minlength="8"
               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
               placeholder="Min. 8 characters">
    </div>
</div>

@if($user)
<div class="flex items-center gap-3">
    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
           class="w-4 h-4 rounded border-white/20 bg-white/5 text-primary">
    <label for="is_active" class="text-sm text-gray-300 cursor-pointer">Account is active</label>
</div>
@endif
