<?php

namespace App\Models;

use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Approval extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'dept_id',
        'reject_reason',
        'approval_reason',
        'assigned_pic_date',
        'assigned_manager_date',
    ];
    protected $casts = [
        'assigned_pic_date' => 'datetime',
        'assigned_manager_date' => 'datetime',
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
    public function requests()
    {
        return $this->hasMany(Request::class, 'approval_id');
    }
}
