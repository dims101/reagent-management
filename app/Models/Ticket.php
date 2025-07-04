<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'spk_no',
        'request_qty',
        'requested_by',
        'assigned_to',
        'reagent_id',
        'purpose',
        'purpose_id', // Add this if you want to store purpose_id as well
        'expected_date',
        'expected_reason',
        'start_date',
        'end_date',
        'attachment',
        'status',
        'reject_reason',
        'uom',
        'customer_id',
    ];

    protected $casts = [
        'expected_date' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationship to Stock (reagent).
     */
    public function reagent()
    {
        return $this->belongsTo(Reagent::class, 'reagent_id');
    }

    /**
     * Relationship to User (requested_by).
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relationship to Purpose.
     */
    public function purposeRelation()
    {
        return $this->belongsTo(Purpose::class, 'purpose_id');
    }
}
