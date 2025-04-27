<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use App\Models\company;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    /**
     * Determina si el usuario puede ver la factura.
     *
     * @param  \App\Models\User|\App\Models\company|null  $user
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view($user, Invoice $invoice)
    {
        // Si el modelo autenticado es un company
        if ($user instanceof company) {
            // Solo si es el dueño de la factura
            return $user->id === $invoice->company_id
                ? Response::allow()
                : Response::deny('No está autorizado para acceder a esta factura.');
        }
        
        // Si el modelo autenticado es un User
        if ($user instanceof User) {
            // Si el usuario es comerciante de este comercio
            if ($user->hasRole('comerciante') && $user->company_id === $invoice->company_id) {
                return Response::allow();
            }
            
            // Si es un cliente con el token con capacidad para ver la factura
            if (request()->user('sanctum')->tokenCan('view_invoice')) {
                // Aquí podríamos verificar si el email/ID del usuario coincide con el cliente de la factura
                // Para este ejemplo, asumimos que si tiene el permiso, puede acceder
                return Response::allow();
            }
        }
        
        return Response::deny('No está autorizado para acceder a esta factura.');
    }

    /**
     * Determina si el usuario puede descargar el PDF de la factura.
     *
     * @param  \App\Models\User|\App\Models\company|null  $user
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function downloadPdf($user, Invoice $invoice)
    {
        // Primero verificamos que la factura no sea un ticket POS
        if ($invoice->document_type === 'ticket_pos') {
            return Response::deny('Los tickets POS no tienen PDF disponible para descarga.', 404);
        }
        
        // Luego aplicamos las mismas reglas de autorización que para ver
        return $this->view($user, $invoice);
    }
} 