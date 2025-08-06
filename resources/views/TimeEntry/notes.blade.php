@foreach($notes as $key => $value)
	@php
        $noteTitle = Str::words(strip_tags($value->note), 5, '...'); // Extract first 5 words
    @endphp
    <div class="accordion-item mb-2">
        <h2 class="accordion-header" id="headingOne{{$key+1}}">
            <button class="accordion-button {{$key == 0 ? '' : 'collapsed'}}" type="button" data-bs-toggle="collapse" 
                data-bs-target="#collapseOne{{$key+1}}" 
                aria-expanded="true" 
                aria-controls="collapseOne{{$key+1}}">
                {{$noteTitle}} <span class="note_date">{{ \Carbon\Carbon::parse($value->created_at)->format('d/m/Y') }}</span>
            </button>
        </h2>
        <div id="collapseOne{{$key+1}}" class="accordion-collapse collapse {{$key == 0 ? 'show' : ''}}" aria-labelledby="headingOne">
            <div class="accordion-body">{{ $value->note }}</div>
        </div>
    </div>
@endforeach
