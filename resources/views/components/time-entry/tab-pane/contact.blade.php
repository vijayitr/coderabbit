@php
    $arr = array(
        array(
            'name' => 'Mahavirsaraf Test',
            'number' => '9899767897',
            'extenssion' => '1111',
        ),
        array(
            'name' => 'Test',
            'number' => '7768765467',
            'extenssion' => '9898',
        ),
        array(
            'name' => 'Hgren',
            'number' => '544444',
            'extenssion' => '8777',
        ),
        array(
            'name' => 'Testsho',
            'number' => '123456789',
            'extenssion' => '8656',
        ),
        array(
            'name' => 'Rannne',
            'number' => '123456789',
            'extenssion' => '7676',
        )
    );
@endphp
<div class="p-3">
    @foreach($arr as $value)
        <div class="mb-3 task_contact_box">
            <div class="card p-3 m-0 border-0 shadow-sm" style="background-color: #f7fbfe; border-radius: 8px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="task_contact_label mb-1">{{$value['name']}}</h6>
                            <p class="mb-0 task_contact_content fw-bold">{{$value['number']}}</p>
                        </div>
                        <div>
                            <h6 class="task_contact_label mb-1">Extension</h6>
                            <p class="mb-0 task_contact_content fw-bold">{{$value['extenssion']}}</p>
                        </div>
                        <div class="action-icons d-flex align-items-center gap-3">
                            <x-time-entry.tab-pane.call-list-icons.user_edit class="task_user_edit" />
                            <x-time-entry.tab-pane.call-list-icons.trash class="task_trash" />
                        </div>
                    </div>
            </div>
        </div>
    @endforeach
</div>
