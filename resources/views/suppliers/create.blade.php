@extends('layouts.app')

@section('page_title', 'Tambah Supplier')
@section('page_subtitle', 'Tambahkan supplier baru beserta kontak utama dan status aktifnya.')

@section('content')
    <div class="mx-auto max-w-4xl">
        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <form action="{{ route('suppliers.store') }}" method="POST" class="space-y-6">
                @csrf

                @include('suppliers._form')

                <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-end">
                    <a href="{{ route('suppliers.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                        Simpan Supplier
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
