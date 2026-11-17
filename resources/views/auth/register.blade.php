<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-xl font-bold tracking-tight text-[#1d1d1f]">Daftar Akun Pelanggan</h1>
        <p class="mt-1 text-xs text-[#86868b]">Nikmati belanja hemat dengan Smart Bundle Discount di LUMEN MART</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Nama Lengkap')" />
            <x-text-input id="name" class="mt-1 block w-full rounded-xl" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1 block w-full rounded-xl" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1 block w-full rounded-xl" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
            <x-text-input id="password_confirmation" class="mt-1 block w-full rounded-xl" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6">
            <button type="submit" class="w-full rounded-full bg-[#1d1d1f] py-3 text-xs font-semibold text-white transition hover:bg-[#0071e3]">
                {{ __('Daftar Sebagai Pelanggan') }}
            </button>
        </div>

        <p class="mt-5 text-center text-xs text-[#86868b]">
            Sudah punya akun?
            <a class="font-semibold text-[#0071e3] hover:underline" href="{{ route('login') }}">
                {{ __('Masuk di sini') }}
            </a>
        </p>
    </form>
</x-guest-layout>
