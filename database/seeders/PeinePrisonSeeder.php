<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Peine;
use App\Models\Prison;

class PeinePrisonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $peineCount = Peine::count();
        Prison::all()->each(function ($prison) use ($peineCount) {
           // $take = random_int(1, $peineCount);
            $take = min(random_int(1, $peineCount), 2);            
            $prisonIds = Peine::inRandomOrder()->take($take)->get()->pluck('id');
            $prison->peines()->sync($prisonIds);
        });



    }
}
