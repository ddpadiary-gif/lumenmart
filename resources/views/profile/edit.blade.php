<x-app-layout>
    <div class="mx-auto max-w-4xl space-y-6 px-4 py-12 sm:px-6 lg:px-8">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-[#0071e3]">Pengaturan Akun</p>
            <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-[#1d1d1f]">Profil Pengguna</h1>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-2xs ring-1 ring-black/[0.04] sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-2xs ring-1 ring-black/[0.04] sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-2xs ring-1 ring-black/[0.04] sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
