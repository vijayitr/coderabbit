<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserChecklistItem extends Model {
    use HasFactory;

    protected $table = 'user_checklist_items';

    protected $fillable = ['user_id', 'assignment_id', 'item_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function assignment() {
        return $this->belongsTo(AssignActivity::class, 'assignment_id');
    }

    public function checklistItem() {
        return $this->belongsTo(ProcessNameChecklist::class, 'item_id');
    }
}
