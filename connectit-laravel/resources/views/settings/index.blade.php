@extends('layouts.app')
@section('title', 'Settings')
@section('page-title', 'System Settings')
@section('page-subtitle', 'Configuration')

@section('content')
<div class="p-6 max-w-3xl mx-auto space-y-5">

    <form method="POST" action="{{ route('settings.update') }}">
        @csrf @method('PUT')

        {{-- General --}}
        <div class="bg-sn-card rounded-2xl overflow-hidden mb-4">
            <div class="p-5 border-b border-white/5"><h3 class="text-sm font-bold">General Settings</h3></div>
            <div class="p-5 space-y-4">
                @foreach([
                    ['app_name',    'Application Name',    'text',   'ConnectIT ITSM'],
                    ['company_name','Company Name',         'text',   'My Company'],
                    ['primary_color','Brand Color (hex)',   'text',   '#81B532'],
                ] as [$key, $label, $type, $placeholder])
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">{{ $label }}</label>
                    <input type="{{ $type }}" name="settings[{{ $key }}]"
                           value="{{ old('settings.'.$key, $settings[$key]?->setting_value ?? $placeholder) }}"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
                           placeholder="{{ $placeholder }}">
                </div>
                @endforeach
            </div>
        </div>

        {{-- SLA Defaults --}}
        <div class="bg-sn-card rounded-2xl overflow-hidden mb-4">
            <div class="p-5 border-b border-white/5"><h3 class="text-sm font-bold">SLA Defaults</h3></div>
            <div class="p-5 grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Default Response Time (hours)</label>
                    <input type="number" name="settings[default_sla_response]" min="1"
                           value="{{ old('settings.default_sla_response', $settings['default_sla_response']?->setting_value ?? 4) }}"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Default Resolution Time (hours)</label>
                    <input type="number" name="settings[default_sla_resolve]" min="1"
                           value="{{ old('settings.default_sla_resolve', $settings['default_sla_resolve']?->setting_value ?? 24) }}"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
                </div>
            </div>
        </div>

        {{-- Notifications --}}
        <div class="bg-sn-card rounded-2xl overflow-hidden mb-4">
            <div class="p-5 border-b border-white/5"><h3 class="text-sm font-bold">Notifications</h3></div>
            <div class="p-5 space-y-3">
                @foreach([
                    ['email_notifications',    'Email Notifications'],
                    ['whatsapp_notifications',  'WhatsApp Notifications'],
                    ['ai_suggestions',          'AI Suggestions (Kiru)'],
                ] as [$key, $label])
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-300">{{ $label }}</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="settings[{{ $key }}]" value="false">
                        <input type="checkbox" name="settings[{{ $key }}]" value="true"
                               {{ ($settings[$key]?->setting_value ?? 'true') === 'true' ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="flex items-center gap-2 bg-primary text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-primary/90 transition-colors">
                <i data-lucide="save" class="w-4 h-4"></i> Save Settings
            </button>
        </div>
    </form>
</div>
@endsection
