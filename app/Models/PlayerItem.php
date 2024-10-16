<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerItem extends Model
{
    public $timestamps = false;
    use HasFactory;

    
    public static function countPlayerItem($id,$itemid)
    {
       return PlayerItem ::where('player_id', $id)
        ->where('item_id', $itemid)
        ->first();
        
    }
}