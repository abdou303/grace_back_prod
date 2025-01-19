<?php

namespace App\Imports;

use App\Models\Dossier;
use App\Models\Detenu;
use App\Models\Affaire;


use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;


class DossierImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $rows->shift();
        foreach ($rows as $row) {

             // Find or create the detenu
             $detenu = Detenu::create([
                'nom' => $row['nom'],
                'prenom' => $row['prenom'],
                'nompere' => $row['nompere'],
                'nommere' => $row['nommere'],
                'cin' => $row['cin'],
                'datenaissance' => $row['datenaissance'],
                'nompere' => $row['nompere'],
                'nommere' => $row['nommere'],
                
            ]);

            // Create the post
            $post = Post::create([
                'title' => $row['post_title'],
                'content' => $row['post_content'],
                'user_id' => $user->id,
            ]);

            // Attach categories
            $categories = explode(',', $row['categories']);
            foreach ($categories as $categoryName) {
                $category = Category::firstOrCreate(['name' => trim($categoryName)]);
                $post->categories()->attach($category->id);
            }

            // Add comments
            if (!empty($row['comments'])) {
                $comments = explode('|', $row['comments']);
                foreach ($comments as $commentContent) {
                    Comment::create([
                        'content' => trim($commentContent),
                        'post_id' => $post->id,
                    ]);
                }
            }
        }
    }
}
