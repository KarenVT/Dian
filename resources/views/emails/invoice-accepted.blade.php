<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura Aceptada por DIAN</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .content {
            margin-bottom: 30px;
        }
        .footer {
            font-size: 12px;
            text-align: center;
            color: #666;
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .invoice-details {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .invoice-details p {
            margin: 5px 0;
        }
        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>¡Factura Aceptada por DIAN!</h1>
    </div>

    <div class="content">
        <p>Estimado/a {{ $invoice->customer_name }},</p>
        
        <p>Nos complace informarle que su factura ha sido validada y aceptada por la Dirección de Impuestos y Aduanas Nacionales (DIAN).</p>
        
        <div class="invoice-details">
            <p><strong>Número de Factura:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Fecha de Emisión:</strong> {{ $invoice->issued_at->format('d/m/Y') }}</p>
            <p><strong>Monto Total:</strong> ${{ number_format($invoice->total, 2, ',', '.') }}</p>
            <p><strong>CUFE:</strong> {{ $invoice->cufe }}</p>
        </div>
        
        <p>Adjunto a este correo encontrará una copia en formato PDF de su factura electrónica.</p>
        
        <p>Si tiene alguna pregunta o requiere información adicional, no dude en contactarnos.</p>
        
        <p>Atentamente,</p>
        <p>{{ $invoice->company->name }}</p>
    </div>

    <div class="footer">
        <p>Este es un correo electrónico automático, por favor no responda a este mensaje.</p>
        <p>&copy; {{ date('Y') }} {{ $invoice->company->name }}. Todos los derechos reservados.</p>
    </div>
</body>
</html> 