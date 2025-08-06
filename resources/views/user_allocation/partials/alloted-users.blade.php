<div class="treeview js-treeview" id="user_hierarchy">
    @if($users && isset($users['0']) && !is_null($users['0']->parent))

    <ul id="tree" class="treeview__level_id" data-id="{{$users['0']->parent->id}}">
        <li class="branch">
                <div class="treeview__level treeview__level_id" style="--bg-url: url('{{$users[0]->parent->profile_image != null ? url($users[0]->parent->profile_image) : ""}}');" data-level="{{$users[0]->parent->profile_image == null ? getFirstCapital($users['0']->parent->name) : ''}}"data-id="{{$users['0']->parent->id}}">
                    <span class="level-title">{{$users['0']->parent->name}} ( {{getRoleNames($users['0']->parent)}} )</span>
                    <div class="treeview__level-btns">
                        @if(!is_null($users) && count($users))
                            <div class="btn btn-default btn-sm disabled"><span class="badge text-dark"> {{ count($users)}}</span></div>
                        @endif
                        @if ($authUser->can('case_allocation.add'))
                            <div class="btn btn-default btn-sm level-add"><span class="fa fa-plus"></span></div>
                        @endif
                    </div>
                </div>
                <ul class="expand show">
                    @foreach($users as $user)
                        @if(!is_null($user->children))
                            <li>
                                <div class="treeview__level treeview__level_id" style="--bg-url: url('{{$user->children->profile_image != null ? url($user->children->profile_image) : ""}}');" data-level="{{$user->children->profile_image == null ? getFirstCapital($user->children->name) : ''}}" data-id="{{$user->children->id}}">
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
                </ul>            
        </li>
    </ul>
    @else

    <ul id="tree" data-id="{{$users->id}}">
        <li class="branch">
            <div class="treeview__level treeview__level_id" data-level="{{getFirstCapital($users->name)}}" data-id="{{$users->id}}">
                <span class="level-title">{{$users->name}} ( {{getRoleNames($users)}} )</span>
                <div class="treeview__level-btns">
                    @if ($authUser->can('case_allocation.add'))
                        <div class="btn btn-default btn-sm level-add"><span class="fa fa-plus"></span></div>
                    @endif
                </div>
            </div>            
        </li>
    </ul>

    @endif

</div>

<script>
    var tree = document.getElementById("tree");
    if(tree){
        tree.querySelectorAll("ul").forEach(function(el,key,parent){
            var elm =  el.parentNode;
            elm.classList.add("branch");
            var x = document.createElement("i");
            x.classList.add("indicator");
            elm.insertBefore(x, elm.firstChild);
            if (!el.classList.contains('show')) {
                el.classList.add("collapse");
            } else {
                el.classList.remove("show");
            }

            elm.addEventListener("click", function(event)
            {
                if (elm === event.target || elm === event.target.parentNode){
                
                    if(el.classList.contains('collapse')){
                        el.classList.add("expand");
                        el.classList.remove("collapse");
                    }else{
                        el.classList.add("collapse");
                        el.classList.remove("expand");
                    }    
                }    
            },false);    
        });
    }
</script>
