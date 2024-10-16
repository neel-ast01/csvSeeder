<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategorySeeder extends Seeder
{
    public function run()
    {
        // Insert languages into the database
        DB::table('languages')->insert([
            ['id' => 1, 'language_name' => 'English', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'language_name' => 'Ronimia', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Read the CSV file
        $path = storage_path('bisac.csv'); // Adjust the path as necessary
        $data = array_map('str_getcsv', file($path));

        $parentCategoryIds = []; // Store the IDs of categories by description
        $translations = []; // To hold translations

        // Process CSV data
        foreach ($data as $row) {
            // Skip the first row if it contains headers or any rows with '2021 Edition'
            if (trim($row[0]) === 'Code' || strpos($row[1], '2021 Edition') !== false) {
                continue;
            }
            
            // Extract the description from row[1] and split by '/'
            $descriptionParts = array_map('trim', explode('/', $row[1]));
            $parentId = null;

            foreach ($descriptionParts as $description) {
                // Check if the category already exists
                if (isset($parentCategoryIds[$description])) {
                    // Set parent ID for nested categories
                    $parentId = $parentCategoryIds[$description]['id'];
                } else {
                    try {
                        // Insert new category
                        $categoryId = DB::table('categories')->insertGetId([
                            'parent_category_id' => $parentId,
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // Generate a unique slug for sys_url
                        $slug = $this->generateSlug($description);
                        $slug = $this->ensureUniqueSlug($slug); // Ensure the slug is unique

                        // Insert slug into sys_url table
                        DB::table('sys_url')->insert([
                            'slug' => $slug,
                            'target_id' => $categoryId, // Set target_id to the newly created category ID
                            'target_type' => 'category', // Adjust type as needed
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // Store the ID and slug of the newly created category
                        $parentCategoryIds[$description] = [
                            'id' => $categoryId,
                            'slug' => $slug,
                        ];

                        // Add translations for the new category
                        $translations[] = [
                            'category_id' => $categoryId,
                            'language_id' => 1, // Assuming language_id 1 is English
                            'name' => $description,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        // Add translation for another language (if required)
                        $translations[] = [
                            'category_id' => $categoryId,
                            'language_id' => 2, // Assuming language_id 2 for another language
                            'name' => $description,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    } catch (\Exception $e) {
                        // Log the error for this particular entry
                        Log::error('Error inserting category:', [
                            'description' => $description,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        // Insert translations into the database
        DB::table('category_translations')->insert($translations);
    }

    private function generateSlug($description)
    {
        // Convert to lowercase, replace spaces with underscores, and remove special characters
        $slug = strtolower(trim($description));
        $slug = preg_replace('/[^a-z0-9_]+/', '_', $slug); // Replace special characters with underscores
        $slug = preg_replace('/_+/', '_', $slug); // Replace multiple underscores with a single underscore
        return rtrim($slug, '_'); // Trim trailing underscores
    }

    private function ensureUniqueSlug($slug)
    {
        $originalSlug = $slug;
        $counter = 1;

        // Check if the slug exists in the sys_url table
        while (DB::table('sys_url')->where('slug', $slug)->exists()) {
            // If the slug exists, append a counter to make it unique
            $slug = $originalSlug . '_' . $counter; // Use underscore instead of hyphen
            $counter++;
        }

        return $slug;
    }
}
