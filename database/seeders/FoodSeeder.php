<?php

namespace Database\Seeders;

use App\Models\Food;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        $foods = [
            // Proteine
            ['name' => 'Petto di pollo', 'category' => 'protein', 'calories_per_100g' => 165, 'protein_per_100g' => 31, 'carbs_per_100g' => 0, 'fat_per_100g' => 3.6, 'fiber_per_100g' => 0],
            ['name' => 'Petto di tacchino', 'category' => 'protein', 'calories_per_100g' => 135, 'protein_per_100g' => 30, 'carbs_per_100g' => 0, 'fat_per_100g' => 1.5, 'fiber_per_100g' => 0],
            ['name' => 'Manzo magro', 'category' => 'protein', 'calories_per_100g' => 250, 'protein_per_100g' => 26, 'carbs_per_100g' => 0, 'fat_per_100g' => 15, 'fiber_per_100g' => 0],
            ['name' => 'Tonno al naturale', 'category' => 'protein', 'calories_per_100g' => 116, 'protein_per_100g' => 26, 'carbs_per_100g' => 0, 'fat_per_100g' => 1, 'fiber_per_100g' => 0],
            ['name' => 'Salmone fresco', 'category' => 'protein', 'calories_per_100g' => 208, 'protein_per_100g' => 20, 'carbs_per_100g' => 0, 'fat_per_100g' => 13, 'fiber_per_100g' => 0],
            ['name' => 'Merluzzo', 'category' => 'protein', 'calories_per_100g' => 82, 'protein_per_100g' => 18, 'carbs_per_100g' => 0, 'fat_per_100g' => 0.7, 'fiber_per_100g' => 0],
            ['name' => 'Uova intere', 'category' => 'protein', 'calories_per_100g' => 155, 'protein_per_100g' => 13, 'carbs_per_100g' => 1.1, 'fat_per_100g' => 11, 'fiber_per_100g' => 0],
            ['name' => 'Albume d\'uovo', 'category' => 'protein', 'calories_per_100g' => 52, 'protein_per_100g' => 11, 'carbs_per_100g' => 0.7, 'fat_per_100g' => 0.2, 'fiber_per_100g' => 0],
            ['name' => 'Prosciutto crudo sgrassato', 'category' => 'protein', 'calories_per_100g' => 159, 'protein_per_100g' => 28, 'carbs_per_100g' => 0, 'fat_per_100g' => 5, 'fiber_per_100g' => 0],
            ['name' => 'Bresaola', 'category' => 'protein', 'calories_per_100g' => 151, 'protein_per_100g' => 32, 'carbs_per_100g' => 0, 'fat_per_100g' => 2.6, 'fiber_per_100g' => 0],
            ['name' => 'Gamberi', 'category' => 'protein', 'calories_per_100g' => 99, 'protein_per_100g' => 24, 'carbs_per_100g' => 0.2, 'fat_per_100g' => 0.3, 'fiber_per_100g' => 0],

            // Carboidrati
            ['name' => 'Pasta di semola', 'category' => 'carbohydrate', 'calories_per_100g' => 353, 'protein_per_100g' => 12.5, 'carbs_per_100g' => 72, 'fat_per_100g' => 1.5, 'fiber_per_100g' => 3],
            ['name' => 'Pasta integrale', 'category' => 'carbohydrate', 'calories_per_100g' => 348, 'protein_per_100g' => 13.4, 'carbs_per_100g' => 66, 'fat_per_100g' => 2.5, 'fiber_per_100g' => 7],
            ['name' => 'Riso bianco', 'category' => 'carbohydrate', 'calories_per_100g' => 360, 'protein_per_100g' => 6.7, 'carbs_per_100g' => 80, 'fat_per_100g' => 0.6, 'fiber_per_100g' => 1],
            ['name' => 'Riso basmati', 'category' => 'carbohydrate', 'calories_per_100g' => 350, 'protein_per_100g' => 7, 'carbs_per_100g' => 78, 'fat_per_100g' => 0.6, 'fiber_per_100g' => 0.4],
            ['name' => 'Riso integrale', 'category' => 'carbohydrate', 'calories_per_100g' => 362, 'protein_per_100g' => 7.5, 'carbs_per_100g' => 76, 'fat_per_100g' => 2.7, 'fiber_per_100g' => 3.5],
            ['name' => 'Pane integrale', 'category' => 'carbohydrate', 'calories_per_100g' => 247, 'protein_per_100g' => 13, 'carbs_per_100g' => 41, 'fat_per_100g' => 3.4, 'fiber_per_100g' => 7],
            ['name' => 'Pane bianco', 'category' => 'carbohydrate', 'calories_per_100g' => 265, 'protein_per_100g' => 9, 'carbs_per_100g' => 49, 'fat_per_100g' => 3.2, 'fiber_per_100g' => 2.7],
            ['name' => 'Fette biscottate', 'category' => 'carbohydrate', 'calories_per_100g' => 408, 'protein_per_100g' => 11, 'carbs_per_100g' => 75, 'fat_per_100g' => 6, 'fiber_per_100g' => 3.5],
            ['name' => 'Fiocchi d\'avena', 'category' => 'carbohydrate', 'calories_per_100g' => 389, 'protein_per_100g' => 16.9, 'carbs_per_100g' => 66, 'fat_per_100g' => 6.9, 'fiber_per_100g' => 10.6],
            ['name' => 'Patate', 'category' => 'carbohydrate', 'calories_per_100g' => 77, 'protein_per_100g' => 2, 'carbs_per_100g' => 17, 'fat_per_100g' => 0.1, 'fiber_per_100g' => 2.2],
            ['name' => 'Quinoa', 'category' => 'carbohydrate', 'calories_per_100g' => 368, 'protein_per_100g' => 14, 'carbs_per_100g' => 64, 'fat_per_100g' => 6, 'fiber_per_100g' => 7],
            ['name' => 'Farro', 'category' => 'carbohydrate', 'calories_per_100g' => 335, 'protein_per_100g' => 15, 'carbs_per_100g' => 67, 'fat_per_100g' => 2.5, 'fiber_per_100g' => 6.8],

            // Verdure
            ['name' => 'Zucchine', 'category' => 'vegetable', 'calories_per_100g' => 17, 'protein_per_100g' => 1.2, 'carbs_per_100g' => 3.1, 'fat_per_100g' => 0.3, 'fiber_per_100g' => 1],
            ['name' => 'Pomodori', 'category' => 'vegetable', 'calories_per_100g' => 18, 'protein_per_100g' => 0.9, 'carbs_per_100g' => 3.9, 'fat_per_100g' => 0.2, 'fiber_per_100g' => 1.2],
            ['name' => 'Spinaci', 'category' => 'vegetable', 'calories_per_100g' => 23, 'protein_per_100g' => 2.9, 'carbs_per_100g' => 3.6, 'fat_per_100g' => 0.4, 'fiber_per_100g' => 2.2],
            ['name' => 'Broccoli', 'category' => 'vegetable', 'calories_per_100g' => 34, 'protein_per_100g' => 2.8, 'carbs_per_100g' => 7, 'fat_per_100g' => 0.4, 'fiber_per_100g' => 2.6],
            ['name' => 'Insalata mista', 'category' => 'vegetable', 'calories_per_100g' => 15, 'protein_per_100g' => 1.3, 'carbs_per_100g' => 2.9, 'fat_per_100g' => 0.2, 'fiber_per_100g' => 1.3],
            ['name' => 'Carote', 'category' => 'vegetable', 'calories_per_100g' => 41, 'protein_per_100g' => 0.9, 'carbs_per_100g' => 10, 'fat_per_100g' => 0.2, 'fiber_per_100g' => 2.8],
            ['name' => 'Peperoni', 'category' => 'vegetable', 'calories_per_100g' => 31, 'protein_per_100g' => 1, 'carbs_per_100g' => 6, 'fat_per_100g' => 0.3, 'fiber_per_100g' => 2.1],
            ['name' => 'Melanzane', 'category' => 'vegetable', 'calories_per_100g' => 25, 'protein_per_100g' => 1, 'carbs_per_100g' => 6, 'fat_per_100g' => 0.2, 'fiber_per_100g' => 3],
            ['name' => 'Finocchi', 'category' => 'vegetable', 'calories_per_100g' => 31, 'protein_per_100g' => 1.2, 'carbs_per_100g' => 7, 'fat_per_100g' => 0.2, 'fiber_per_100g' => 3.1],
            ['name' => 'Asparagi', 'category' => 'vegetable', 'calories_per_100g' => 20, 'protein_per_100g' => 2.2, 'carbs_per_100g' => 3.9, 'fat_per_100g' => 0.1, 'fiber_per_100g' => 2.1],

            // Frutta
            ['name' => 'Mela', 'category' => 'fruit', 'calories_per_100g' => 52, 'protein_per_100g' => 0.3, 'carbs_per_100g' => 14, 'fat_per_100g' => 0.2, 'fiber_per_100g' => 2.4],
            ['name' => 'Banana', 'category' => 'fruit', 'calories_per_100g' => 89, 'protein_per_100g' => 1.1, 'carbs_per_100g' => 23, 'fat_per_100g' => 0.3, 'fiber_per_100g' => 2.6],
            ['name' => 'Arancia', 'category' => 'fruit', 'calories_per_100g' => 47, 'protein_per_100g' => 0.9, 'carbs_per_100g' => 12, 'fat_per_100g' => 0.1, 'fiber_per_100g' => 2.4],
            ['name' => 'Fragole', 'category' => 'fruit', 'calories_per_100g' => 32, 'protein_per_100g' => 0.7, 'carbs_per_100g' => 7.7, 'fat_per_100g' => 0.3, 'fiber_per_100g' => 2],
            ['name' => 'Mirtilli', 'category' => 'fruit', 'calories_per_100g' => 57, 'protein_per_100g' => 0.7, 'carbs_per_100g' => 14, 'fat_per_100g' => 0.3, 'fiber_per_100g' => 2.4],
            ['name' => 'Kiwi', 'category' => 'fruit', 'calories_per_100g' => 61, 'protein_per_100g' => 1.1, 'carbs_per_100g' => 15, 'fat_per_100g' => 0.5, 'fiber_per_100g' => 3],

            // Latticini
            ['name' => 'Yogurt greco 0%', 'category' => 'dairy', 'calories_per_100g' => 59, 'protein_per_100g' => 10, 'carbs_per_100g' => 3.6, 'fat_per_100g' => 0.4, 'fiber_per_100g' => 0],
            ['name' => 'Yogurt greco intero', 'category' => 'dairy', 'calories_per_100g' => 97, 'protein_per_100g' => 9, 'carbs_per_100g' => 3.6, 'fat_per_100g' => 5, 'fiber_per_100g' => 0],
            ['name' => 'Ricotta vaccina', 'category' => 'dairy', 'calories_per_100g' => 174, 'protein_per_100g' => 11, 'carbs_per_100g' => 3, 'fat_per_100g' => 13, 'fiber_per_100g' => 0],
            ['name' => 'Mozzarella', 'category' => 'dairy', 'calories_per_100g' => 280, 'protein_per_100g' => 22, 'carbs_per_100g' => 2.2, 'fat_per_100g' => 17, 'fiber_per_100g' => 0],
            ['name' => 'Parmigiano Reggiano', 'category' => 'dairy', 'calories_per_100g' => 392, 'protein_per_100g' => 33, 'carbs_per_100g' => 0, 'fat_per_100g' => 29, 'fiber_per_100g' => 0],
            ['name' => 'Latte scremato', 'category' => 'dairy', 'calories_per_100g' => 34, 'protein_per_100g' => 3.4, 'carbs_per_100g' => 5, 'fat_per_100g' => 0.1, 'fiber_per_100g' => 0],
            ['name' => 'Fiocchi di latte', 'category' => 'dairy', 'calories_per_100g' => 98, 'protein_per_100g' => 11, 'carbs_per_100g' => 3.4, 'fat_per_100g' => 4.3, 'fiber_per_100g' => 0],

            // Grassi
            ['name' => 'Olio extravergine d\'oliva', 'category' => 'fat', 'calories_per_100g' => 884, 'protein_per_100g' => 0, 'carbs_per_100g' => 0, 'fat_per_100g' => 100, 'fiber_per_100g' => 0],
            ['name' => 'Avocado', 'category' => 'fat', 'calories_per_100g' => 160, 'protein_per_100g' => 2, 'carbs_per_100g' => 9, 'fat_per_100g' => 15, 'fiber_per_100g' => 7],
            ['name' => 'Mandorle', 'category' => 'fat', 'calories_per_100g' => 579, 'protein_per_100g' => 21, 'carbs_per_100g' => 22, 'fat_per_100g' => 49, 'fiber_per_100g' => 12],
            ['name' => 'Noci', 'category' => 'fat', 'calories_per_100g' => 654, 'protein_per_100g' => 15, 'carbs_per_100g' => 14, 'fat_per_100g' => 65, 'fiber_per_100g' => 7],
            ['name' => 'Burro di arachidi', 'category' => 'fat', 'calories_per_100g' => 588, 'protein_per_100g' => 25, 'carbs_per_100g' => 20, 'fat_per_100g' => 50, 'fiber_per_100g' => 6],
            ['name' => 'Semi di chia', 'category' => 'fat', 'calories_per_100g' => 486, 'protein_per_100g' => 17, 'carbs_per_100g' => 42, 'fat_per_100g' => 31, 'fiber_per_100g' => 34],
            ['name' => 'Semi di lino', 'category' => 'fat', 'calories_per_100g' => 534, 'protein_per_100g' => 18, 'carbs_per_100g' => 29, 'fat_per_100g' => 42, 'fiber_per_100g' => 27],

            // Generici
            ['name' => 'Legumi misti cotti', 'category' => 'generic', 'calories_per_100g' => 127, 'protein_per_100g' => 9, 'carbs_per_100g' => 20, 'fat_per_100g' => 0.5, 'fiber_per_100g' => 8],
            ['name' => 'Lenticchie cotte', 'category' => 'generic', 'calories_per_100g' => 116, 'protein_per_100g' => 9, 'carbs_per_100g' => 20, 'fat_per_100g' => 0.4, 'fiber_per_100g' => 8],
            ['name' => 'Ceci cotti', 'category' => 'generic', 'calories_per_100g' => 164, 'protein_per_100g' => 9, 'carbs_per_100g' => 27, 'fat_per_100g' => 2.6, 'fiber_per_100g' => 8],
            ['name' => 'Tofu', 'category' => 'generic', 'calories_per_100g' => 76, 'protein_per_100g' => 8, 'carbs_per_100g' => 1.9, 'fat_per_100g' => 4.8, 'fiber_per_100g' => 0.3],
            ['name' => 'Miele', 'category' => 'generic', 'calories_per_100g' => 304, 'protein_per_100g' => 0.3, 'carbs_per_100g' => 82, 'fat_per_100g' => 0, 'fiber_per_100g' => 0],
        ];

        foreach ($foods as $food) {
            Food::firstOrCreate(
                ['name' => $food['name'], 'nutritionist_id' => null],
                $food
            );
        }
    }
}
