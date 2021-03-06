<?php

namespace App\Models\Forum;

use App\User;
use Illuminate\Database\Eloquent\Model;

class ForumComment extends Model
{
    protected $fillable = [
        'user_id', 'content', 'forum_id','school_id','status'
    ];

    public  function reply()
    {
        return $this->hasMany(ForumCommentReply::class, 'comment_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
