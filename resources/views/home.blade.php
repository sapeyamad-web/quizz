<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Exam Portal</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-950 text-slate-100 antialiased">
        <div class="relative isolate overflow-hidden bg-slate-950">
            <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top,_rgba(99,102,241,0.35),_transparent_35%),radial-gradient(circle_at_bottom_right,_rgba(16,185,129,0.2),_transparent_25%)]"></div>
            <header class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-500/20 ring-1 ring-indigo-400/50">
                        <span class="text-xl font-bold text-indigo-200">Q</span>
                    </div>
                    <div>
                        <p class="text-lg font-semibold">QuizCrewStore</p>
                        <p class="text-xs text-slate-400">Assessment platform</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-full border border-slate-700 px-4 py-2 text-sm text-slate-100 transition hover:border-indigo-400 hover:text-indigo-200">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-full border border-slate-700 px-4 py-2 text-sm text-slate-100 transition hover:border-indigo-400 hover:text-indigo-200">Login</a>
                    @endauth
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-6 pb-20 pt-8 lg:px-8">
                <section class="grid gap-10 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
                    <div>
                        <span class="inline-flex items-center rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-300">Modern exam management</span>
                        <h1 class="mt-6 text-4xl font-black tracking-tight text-white sm:text-5xl">Kerjakan dengan serius dan tepat.</h1>
                        <p class="mt-5 max-w-xl text-lg text-slate-300">Timer otomatis dilarang cheat karana jika keluar halaman sebanyak 3 kali, maka tes akan selesai otomatis.</p>
                        <div class="mt-8 flex flex-wrap gap-4">
                            <a href="#sessions" class="rounded-xl bg-indigo-500 px-6 py-3 font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400">Lihat sesi</a>
                            @auth
                               
                            @else
                              
                            @endauth
                        </div>
                        <div class="mt-10 grid max-w-lg grid-cols-3 gap-4">
    
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-800 bg-slate-900/80 p-6 shadow-2xl shadow-indigo-900/20">
                        <div class="mb-5 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-400">Ringkasan fitur</p>
                                <p class="text-xl font-semibold text-white">Sesi ujian</p>
                            </div>
                            <span class="rounded-full bg-indigo-500/15 px-3 py-1 text-xs font-medium text-indigo-200">Ready</span>
                        </div>
                        <ul class="space-y-4 text-sm text-slate-300">
                            <li class="flex gap-3 rounded-2xl border border-slate-800 bg-slate-950/50 p-3"><span class="mt-1 h-2.5 w-2.5 rounded-full bg-emerald-400"></span>Sesi TRX,10 soal dengan  durasi, 15 menit</li>
                            <li class="flex gap-3 rounded-2xl border border-slate-800 bg-slate-950/50 p-3"><span class="mt-1 h-2.5 w-2.5 rounded-full bg-sky-400"></span> Sesi prodak,15 soal dengan durasi, 15 menit</li>
                            <li class="flex gap-3 rounded-2xl border border-slate-800 bg-slate-950/50 p-3"><span class="mt-1 h-2.5 w-2.5 rounded-full bg-violet-400"></span> Sesi komputer, 20 soal dengan durasi, 30 menit</li>
                        </ul>
                    </div>
                </section>

                <section id="sessions" class="mt-20">
                    <div class="mb-8 flex items-end justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium uppercase tracking-[0.2em] text-indigo-300">Sesi tersedia</p>
                            <h2 class="mt-2 text-3xl font-bold text-white">Pilih ujian yang ingin dikerjakan</h2>
                        </div>
                    </div>

                    @if ($sessions->isEmpty())
                        <div class="rounded-3xl border border-dashed border-slate-700 bg-slate-900/50 p-10 text-center text-slate-400">
                            Belum ada sesi ujian yang dipublikasikan.
                        </div>
                    @else
                        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                            @foreach ($sessions as $session)
                                <article class="group rounded-3xl border border-slate-800 bg-slate-900/70 p-6 transition hover:-translate-y-1 hover:border-indigo-500/60 hover:shadow-xl hover:shadow-indigo-950/30">
                                    <div class="mb-4 flex items-center justify-between">
                                        <span class="rounded-full bg-emerald-500/15 px-2.5 py-1 text-xs font-semibold text-emerald-300">{{ $session->questions_count }} soal</span>
                                        <span class="text-xs text-slate-400">{{ $session->duration_minutes }} menit</span>
                                    </div>
                                    <h3 class="text-2xl font-bold text-white">{{ $session->title }}</h3>
                                    <p class="mt-3 line-clamp-4 min-h-[72px] text-sm text-slate-300">{{ $session->description ?: 'Tidak ada deskripsi untuk sesi ini.' }}</p>
                                    <div class="mt-6 flex items-center justify-between border-t border-slate-800 pt-4">
                                        <span class="text-xs text-slate-400">Batas pindah tab: {{ $session->tab_switch_limit }}x</span>
                                        <a href="{{ route('sessions.public', $session) }}" class="rounded-lg bg-indigo-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-400">Mulai tes</a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            </main>
        </div>
    </body>
</html>
