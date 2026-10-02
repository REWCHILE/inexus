<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_rut',
        'document_type',
        'company_name',
        'company_rut',
        'company_giro',
        'shipping_address',
        'shipping_city',
        'shipping_region',
        'shipping_notes',
        'payment_method',
        'payment_status',
        'payment_id',
        'subtotal',
        'tax_amount',
        'shipping_cost',
        'total',
        'status',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'tax_amount' => 'float',
        'shipping_cost' => 'float',
        'total' => 'float',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total, 0, ',', '.') . ' CLP';
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal, 0, ',', '.') . ' CLP';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'completed' => '<span class="badge bg-success">Completado</span>',
            'processing' => '<span class="badge bg-info text-dark">En Proceso</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelado</span>',
            default => '<span class="badge bg-warning text-dark">Pendiente</span>',
        };
    }

    public function getPaymentStatusBadgeAttribute(): string
    {
        return match ($this->payment_status) {
            'approved' => '<span class="badge bg-success">Aprobado</span>',
            'rejected' => '<span class="badge bg-danger">Rechazado</span>',
            default => '<span class="badge bg-warning text-dark">Pendiente</span>',
        };
    }

    public function getPaymentMethodNameAttribute(): string
    {
        return match ($this->payment_method) {
            'flow' => 'Flow Chile (Webpay / Tarjetas)',
            'mercadopago' => 'Mercado Pago',
            'transferencia' => 'Transferencia Bancaria Directa',
            default => strtoupper((string) $this->payment_method),
        };
    }
}
