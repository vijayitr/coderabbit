@props(['notes'])
<svg viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg" class="view-notes-btn" data-notes="{{ $notes }}"  data-bs-toggle="modal" 
data-bs-target="#callSummaryModal">
  <rect x="0.821777" y="0.764648" width="24.7646" height="24.7646" rx="12.3823" fill="#800080" fill-opacity="0.1"/>
  
  <!-- Phone Handset -->
  <path d="M10 10C10.8 11.5 12.5 13.2 14 14" stroke="#800080" stroke-width="0.714362" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M14 14L16 12" stroke="#800080" stroke-width="0.714362" stroke-linecap="round" stroke-linejoin="round"/>
  
  <!-- Notes -->
  <path d="M10 16H16" stroke="#800080" stroke-width="0.714362" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M10 18H14" stroke="#800080" stroke-width="0.714362" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
