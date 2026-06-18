<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EndurQuizQuestion extends Model
{
    protected $fillable = ['module_id', 'sort_order', 'question',
                           'option_a', 'option_b', 'option_c', 'option_d', 'correct_option'];

    public function optionText(string $letter): string
    {
        return $this->{"option_{$letter}"};
    }
}
