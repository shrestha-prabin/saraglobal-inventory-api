<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call([
            
        ]);

        $this->createUsers();
        $this->createCategories();
        $this->createProducts();
        $this->createInventory();
    }

    public function createUsers()
    {
        // User::truncate();
        User::create(['id' => '1', 'name' => 'admin', 'email' => 'admin@email.com', 'password' => bcrypt(111111), 'role' => 'admin']);
        User::create(['id' => '2', 'name' => 'dealer', 'email' => 'dealder@email.com', 'password' => bcrypt(111111),'parent_user_id' => '1', 'role' => 'dealer']);
        User::create(['id' => '3', 'name' => 'subdealer', 'email' => 'subdealer@email.com', 'password' => bcrypt(111111),'parent_user_id' => '2', 'role' => 'subdealer']);
        User::create(['id' => '4', 'name' => 'customer', 'email' => 'customer@email.com', 'parent_user_id' => '3', 'password' => bcrypt(111111), 'role' => 'customer']);
    }

    public function createCategories()
    {
        // ProductCategory::truncate();

        ProductCategory::create(['id' => 1, 'name' => 'Fire Extinguisher']);
        ProductCategory::create(['id' => 2, 'name' => 'Smoke Detector']);

        ProductCategory::create(['id' => 3, 'name' => 'Foam', 'parent_category_id' => 1]);
        ProductCategory::create(['id' => 4, 'name' => 'CO2', 'parent_category_id' => 1]);
    }

    public function createProducts()
    {
        // Product::truncate();

        Product::create(['id' => 1, 'name' => 'Fire Extinguisher 20 KG | SKU008837292', 'description' => 'Weight: 20 KG', 'category_id' => 1, 'subcategory_id' => 3]);
        Product::create(['id' => 2, 'name' => 'Fire Extinguisher 50 KG | SKU008832000', 'description' => 'Weight: 50 KG', 'category_id' => 1, 'subcategory_id' => 3]);
        Product::create(['id' => 3, 'name' => 'Foam Fire Extinguisher | SKU277783882', 'category_id' => 1, 'subcategory_id' => 4]);
        Product::create(['id' => 4, 'name' => 'Smoke Detector | SKU0000023A3', 'category_id' => 2]);
    }

    public function createInventory()
    {
        Inventory::create(['product_id' => 1, 'stock_holder_user_id' => 1, 'stock' => 40, 'stock_defective' => 4]);
        Inventory::create(['product_id' => 2, 'stock_holder_user_id' => 1, 'stock' => 80]);
        Inventory::create(['product_id' => 3, 'stock_holder_user_id' => 1, 'stock' => 10]);
        Inventory::create(['product_id' => 4, 'stock_holder_user_id' => 1, 'stock' => 20]);
        Inventory::create(['product_id' => 2, 'stock_holder_user_id' => 2, 'stock' => 1]);
        Inventory::create(['product_id' => 2, 'stock_holder_user_id' => 3, 'stock' => 1]);
        Inventory::create(['product_id' => 1, 'stock_holder_user_id' => 4, 'stock' => 1]);
    }
}
