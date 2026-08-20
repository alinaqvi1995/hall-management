<?php

namespace App\Models;

use App\Traits\BelongsToHall;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use BelongsToHall, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'booking_id', 'hall_id', 'receipt_number', 'amount', 'method',
        'direction', 'reference', 'paid_on', 'notes', 'received_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_on' => 'date',
    ];

    public const METHODS = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'cheque' => 'Cheque',
        'card' => 'Card',
        'easypaisa' => 'EasyPaisa',
        'jazzcash' => 'JazzCash',
        'other' => 'Other',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function scopeReceipts($query)
    {
        return $query->where('direction', 'in');
    }

    public function scopeRefunds($query)
    {
        return $query->where('direction', 'refund');
    }

    public function getMethodLabelAttribute(): string
    {
        return self::METHODS[$this->method] ?? ucfirst((string) $this->method);
    }

    /** Refunds reduce the amount collected, so they carry a negative weight. */
    public function getSignedAmountAttribute(): float
    {
        return $this->direction === 'refund'
            ? -(float) $this->amount
            : (float) $this->amount;
    }
}
