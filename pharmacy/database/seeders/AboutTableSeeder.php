<?php

namespace Database\Seeders;

use App\Models\About;
use Illuminate\Database\Seeder;

class AboutTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $about = [
            [
                'id'=>1,'details'=>'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Architecto atque, quam et recusandae voluptate accusantium odit officia aut soluta quia quos sequi repellendus veniam quis facere fugiat ut asperiores distinctio.'
 
            ],
            
         ];
         About::insert($about);
    }
}
