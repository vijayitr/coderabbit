<table class="table w-100" id="qcAgentReportTable">
    <thead>
        <tr>
            <th>#</th>
            <th class="w-10">

                <!--  <div class="filter d-none">
                    <input type="input" class="rangedatepicker form-control qc-date-filter">
                    <input type="hidden" class="start_date" name="">
                    <input type="hidden" class="end_date" name="">
                </div> -->
                <span>Date</span>
            </th>
            <th>
                <div class="filter d-none">
                    <select class="form-control qc-agent-filter">
                      <option value="">Select</option>
                    </select>
                </div>
                <span>Agent Name</span>
            </th>
            <th>
                <div class="filter d-none">
                    <select class="form-control qc-user-filter">
                      <option value="">Select</option>
                    </select>
                </div>
                <span>User Name</span>
            </th>
            <th class="w-10">
                <div class="filter d-none">
                    <select class="form-control qc-client-filter">
                        <option value="">Select</option>
                    </select>
                </div>
                <span>Client</span>
              
            </th>
            <th class="w-10">
                <div class="filter d-none">
                    <select class="form-control qc-activity-filter">
                        <option value="">Select</option>
                    </select>
                </div>
                <span>Activity</span>
            </th>
            <!-- <th>Sub Category</th> -->
            <th>
                <div class="filter d-none">
                    <select class="form-control qc-status-filter">
                        <option value="">Select</option>
                        <option value="pending" >Pending</option>
                        <option value="in_progress" >In Progress</option>
                        <option value="completed" >Completed</option>
                        
                    </select>
                </div>
                <span>Status</span>
               
            </th>
            <th>Call Duration</th>
            <th class="action-box">Actions</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>