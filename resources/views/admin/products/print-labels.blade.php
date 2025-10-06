<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Label Produk</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .label {
            width: 250px;
            height: 150px;
            border: 1px solid #000;
            border-radius: 6px;
            margin: 10px;
            padding: 8px;
            display: inline-block;
            text-align: center;
            page-break-inside: avoid;
        }
        .label-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .label-header .logo {
            flex: 1;                /* ambil full width */
            text-align: center;     /* center text & gambar */
        }
        .label-header .logo img {
            display: inline-block;  /* biar bisa ikut center */
            max-width: 350px;
            max-height: 35px;
            padding-top: 20px;
        }

        .label-header .qr img {
            width: 80px;
            height: 80px;
        }
        .barcode {
            width: 100%;
            height: 60px; /* lebih proporsional */
            margin: 8px 0 4px 0;
        }
    </style>
</head>
<body>
    <div class="labels">
        @foreach($products as $product)
            <div class="label">
                <div class="label-header">
                    <div class="logo">
                        <img src="{{ asset('img/logo_be2.png') }}" alt="Logo">
                    </div>
                    <div class="qr">
                        @if($product->qr_code)
                            <img src="{{ 'https://stesy.beacontelemetry.com/product/qr_code/'. $product->qr_code }}"
                                 alt="QR Code">
                        @endif
                    </div>
                </div>

                {{-- Barcode dari serial number --}}
                <svg class="barcode"
                     jsbarcode-format="CODE128"
                     jsbarcode-value="{{ $product->serial_number ?? '000000' }}"
                     jsbarcode-textmargin="0"
                     jsbarcode-fontsize="24"
                     jsbarcode-height="50">
                </svg>
            </div>
        @endforeach
    </div>

    {{-- Load JsBarcode --}}
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script>
        window.onload = function() {
            JsBarcode(".barcode").init();
            window.print();
        };
    </script>
</body>
</html>
