<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Devis {{ $devis->reference ?? sprintf('DEV-%05d', $devis->id) }}</title>
    
    <style>
        /* ============================================
           CONFIGURATION & POLICES
           ============================================ */
        
        @page {
            margin: 0;
            size: A4 portrait;
        }

        @font-face {
            font-family: 'Roboto';
            font-style: normal;
            font-weight: 400;
            src: url('{{ storage_path("fonts/Roboto-Regular.ttf") }}') format('truetype');
        }
        
        @font-face {
            font-family: 'Roboto';
            font-style: normal;
            font-weight: 700;
            src: url('{{ storage_path("fonts/Roboto-Bold.ttf") }}') format('truetype');
        }

        /* ============================================
           VARIABLES CSS (Inline pour PDF)
           ============================================ */
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.6;
            color: #1f2937;
            background: #ffffff;
        }

        /* ============================================
           LAYOUT STRUCTURE
           ============================================ */
        
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 0;
            margin: 0 auto;
            background: white;
            position: relative;
        }

        .content-wrapper {
            padding: 20mm 15mm 25mm 15mm;
        }

        /* ============================================
           HEADER - Bande bleue moderne
           ============================================ */
        
        .header-band {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            height: 15mm;
            width: 100%;
            position: relative;
        }

        .header-band::after {
            content: '';
            position: absolute;
            bottom: -8mm;
            right: 15mm;
            width: 0;
            height: 0;
            border-left: 40mm solid transparent;
            border-right: 0 solid transparent;
            border-top: 8mm solid #3b82f6;
            opacity: 0.3;
        }

        /* ============================================
           SECTION IDENTITÉ
           ============================================ */
        
        .identity-section {
            display: table;
            width: 100%;
            margin-top: -10mm;
            margin-bottom: 8mm;
        }

        .company-info,
        .document-info {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }

        .company-logo {
            background: white;
            padding: 4mm;
            border-radius: 2mm;
            box-shadow: 0 2mm 8mm rgba(0, 0, 0, 0.1);
            display: inline-block;
            margin-left: 15mm;
        }

        .company-logo img {
            height: 12mm;
            display: block;
        }

        .company-name {
            font-size: 16pt;
            font-weight: 700;
            color: #3b82f6;
            margin-bottom: 2mm;
        }

        .company-details {
            font-size: 9pt;
            color: #6b7280;
            line-height: 1.5;
        }

        .document-title {
            text-align: right;
            background: white;
            padding: 5mm;
            border-radius: 2mm;
            box-shadow: 0 2mm 8mm rgba(0, 0, 0, 0.1);
            margin-right: 15mm;
            margin-top: 0;
        }

        .document-title h1 {
            font-size: 24pt;
            font-weight: 700;
            color: #3b82f6;
            margin-bottom: 3mm;
            letter-spacing: 1pt;
        }

        .document-meta {
            font-size: 10pt;
            color: #374151;
            line-height: 1.8;
        }

        .document-meta strong {
            color: #1f2937;
            font-weight: 700;
        }

        .document-reference {
            background: #f3f4f6;
            padding: 2mm 3mm;
            border-radius: 1mm;
            display: inline-block;
            font-family: 'Courier New', monospace;
            font-weight: 700;
            color: #3b82f6;
            font-size: 11pt;
        }

        /* ============================================
           SECTION CLIENT
           ============================================ */
        
        .client-section {
            background: #f9fafb;
            border-left: 3mm solid #3b82f6;
            padding: 4mm 5mm;
            margin-bottom: 8mm;
            border-radius: 0 2mm 2mm 0;
        }

        .client-section h2 {
            font-size: 11pt;
            font-weight: 700;
            color: #374151;
            margin-bottom: 2mm;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
        }

        .client-details {
            font-size: 10pt;
            color: #1f2937;
            line-height: 1.6;
        }

        .client-name {
            font-weight: 700;
            font-size: 11pt;
            color: #3b82f6;
            margin-bottom: 1mm;
        }

        /* ============================================
           TABLEAU DES PRESTATIONS - Design moderne
           ============================================ */
        
        .services-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8mm;
            box-shadow: 0 1mm 3mm rgba(0, 0, 0, 0.08);
        }

        .services-table thead {
            background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
            color: white;
        }

        .services-table thead th {
            padding: 4mm;
            text-align: left;
            font-weight: 700;
            font-size: 10pt;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
        }

        .services-table thead th:last-child {
            text-align: right;
            width: 25%;
        }

        .services-table tbody td {
            padding: 4mm;
            border-bottom: 1pt solid #e5e7eb;
            vertical-align: top;
            font-size: 10pt;
        }

        .services-table tbody tr:last-child td {
            border-bottom: none;
        }

        .services-table tbody tr:hover {
            background: #f9fafb;
        }

        .service-name {
            font-weight: 700;
            color: #1f2937;
            font-size: 10.5pt;
            margin-bottom: 1mm;
        }

        .service-description {
            font-size: 9pt;
            color: #6b7280;
            line-height: 1.5;
            margin-top: 1mm;
        }

        .amount-cell {
            text-align: right;
            font-weight: 700;
            color: #1f2937;
            white-space: nowrap;
            font-size: 11pt;
        }

        /* ============================================
           TOTAUX - Design carte moderne
           ============================================ */
        
        .totals-section {
            margin-bottom: 10mm;
            page-break-inside: avoid;
        }

        .totals-card {
            width: 70mm;
            float: right;
            background: white;
            border-radius: 2mm;
            box-shadow: 0 2mm 8mm rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .totals-rows {
            padding: 4mm;
        }

        .total-row {
            display: table;
            width: 100%;
            padding: 2mm 0;
            border-bottom: 1pt solid #f3f4f6;
        }

        .total-row:last-child {
            border-bottom: none;
        }

        .total-label {
            display: table-cell;
            font-size: 10pt;
            color: #6b7280;
            width: 60%;
        }

        .total-value {
            display: table-cell;
            text-align: right;
            font-weight: 700;
            font-size: 11pt;
            color: #1f2937;
            width: 40%;
        }

        .grand-total {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            padding: 4mm;
            margin-top: 2mm;
        }

        .grand-total .total-label {
            color: white;
            font-weight: 700;
            font-size: 11pt;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
        }

        .grand-total .total-value {
            color: white;
            font-size: 16pt;
            font-weight: 700;
        }

        /* ============================================
           CONDITIONS & NOTES
           ============================================ */
        
        .conditions-section {
            clear: both;
            margin-top: 10mm;
            padding-top: 5mm;
            border-top: 2pt solid #e5e7eb;
            page-break-inside: avoid;
        }

        .conditions-section h3 {
            font-size: 11pt;
            font-weight: 700;
            color: #374151;
            margin-bottom: 3mm;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
        }

        .condition-block {
            background: #f9fafb;
            padding: 3mm 4mm;
            border-radius: 1mm;
            margin-bottom: 3mm;
            border-left: 2mm solid #e5e7eb;
        }

        .condition-block strong {
            color: #3b82f6;
            font-weight: 700;
        }

        .condition-block p {
            margin: 0;
            font-size: 9pt;
            color: #4b5563;
            line-height: 1.5;
        }

        /* ============================================
           SIGNATURE
           ============================================ */
        
        .signature-section {
            margin-top: 10mm;
            border: 2pt dashed #d1d5db;
            border-radius: 2mm;
            padding: 5mm;
            background: #fefefe;
            page-break-inside: avoid;
        }

        .signature-section h4 {
            font-size: 10pt;
            font-weight: 700;
            color: #374151;
            margin-bottom: 3mm;
        }

        .signature-fields {
            display: table;
            width: 100%;
            margin-top: 5mm;
        }

        .signature-field {
            display: table-cell;
            width: 50%;
            text-align: center;
        }

        .signature-field label {
            display: block;
            font-size: 9pt;
            color: #6b7280;
            margin-bottom: 8mm;
        }

        .signature-line {
            border-top: 1pt solid #9ca3af;
            width: 70%;
            margin: 0 auto;
            padding-top: 2mm;
            font-size: 8pt;
            color: #9ca3af;
        }

        /* ============================================
           FOOTER - Mentions légales
           ============================================ */
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #f9fafb;
            border-top: 1pt solid #e5e7eb;
            padding: 3mm 15mm;
            font-size: 7pt;
            color: #6b7280;
            text-align: center;
            line-height: 1.4;
        }

        .footer strong {
            color: #374151;
            font-weight: 700;
        }

        /* ============================================
           BADGES & STATUTS
           ============================================ */
        
        .badge {
            display: inline-block;
            padding: 1mm 3mm;
            border-radius: 1mm;
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
        }

        .badge-valid {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-urgent {
            background: #fee2e2;
            color: #991b1b;
        }

        /* ============================================
           WATERMARK (Optionnel)
           ============================================ */
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80pt;
            font-weight: 700;
            color: rgba(59, 130, 246, 0.05);
            z-index: -1;
            letter-spacing: 5pt;
        }

        /* ============================================
           UTILITAIRES
           ============================================ */
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .mb-small {
            margin-bottom: 2mm;
        }

        .mb-medium {
            margin-bottom: 4mm;
        }

        .mb-large {
            margin-bottom: 8mm;
        }
    </style>
