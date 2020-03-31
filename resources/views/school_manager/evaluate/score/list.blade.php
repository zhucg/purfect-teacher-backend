
@extends('layouts.app')
@section('content')
    <div class="col-sm-12 col-md-12 col-xl-12">
        <div class="card">
            <div class="card-head">
                <header>评分列表</header>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="table-responsive">
                        <table
                                class="table table-striped table-bordered table-hover table-checkable order-column valign-middle">
                            <thead>
                            <tr>
                                <th>序号</th>
                                <th>教师</th>
                                <th>课程</th>
                                <th>学年</th>
                                <th>学期</th>
                                <th>评分状态</th>
                                <th>时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list as $key => $val)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $val->teacher->name }}</td>
                                    <td>{{ $val->course->name }}</td>
                                    <td>{{ $val->year }}.学年</td>
                                    <td>{{ $val->getTerm() }}</td>
                                    <td>
                                        <button type="button" class="btn btn-circle btn-info">审核中</button></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
