<x-guest-layout>
    <div class="mb-6">
        <p class="text-sm font-black uppercase text-[#9A712E]">Verifikim</p>
        <h1 class="mt-2 text-2xl font-black text-[#15181B]">Verifikoni email-in</h1>
        <p class="mt-3 text-sm font-semibold leading-6 text-[#6B6F74]">
            {{ session('status') === 'verification-link-sent-on-registration'
                ? 'Ta kemi dërguar një link verifikimi në emailin tuaj.'
                : 'Para se të vazhdoni, hapni linkun e verifikimit që ju dërguam në email.' }}
        </p>
    </div>

    @if(session('error'))
        <div class="mb-5 rounded-md border border-[#C9473D]/30 bg-[#C9473D]/10 px-4 py-3 text-sm font-semibold text-[#C9473D]">
            {{ session('error') }}
        </div>
    @endif

    @if (session('status') == 'verification-link-sent')
        <div class="mb-5 rounded-md border border-[#25865A]/30 bg-[#25865A]/10 px-4 py-3 text-sm font-semibold text-[#1f6d49]">
            Linku i ri i verifikimit u dergua ne email-in tuaj.
        </div>
    @endif

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>
                Ridërgo emailin e verifikimit
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm font-bold text-[#6B6F74] underline-offset-4 transition hover:text-[#9A712E] hover:underline focus:outline-none focus:ring-2 focus:ring-[#B88A3B] focus:ring-offset-2">
                Dilni
            </button>
        </form>
    </div>
</x-guest-layout>
