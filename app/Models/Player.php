<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    public $timestamps = false;
    use HasFactory;

   /**
     * プレイヤーを全件取得する
     * 
     * @return 全プレイヤー情報
     */
    public function playerIndex() 
    {
        return (Player::query()->get());
    }

    /**
     * プレイヤーを1件取得
     * @return １件のプレイヤー情報
     */
    public function playerShow($id) 
    {
        return (Player::query()->where('id', $id)->select('id' ,'name')->first());
    }

    
    /**
     * 新規プレイヤーのレコードを作成し、idを返す
     * 
     * @param int name,hp,mp,money
     * @return 新規プレイヤーのid
     */
    public function playerCreate($name, $hp, $mp, $money)
    {  
        return(Player::query()
        ->insertGetId(
            ['name'=>$name,
            'hp'=>$hp,
            'mp'=>$mp,
            'money'=>$money]
            )
        );
    }
   
    public function playerUpdate($id,$name,$hp,$mp,$money)
    {
        return(Player::query() 
        ->where('id', $id)
        ->update(['name'=>$name,
            'hp'=>$hp,
            'mp'=>$mp,
            'money'=>$money]
             )
        );
    }

    public function playerDestroy($id)
    {
        return(Player::query() ->where('id',$id)
        ->delete());
    }
}