@extends('layouts.app')

@section('content')
    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold">Tambah Produk</h3>
        <p class="mt-1 text-sm text-slate-600">Lengkapi data utama produk.</p>

        <form method="POST" action="{{ route('products.store') }}" class="mt-6">
            @include('products._form')
        </form>
    </section>
@endsection
