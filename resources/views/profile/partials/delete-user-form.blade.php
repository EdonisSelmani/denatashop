{{-- resources/views/profile/partials/delete-user-form.blade.php --}}
<section>
    <header>
        <h2 class="text-xl font-black text-[#15181B]">Fshi llogarine</h2>
        <p class="mt-1 text-sm font-semibold text-[#6B6F74]">Ky veprim fshin llogarine tuaj dhe nuk mund te kthehet nga kjo faqe.</p>
    </header>

    <button type="button" onclick="openDeleteModal()" class="mt-6 rounded-md bg-[#C9473D] px-4 py-2 text-sm font-black text-white transition hover:bg-[#a73a32]">Fshi llogarine</button>

    <!-- Delete Modal -->
    <div id="deleteModal" class="fixed inset-0 z-50 hidden h-full w-full overflow-y-auto bg-[#15181B]/60 px-4">
        <div class="relative top-20 mx-auto w-full max-w-md rounded-lg border border-[#E5E1DA] bg-white p-5 shadow-lg">
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')
                
                <h3 class="text-lg font-black text-[#15181B]">A jeni te sigurt?</h3>
                <p class="mt-2 text-sm font-semibold text-[#6B6F74]">Per ta konfirmuar, shkruani fjalekalimin tuaj.</p>
                
                <div class="mt-4">
                    <label for="password" class="mb-2 block text-sm font-black text-[#22272B]">Fjalekalimi</label>
                    <input id="password" name="password" type="password" class="store-input block w-full" placeholder="Shkruani fjalekalimin" />
                    @error('password')
                        <p class="mt-1 text-sm font-semibold text-[#C9473D]">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="closeDeleteModal()" class="btn-secondary">Anulo</button>
                    <button type="submit" class="rounded-md bg-[#C9473D] px-4 py-2 text-sm font-black text-white transition hover:bg-[#a73a32]">Fshi llogarine</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openDeleteModal() {
            document.getElementById('deleteModal').classList.remove('hidden');
        }
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
    </script>
</section>
