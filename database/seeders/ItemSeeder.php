<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $testData = [
            ['name' => 'HPかいふく薬', 'price' => 10, 'value' => 100, 'type'=>1, '%'=>30],
            ['name' => 'MPかいふく薬', 'price' => 50, 'value' => 20, 'type'=>2, '%'=>70]
        ];

        foreach ($testData as $datum) {
            $item = new Item;
            $item->name = $datum['name'];
            $item->price = $datum['price'];
            $item->value = $datum['value'];
            $item->type = $datum['type'];
            $item->parcent = $datum['%'];

            $item->save();
        }
    }
}
