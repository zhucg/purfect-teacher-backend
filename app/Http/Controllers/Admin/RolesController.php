<?php
/**
 * 角色管理的控制器类
 */
namespace App\Http\Controllers\Admin;

use App\Dao\Users\RoleDao;
use App\Http\Requests\RoleRequest;
use App\Http\Controllers\Controller;
use App\Models\Acl\Role;

class RolesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param RoleRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(RoleRequest $request){
        $this->dataForView['pageTitle'] = trans('thumbnail.roles');
        $this->dataForView['roles'] = Role::AllNames();
        return view('admin.roles.list', $this->dataForView);
    }

    /**
     * @param RoleRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(RoleRequest $request){
        $dao = new RoleDao();
        $role = $dao->getBySlug($request->get('slug'));
        $this->dataForView['roles'] = $role;
        $this->dataForView['pageTitle'] = '编辑角色权限: '.$role->name;
        return view('admin.roles.edit', $this->dataForView);
    }
}
