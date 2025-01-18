<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class reports extends Model
{
    protected $table = 'reports';
    protected $fillable = ['user_id', 'program_name', 'province_code', 'district_code', 'subdistrict_code', 'recipient_count', 'distribution_date', 'distribution_proof', 'notes', 'status', 'rejection_reason'];
    public $timestamps = true;
}
