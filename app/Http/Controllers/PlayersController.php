<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlayerResource;
use App\Models\Player;
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
        return new Response(["id"=>$newId]);
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
}
