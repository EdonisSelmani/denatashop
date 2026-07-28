<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-black uppercase text-[#9A712E]">Llogaria</p>
            <h1 class="text-3xl font-black text-[#15181B]">Profili im</h1>
        </div>
    </x-slot>

    <div class="bg-[#F7F5F1] py-10">
        <div class="container-custom space-y-6">
            <div class="rounded-lg border border-[#E5E1DA] bg-white p-5 shadow-[0_18px_45px_rgba(21,24,27,0.06)] sm:p-7">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="rounded-lg border border-[#E5E1DA] bg-white p-5 shadow-[0_18px_45px_rgba(21,24,27,0.06)] sm:p-7">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="rounded-lg border border-[#E5E1DA] bg-white p-5 shadow-[0_18px_45px_rgba(21,24,27,0.06)] sm:p-7">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
