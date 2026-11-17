<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-xl font-bold tracking-tight text-[#1d1d1f]">Masuk ke LUMEN MART</h1>
        <p class="mt-1 text-xs text-[#86868b]">Pilih akun demo instan atau masuk dengan akun Anda</p>
    </div>

    <!-- Tombol pengisi cepat akun demo -->
    <div class="mb-6 rounded-2xl bg-[#f5f5f7] p-3">
        <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-[#86868b]">Klik Akun Demo:</p>
        <div class="grid grid-cols-3 gap-2 text-xs">
            <button
                type="button"
                onclick="document.getElementById('email').value='admin@lumenmart.test';document.getElementById('password').value='password';"
                class="rounded-xl bg-white px-2.5 py-2 font-semibold text-[#1d1d1f] shadow-2xs ring-1 ring-black/[0.06] transition hover:bg-[#1d1d1f] hover:text-white"
            >
                Admin
            </button>
            <button
                type="button"
                onclick="document.getElementById('email').value='pemilik@lumenmart.test';document.getElementById('password').value='password';"
                class="rounded-xl bg-white px-2.5 py-2 font-semibold text-[#1d1d1f] shadow-2xs ring-1 ring-black/[0.06] transition hover:bg-[#1d1d1f] hover:text-white"
            >
                Pemilik
            </button>
            <button
                type="button"
                onclick="document.getElementById('email').value='pelanggan@lumenmart.test';document.getElementById('password').value='password';"
                class="rounded-xl bg-white px-2.5 py-2 font-semibold text-[#1d1d1f] shadow-2xs ring-1 ring-black/[0.06] transition hover:bg-[#0071e3] hover:text-white"
            >
                Pelanggan
            </button>
        </div>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1 block w-full rounded-xl" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1 block w-full rounded-xl" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="mt-4 flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-[#0071e3] shadow-2xs focus:ring-[#0071e3]" name="remember">
                <span class="ms-2 text-xs text-[#86868b]">{{ __('Ingat saya') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-xs text-[#0071e3] hover:underline" href="{{ route('password.request') }}">
                    {{ __('Lupa password?') }}
                </a>
            @endif
        </div>

        <div class="mt-6">
            <button type="submit" class="w-full rounded-full bg-[#1d1d1f] py-3 text-xs font-semibold text-white transition hover:bg-[#0071e3]">
                {{ __('Masuk') }}
            </button>
        </div>

        <p class="mt-5 text-center text-xs text-[#86868b]">
            Belum punya akun pelanggan?
            <a href="{{ route('register') }}" class="font-semibold text-[#0071e3] hover:underline">Daftar sekarang</a>
        </p>
    </form>
</x-guest-layout>
