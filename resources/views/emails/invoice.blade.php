<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->document_type === 'invoice' ? 'Factura Electrónica' : 'Ticket POS' }}</title>
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
            text-align: center;
        }
        .btn-primary {
            background-color: #007bff;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $invoice->document_type === 'invoice' ? 'Factura Electrónica' : 'Comprobante de Venta' }}</h1>
    </div>

    <div class="content">
        <p>Estimado/a {{ $invoice->customer_name }},</p>
        
        <p>
            {{ $invoice->document_type === 'invoice' 
                ? 'Le adjuntamos su factura electrónica que ha sido validada correctamente por la DIAN.' 
                : 'Le adjuntamos su comprobante de venta.'
            }}
        </p>
        
        <div class="invoice-details">
            <p><strong>Número de {{ $invoice->document_type === 'invoice' ? 'Factura' : 'Ticket' }}:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Fecha de Emisión:</strong> {{ $invoice->issued_at->format('d/m/Y') }}</p>
            <p><strong>Monto Total:</strong> ${{ number_format($invoice->total, 2, ',', '.') }}</p>
            @if($invoice->document_type === 'invoice' && $invoice->cufe)
            <p><strong>CUFE:</strong> {{ $invoice->cufe }}</p>
            @endif
        </div>
        
        @if($invoice->document_type === 'invoice')
        <p>Si lo desea, puede descargar su factura directamente desde nuestro portal haciendo clic en el siguiente botón:</p>
        <p style="text-align: center;">
            <a href="{{ config('app.url') }}/api/invoices/{{ $invoice->id }}/pdf" class="btn btn-primary">Descargar Factura</a>
        </p>
        @endif
        
        <p>Gracias por su preferencia.</p>
        
        <p>Atentamente,</p>
        <p>{{ $invoice->merchant->business_name }}</p>
    </div>

    <div class="footer">
        <p>Este es un correo electrónico automático, por favor no responda a este mensaje.</p>
        <p>&copy; {{ date('Y') }} {{ $invoice->merchant->business_name }}. Todos los derechos reservados.</p>
    </div>
</body>
</html> 