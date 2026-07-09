<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversionHistory extends Model
{
    protected $fillable = ['font_names', 'zip_path', 'file_count'];

    public function getFullPathAttribute(): string
    {
        return storage_path('app/' . $this->zip_path);
    }
}
