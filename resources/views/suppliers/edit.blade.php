@extends('layouts.app')

@section('page_title', 'Edit Supplier')
@section('page_subtitle', 'Perbarui informasi supplier, kontak, dan status aktif/nonaktif.')

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.72fr_1.28fr]">
        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Ringkasan Supplier</h2>
            <div class="mt-5 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Nama Supplier</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $supplier->nama_supplier }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Kontak Saat Ini</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $supplier->nama_kontak }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $supplier->telepon }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <div class="mb-6 border-b border-slate-200 pb-5">
                <h2 class="text-lg font-semibold text-slate-900">Perbarui Data Supplier</h2>
                <p class="mt-1 text-sm text-slate-500">Sesuaikan kontak, alamat, dan status supplier agar data pembelian tetap akurat.</p>
            </div>
            <form action="{{ route('suppliers.update', $supplier) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                @include('suppliers._form', ['supplier' => $supplier])

                <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-end">
                    <a href="{{ route('suppliers.show', $supplier) }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
