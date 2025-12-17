<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\practical_video_model;
use App\Models\author_model;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Get all unique author names from practical_video table
        $uniqueAuthors = practical_video_model::whereNotNull('author')
            ->where('author', '!=', '')
            ->distinct()
            ->pluck('author')
            ->unique()
            ->filter()
            ->toArray();

        echo "Found " . count($uniqueAuthors) . " unique authors to migrate.\n";

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($uniqueAuthors as $authorName) {
            // Trim whitespace
            $authorName = trim($authorName);
            
            if (empty($authorName)) {
                continue;
            }

            // Check if author already exists
            $author = author_model::where('name', $authorName)->first();

            if (!$author) {
                // Create new author
                $author = author_model::create([
                    'name' => $authorName,
                    'biography' => null,
                    'thumbnail' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'created_by' => 1, // Set to admin user ID or null
                    'updated_by' => 1,
                ]);
                $createdCount++;
                echo "Created author: {$authorName} (ID: {$author->id})\n";
            } else {
                echo "Author already exists: {$authorName} (ID: {$author->id})\n";
            }

            // Update all practical_video records with this author name
            $updated = practical_video_model::where('author', $authorName)
                ->update(['author_id' => $author->id]);
            
            $updatedCount += $updated;
            echo "Updated {$updated} video(s) for author: {$authorName}\n";
        }

        echo "\nMigration completed!\n";
        echo "Created {$createdCount} new authors.\n";
        echo "Updated {$updatedCount} video records with author_id.\n";
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Optionally, you can set author_id back to NULL
        // But we'll keep the author records in case they're needed
        practical_video_model::whereNotNull('author_id')->update(['author_id' => null]);
        
        // Optionally delete created authors (be careful!)
        // author_model::whereIn('name', $uniqueAuthors)->delete();
    }
};

