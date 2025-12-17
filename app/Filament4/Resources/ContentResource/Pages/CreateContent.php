<?php

namespace App\Filament\Resources\ContentResource\Pages;

use App\Filament\Resources\ContentResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateContent extends CreateRecord
{
    protected static string $resource = ContentResource::class;

    protected function afterCreate(): void
    {
        // Runs after the form fields are saved to the database.
        $content = static::getModel()::latest('id')->first();

        var_dump('lll');
        dd($content);

        //Add Course skills
       /* foreach ($course->skills as $skill){
             Skill::create([
                 "name"=>$skill,
                 "course_id"=>$course->id
             ]);
        }*/

        //Update dates
        // $update_date = static::getModel()::where("id",$course->id)->update(["created_date"=>date('Y-m-d H:i:s'),"updated_date"=>date('Y-m-d H:i:s'),"created_by"=>auth()->id()]);  

        //Change approve or pending status
       //  if($course->is_visible == true){
       //       $update_is_visible = static::getModel()::where("id",$course->id)->update(["status"=>'approved']);
      //   }else{
      //       $update_is_visible = static::getModel()::where("id",$course->id)->update(["status"=>'pending']);
      //   }


        //Change picture location

        //  $insert_image = Image::create([
        //      "image_name"=>$course->thumbnail,
        //      "image_path"=>env('APP_URL')."/storage/".$course->thumbnail,
        //      "course_id"=>$course->id
        //  ]);



    }
}
