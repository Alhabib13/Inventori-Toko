<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $sectionTitle }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #0f172a; margin: 32px; }
        h1 { font-size: 22px; margin-bottom: 6px; }
        p { margin: 0 0 16px; color: #475569; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #cbd5e1; padding: 10px; font-size: 12px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; text-transform: uppercase; letter-spacing: .04em; font-size: 11px; }
        .summary { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-top: 20px; }
        .card { border: 1px solid #cbd5e1; padding: 12px; border-radius: 8px; }
        .label { font-size: 11px; text-transform: uppercase; color: #64748b; margin-bottom: 6px; }
        .value { font-size: 18px; font-weight: 700; color: #0f172a; }
        @media print { body { margin: 16px; } }
    </style>
</head>
<body onload="window.print()">
    <h1>{{ $sectionTitle }}</h1>
    <p>Periode: {{ $periodLabel }}</p>

    @if ($section === 'sales')
        <div class="summary">
            <div class="card">
                <div class="label">Total Penjualan</div>
                <div class="value">Rp{{ number_format($salesTotal, 0, ',', '.') }}</div>
            </div>
            <div class="card">
                <div class="label">Jumlah Transaksi</div>
                <div class="value">{{ $sales->count() }}</div>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Kasir</th>
                    <th>Tanggal</th>
                    <th>Total Item</th>
                    <th>Metode</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sales as $sale)
                    <tr>
                        <td>{{ $sale->kode_transaksi }}</td>
                        <td>{{ $sale->kasir?->name ?? '-' }}</td>
                        <td>{{ $sale->tanggal_transaksi?->format('d/m/Y H:i') }}</td>
                        <td>{{ $sale->total_item }}</td>
                        <td>{{ $sale->metode_pembayaran ?? '-' }}</td>
                        <td>Rp{{ number_format((float) $sale->total_bayar, 0, ',', '.') }}</td>
                        <td>{{ ucfirst($sale->status) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">Belum ada transaksi penjualan pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif ($section === 'purchases')
        <div class="summary">
            <div class="card">
                <div class="label">Total Pembelian</div>
                <div class="value">Rp{{ number_format($purchaseTotal, 0, ',', '.') }}</div>
            </div>
            <div class="card">
                <div class="label">Jumlah Pembelian</div>
                <div class="value">{{ $purchases->count() }}</div>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Supplier</th>
                    <th>Dicatat Oleh</th>
                    <th>Tanggal</th>
                    <th>Subtotal</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->kode_pembelian }}</td>
                        <td>{{ $purchase->supplier?->nama_supplier ?? '-' }}</td>
                        <td>{{ $purchase->pengguna?->name ?? '-' }}</td>
                        <td>{{ $purchase->tanggal_pembelian?->format('d/m/Y H:i') }}</td>
                        <td>Rp{{ number_format((float) $purchase->subtotal, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format((float) $purchase->total_bayar, 0, ',', '.') }}</td>
                        <td>{{ ucfirst($purchase->status) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">Belum ada pembelian pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif ($section === 'stock')
        <div class="summary">
            <div class="card">
                <div class="label">Nilai Stok</div>
                <div class="value">Rp{{ number_format($stockValue, 0, ',', '.') }}</div>
            </div>
            <div class="card">
                <div class="label">Jumlah Produk</div>
                <div class="value">{{ $stockProducts->count() }}</div>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Supplier</th>
                    <th>Stok</th>
                    <th>Stok Minimum</th>
                    <th>Nilai Modal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stockProducts as $product)
                    <tr>
                        <td>{{ $product->nama_produk }}</td>
                        <td>{{ $product->kategori?->nama_kategori ?? '-' }}</td>
                        <td>{{ $product->supplier?->nama_supplier ?? '-' }}</td>
                        <td>{{ $product->stok }} {{ $product->satuan }}</td>
                        <td>{{ $product->stok_minimum }} {{ $product->satuan }}</td>
                        <td>Rp{{ number_format($product->stok * (float) $product->harga_beli, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Belum ada data stok produk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <div class="summary">
            <div class="card">
                <div class="label">Pendapatan</div>
                <div class="value">Rp{{ number_format($revenue, 0, ',', '.') }}</div>
            </div>
            <div class="card">
                <div class="label">Modal</div>
                <div class="value">Rp{{ number_format($capital, 0, ',', '.') }}</div>
            </div>
            <div class="card">
                <div class="label">Keuntungan</div>
                <div class="value">Rp{{ number_format($grossProfit, 0, ',', '.') }}</div>
            </div>
            <div class="card">
                <div class="label">Margin</div>
                <div class="value">{{ number_format($margin, 1, ',', '.') }}%</div>
            </div>
        </div>
    @endif
</body>
</html>
