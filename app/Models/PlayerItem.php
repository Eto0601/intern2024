<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerItem extends Model
{
    public $timestamps = false;
    use HasFactory;

    

    /**
     * プレイヤーがアイテムを持っているか確認する
     * 
     * @param int $playerid - プレイヤーのID
     * @param int $itemid - アイテムのID
     * @return PlayerItem|null - 見つかったプレイヤーアイテムまたはnull
     */
    public static function findplayeritem($playerid, $itemid)
    {
        return (PlayerItem::where('player_id', $playerid)
            ->where('item_id', $itemid)
            ->first());
    }

    /**
     * プレイヤーが持つアイテムを更新する
     * 
     * @param int $playerid - プレイヤーのID
     * @param int $itemid - アイテムのID
     * @param int $count - 追加するアイテムの数
     * @return PlayerItem - 更新されたプレイヤーのアイテム
     */
    public static function updateplayeritem($playerid,$itemid, $count)
    {
        // 現在のアイテムを取得
        $playerItem = PlayerItem::findplayeritem($playerid, $itemid);
        // 数量を更新
        $newCount = $playerItem->count + $count;
        
        return( PlayerItem::where('player_id', $playerid)
            ->where('item_id', $itemid)
            ->update(['count' => $newCount]));
        
    }

    /**
     * プレイヤーが持つアイテムを新規に追加する
     * 
     * @param int $playerid - プレイヤーのID
     * @param int $itemid - アイテムのID
     * @param int $count - 追加するアイテムの数
     * @return PlayerItem - 新規に追加されたプレイヤーのアイテム
     */
    public static function createplayeritem($playerid, $itemid, $count)
    {
        return(PlayerItem::insert([
            'player_id' => $playerid,
            'item_id' => $itemid,
            'count' => $count
        ]));
    }



    public static function countplayeritem($id,$itemid)
    {
       return PlayerItem ::where('player_id', $id)
        ->where('item_id', $itemid)
        ->first();
        
    }
}