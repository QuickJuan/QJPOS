<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptFooter extends Model
{
    protected $fillable = [
        'footer_notes',
        'type',
    ];
}
