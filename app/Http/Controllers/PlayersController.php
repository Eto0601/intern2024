<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlayerResource;
use App\Models\Player;
use App\Models\Item;
use App\Models\PlayerItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PlayersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new Response(
            Player::query()->
            select(['id', 'name'])->
            get());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $player = new Player();

        return new Response(
            $player->playerShow($id)
        );

        // プレイヤーが見つからなかった場合、404エラーレスポンスを返す
        if (!$player) 
        {
            return response()->json(['message' => 'Player not found'], 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $player = new Player();
        $Updatedata = $player->playerUpdate($id,$request->name,
        $request->hp, $request->mp, $request->money);
         // 更新成功のレスポンスを返す
        return response()->json(['message' => 'Update Success!'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $player = new Player();
        $player->playerDestroy($id);
 
        // プレイヤーが見つからなかった場合、404エラーレスポンスを返す
        if (!$player) 
        {
            return response()->json(['message' => 'Player not found'], 404);
        }
        // 削除成功のレスポンスを返す
        return response()->json(['message' => 'Player deleted successfully'], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $player = new Player();
        $newId = $player->playerCreate($request->name,
        $request->hp, $request->mp, $request->money);
        return response()->json(["id"=>$newId]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * プレイヤーにアイテムを持たせる
     */
    public function additem(Request $request,$id)
    {

        // プレイヤーIDを取得
        $playerid = Player::where('id', $id)->pluck('id')->first();
       
        // アイテムIDを取得
        $itemid = Item::where('id', $request->itemid)->pluck('id')->first();
        
        // プレイヤーがアイテムを持っているか確認
        $playerItem = PlayerItem ::where('player_id', $playerid)
        ->where('item_id', $request->itemid)
        ->first();

        if ($playerItem) {
            // アイテムを持っている場合、現在の数量を取得
            $newcount = $playerItem->count + $request->count;
    
            // 数量を更新
            PlayerItem::where('player_id', $playerid)
            ->where('item_id', $request->itemid)
            ->update(['count' => $newcount]);

            return response()->json([
                'item_id' => $request->itemid,
                'count' => $newcount
                ]);
                
        }
         else
        {
            // アイテムを持っていない場合、新規追加
            PlayerItem:: insert([
                'player_id' => $playerid,
                'item_id' => $request->itemid,
                'count' => $request->count
                ]);

            return response()->json([
                'item_id' => $request->itemid,
                'count' => $request->count
                ]);
        }
    }

   
    /**
     * アイテムを使用する
     */
    public function useitem(Request $request,$id)
    {
        // プレイヤーの存在確認
        $player = Player::where('id', $id)->select('id','hp','mp')->first();
       
        // アイテムの存在確認
        $item = Item::where('id', $request->itemid)->select('id','type','value')->first();
        
        // プレイヤーアイテムテーブルにプレイヤーがアイテムを持っているか確認
        $playerItem = PlayerItem ::countplayeritem($id,$item->id);
        $playerItem->count;

        $requestCount = $request->count;

        // アイテムの所持数がゼロじゃないかどうか確認
        if ($playerItem->count <= 0) {
            return response()->json(['error' => 'No item left to use']);
        }

        // アイテムの種類を判別し、HP,MPの上限チェック
        $newHp = $player->hp;
        $newMp = $player->mp;
        $value = $item->value;

        
        //HPが上限の場合、アイテムを所持数はそのままでレスポンスを返す
        if($newHp>=200)
        {
            return response()->json([
                'item_id' => $item->id,
                'count' => $playerItem->count,
              'players'=>[
                    'id' => $id,
                    'hp' => $newHp,
                    'mp' => $newMp
              ],
              'Message' => 'HPが上限に達しているのでアイテムを使いませんでした。'
              ]);
        }

        //HPが上限の場合、アイテムを所持数はそのままでレスポンスを返す
        if($newMp>=200)
        {
            return response()->json([
                'item_id' => $item->id,
                'count' => $playerItem->count,
              'players'=>[
                    'id' => $id,
                    'hp' => $newHp,
                    'mp' => $newMp
              ],
              'Message' => 'MPが上限に達しているのでアイテムを使いませんでした。'
              ]);
        }
        

        
        // アイテムを消費する
        $newCount = $playerItem->count - $requestCount;
       
        if ($newCount == 0)
        {
            return response()->json(['error' => 'No items!'], 400);
        }
       

    
        // 効果を発揮
        $usedCount = 0; //実際に使用したアイテムの数
        for ($i = 0; $i < $requestCount; $i++)
        {
            if($newHp==200 || $newMp==200)
            {
                break;
            }
            else
            {
               // 効果を発揮
               if ($item->type == 1) // HPアイテムの効果
               { 
                   $newHp = $newHp + $value; 
                   $usedCount++;
               } 
               elseif ($item->type == 2)  // MPアイテムの効果
               {
                   $newMp = $newMp + $value; 
                   $usedCount++;
               }
            }
        }

        $finalCount=$playerItem->count - $usedCount;

        //プレイヤーを更新
        Player::where('id', $id)
            ->update(['hp' => $newHp, 'mp' => $newMp]);
        
        //プレイヤーアイテムを更新    
        PlayerItem::query()->where('player_id', $player->id)
            ->where('item_id', $item->id)
            ->update(['count' => $finalCount]);

        return response()->json([
            'item_id' => $item->id,
            'count' => $finalCount,
          'players'=>[
                'id' => $id,
                'hp' => $newHp,
                'mp' => $newMp
          ]
            
        ]);
    }


    /**
     * ガチャを引く
     */
    public function usegacha(Request $request,$id)
    {
        //プレイヤーの存在確認
        $player = Player::query()->where('id',$id)->get();

        //アイテムの存在確認
        $item = Item::query()->where('id',$id)->get();

        //プレイヤーアイテムの存在確認
        $playerItem = PlayerItem::query()->where('id',$id)->get();

        //ガチャを引く回数
        $gachaCount = $request->count;
        //ガチャ１回ごとのコスト
        $gachaCost = 10;
        //合計のガチャコスト
        $totalCost = $gachaCount * $gachaCost;

        // プレイヤーの所持金が足りるか確認
        if ($player->money < $totalCost)
        {
            return response()->json(['error' => 'Not enough money'], 400);
        }

        //プレイヤーの所持金からガチャのコスト分引く
        $playermoney = $player->money - $totalCost;

        //ガチャを引く
        for($i = 0; $i < $gachaCount; $i++)
        {

        }

        

    }
}
 