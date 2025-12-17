<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendance';
    protected $fillable = [
        'employee_id',
        'time_in_AM',
        'time_out_AM',
        'time_in_PM',
        'time_out_PM',
        'status',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
