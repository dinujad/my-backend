<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductAjaxController extends Controller
{
    public function storeCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $category = Category::firstOrCreate(
            ['slug' => Str::slug($request->name)],
            ['name' => $request->name, 'is_active' => true]
        );
        return response()->json($category);
    }

    public function storeTag(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $tag = Tag::firstOrCreate(
            ['slug' => Str::slug($request->name)],
            ['name' => $request->name]
        );
        return response()->json($tag);
    }

    public function storeBrand(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $brand = Brand::firstOrCreate(
            ['slug' => Str::slug($request->name)],
            ['name' => $request->name]
        );
        return response()->json($brand);
    }

    public function generateVariations(Request $request)
    {
        // Expects: { attributes: [ { name: 'Size', values: ['S', 'M'] }, { name: 'Color', values: ['Red', 'Blue'] } ] }
        $attributes = $request->input('attributes', []);
        
        $combinations = [[]];
        
        foreach ($attributes as $attribute) {
            $newCombinations = [];
            foreach ($combinations as $combo) {
                foreach ($attribute['values'] as $value) {
                    $newCombo = $combo;
                    $newCombo[$attribute['name']] = $value;
                    $newCombinations[] = $newCombo;
                }
            }
            $combinations = $newCombinations;
        }

        $variations = array_map(function ($combo) {
            $skuSuffix = implode('-', array_values($combo));
            return [
                'id' => 'new_' . Str::random(8), // Temporary ID for frontend tracking
                'attributes' => $combo,
                'sku' => strtoupper(Str::slug($skuSuffix)),
                'price' => '',
                'sale_price' => '',
                'stock_quantity' => '',
                'stock_status' => 'instock'
            ];
        }, $combinations);

        return response()->json(['variations' => $variations]);
    }
}
