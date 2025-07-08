<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drug extends Model
{
    use HasFactory;

    protected $table = 'drugs';

    protected $fillable = [
        'manufacturer',
        'name',
        'reg_no',
        'generic_name',
        'strength',
        'dosage_form',
        'pack_size',
        'licence_holder',
        'country_of_manufacture',
        'ltr',
        'category',
        'regn_date',
    ];
} 