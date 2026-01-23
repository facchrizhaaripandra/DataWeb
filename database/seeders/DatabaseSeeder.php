<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Dataset;
use App\Models\DatasetShare;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin2@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);
        
        // Create regular users
        $user1 = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user'
        ]);
        
        $user2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user'
        ]);
        
        // Create datasets for each user
        // User 1 datasets
        $dataset1 = Dataset::create([
            'name' => 'John\'s Sales Data',
            'description' => 'Monthly sales data',
            'user_id' => $user1->id,
            'columns' => ['Month', 'Sales', 'Profit'],
            'row_count' => 12,
            'is_public' => false,
            'access_type' => 'private'
        ]);
        
        // User 2 datasets
        $dataset2 = Dataset::create([
            'name' => 'Jane\'s Inventory',
            'description' => 'Product inventory tracking',
            'user_id' => $user2->id,
            'columns' => ['Product', 'Quantity', 'Price'],
            'row_count' => 50,
            'is_public' => true,
            'access_type' => 'public'
        ]);
        
        // Share dataset1 with user2
        DatasetShare::create([
            'dataset_id' => $dataset1->id,
            'user_id' => $user2->id,
            'shared_by' => $user1->id,
            'permission' => 'edit'
        ]);
        
        // Share dataset2 with user1 (view only)
        DatasetShare::create([
            'dataset_id' => $dataset2->id,
            'user_id' => $user1->id,
            'shared_by' => $user2->id,
            'permission' => 'view'
        ]);
        
        // Create some dataset rows for testing
        for ($i = 1; $i <= 12; $i++) {
            $dataset1->rows()->create([
                'data' => [
                    'Month' => date('F', mktime(0, 0, 0, $i, 1)),
                    'Sales' => rand(1000, 5000),
                    'Profit' => rand(200, 1500)
                ]
            ]);
        }
        
        for ($i = 1; $i <= 50; $i++) {
            $dataset2->rows()->create([
                'data' => [
                    'Product' => 'Product ' . $i,
                    'Quantity' => rand(1, 100),
                    'Price' => rand(10, 500)
                ]
            ]);
        }
    }
}