</head>
<body>
    {{-- Watermark optionnel --}}
    @if($devis->status === 'draft')
        <div class="watermark">BROUILLON</div>
    @endif

    <div class="page">
        {{-- Bande supérieure bleue --}}
        <div class="header-band"></div>

        <div class="content-wrapper">
            {{-- ============================================
                SECTION IDENTITÉ
                ============================================ --}}
            <div class="identity-section">
                {{-- Infos entreprise --}}
                <div class="company-info">
                    <div class="company-logo">
                        @if(isset($logoBase64) && $logoBase64)
                            <img src="{{ $logoBase64 }}" alt="{{ $company['name'] }}">
                        @else
                            <div class="company-name">{{ $company['name'] }}</div>
                        @endif
                    </div>
                    
                    <div class="company-details" style="margin-left: 15mm; margin-top: 3mm;">
                        <strong>{{ $company['name'] }}</strong><br>
                        {{ $company['address'] }}<br>
                        {{ $company['zip'] }} {{ $company['city'] ?? '' }}<br>
                        <br>
                        <strong>Tél :</strong> {{ $company['phone'] }}<br>
                        <strong>Email :</strong> {{ $company['email'] }}<br>
                        @if(isset($company['website']))
                            <strong>Web :</strong> {{ $company['website'] }}<br>
                        @endif
                    </div>
                </div>

                {{-- Infos document --}}
                <div class="document-info">
                    <div class="document-title">
                        <h1>DEVIS</h1>
                        <div class="document-meta">
                            <div class="mb-small">
                                <span class="document-reference">{{ $devis->reference ?? sprintf('DEV-%05d', $devis->id) }}</span>
                            </div>
                            <div>
                                <strong>Date d'émission :</strong><br>
                                {{ \Carbon\Carbon::parse($devis->created_at)->format('d/m/Y') }}
                            </div>
                            <div style="margin-top: 2mm;">
                                <strong>Valable jusqu'au :</strong><br>
                                <span class="badge badge-valid">
                                    {{ \Carbon\Carbon::parse($devis->created_at)->addDays(30)->format('d/m/Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============================================
                SECTION CLIENT
                ============================================ --}}
            <div class="client-section">
                <h2>Client</h2>
                <div class="client-details">
                    <div class="client-name">{{ $devis->name ?? 'Client inconnu' }}</div>
                    <div>
                        {{ $devis->email ?? 'Email non renseigné' }}<br>
                        @if($devis->phone)
                            <strong>Tél :</strong> {{ $devis->phone }}<br>
                        @endif
                        @if($devis->address)
                            {{ $devis->address }}<br>
                            @if($devis->zip_code)
                                {{ $devis->zip_code }}
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            {{-- ============================================
                TABLEAU DES PRESTATIONS
                ============================================ --}}
            <table class="services-table">
                <thead>
                    <tr>
                        <th>Prestation</th>
                        <th style="text-align: right;">Montant HT</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="service-name">{{ $devis->service }}</div>
                            @if($devis->message)
                                <div class="service-description">
                                    {{ Str::limit($devis->message, 200) }}
                                </div>
                            @endif
                            @if($devis->budget)
                                <div class="service-description">
                                    <strong>Budget estimé :</strong> {{ $devis->budget }}
                                </div>
                            @endif
                        </td>
                        <td class="amount-cell">
                            {{ number_format($devis->amount, 2, ',', ' ') }} €
                        </td>
                    </tr>

                    {{-- Exemple de lignes supplémentaires --}}
                    @if(false) {{-- Activez si vous avez des items multiples --}}
                        <tr>
                            <td>
                                <div class="service-name">Module complémentaire</div>
                                <div class="service-description">Description du module</div>
                            </td>
                            <td class="amount-cell">500,00 €</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            {{-- ============================================
                SECTION TOTAUX
                ============================================ --}}
            <div class="totals-section clearfix">
                <div class="totals-card">
                    <div class="totals-rows">
                        <div class="total-row">
                            <div class="total-label">Total HT</div>
                            <div class="total-value">{{ number_format($devis->amount, 2, ',', ' ') }} €</div>
                        </div>
                        <div class="total-row">
                            <div class="total-label">TVA (20%)</div>
                            <div class="total-value">{{ number_format($devis->amount * 0.20, 2, ',', ' ') }} €</div>
                        </div>
                    </div>
                    <div class="grand-total">
                        <div class="total-row">
                            <div class="total-label">Total TTC</div>
                            <div class="total-value">{{ number_format($devis->amount * 1.20, 2, ',', ' ') }} €</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============================================
                CONDITIONS
                ============================================ --}}
            <div class="conditions-section">
                <h3>Conditions de vente</h3>
                
                <div class="condition-block">
                    <p>
                        <strong>Modalités de paiement :</strong> 
                        30% à la commande, 40% à mi-parcours, 30% à la livraison. 
                        Virement bancaire sous 30 jours nets.
                    </p>
                </div>

                <div class="condition-block">
                    <p>
                        <strong>Validité :</strong> 
                        Ce devis est valable 30 jours à compter de sa date d'émission.
                    </p>
                </div>

                <div class="condition-block">
                    <p>
                        <strong>Délai de réalisation :</strong> 
                        Les délais seront confirmés après validation du cahier des charges.
                    </p>
                </div>

                <div class="condition-block">
                    <p>
                        <strong>Conditions générales :</strong> 
                        En cas de litige, seul le tribunal de commerce de [Ville] sera compétent.
                    </p>
                </div>
            </div>

            {{-- ============================================
                SIGNATURE
                ============================================ --}}
            <div class="signature-section">
                <h4>Bon pour accord</h4>
                <p style="font-size: 9pt; color: #6b7280; margin-bottom: 5mm;">
                    En signant ce devis, vous acceptez les conditions générales de vente.
                </p>
                
                <div class="signature-fields">
                    <div class="signature-field">
                        <label>Date et signature du client</label>
                        <div class="signature-line">Signature</div>
                    </div>
                    <div class="signature-field">
                        <label>Cachet de l'entreprise</label>
                        <div class="signature-line">Cachet</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================
            FOOTER
            ============================================ --}}
        <div class="footer">
            <strong>{{ $company['name'] }}</strong> - {{ $company['address'] }} - {{ $company['zip'] }} {{ $company['city'] ?? '' }}<br>
            SIRET : {{ $company['siret'] ?? 'N/A' }} | TVA : {{ $company['tva'] ?? 'N/A' }} | 
            Tél : {{ $company['phone'] }} | Email : {{ $company['email'] }}
        </div>
    </div>
</body>
</html>