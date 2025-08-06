<link href="{{asset('css/floating_window.css')}}" rel="stylesheet">
<div id="drag">
    <div class="title px-2">
        <h2>Task Details</h2>
        <div>
            <a class="min d-none" href="javascript:;" title=""></a>
            <a class="max d-none" href="javascript:;" title=""></a>
            <a class="revert d-none" href="javascript:;" title=""></a>
            <a class="close" href="javascript:;" title=""><i class="fa-regular fa-circle-xmark text-danger"></i></a>
        </div>
    </div>
    <div class="resizeL"></div>
    <div class="resizeT"></div>
    <div class="resizeR"></div>
    <div class="resizeB"></div>
    <div class="resizeLT"></div>
    <div class="resizeTR"></div>
    <div class="resizeBR"></div>
    <div class="resizeLB"></div>
    <div class="content py-0"></div>    
</div>
<script src="{{asset('js/floating_window.js')}}"></script>