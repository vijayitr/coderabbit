<ul>
    @if(isset($childrens->children_arr))
        @foreach($childrens->children_arr as $user)
        @if($user->children && $user->children->isNotEmpty())
            <li>
                <div class="treeview__level treeview__level_id" data-level="{{getFirstCapital(optional($user->children)->name)}}" data-id="{{$user->children->id}}">
                    <span class="level-title">{{ $user->children->name }} ( {{getRoleNames($user->children)}} )</span>
                    <div class="treeview__level-btns">
                        @if(!is_null($user->children->children_arr) && count($user->children->children_arr))
                            <div class="btn btn-default btn-sm disabled"><span class="badge text-dark"> {{ count($user->children->children_arr)}}</span></div>
                        @endif
                        @if ($authUser->can('case_allocation.add'))
                            <div class="btn btn-default btn-sm level-add"><span class="fa fa-plus"></span></div>
                        @endif
                        @if ($authUser->can('case_allocation.remove'))
                            <div class="btn btn-default btn-sm level-remove"><span class="fa fa-trash text-danger"></span></div>
                        @endif
                    </div>
                </div>
                @include('user_allocation.partials.tree-children', ['childrens' => $user->children])
            </li>
            @endif
        @endforeach
    @endif
</ul>