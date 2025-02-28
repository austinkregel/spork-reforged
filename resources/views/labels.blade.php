<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    <style>
        @media print {

            html, body {
                height:100%;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden;
            }

        }
    </style>
    @routes
</head>
<body class="font-sans antialiased w-full">

@foreach($assets as $asset)
<div class="w-64 overflow-hidden text-black">
    <div>{!! (new Picqer\Barcode\BarcodeGeneratorSVG)->getBarcode(base64_encode($asset->id), \Picqer\Barcode\BarcodeGeneratorSVG::TYPE_CODE_128_B, 1, 40) !!}</div>
</div>
@endforeach

</body>
</html>
