@extends('layouts.app')

@section('content')
<div class="row manage_users">
    <div class="col-md-12">
        <div class="d-flex gap-2 align-items-center mb-3 filter-bar">
            <x-filter-select
                label="Date"
                :options="['Last Week', 'Last Month', 'Last Year', 'Show All']"
                selectedOption="Last Week"
                id="dateDropdown"/>

            <x-filter-select
                label="Sort By"
                :options="['Z to A', 'Date Added']"
                selectedOption="Date Added"
                id="sortByDropdown"/>
        </div> 

        <table class="table" id="clientTable">
            <thead>
                <tr>
                    <th>Client ID</th>
                    <th>Client Name</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        let dateFilter = ''; 
        let sortBy = ''; 
        
        $('#dateDropdown .option').on('click', function() {
            dateFilter = $(this).data('value');
            $('#dateDropdown .selected-option strong').text(dateFilter);
            clientsTable.ajax.reload(); 
        });
        
        $('#sortByDropdown .option').on('click', function() {
            sortBy = $(this).data('value');
            $('#sortByDropdown .selected-option strong').text(sortBy);
            clientsTable.ajax.reload(); 
        });

        const clientTable = $('#clientTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('getClients') }}",
                "type": "GET",
                "data": function(d) {
                    d.date_filter = dateFilter; 
                    d.sort_by = sortBy; 
                }
            },
            "columns": [
                {
                    "data": "formatted_id"
                }, // Client ID
                {
                    "data": "clientName",
                    "width": "60%"
                }, // Client Name
                {
                    "data": "formatted_created_at"
                }, // Created At
                {
                    "data": null, 
                    "render": function(data, type, row) {
                        return `
                            <span class="d-flex actions">
                                <a href="/clients/edit/${data.id}" class="table-edit"><img src="{{ url('images/icons/table-edit.png') }}" alt="Edit"></a>
                                <form action="/clients/${data.id}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this client?');">
                                    @csrf
                                    @method('DELETE')
                                    <img src="{{ url('images/icons/table-delete.png') }}" alt="Delete" style="cursor:pointer;" onclick="this.closest('form').submit();">
                                </form>
                            </span>
                        `;
                    }
                }
            ],
            "searching": true,
            "paging": true,
            "lengthChange": false,
            "order": [[0, 'asc']],
            "ordering": false,
            "dom": 'Bfrtip',
            "buttons": ['csv', 'excel'],
            "info": false,
            "language": {
                "paginate": {
                    "previous": '<i class="fa-solid fa-arrow-left"></i> Previous', 
                    "next": 'Next <i class="fa-solid fa-arrow-right"></i>' 
                }
            },
            "drawCallback": function(settings) {
                $('.page-numbers').remove();
                $('#clientTable_paginate .pagination li:not(.previous):not(.next)').wrapAll('<span class="page-numbers"></span>');
            }
        });
    });

</script>
@endpush