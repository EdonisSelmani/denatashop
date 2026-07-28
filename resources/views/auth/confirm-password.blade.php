<x-guest-layout>
    <div class="mb-6">
        <p class="text-sm font-black uppercase text-[#9A712E]">Siguri</p>
        <h1 class="mt-2 text-2xl font-black text-[#15181B]">Konfirmoni fjalekalimin</h1>
        <p class="mt-3 text-sm font-semibold leading-6 text-[#6B6F74]">
            Kjo zone eshte e mbrojtur. Vendosni fjalekalimin per te vazhduar.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="password" value="Fjalekalimi" />
            <x-text-input id="password" class="mt-2 block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">
            Konfirmo
        </x-primary-button>
    </form>
</x-guest-layout>
