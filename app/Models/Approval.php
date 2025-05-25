<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $fillable = [
        'reason',
        'dept_id',
        'assigned_pic_id',
        'assigned_pic_date',
        'assigned_manager_id',
        'assigned_manager_date',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id');
    }

    public function assignedPic()
    {
        return $this->belongsTo(User::class, 'assigned_pic_id');
    }

    public function assignedManager()
    {
        return $this->belongsTo(User::class, 'assigned_manager_id');
    }
}
