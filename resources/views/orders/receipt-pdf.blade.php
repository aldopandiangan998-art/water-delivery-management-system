<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Struk #{{ $order->order_number }}</title>
    <style>
        @page {
            margin: 4mm 5mm;
            size: 80mm 200mm;
        }
        body {
            font-family: 'Courier New', Courier, monospace, 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            line-height: 1.25;
            color: #111;
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #555;
            margin: 6px 0;
        }
        .double-divider {
            border-top: 2px dashed #222;
            margin: 6px 0;
        }
        .store-title {
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .store-sub {
            font-size: 7.5pt;
            color: #444;
            margin-bottom: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td, th {
            padding: 2px 0;
            vertical-align: top;
        }
        .meta-table td {
            font-size: 8pt;
        }
        .item-table td {
            font-size: 8.5pt;
        }
        .item-desc {
            font-size: 7.5pt;
            color: #555;
        }
        .total-table td {
            font-size: 8.5pt;
            padding: 1.5px 0;
        }
        .grand-total {
            font-size: 10.5pt;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8pt;
            font-weight: bold;
            border: 1px solid #111;
            text-transform: uppercase;
        }
        .footer {
            margin-top: 10px;
            font-size: 7.5pt;
            text-align: center;
            color: #444;
        }
    </style>
</head>
<body>
    <div class="text-center">
        <div class="store-title">{{ config('app.name', 'DEPOT AIR MINUM') }}</div>
        <div class="store-sub">Layanan Antar Air Minum Higienis & Cepat</div>
        <div class="store-sub">Telp/WA: {{ config('app.contact_phone', '0812-3456-7890') }}</div>
    </div>

    <div class="double-divider"></div>

    <table class="meta-table">
        <tr>
            <td style="width: 35%;">No. Struk</td>
            <td style="width: 5%;">:</td>
            <td class="font-bold">{{ $order->order_number }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>:</td>
            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Pelanggan</td>
            <td>:</td>
            <td>{{ $order->recipient_name }}</td>
        </tr>
        <tr>
            <td>No. HP</td>
            <td>:</td>
            <td>{{ $order->recipient_phone ?? '-' }}</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>:</td>
            <td>
                {{ $order->delivery_address_text }}
                @if($order->delivery_benchmark)
                    <div class="item-desc">Patokan: {{ $order->delivery_benchmark }}</div>
                @endif
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="item-table">
        <thead>
            <tr style="border-bottom: 1px dashed #777;">
                <th class="text-left font-bold" style="width: 50%;">Produk</th>
                <th class="text-center font-bold" style="width: 15%;">Qty</th>
                <th class="text-right font-bold" style="width: 35%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td colspan="3" class="font-bold" style="padding-top: 4px;">
                        {{ $item->product_name }}
                    </td>
                </tr>
                <tr>
                    <td class="item-desc">
                        @ {{ number_format($item->price, 0, ',', '.') }}
                        @if($item->is_exchange)
                            (Tukar Galon)
                        @else
                            (Beli Galon Baru)
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right font-bold">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <table class="total-table">
        <tr>
            <td>Subtotal Barang</td>
            <td class="text-right">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Ongkos Kirim</td>
            <td class="text-right">
                @if($order->delivery_fee > 0)
                    Rp {{ number_format($order->delivery_fee, 0, ',', '.') }}
                @else
                    GRATIS
                @endif
            </td>
        </tr>
        @if($order->discount > 0)
            <tr>
                <td>Diskon</td>
                <td class="text-right">- Rp {{ number_format($order->discount, 0, ',', '.') }}</td>
            </tr>
        @endif
        <tr class="grand-total" style="border-top: 1px dashed #333; border-bottom: 1px dashed #333;">
            <td style="padding: 4px 0;">TOTAL AKHIR</td>
            <td class="text-right" style="padding: 4px 0;">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div style="margin-top: 8px;">
        <table class="meta-table">
            <tr>
                <td style="width: 45%;">Metode Bayar</td>
                <td style="width: 5%;">:</td>
                <td class="font-bold">
                    {{ strtoupper($order->payment_method ?? 'COD / TUNAI') }}
                </td>
            </tr>
            <tr>
                <td>Status Bayar</td>
                <td>:</td>
                <td>
                    @if($order->payment_status === 'paid')
                        <span class="badge">LUNAS</span>
                    @else
                        <span class="badge">BAYAR DI TEMPAT</span>
                    @endif
                </td>
            </tr>
            @if($order->empty_gallons_due > 0)
                <tr>
                    <td>Galon Kosong Wajib</td>
                    <td>:</td>
                    <td class="font-bold">{{ $order->empty_gallons_due }} Galon</td>
                </tr>
            @endif
        </table>
    </div>

    @if($order->customer_notes)
        <div class="divider"></div>
        <div style="font-size: 7.5pt; font-style: italic;">
            Catatan: {{ $order->customer_notes }}
        </div>
    @endif

    <div class="double-divider"></div>

    <div class="footer">
        <div class="font-bold">TERIMA KASIH ATAS PESANAN ANDA</div>
        <div>Simpan struk ini sebagai bukti transaksi resmi.</div>
        <div style="margin-top: 4px;">Dicetak: {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>
</body>
</html>
