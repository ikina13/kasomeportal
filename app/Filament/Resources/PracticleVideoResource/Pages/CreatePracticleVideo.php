<?php

namespace App\Filament\Resources\PracticleVideoResource\Pages;

use App\Filament\Resources\PracticleVideoResource;
use Filament\Pages\Actions;
use App\Models\practical_video_model as PracticleVideo;
use Filament\Resources\Pages\CreateRecord;

class CreatePracticleVideo extends CreateRecord
{
    protected static string $resource = PracticleVideoResource::class;

    protected function afterCreate(): void
    {

   
        // Runs after the form fields are saved to the database.
        $course = static::getModel()::latest('id')->first();

         

        //Update dates
         $update_date = static::getModel()::where("id",$course->id)->update(["created_at"=>date('Y-m-d H:i:s'),"updated_at"=>date('Y-m-d H:i:s'),"created_by"=>auth()->id(),"updated_by"=>auth()->id()]);  
    


    }
}
