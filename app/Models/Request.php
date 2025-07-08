<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Request extends Model
{
    use HasFactory, SoftDeletes;
    protected $primaryKey = 'request_no';
    protected $keyType = 'int';

    protected $fillable = [
        'request_no',
        'reagent_id',
        'request_qty',
        'purpose',
        'requested_by',
        'approval_id',
        'customer_id',
        'status',
    ];

    /**
     * Relationship to Stock (reagent).
     */
    public function reagent()
    {
        return $this->belongsTo(Stock::class, 'reagent_id');
    }

    /**
     * Relationship to User (requested_by).
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Relationship to Approval.
     */
    public function approval()
    {
        return $this->belongsTo(Approval::class, 'approval_id');
    }
}
