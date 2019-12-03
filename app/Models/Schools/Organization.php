<?php

namespace App\Models\Schools;

use App\Models\School;
use App\Models\Users\UserOrganization;
use App\Utils\Misc\Contracts\Title;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;

class Organization extends Model
{
    const ROOT = 0;
    protected $fillable = [
        'school_id',
        'name',
        'level',
        'parent_id',
        'phone',
        'description',
        'address',
    ];

    /**
     * 返回部门所有成员角色的集合
     * @return array
     */
    public static function AllTitles(){
        return [
            Title::LEADER=>Title::LEADER_TXT,
            Title::DEPUTY=>Title::DEPUTY_TXT,
            Title::MEMBER=>Title::MEMBER_TXT,
        ];
    }

    /**
     * 部门的成员
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function members(){
        return $this->hasMany(UserOrganization::class)
            ->orderBy('title_id','asc');
    }

    public function school(){
        return $this->belongsTo(School::class);
    }

    public function branch(){
        return $this->hasMany(Organization::class, 'parent_id');
    }

    public function parent(){
        return $this->belongsTo(Organization::class, 'parent_id');
    }

    /**
     * 组织输出自己的 html 代码, 利用迭代循环输出
     * @return string
     */
    public function output(){
        $branchesCount = count($this->branch);
        return '<div class="org '.($this->level > 1 && $this->level < 4 ? 'the-box' : null).'" style="'.($branchesCount===0?'border:none;':null).'">'
            .View::make('reusable_elements.ui.org'.$this->level,['name'=>$this->name,'id'=>$this->id])->render()
            .$this->outputBranch()
            .'</div>';
    }

    /**
     * 组织输出自己的下级组织
     * @return string
     */
    public function outputBranch(){
        $html = '<div class="level-row">';
        foreach ($this->branch as $branch){
            $html .= $branch->output();
        }
        return $html.'</div>';
    }
}
