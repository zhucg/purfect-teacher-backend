<?php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
?>

@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="card-box">
                <div class="card-head">
                    <header>修改学校 ({{ session('school.name') }}) 的校区 - {{ $campus->name }}</header>
                </div>
                <div class="card-body " id="bar-parent">
                    <form action="{{ route('school_manager.campus.update') }}" method="post" id="edit-campus-form">
                        @csrf
                        <input type="hidden" name="campus[id]" value="{{ $campus->id }}">
                        <div class="form-group">
                            <label for="school-name-input">校区名称</label>
                            <input required type="text" class="form-control" id="campus-name-input" value="{{ $campus->name }}" placeholder="学校名称" name="campus[name]">
                        </div>
                        <div class="form-group">
                            <label for="max-students">简介</label>
                            <textarea class="form-control" name="campus[description]" id="campus-desc-input" cols="30" rows="10" placeholder="学校简介">{{ $campus->description }}</textarea>
                        </div>
                        <?php
                        Button::Print(['id'=>'btn-create-campus','text'=>trans('general.submit')], Button::TYPE_PRIMARY);
                        ?>&nbsp;
                        <?php
                        Anchor::Print(['text'=>trans('general.return'),'href'=>route('school_manager.school.view'),'class'=>'pull-right link-return'], Button::TYPE_SUCCESS,'arrow-circle-o-right')
                        ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
