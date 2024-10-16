<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function create()
    {
        // Load the CSV file
        $csvPath = storage_path('bisac.csv'); // Use storage_path() to get the correct path
        $data = [];

        // Check if the file exists
        if (file_exists($csvPath)) {
            // Open the CSV file for reading
            if (($handle = fopen($csvPath, 'r')) !== false) {
                // Initialize a row counter
                $rowCounter = 0;

                // Loop through the rows of the CSV
                while (($row = fgetcsv($handle)) !== false) {
                    // Increment the row counter
                    $rowCounter++;

                    // Skip the first two rows
                    if ($rowCounter <= 2) {
                        continue; // Skip this iteration
                    }

                    // Assuming the subject is in the second column (index 1)
                    if (isset($row[1])) {
                        $data[] = trim($row[1]); // Trim the subject name and add to data
                    }
                }
                fclose($handle); // Close the file handle
            }
        } else {
            return response()->json(['error' => 'CSV file not found.'], 404);
        }

        // Initialize subject mapping and parent mapping
        $subjectMap = [];
        $parentMap = [];
        $idCounter = 1;

        // Recursive function to add subjects
        $this->addSubject($subjectMap, $parentMap, $idCounter, $data);

        // Prepare final result in the required format
        $result = [];
        foreach ($subjectMap as $name => $id) {
            $result[] = [
                'subject_name' => $name,
                'id' => $id,
                'parent_id' => $parentMap[$id],
            ];
        }

        // Save subjects to the database
        foreach ($result as $subject) {
            Category::create([
                'id'=> $subject['id'],
                'parent_category_id' => $subject['parent_id'] === -1 ? null : $subject['parent_id'], // Use null for root categories
                'is_active' => 1, // Assuming you want to set it as active
            ]);
        }

        // Return the result as JSON
        return response()->json($result);
    }

    private function addSubject(&$subjectMap, &$parentMap, &$idCounter, $data)
    {
        foreach ($data as $item) {
            // Split the string into parts by '/'
            $parts = explode(' / ', $item);
            
            // Initialize parent ID
            $parentId = null;

            // Process each part to build the hierarchy
            foreach ($parts as $index => $part) {
                $part = trim($part); // Trim whitespace from part
                $this->createSubject($subjectMap, $parentMap, $idCounter, $part, $parentId);
                
                // Update parent ID for the next part
                $parentId = $subjectMap[$part];
            }
        }
    }

    private function createSubject(&$subjectMap, &$parentMap, &$idCounter, $name, $parentId)
    {
        $name = trim($name);
        if (!isset($subjectMap[$name])) {
            $subjectMap[$name] = $idCounter++;
            $parentMap[$subjectMap[$name]] = $parentId; // Assign parent ID
        }
    }
}
