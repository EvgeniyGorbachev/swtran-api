<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use File;
use App\User;

class Document extends Model
{

    public static function saveDocument($file_name, $file_type, $user_id)
    {
        $userDocument = User::find($user_id)->documents()->first();

        if ($userDocument) {
            $oldFileName = $userDocument->{$file_type};
            $userDocument->{$file_type} =  $file_name;

            //resave new file name
            $userDocument->save();

            //delete old file from directiv
            if ($oldFileName) {
                Document::deleteDocument($oldFileName, $user_id);
            }
        } else {
            $newDocument = new Document();
            $newDocument->{$file_type} = $file_name;
            $newDocument->user_id = $user_id;

            if (!$newDocument->save()) {
                return response()->error('Invalid file type', 403);
            }
        }
    }

    public static function deleteDocument($file_name, $user_id){
        $path =  public_path('img/documents/user/' . $user_id . '/' . $file_name);
        File::delete($path);
    }


}
