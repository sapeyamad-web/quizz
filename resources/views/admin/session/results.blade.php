<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-indigo-600">Hasil ujian</p>
                <h2 class="mt-2 text-2xl font-bold text-slate-900">{{ $examSession->title }}</h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.sessions.show', $examSession) }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Kembali</a>
                <a href="{{ route('admin.sessions.results.export', $examSession) }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-500">Export Excel (CSV)</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-6 grid gap-4 md:grid-cols-3">
            <div class="rounded-3xl border border-indigo-100 bg-gradient-to-br from-indigo-50 to-white p-5 shadow-sm">
                <p class="text-sm text-indigo-600">Total peserta</p>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $attempts->count() }}</p>
            </div>
            <div class="rounded-3xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm">
                <p class="text-sm text-emerald-600">Rata-rata skor</p>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $attempts->isEmpty() ? 0 : round($attempts->avg('score'), 1) }}</p>
            </div>
            <div class="rounded-3xl border border-amber-100 bg-gradient-to-br from-amber-50 to-white p-5 shadow-sm">
                <p class="text-sm text-amber-600">Skor tertinggi</p>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $attempts->max('score') ?? 0 }}</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                <h3 class="text-lg font-bold text-slate-900">Daftar hasil peserta</h3>
            </div>

            @if ($attempts->isEmpty())
                <div class="p-8 text-center text-sm text-slate-500">Belum ada peserta yang menyelesaikan ujian ini.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 font-semibold">No</th>
                                <th class="px-5 py-3 font-semibold">Nama peserta</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                                <th class="px-5 py-3 font-semibold">Skor</th>
                                <th class="px-5 py-3 font-semibold">Tab switch</th>
                                <th class="px-5 py-3 font-semibold">Mulai</th>
                                <th class="px-5 py-3 font-semibold">Selesai</th>
                                <th class="px-5 py-3 font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($attempts as $index => $attempt)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="px-5 py-3 font-medium text-slate-600">{{ $index + 1 }}</td>
                                    <td class="px-5 py-3">
                                        <div class="font-semibold text-slate-900">{{ $attempt->user_name }}</div>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="rounded-full {{ $attempt->status === 'submitted' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }} px-2.5 py-1 text-xs font-semibold">
                                            {{ $attempt->status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 font-bold text-slate-900">{{ $attempt->score ?? 0 }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ $attempt->tab_switch_count ?? 0 }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ $attempt->started_at?->format('d M Y, H:i') ?? '-' }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ $attempt->submitted_at?->format('d M Y, H:i') ?? '-' }}</td>
                                    <td class="px-5 py-3">
                                        <form method="POST" action="{{ route('admin.sessions.attempts.destroy', [$examSession, $attempt]) }}" onsubmit="return confirm('Hapus hasil ujian peserta ini? Jawaban peserta juga akan dihapus.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-50">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
