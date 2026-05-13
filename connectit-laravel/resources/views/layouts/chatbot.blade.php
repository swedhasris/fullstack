{{-- ── Kiru AI Chatbot ─────────────────────────────────────────────────────── --}}
<div x-data="kiruChatbot()" class="fixed bottom-6 right-6 z-50">

    {{-- Chat Window --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
         class="mb-4 w-80 bg-[#161615] border border-white/10 rounded-2xl shadow-2xl overflow-hidden flex flex-col"
         style="height: 480px;">

        {{-- Header --}}
        <div class="flex items-center gap-3 px-4 py-3 border-b border-white/10 bg-primary/5">
            <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center">
                <i data-lucide="bot" class="w-4 h-4 text-primary"></i>
            </div>
            <div>
                <div class="text-sm font-bold">Kiru AI</div>
                <div class="text-[9px] text-primary font-bold uppercase tracking-widest">IT Assistant · Online</div>
            </div>
            <button @click="open = false" class="ml-auto p-1 hover:bg-white/10 rounded-lg">
                <i data-lucide="x" class="w-4 h-4 text-gray-400"></i>
            </button>
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar" x-ref="messages">
            <template x-for="msg in messages" :key="msg.id">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user'
                            ? 'bg-primary/20 text-white rounded-2xl rounded-tr-sm'
                            : 'bg-white/5 text-gray-200 rounded-2xl rounded-tl-sm'"
                         class="max-w-[85%] px-3 py-2 text-sm leading-relaxed">
                        <span x-text="msg.content"></span>
                    </div>
                </div>
            </template>
            <div x-show="loading" class="flex justify-start">
                <div class="bg-white/5 rounded-2xl rounded-tl-sm px-4 py-3">
                    <div class="flex gap-1">
                        <span class="w-1.5 h-1.5 bg-primary rounded-full animate-bounce" style="animation-delay:0ms"></span>
                        <span class="w-1.5 h-1.5 bg-primary rounded-full animate-bounce" style="animation-delay:150ms"></span>
                        <span class="w-1.5 h-1.5 bg-primary rounded-full animate-bounce" style="animation-delay:300ms"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div class="p-3 border-t border-white/10">
            <form @submit.prevent="sendMessage" class="flex gap-2">
                <input x-model="input" type="text" placeholder="Ask Kiru anything..."
                       class="flex-1 bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-colors placeholder-gray-600">
                <button type="submit" :disabled="loading || !input.trim()"
                        class="p-2 bg-primary rounded-xl text-white hover:bg-primary/90 disabled:opacity-50 transition-colors">
                    <i data-lucide="send" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>

    {{-- Toggle Button --}}
    <button @click="open = !open"
            class="w-14 h-14 bg-primary rounded-2xl shadow-lg flex items-center justify-center hover:bg-primary/90 transition-all hover:scale-105 active:scale-95">
        <i data-lucide="bot" class="w-6 h-6 text-white" x-show="!open"></i>
        <i data-lucide="x" class="w-6 h-6 text-white" x-show="open"></i>
    </button>
</div>

<script>
function kiruChatbot() {
    return {
        open: false,
        input: '',
        loading: false,
        messages: [
            { id: 1, role: 'assistant', content: "Hi! I'm Kiru, your IT assistant. How can I help you today?" }
        ],
        async sendMessage() {
            if (!this.input.trim() || this.loading) return;
            const userMsg = this.input.trim();
            this.input = '';
            this.messages.push({ id: Date.now(), role: 'user', content: userMsg });
            this.loading = true;
            this.$nextTick(() => this.scrollToBottom());
            try {
                const data = await window.api.post('/api/ai/chat', { message: userMsg });
                this.messages.push({ id: Date.now() + 1, role: 'assistant', content: data.response || 'Sorry, I could not process that.' });
            } catch (e) {
                this.messages.push({ id: Date.now() + 1, role: 'assistant', content: 'Sorry, I encountered an error. Please try again.' });
            } finally {
                this.loading = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },
        scrollToBottom() {
            const el = this.$refs.messages;
            if (el) el.scrollTop = el.scrollHeight;
        }
    };
}
</script>
