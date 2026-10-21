<?php

namespace App\Models;

use App\Support\RichText;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'body'];

    /**
     * Values the search boxes look through.
     *
     * Bodies are rich text, so only their visible words can match.
     *
     * @return array<int, string|null>
     */
    public function searchFields(): array
    {
        return [$this->title, RichText::toPlainText((string) $this->body)];
    }
}
