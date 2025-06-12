{{-- filepath: resources/views/livewire/show-ticket.blade.php --}}
<div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tickets-table" class="table table-hover table-striped">
                    <thead class="thead-light">
                        <tr>
                            <th>Status</th>
                            <th>No SPK</th>
                            <th>Input Date</th>
                            <th>Expected Finish Date</th>
                            <th>Reason</th>
                            <th>Reagent Name</th>
                            <th>Purpose</th>
                            <th>Request by</th>
                            <th>Department</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tickets as $ticket)
                            <tr>
                                <td>
                                    <span
                                        class="badge
                                        @if ($ticket->status === 'open') badge-danger
                                        @elseif($ticket->status === 'assigned') badge-warning
                                        @elseif($ticket->status === 'closed') badge-success @endif"
                                        @if ($ticket->status === 'rejected') style="background-color:#8B0000;color:#fff" @endif>
                                        {{ ucfirst($ticket->status) }} </span>
                                </td>
                                <td>{{ $ticket->spk_no }}</td>
                                <td>{{ $ticket->created_at ? $ticket->created_at->format('d-m-Y') : '-' }}</td>
                                <td>{{ $ticket->expected_date ? $ticket->expected_date->format('d-m-Y') : '-' }}</td>
                                <td>{{ $ticket->purpose ?? '-' }}</td>
                                <td>{{ optional($ticket->reagent)->reagent_name ?? '-' }}</td>
                                <td>{{ $ticket->purpose ?? '-' }}</td>
                                <td>{{ optional($ticket->requester)->name ?? '-' }}</td>
                                <td>{{ optional($ticket->requester->department)->name ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        const allowedStatuses = ['Open', 'Assigned', 'Rejected', 'Closed'];

        function addFilters() {
            if ($('#status-filter').length === 0 && $('#department-filter').length === 0) {
                // Get unique departments from the table
                let departments = new Set();
                $('#tickets-table tbody tr').each(function() {
                    let department = $(this).find('td').eq(8).text().trim();
                    if (department && department !== '-') departments.add(department);
                });

                // Build department select element
                let departmentSelect = $(
                    '<select id="department-filter" class="form-control form-control-sm" style="width:auto; display:inline-block; margin-right:10px;"><option value="">All Departments</option></select>'
                );
                Array.from(departments).sort().forEach(function(department) {
                    departmentSelect.append($('<option>').val(department).text(department));
                });

                // Build status select element with only allowed statuses
                let statusSelect = $(
                    '<select id="status-filter" class="form-control form-control-sm" style="width:auto; display:inline-block; margin-right:10px;"><option value="">All Statuses</option></select>'
                );
                allowedStatuses.forEach(function(status) {
                    statusSelect.append($('<option>').val(status).text(status));
                });

                $('#tickets-table_filter label').before(statusSelect);
                $('#tickets-table_filter label').before(departmentSelect);

                // Department filter logic
                departmentSelect.on('change', function() {
                    let val = $(this).val();
                    let dt = $('#tickets-table').DataTable();
                    if (val) {
                        dt.column(8).search('^' + $.fn.dataTable.util.escapeRegex(val) + '$', true, false).draw();
                    } else {
                        dt.column(8).search('', true, false).draw();
                    }
                });

                // Status filter logic
                statusSelect.on('change', function() {
                    let val = $(this).val();
                    let dt = $('#tickets-table').DataTable();
                    if (val) {
                        // Use custom search to match badge text
                        dt.column(0).search('^' + $.fn.dataTable.util.escapeRegex(val) + '$', true, false).draw();
                    } else {
                        dt.column(0).search('', true, false).draw();
                    }
                });
            }
        }

        function initTicketsTable() {
            if ($.fn.DataTable.isDataTable('#tickets-table')) {
                $('#tickets-table').DataTable().destroy();
            }
            $('#tickets-table').DataTable({
                order: [
                    [2, 'desc']
                ],
                language: {
                    emptyTable: "No tickets available"
                },
                columnDefs: [{
                    targets: 0,
                    render: function(data, type, row, meta) {
                        // Always return the badge HTML for display
                        if (type === 'display' || type === 'sort') {
                            return data;
                        }
                        // For filtering/searching, use the badge text
                        var div = document.createElement("div");
                        div.innerHTML = data;
                        var text = div.textContent || div.innerText || "";
                        return text.trim();
                    }
                }],
                initComplete: function() {
                    addFilters();
                }
            });
        }

        document.addEventListener('livewire:navigated', function() {
            setTimeout(initTicketsTable, 100);
        });

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initTicketsTable, 100);
        });
    </script>
@endpush
