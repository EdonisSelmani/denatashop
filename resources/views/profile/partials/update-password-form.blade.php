{{-- resources/views/profile/partials/update-password-form.blade.php --}}
<section>
    <header>
        <h2 class="text-xl font-black text-[#15181B]">Ndrysho fjalekalimin</h2>
        <p class="mt-1 text-sm font-semibold text-[#6B6F74]">Perdor nje fjalekalim te forte per llogarine tuaj.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="current_password" class="mb-2 block text-sm font-black text-[#22272B]">Fjalekalimi aktual</label>
            <input id="current_password" name="current_password" type="password" class="store-input block w-full" />
            @error('current_password')
                <p class="mt-1 text-sm font-semibold text-[#C9473D]">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="mb-2 block text-sm font-black text-[#22272B]">Fjalekalimi i ri</label>
            <input id="password" name="password" type="password" class="store-input block w-full" />
            @error('password')
                <p class="mt-1 text-sm font-semibold text-[#C9473D]">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-black text-[#22272B]">Konfirmo fjalekalimin</label>
            <input id="password_confirmation" name="password_confirmation" type="password" class="store-input block w-full" />
            @error('password_confirmation')
                <p class="mt-1 text-sm font-semibold text-[#C9473D]">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-primary">Ruaj fjalekalimin</button>
            @if (session('status') === 'password-updated')
                <p class="text-sm font-semibold text-[#25865A]">U ruajt.</p>
            @endif
        </div>
    </form>
</section>
