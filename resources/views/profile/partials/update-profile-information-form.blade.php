{{-- resources/views/profile/partials/update-profile-information-form.blade.php --}}
<section>
    <header>
        <h2 class="text-xl font-black text-[#15181B]">Te dhenat e profilit</h2>
        <p class="mt-1 text-sm font-semibold text-[#6B6F74]">Perditesoni emrin dhe email-in e llogarise.</p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="mb-2 block text-sm font-black text-[#22272B]">Emri</label>
            <input id="name" name="name" type="text" class="store-input block w-full" value="{{ old('name', Auth::user()->name) }}" required autofocus />
            @error('name')
                <p class="mt-1 text-sm font-semibold text-[#C9473D]">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="mb-2 block text-sm font-black text-[#22272B]">Email</label>
            <input id="email" name="email" type="email" class="store-input block w-full" value="{{ old('email', Auth::user()->email) }}" required />
            @error('email')
                <p class="mt-1 text-sm font-semibold text-[#C9473D]">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-primary">Ruaj ndryshimet</button>
            @if (session('status') === 'profile-updated')
                <p class="text-sm font-semibold text-[#25865A]">U ruajt.</p>
            @endif
        </div>
    </form>
</section>
