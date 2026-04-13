@extends('layouts.auth')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold">Login Aplikasi Inventori</h1>
            <p class="mt-2 text-sm text-slate-300">Form autentikasi untuk Admin Pemilik, Admin Gudang, dan Kasir.</p>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-red-400/30 bg-red-500/10 px-4 py-3 text-sm text-red-100">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.process') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="mb-2 block text-sm font-medium">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none" />
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-medium">Password</label>
                <input id="password" name="password" type="password" class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none" />
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-300">
                <input type="checkbox" name="remember" value="1" class="rounded border-white/10 bg-slate-900" />
                Ingat saya
            </label>

            <button type="submit" class="w-full rounded-xl bg-emerald-500 px-4 py-3 font-semibold text-slate-950">
                Masuk
            </button>
        </form>

        <div class="rounded-xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
            <p class="font-semibold">Akun admin demo</p>
            <p>Email: admin@inventori.test</p>
            <p>Password: admin12345</p>
            <p>Role: owner</p>
        </div>
    </div>
@endsection
