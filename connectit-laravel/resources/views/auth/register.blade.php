<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | ConnectIT ITSM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: '#81B532' }, fontFamily: { sans: ['Inter', 'ui-sans-serif'] } } } }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-grid { background-image: radial-gradient(rgba(129,181,50,0.08) 1px, transparent 1px); background-size: 32px 32px; }
    </style>
</head>
<body class="h-full bg-[#0a0a0a] text-white flex items-center justify-center bg-grid py-8">

    <div class="w-full max-w-md px-6" x-data="{ loading: false }">

        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-primary/10 border border-primary/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <span class="text-2xl font-black text-primary">C</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight">Create Account</h1>
            <p class="text-gray-500 text-sm mt-1">Join ConnectIT ITSM</p>
        </div>

        <div class="bg-[#161615] border border-white/10 rounded-2xl p-8 shadow-2xl">

            @if($errors->any())
            <div class="bg-red-900/30 border border-red-500/30 text-red-300 rounded-xl px-4 py-3 mb-6 text-sm">
                <ul class="space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('register.post') }}" @submit="loading = true">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
                               placeholder="John Smith">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
                               placeholder="you@company.com">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Password</label>
                        <input type="password" name="password" required minlength="8"
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
                               placeholder="Min. 8 characters">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Confirm Password</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
                               placeholder="Repeat password">
                    </div>
                </div>

                <button type="submit" :disabled="loading"
                        class="w-full mt-6 bg-primary text-white font-bold py-3 rounded-xl hover:bg-primary/90 transition-colors disabled:opacity-70 flex items-center justify-center gap-2">
                    <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="loading ? 'Creating account...' : 'Create Account'">Create Account</span>
                </button>
            </form>

            <p class="text-center text-xs text-gray-500 mt-6">
                Already have an account?
                <a href="{{ route('login') }}" class="text-primary hover:underline font-medium">Sign in</a>
            </p>
        </div>
    </div>
</body>
</html>
