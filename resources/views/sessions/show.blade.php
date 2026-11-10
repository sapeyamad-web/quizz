<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $examSession->title }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-5xl px-6 py-12">
            <div class="rounded-3xl border border-slate-800 bg-slate-900/70 p-8 shadow-2xl shadow-indigo-950/20">
                <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-sm font-medium uppercase tracking-[0.2em] text-indigo-300">Sesi ujian</p>
                        <h1 class="mt-3 text-3xl font-bold text-white">{{ $examSession->title }}</h1>
                    </div>
                    <div class="rounded-2xl border border-slate-700 bg-slate-950/80 px-5 py-3 text-sm text-slate-300">
                        <span class="font-semibold text-white">{{ $examSession->questions->count() }}</span> soal • <span class="font-semibold text-white">{{ $examSession->duration_minutes }}</span> menit • <span class="font-semibold text-white">{{ $examSession->tab_switch_limit }}</span>x batas tab
                    </div>
                </div>

                <p class="mt-5 text-base text-slate-300">{{ $examSession->description ?: 'Sesi ujian ini tidak memiliki deskripsi tambahan.' }}</p>

                <form method="POST" action="{{ route('sessions.start', $examSession) }}" class="mt-8">
                    @csrf
                    <label for="user_name" class="mb-2 block text-sm font-medium text-slate-200">Nama peserta</label>
                    <input id="user_name" name="user_name" type="text" required value="{{ old('user_name') }}" class="w-full rounded-2xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-500 focus:border-indigo-500 focus:outline-none" placeholder="Masukkan nama Anda">
                    <button type="submit" class="mt-6 inline-flex rounded-xl bg-indigo-500 px-6 py-3 text-base font-semibold text-white transition hover:bg-indigo-400">Mulai ujian</button>
                </form>
            </div>
        </div>
    </body>
</html>
