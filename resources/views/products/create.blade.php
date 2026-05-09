@extends('layouts.app')

@section('content')
    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold">Tambah Produk</h3>
        <p class="mt-1 text-sm text-slate-600">Lengkapi data utama produk.</p>

        @if ($categories->isEmpty() || $suppliers->isEmpty())
            <div class="mt-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-700">
                Produk membutuhkan kategori dan supplier aktif. Tambahkan dulu data master kategori dan supplier.
            </div>
        @endif

        <form method="POST" action="{{ route('products.store') }}" class="mt-6">
            @include('products._form')
        </form>
    </section>
@endsection
