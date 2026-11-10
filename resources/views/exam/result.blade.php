<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Hasil Ujian</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-950 text-slate-100 antialiased">
        <div class="mx-auto max-w-5xl px-6 py-16">
            <div class="rounded-[32px] border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-900 to-indigo-950/60 p-8 shadow-2xl shadow-indigo-950/30">
                <div class="text-center">
                    <span class="inline-flex rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-300">Ujian selesai</span>
                    <h1 class="mt-5 text-4xl font-black text-white">{{ $examAttempt->examSession->title }}</h1>
                    <p class="mt-3 text-slate-300">Peserta: <span class="font-semibold text-white">{{ $examAttempt->user_name }}</span></p>
                </div>

                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-5 text-left">
                        <p class="text-sm text-slate-400">Skor</p>
                        <p class="mt-2 text-3xl font-bold text-white">{{ $examAttempt->score }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-5 text-left">
                        <p class="text-sm text-slate-400">Status</p>
                        <p class="mt-2 text-lg font-bold text-emerald-300">{{ $examAttempt->status }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-5 text-left">
                        <p class="text-sm text-slate-400">Tab switch</p>
                        <p class="mt-2 text-lg font-bold text-white">{{ $examAttempt->tab_switch_count }}</p>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                    <a href="{{ route('home') }}" class="inline-flex rounded-xl bg-indigo-500 px-6 py-3 text-sm font-semibold text-white transition hover:bg-indigo-400">Kembali ke daftar sesi</a>
                    <a href="{{ route('dashboard') }}" class="inline-flex rounded-xl border border-slate-700 px-6 py-3 text-sm font-semibold text-slate-200 transition hover:border-slate-500 hover:bg-slate-800">Ke dashboard admin</a>
                </div>
            </div>
        </div>
    </body>
</html>
