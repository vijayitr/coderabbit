@props(['recordingId'])

<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-recording-id="{{ $recordingId }}" class="load-audio" >
  <!-- Circular background -->
  <circle cx="12" cy="12" r="12" fill="#FF0000" fill-opacity="0.1"/>
  <!-- Inner circle -->
  <circle cx="12" cy="12" r="6" fill="#FF0000"/>
  <!-- Play button -->
  <polygon points="10,9 16,12 10,15" fill="white"/>
</svg>
