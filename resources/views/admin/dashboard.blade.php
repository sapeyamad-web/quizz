<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-indigo-600">Overview</p>
                <h2 class="mt-2 text-2xl font-bold text-slate-900">Dashboard admin</h2>
                <p class="mt-1 text-sm text-slate-600">Kelola sesi, soal, dan hasil ujian dalam satu dashboard premium.</p>
            </div>
            <a href="{{ route('admin.sessions.create') }}" class="rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:from-indigo-500 hover:to-violet-500">+ Buat sesi baru</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-6 md:grid-cols-3">
            <div class="rounded-3xl border border-indigo-100 bg-gradient-to-br from-indigo-50 via-white to-indigo-100 p-5 shadow-sm">
                <p class="text-sm text-indigo-600">Total sesi</p>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $sessions->count() }}</p>
            </div>
            <div class="rounded-3xl border border-emerald-100 bg-gradient-to-br from-emerald-50 via-white to-emerald-100 p-5 shadow-sm">
                <p class="text-sm text-emerald-600">Total soal</p>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $sessions->sum('questions_count') }}</p>
            </div>
            <div class="rounded-3xl border border-amber-100 bg-gradient-to-br from-amber-50 via-white to-amber-100 p-5 shadow-sm">
                <p class="text-sm text-amber-600">Total peserta</p>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $sessions->sum('attempts_count') }}</p>
            </div>
        </div>

        <div class="mt-10 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-6 py-4">
                <h3 class="text-xl font-bold text-slate-900">Daftar sesi ujian</h3>
                <span class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-medium text-slate-700">{{ $sessions->count() }} aktif</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Judul</th>
                            <th class="px-5 py-3 font-semibold">Durasi</th>
                            <th class="px-5 py-3 font-semibold">Soal</th>
                            <th class="px-5 py-3 font-semibold">Peserta</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($sessions as $session)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-900">{{ $session->title }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ Str::limit($session->description ?? 'Tidak ada deskripsi', 60) }}</div>
                                </td>
                                <td class="px-5 py-4">{{ $session->duration_minutes }} menit</td>
                                <td class="px-5 py-4">{{ $session->questions_count }}</td>
                                <td class="px-5 py-4">{{ $session->attempts_count }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full {{ $session->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }} px-2.5 py-1 text-xs font-semibold">
                                        {{ $session->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.sessions.show', $session) }}" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-700">Detail</a>
                                        <a href="{{ route('admin.sessions.results', $session) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">Hasil</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
