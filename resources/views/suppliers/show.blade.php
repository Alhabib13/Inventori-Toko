@extends('layouts.app')

@section('page_title', 'Detail Supplier')
@section('page_subtitle', 'Lihat informasi supplier, kontak utama, alamat, dan status aktifnya.')

@section('page_actions')
    <a href="{{ route('suppliers.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
        Kembali
    </a>
    <a href="{{ route('suppliers.edit', $supplier) }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
        Edit Supplier
    </a>
@endsection

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.85fr_1.15fr]">
        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-slate-900">{{ $supplier->nama_supplier }}</h2>
            <div class="mt-4">
                <span class="inline-flex items-center gap-2 rounded-full {{ $supplier->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }} px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em]">
                    <span class="h-2 w-2 rounded-full {{ $supplier->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                    {{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
        </section>

        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Informasi Supplier</h3>
            <dl class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 p-4">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Nama Kontak</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $supplier->nama_kontak }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Telepon</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $supplier->telepon }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 p-4 sm:col-span-2">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Alamat</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $supplier->alamat }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 p-4 sm:col-span-2">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Keterangan</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $supplier->keterangan ?: '-' }}</dd>
                </div>
            </dl>
        </section>
    </div>
@endsection
