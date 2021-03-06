<?php
/**
 * Created by PhpStorm.
 * User: zhang.kui
 * Date: 20/01/11
 * Time: 11:33 AM
 */
namespace App\Models\Affiche;

use App\Models\Users\GradeUser;
use App\User;
use App\Models\School;
use Illuminate\Database\Eloquent\Model;

class Affiche extends Model
{
    protected $table = 'affiches';
    protected $fillable = [
        'icheid',
        'user_id',
        'cate_id',
        'minx_id',
        'school_id',
        'campus_id',
        'iche_type',
        'iche_title',
        'iche_content',
        'iche_hot',
        'iche_recomm',
        'recomm_time',
        'iche_stick',
        'iche_sticktime',
        'iche_stick',
        'iche_sticktime',
        'iche_view_num',
        'iche_share_num',
        'iche_praise_num',
        'iche_praise_numtall',
        'iche_comment_num',
        'iche_checktime',
        'iche_is_open_number',
        'iche_checkdesc',
        'iche_categroypid',
        'iche_categroypid_name',
        'iche_categroyjson',
        'houtai_operateid',
        'houtai_operatename',
        'iche_report',
        'iche_reporttime',
        'status',
        'is_source',
        'is_delete',
        'delete_at',
        'created_at',
        'updated_at'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school() {
        return $this->belongsTo(School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gradeUser() {
        return $this->belongsTo(GradeUser::class, 'user_id', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function afficheVideo() {
        return $this->belongsTo(AfficheVideo::class, 'icheid', 'iche_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function affichePics() {
        return $this->hasMany(AffichePics::class, 'iche_id', 'icheid');
    }
}
