<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-indigo-600">Create session</p>
            <h2 class="mt-2 text-2xl font-bold text-slate-900">Buat sesi ujian</h2>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('admin.sessions.store') }}" class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            @csrf
            <div class="border-b border-slate-200 bg-gradient-to-r from-indigo-50 via-white to-violet-50 px-8 py-6">
                <h3 class="text-xl font-bold text-slate-900">Detail sesi</h3>
                <p class="mt-1 text-sm text-slate-600">Atur judul, durasi, dan pengaturan anti-kecurangan.</p>
            </div>

            <div class="grid gap-6 p-8 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="title" class="mb-2 block text-sm font-medium text-slate-700">Judul sesi</label>
                    <input id="title" name="title" type="text" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-indigo-500 focus:outline-none" placeholder="Contoh: Ujian Tengah Semester">
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="mb-2 block text-sm font-medium text-slate-700">Deskripsi</label>
                    <textarea id="description" name="description" rows="4" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-indigo-500 focus:outline-none" placeholder="Tuliskan informasi umum sesi ujian..."></textarea>
                </div>

                <div>
                    <label for="duration_minutes" class="mb-2 block text-sm font-medium text-slate-700">Durasi (menit)</label>
                    <input id="duration_minutes" name="duration_minutes" type="number" min="5" max="300" required value="30" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label for="tab_switch_limit" class="mb-2 block text-sm font-medium text-slate-700">Batas pindah tab</label>
                    <input id="tab_switch_limit" name="tab_switch_limit" type="number" min="1" max="10" required value="3" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-indigo-500 focus:outline-none">
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50 px-8 py-5">
                <a href="{{ route('admin.dashboard') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100">Batal</a>
                <button type="submit" class="rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/20 hover:from-indigo-500 hover:to-violet-500">Simpan sesi</button>
            </div>
        </form>
    </div>
</x-app-layout>
