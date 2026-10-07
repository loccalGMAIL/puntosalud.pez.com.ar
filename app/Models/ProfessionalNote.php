<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /** @return BelongsTo<Professional, $this> */
    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
