@extends('layouts.app')

@section('content')
<div class="row manage_allocation">
    <div class="col-md-6">
        <h2>Update Profile</h2>

	    @if(session('success'))
	        <div class="alert alert-success">{{ session('success') }}</div>
	    @endif

	    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
		    @csrf
		    <div class="mb-3">
		        <label class="form-label">Profile Image</label>
		        <input type="file" id="profileImageInput" class="form-control">
		    </div>

		    <div class="mb-3">
		        <label class="form-label">Name</label>
		        <input type="text" name="name" class="form-control" value="{{ Auth::user()->name }}" required>
		    </div>

		    <div class="mb-3">
		        <label class="form-label">Email</label>
		        <input type="email" name="email" class="form-control" value="{{ Auth::user()->email }}" required disabled>
		    </div>

		    <button type="submit" class="btn btn-primary">Update Profile</button>
		</form>
    </div>
</div>
@endsection
@push('scripts')
<script>
    $(document).ready(function() {

    	document.getElementById("profileImageInput").addEventListener("change", function(event) {
    		let file = event.target.files[0];
            let errorMessage = '';

            // Validate if a file is selected
            if (file) {
	            // Validate file type (jpeg, png, jpg, gif)
	            const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
	            if (file && !validTypes.includes(file.type)) {
	                errorMessage = 'Please select a valid image file (jpeg, png, jpg, gif).';
	            }

	            // Validate file size (max 2MB)
	            const maxSize = 2 * 1024 * 1024; // 2MB
	            if (file && file.size > maxSize) {
	                errorMessage = 'The image size should be less than 2MB.';
	            }

	            // If there is an error message, display it and stop the upload process
	            if (errorMessage) {
	               toastr.warning(errorMessage, 'Warning');
	                return;
	            }
			    let formData = new FormData();
			    formData.append("profile_image", event.target.files[0]);
			    formData.append("_token", "{{ csrf_token() }}");

			    fetch("{{ route('profile.upload.image') }}", {
			        method: "POST",
			        body: formData
			    })
			    .then(response => response.json())
			    .then(data => {
			        if (data.success) {
			            document.querySelector(".user-avatar").src = data.image;
			        } else {
			            alert("Failed to upload image");
			        }
			    });
            }

		});
    });
</script>

@endpush