<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class InvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Invoice $invoice
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $documentType = $this->invoice->document_type === 'invoice' ? 'Factura' : 'Ticket POS';
        
        return new Envelope(
            subject: $documentType . ' ' . $this->invoice->invoice_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // Adjuntar PDF si existe
        if ($this->invoice->pdf_path && Storage::exists($this->invoice->pdf_path)) {
            $attachments[] = Attachment::fromPath(storage_path('app/' . $this->invoice->pdf_path))
                ->as('factura-' . $this->invoice->invoice_number . '.pdf')
                ->withMime('application/pdf');
        }
        
        // Adjuntar XML si existe
        if ($this->invoice->xml_path && Storage::exists($this->invoice->xml_path)) {
            $attachments[] = Attachment::fromPath(storage_path('app/' . $this->invoice->xml_path))
                ->as('factura-' . $this->invoice->invoice_number . '.xml')
                ->withMime('application/xml');
        }

        return $attachments;
    }
} 