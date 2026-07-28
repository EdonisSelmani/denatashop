<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-black uppercase text-[#9A712E]">Llogaria</p>
                <h1 class="text-3xl font-black text-[#15181B]">Dashboard</h1>
            </div>
            <a href="{{ route('shop') }}" class="btn-secondary inline-flex w-fit items-center justify-center gap-2">
                Shfleto produktet
                <x-store.icon name="arrow-right" class="h-4 w-4" />
            </a>
        </div>
    </x-slot>

    <div class="bg-[#F7F5F1] py-10">
        <div class="container-custom">
            <div class="rounded-lg border border-[#E5E1DA] bg-white p-6 shadow-[0_18px_45px_rgba(21,24,27,0.06)]">
                <p class="text-lg font-black text-[#15181B]">Jeni kycur me sukses.</p>
                <p class="mt-2 text-sm font-semibold text-[#6B6F74]">Nga ketu mund te vazhdoni blerjet, te shihni porosite ose te perditesoni profilin.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('orders.index') }}" class="btn-primary inline-flex items-center justify-center gap-2">Porosite</a>
                    <a href="{{ route('profile.edit') }}" class="btn-secondary inline-flex items-center justify-center gap-2">Profili</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
