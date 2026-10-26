<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;
use Illuminate\Database\Eloquent\SoftDeletes;

class Upload extends Model
{
    use SoftDeletes,PreventDemoModeChanges;

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = [
        'file_original_name', 'file_name', 'user_id', 'extension', 'type', 'file_size',
    ];

    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    /**
     * Keep file_original_name unique for a user.
     * If a file with the same name already exists, append an incremental number after the name.
     */
    public static function uniqueOriginalName($name, $userId)
    {
        $baseName = $name;
        $counter = 1;
        while (Upload::where('user_id', $userId)->where('file_original_name', $name)->exists()) {
            $name = $baseName . '-' . $counter;
            $counter++;
        }
        return $name;
    }
}
