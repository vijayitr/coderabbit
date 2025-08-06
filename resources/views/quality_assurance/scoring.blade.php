@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div >
            <h2 class="mb-4">Quality Check Score Sheet</h2>
            <div class="row">
                  <div class="col-md-12">
                    <div class=" ms-4 mb-2 mb-2">
                        <table class="table" id="callData">
                            <thead>
                                <tr>
                                    <th>User Name</th>
                                    <th>Client Name</th>
                                    <th>Workflow Name</th>
                                    <th>Process Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $agent_name }}</td>
                                    <td>{{ $client_name }}</td>
                                    <td>{{ $workflow_name }}</td>
                                    <td>{{ $process_name }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                  </div>
                    <div class="col-md-12">
                        <div class=" ms-4 mb-2 mb-2">
                            <div class="row mt-2 recording-container score_recording">
                                <h6>
                                    <strong>Call Recording:</strong>
                                    <button class="ms-2 btn btn-sm btn-primary load-recording-btn">
                                      <i class="bi bi-cloud-download"></i> Load Recording
                                    </button>
                                    <i class="ms-2 fas fa-spinner fa-spin d-none call_recording_loading_icon"></i>
                                </h6>
                                <audio controls="" data-recording-id="{{$recording_id}}" class="d-none">
                                    Your browser does not support the audio element.
                                </audio>
                            </div>
                        </div>
                    </div>
                </div>
            <form id="qcForm" action="{{ route('qualityAssurance.submitScore') }}" method="POST">
                @csrf
                <input type="hidden" name="score" class="qa_score" value="0">
                <input type="hidden" name="allocation_id" value="{{$allocation_id}}">
              <div id="question-container"></div>

                <div class="row mb-3">
                  <div class="col-md-12">
                    <div class=" ms-4 mb-2 mb-2">
                        <label>Details</label>
                        <textarea type="text" name="details" class="form-control" placeholder="Details" value="{{ $score->details ?? '' }}">{{ $score->details ?? '' }}</textarea>
                    </div>
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-4">
                    <div class=" ms-4 mb-2 mb-2">
                        <label>Status</label>
                        <select name="status" class="form-control" required="">
                            <option value="pending" {{$status == 'pending' ? 'selected' : ''}}>Pending</option>
                            <option value="in_progress" {{$status == 'in_progress' ? 'selected' : ''}}>In-progress</option>
                            <option value="completed" {{$status == 'completed' ? 'selected' : ''}}>Completed</option>
                        </select>
                    </div>
                  </div>
                </div>

                <div class="card shadow rounded-2 mb-4">
                  <div class="card-body text-center">
                    <h3 class="card-title fw-bold">Call Quality Score</h3>
                    <h1 id="total-score" class="display-4 text-success fw-bold">0 / 100</h1>
                    <div class="progress mx-auto mt-3" style="height: 20px; max-width: 300px;">
                      <div id="score-progress" class="progress-bar progress-bar-striped bg-success" style="width: 0%;">0%</div>
                    </div>
                  </div>
                </div>


              <button type="submit" class="btn btn-primary mt-3">Submit Score</button>
            </form>

            <div id="result" class="mt-4 alert alert-info d-none"></div>
          </div>
        
    </div>
</div>

@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        $('#callData').DataTable({
            "searching": false,
            "paging": false,
            "lengthChange": false,
            "order": [[0, 'asc']],
            "ordering": false,
            "dom": 'Bfrtip',
            "buttons": ['csv', 'excel'],
            "info": false
        });

        var parameters = [];

        @foreach($QcParameter as $param)
            parameters.push({ id: "{{$param->id}}", name: "{{$param->name}}", weight: "{{$param->weight}}" });
        @endforeach
        var scores = {};
        @if(!empty($score->parameter_scores))
            scores = {!! json_encode($score->parameter_scores) !!};
        @endif

        const container = document.getElementById("question-container");
        parameters.forEach((param, index) => {
            const scoreValue = scores[param.id];
            const checked = typeof scoreValue !== "undefined" ? String(scoreValue) : '';
            console.log(param.id, scores[param.id], checked, checked == '3');
          const i = index + 1;
          const card = document.createElement("div");
          card.className = "card mb-3 rounded-2";
          card.setAttribute("data-weight", param.weight);
          card.innerHTML = `
            <div class="card-header fw-bold">
              ${i}. ${param.name} <span>(Weight: ${param.weight})</span>
            </div>
            <div class="card-body">
              <div class="form-check form-check-inline">
                <input class="form-check-input score-input" type="radio" name="q[${param.id}]" value="0" ${checked == '0' ? 'checked' : ''}>
                <label class="form-check-label text-danger">0 - Fatal</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input score-input" type="radio" name="q[${param.id}]" value="1" ${checked == '1' ? 'checked' : ''}>
                <label class="form-check-label text-warning">1 - Average</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input score-input" type="radio" name="q[${param.id}]" value="2" ${checked == '2' ? 'checked' : ''}>
                <label class="form-check-label text-success">2 - Good</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input score-input" type="radio" name="q[${param.id}]" value="3" ${checked == '3' ? 'checked' : ''}>
                <label class="form-check-label text-primary">3 - Excellent</label>
              </div>
              <div class="form-check mt-2">
                <input class="form-check-input na-check" type="checkbox" name="q[${param.id}]" value="Not Applicable" data-question="q[${param.id}]" ${checked == 'Not Applicable' ? 'checked' : ''}>
                <label class="form-check-label">Not Applicable</label>
              </div>
            </div>
          `;
          container.appendChild(card);
        });
         calculateScore();

    document.addEventListener("change", calculateScore);

       function calculateScore() {
          const cards = document.querySelectorAll(".card[data-weight]");
          let totalScore = 0;
          let totalWeight = 0;
          const maxScorePerItem = 3;

          cards.forEach(card => {
            const weight = parseFloat(card.getAttribute("data-weight"));
            const input  = card.querySelector(".score-input");
            const name = input?.name;
            const isNA = card.querySelector(`.na-check[data-question='${name}']`)?.checked;

            if (!isNA) {
              const selected = card.querySelector(`input[name='${name}']:checked`);
              if (selected) {
                const score = parseInt(selected.value);
                totalScore += (score / maxScorePerItem) * weight;
                totalWeight += weight;
              }
            }
          });

          const finalScore = totalWeight > 0 ? (totalScore / totalWeight) * 100 : 0;

          // Update Score UI
          const scoreElement = document.getElementById("total-score");
          const progressBar = document.getElementById("score-progress");

          $('.qa_score').val(finalScore.toFixed(0));
          scoreElement.textContent = `${finalScore.toFixed(0)} / 100`;
          progressBar.style.width = `${finalScore.toFixed(0)}%`;
          progressBar.textContent = `${finalScore.toFixed(0)}%`;

          // Optional: Change bar color based on score
          progressBar.classList.remove("bg-success", "bg-warning", "bg-danger");
          if (finalScore >= 80) {
            progressBar.classList.add("bg-success");
          } else if (finalScore >= 50) {
            progressBar.classList.add("bg-warning");
          } else {
            progressBar.classList.add("bg-danger");
          }
        }

    });
</script>
@endpush