<li class="nav-item">
    <a href="javascript:void(0);" class="nav-link nav-toggle">
        <i class="material-icons">account_circle</i>
        <span class="title">评教,评学</span>
        <span class="arrow"></span>
    </a>
    <ul class="sub-menu">
        <li class="nav-item">
            <a href="{{ route('school_manager.evaluate.content-list',['uuid'=>session('school.uuid')]) }}" class="nav-link">
                <span class="title">评价模板</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('school_manager.evaluate.teacher-list',['uuid'=>session('school.uuid')]) }}" class="nav-link">
                <span class="title">评教老师</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('school_manager.evaluate.student.list',['uuid'=>session('school.uuid')]) }}" class="nav-link">
                <span class="title">评学列表</span>
            </a>
        </li>
    </ul>
</li>
