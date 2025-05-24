<?php

namespace App\Models;

use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reagent_name',
        'po_no',
        'maker',
        'catalog_no',
        'site',
        'location',
        'price',
        'lead_time',
        'initial_qty',
        'remaining_qty',
        'minimum_qty',
        'quantity_uom',
        'expired_date',
        'dept_owner_id',
    ];

    protected $casts = [
        'expired_date' => 'date',
        'initial_qty' => 'decimal:2',
        'remaining_qty' => 'decimal:2',
        'minimum_qty' => 'decimal:2',
        'price' => 'decimal:2',
        'lead_time' => 'integer',
        'dept_owner_id' => 'integer',
    ];

    protected $dates = [
        'expired_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_owner_id');
    }
}
