<?php

namespace App\Http\Controllers;

use Exception;
use App\Http\Resources\PlayerResource;
use App\Models\Player;
use App\Models\Item;
use App\Models\PlayerItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
    public function addItem(Request $request,$id)
    {

        // プレイヤーIDを取得
        $playerid = Player::where('id', $id)->pluck('id')->first();
       
        // アイテムIDを取得
        $itemid = Item::where('id', $request->itemid)->pluck('id')->first();
        
        // プレイヤーがアイテムを持っているか確認
        $playerItem = PlayerItem ::where('player_id', $playerid)
        ->where('item_id', $request->itemid)
        ->first();

        $itemCount=$request->count;

        if ($playerItem) {
            // アイテムを持っている場合、現在の数量を取得
            $newcount = $playerItem->count + $request->count;
    
            // 数量を更新
            PlayerItem::where('player_id', $playerid)
            ->where('item_id', $request->itemid)
            ->update(['count' => $newcount]);

           $itemCount=$newCount;
                
        
        return response()->json([
            'item_id' => $request->itemid,
            'count' => $itemCount
            ]);
        }
    }

   
    /**
     * アイテムを使用する
     */
    public function useItem(Request $request,$id)
    {
        DB::beginTransaction();
        try
        {
             // プレイヤーの存在確認
            $player = Player::where('id', $id)->select('id','hp','mp')->first();
            if(!$player)
            {
                throw new Exception('No player');
            }

            // アイテムの存在確認
            $item = Item::where('id', $request->itemid)->select('id','type','value')->first();
            if(!$item)
            {
                throw new Exception('No item');
            }
            
            // プレイヤーアイテムテーブルにプレイヤーがアイテムを持っているか確認
            $playerItem = PlayerItem ::countPlayerItem($id,$item->id);

            //何個消費すること
            $requestCount = $request->count;

            // アイテムの所持数がゼロじゃないかどうか確認
            if ($playerItem->count <= 0) 
            {
                throw new Exception('No item left to use');
            }

            // アイテムの種類を判別し、HP,MPの上限チェック
            $newHp = $player->hp;
            $newMp = $player->mp;
            $value = $item->value;

            
            //HPが上限の場合、MPが上限の場合はアイテムの所持数はそのままでレスポンスを返す
        
            if ($item->type == 1) // HPアイテムの効果
            { 
                if ($newHp >= 200) 
                {
                    throw new Exception('HPが上限に達しているのでアイテムを使いませんでした。');
                }
            } 
            elseif ($item->type == 2)  // MPアイテムの効果
            {
                if ($newMp >= 200) 
                {
                    throw new Exception('MPが上限に達しているのでアイテムを使いませんでした。');
                }
            }
                
                
            // アイテムを消費する
            $newCount = $playerItem->count - $requestCount;
        
            if ($newCount == 0)
            {
                throw new Exception('No items!');
            }
        
            $usedCount = 0; //実際に使用したアイテムの数

            // 効果を発揮
            for ($i = 0; $i < $requestCount; $i++)
            {
                if ($item->type == 1) // HPアイテムの効果
                { 
                    $newHp = $newHp + $value; 
                    $usedCount++;
                    if($newHp>=200)
                    {
                            $newHp = 200;
                            break;
                    }
                } 
                elseif ($item->type == 2)  // MPアイテムの効果
                {
                    $newMp = $newMp + $value; 
                    $usedCount++;
                    if($newMp>=200)
                    {
                            $newMp = 200;
                            break;
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

            DB::commit();

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
        catch(Exception $e)
        {
            DB::rollback();
            return response()->json(["message"=>$e->getMessage()]);
        }
    }


    /**
     * ガチャを引く
     */
    public function useGacha(Request $request,$id)
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
 