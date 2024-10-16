<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    public $timestamps = false;
    use HasFactory;

    /**
     * アイテムを1件取得
     * @return １件のプレイヤー情報
     */
   
}
