<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
        <div class="text-center mb-6">
            <x-application-logo class="w-20 h-20 mx-auto" />
            <h1 class="text-2xl font-bold text-gray-700 mt-4">تسجيل الدخول</h1>
            <p class="text-gray-500 mt-1">أدخل بريدك وكلمة المرور للمتابعة</p>
        </div>

        <form wire:submit.prevent="authenticate" class="space-y-5">
            {{-- Email --}}
            <div>
                <label class="block text-gray-600 font-medium mb-1" for="email">البريد الإلكتروني</label>
                <input
                    type="email"
                    wire:model.defer="email"
                    id="email"
                    placeholder="you@example.com"
                    required
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                >
                @error('form.email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label class="block text-gray-600 font-medium mb-1" for="password">كلمة المرور</label>
                <input
                    type="password"
                    wire:model.defer="password"
                    id="password"
                    placeholder="********"
                    required
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                >
                @error('form.password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember Me --}}
            <div class="flex items-center justify-between">
                <label class="flex items-center text-gray-600">
                    <input type="checkbox" wire:model.defer="remember" class="h-4 w-4 text-blue-600 rounded border-gray-300">
                    <span class="ml-2 text-sm">تذكرني</span>
                </label>
                <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:underline">نسيت كلمة المرور؟</a>
            </div>

            {{-- Submit --}}
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg shadow-md transition">
                تسجيل الدخول
            </button>
        </form>

        {{-- Divider --}}
        <div class="flex items-center my-6">
            <hr class="flex-1 border-gray-300">
            <span class="mx-2 text-gray-400 text-sm">أو</span>
            <hr class="flex-1 border-gray-300">
        </div>

        {{-- Social Login (اختياري) --}}
        <div class="flex space-x-4">
            <button class="flex-1 py-2 px-4 border rounded-lg hover:bg-gray-100 transition flex items-center justify-center">
                <img src="/images/google-icon.svg" class="w-5 h-5 mr-2"> تسجيل الدخول بـ Google
            </button>
            <button class="flex-1 py-2 px-4 border rounded-lg hover:bg-gray-100 transition flex items-center justify-center">
                <img src="/images/facebook-icon.svg" class="w-5 h-5 mr-2"> Facebook
            </button>
        </div>
    </div>
</div>
