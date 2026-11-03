<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $preset->name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            color: #1f2937;
            font-family: Arial, sans-serif;
        }

        body {
            background: #e5e7eb;
        }

        .sheet {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 210mm;
            min-height: 297mm;
            margin: 16px auto;
            padding: 24mm 22mm;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 12px 35px rgb(15 23 42 / 14%);
        }

        .ink {
            color: #193b63;
        }

        .muted {
            color: #64748b;
        }

        .accent {
            color: #a66b35;
        }

        .cover {
            align-items: center;
            text-align: center;
        }

        .cover-modern {
            border-left: 12mm solid #193b63;
        }

        .cover-classic::before,
        .cover-classic::after {
            position: absolute;
            content: "";
            pointer-events: none;
        }

        .cover-classic::before {
            inset: 12mm;
            border: 2px solid #193b63;
        }

        .cover-classic::after {
            inset: 15mm;
            border: 1px solid #c8a86a;
        }

        .cover-logo {
            width: 34mm;
            height: 34mm;
            margin: 12mm auto 8mm;
            object-fit: contain;
        }

        .cover-school {
            margin: 0;
            color: #193b63;
            font-size: 19pt;
            font-weight: 700;
            letter-spacing: .04em;
        }

        .cover-kicker {
            margin-top: 37mm;
            color: #64748b;
            font-size: 10pt;
            font-weight: 700;
            letter-spacing: .2em;
            text-transform: uppercase;
        }

        .cover-title {
            max-width: 150mm;
            margin: 8mm auto;
            color: #193b63;
            font-family: Georgia, serif;
            font-size: 26pt;
            line-height: 1.25;
        }

        .cover-modern .cover-title,
        .cover-classic .cover-title {
            font-family: Arial, sans-serif;
        }

        .cover-date {
            margin-top: 8mm;
            color: #64748b;
            font-size: 11pt;
        }

        .cover-bottom {
            width: 100%;
            margin-top: auto;
            padding-top: 20mm;
        }

        .cover-bottom::before {
            display: block;
            width: 65mm;
            height: 2px;
            margin: 0 auto 7mm;
            background: #a66b35;
            content: "";
        }

        .cover-subtitle {
            margin: 0;
            color: #193b63;
            font-size: 14pt;
            font-weight: 700;
        }

        .cover-author {
            margin-top: 4mm;
            color: #64748b;
            font-size: 10pt;
        }

        .header {
            padding-top: 16mm;
        }

        .logo-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12mm;
            min-height: 26mm;
        }

        .logo-row img {
            width: 23mm;
            height: 23mm;
            object-fit: contain;
        }

        .header-school {
            margin: 8mm 0 2mm;
            color: #193b63;
            font-size: 17pt;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
        }

        .header-letterhead,
        .header-details {
            margin: 1.5mm 0;
            text-align: center;
        }

        .header-letterhead {
            color: #a66b35;
            font-size: 10pt;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .header-details {
            color: #475569;
            font-size: 9pt;
        }

        .header-rule {
            height: 3px;
            margin-top: 7mm;
            background: #193b63;
        }

        .header-three-logos .header-rule,
        .header-four-logos .header-rule {
            height: 5px;
            border-top: 1px solid #c8a86a;
            background: #193b63;
            background-clip: content-box;
        }

        .body-template {
            padding-top: 25mm;
        }

        .section-heading {
            margin: 0;
            color: #193b63;
            font-family: Georgia, serif;
            font-size: 22pt;
        }

        .body-editorial .section-heading {
            color: #a66b35;
            font-style: italic;
        }

        .body-minimal .section-heading {
            font-family: Arial, sans-serif;
            font-size: 19pt;
        }

        .section-rule {
            height: 2px;
            margin: 7mm 0 10mm;
            background: #c8a86a;
        }

        .body-paragraph,
        .appendix-description {
            color: #293548;
            font-family: Georgia, serif;
            font-size: 12pt;
            line-height: 1.8;
            white-space: pre-line;
        }

        .body-minimal .body-paragraph,
        .body-editorial .body-paragraph {
            font-family: Arial, sans-serif;
        }

        figure {
            margin: 12mm auto 0;
            text-align: center;
        }

        figure img {
            display: block;
            max-width: 100%;
            max-height: 105mm;
            margin: 0 auto;
            object-fit: contain;
        }

        figcaption {
            margin-top: 3mm;
            color: #64748b;
            font-size: 9pt;
            font-style: italic;
        }

        .appendix-split-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: start;
            gap: 12mm;
            margin-top: 14mm;
        }

        .appendix-split-layout figure {
            margin: 0;
        }

        .appendix-full-layout {
            text-align: center;
        }

        .appendix-full-layout figure img {
            max-height: 175mm;
        }

        .appendix-grid-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10mm 8mm;
            margin-top: 12mm;
        }

        .appendix-grid-layout figure {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            min-height: 84mm;
            margin: 0;
            padding: 4mm;
            border: 1px solid #e2e8f0;
        }

        .appendix-grid-layout figure img {
            width: 100%;
            height: 65mm;
            object-fit: contain;
        }

        .footer-template {
            justify-content: flex-end;
            padding-bottom: 18mm;
        }

        .footer-line {
            height: 2px;
            margin-bottom: 4mm;
            background: #193b63;
        }

        .footer-modern .footer-line {
            height: 3px;
            background: linear-gradient(90deg, #193b63 70%, #c8a86a 70%);
        }

        .footer-copy {
            color: #64748b;
            font-size: 9pt;
        }

        .page-number {
            position: absolute;
            right: 22mm;
            bottom: 12mm;
            color: #64748b;
            font-size: 9pt;
        }

        .page-number::after {
            content: "Halaman 1";
        }

        .signature-template {
            align-items: flex-end;
            justify-content: flex-end;
            padding-bottom: 32mm;
        }

        .signature-date {
            margin-bottom: 8mm;
            color: #475569;
            text-align: right;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20mm;
            width: 100%;
        }

        .signature-person {
            text-align: center;
        }

        .signature-title {
            min-height: 8mm;
            color: #475569;
            font-size: 10pt;
        }

        .signature-image {
            display: block;
            width: 42mm;
            height: 24mm;
            margin: 2mm auto;
            object-fit: contain;
        }

        .signature-space {
            height: 24mm;
        }

        .signature-name {
            margin: 0;
            color: #193b63;
            font-size: 11pt;
            font-weight: 700;
            text-decoration: underline;
        }

        .signature-number {
            margin: 1mm 0 0;
            color: #64748b;
            font-size: 9pt;
        }

        @media print {
            body {
                background: #fff;
            }

            .sheet {
                width: 210mm;
                min-height: 297mm;
                margin: 0;
                box-shadow: none;
                page-break-after: always;
            }

            .page-number::after {
                content: "Halaman " counter(page);
            }
        }

        @media screen and (max-width: 760px) {
            .sheet {
                width: 100%;
                min-height: 100vh;
                margin: 0;
                padding: 16mm 10mm;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    @if ($template['section'] === 'cover')
        <main class="sheet cover cover-{{ $template['style'] }}">
            @if (! empty($images['logo']))
                <img class="cover-logo" src="{{ $images['logo'] }}" alt="Logo sekolah">
            @endif
            <p class="cover-school">{{ $data['school_name'] ?? '' }}</p>
            <p class="cover-kicker">Laporan Perlombaan</p>
            <h1 class="cover-title">{{ $data['title'] ?? '' }}</h1>
            <p class="cover-date">{{ $data['date'] ?? '' }}</p>
            <div class="cover-bottom">
                <p class="cover-subtitle">{{ $data['subtitle'] ?? '' }}</p>
                <p class="cover-author">{{ $data['author'] ?? '' }}</p>
            </div>
        </main>
    @elseif ($template['section'] === 'header')
        @php
            $logoCount = match ($template['style']) {
                'two-logos' => 2,
                'three-logos' => 3,
                default => 4,
            };
        @endphp
        <main class="sheet header header-{{ $template['style'] }}">
            <div class="logo-row">
                @for ($logoIndex = 1; $logoIndex <= $logoCount; $logoIndex++)
                    @if (! empty($images["logo{$logoIndex}"]))
                        <img src="{{ $images["logo{$logoIndex}"] }}" alt="Logo {{ $logoIndex }}">
                    @endif
                @endfor
            </div>
            <p class="header-letterhead">{{ $data['letterhead'] ?? '' }}</p>
            <h1 class="header-school">{{ $data['school_name'] ?? '' }}</h1>
            <p class="header-details">{{ $data['address'] ?? '' }}</p>
            <p class="header-details">{{ $data['street'] ?? '' }}</p>
            <p class="header-details">{{ $data['contact'] ?? '' }}</p>
            <div class="header-rule"></div>
        </main>
    @elseif ($template['section'] === 'body')
        <main class="sheet body-template body-{{ $template['style'] }}">
            <h1 class="section-heading">{{ $data['heading'] ?? '' }}</h1>
            <div class="section-rule"></div>

            @if ($template['style'] === 'split')
                <div class="appendix-split-layout">
                    <p class="appendix-description">{{ $data['description'] ?? '' }}</p>
                    <figure>
                        @if (! empty($images['image1']))
                            <img src="{{ $images['image1'] }}" alt="{{ $data['caption1'] ?? 'Gambar lampiran' }}">
                        @endif
                        <figcaption>{{ $data['caption1'] ?? '' }}</figcaption>
                    </figure>
                </div>
            @elseif ($template['style'] === 'full')
                <div class="appendix-full-layout">
                    <p class="appendix-description">{{ $data['description'] ?? '' }}</p>
                    <figure>
                        @if (! empty($images['image1']))
                            <img src="{{ $images['image1'] }}" alt="{{ $data['caption1'] ?? 'Gambar lampiran' }}">
                        @endif
                        <figcaption>{{ $data['caption1'] ?? '' }}</figcaption>
                    </figure>
                </div>
            @elseif ($template['style'] === 'grid')
                <div class="appendix-grid-layout">
                    @for ($imageIndex = 1; $imageIndex <= 4; $imageIndex++)
                        <figure>
                            @if (! empty($images["image{$imageIndex}"]))
                                <img src="{{ $images["image{$imageIndex}"] }}" alt="{{ $data["caption{$imageIndex}"] ?? "Gambar {$imageIndex}" }}">
                            @endif
                            <figcaption>{{ $data["caption{$imageIndex}"] ?? '' }}</figcaption>
                        </figure>
                    @endfor
                </div>
            @else
                <p class="body-paragraph">{{ $data['paragraph'] ?? '' }}</p>
                @if (! empty($images['image']))
                    <figure>
                        <img src="{{ $images['image'] }}" alt="{{ $data['caption'] ?? 'Gambar pendukung' }}">
                        <figcaption>{{ $data['caption'] ?? '' }}</figcaption>
                    </figure>
                @endif
            @endif
        </main>
    @elseif ($template['section'] === 'footer')
        <main class="sheet footer-template footer-{{ $template['style'] }}">
            <div class="footer-line"></div>
            <p class="footer-copy">{{ $data['footer_text'] ?? '' }}</p>
            <span class="page-number" aria-label="Nomor halaman"></span>
        </main>
    @else
        <main class="sheet signature-template">
            <p class="signature-date">{{ $data['city_date'] ?? '' }}</p>
            <div class="signature-grid">
                @for ($signerIndex = 1; $signerIndex <= ($template['style'] === 'dual' ? 2 : 1); $signerIndex++)
                    @php
                        $prefix = $signerIndex === 1 ? '' : 'second_';
                        $signatureImageKey = $signerIndex === 1 ? 'signature_image' : 'second_signature_image';
                    @endphp
                    <section class="signature-person">
                        <p class="signature-title">{{ $data["{$prefix}signer_title"] ?? '' }}</p>
                        @if (! empty($images[$signatureImageKey]))
                            <img class="signature-image" src="{{ $images[$signatureImageKey] }}" alt="Tanda tangan">
                        @else
                            <div class="signature-space"></div>
                        @endif
                        <p class="signature-name">{{ $data["{$prefix}signer_name"] ?? '' }}</p>
                        <p class="signature-number">{{ $data["{$prefix}signer_number"] ?? '' }}</p>
                    </section>
                @endfor
            </div>
        </main>
    @endif
</body>
</html>
