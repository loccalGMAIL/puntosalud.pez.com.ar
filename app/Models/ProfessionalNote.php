<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class ProfessionalNote extends Model
{
    use LogsActivity;

    protected $fillable = [
        'professional_id',
        'user_id',
        'content',
    ];

    public function activityDescription(): string
    {
        $professional = $this->professional ?? $this->professional()->first();

        if ($professional) {
            return 'Nota interna — ' . $professional->last_name . ', ' . $professional->first_name;
        }

        return 'Nota interna #' . $this->getKey();
    }

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
