<!-- filepath: resources\views\livewire\history.blade.php -->
<x-slot:subTitle>{{ $subTitle }}</x-slot>
<div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-hover table-striped display datatable text-center">
                        <thead class="thead-light">
                            <tr>
                                <th>Output Date</th>
                                <th>Reagent Name</th>
                                <th>Dept. Owner</th>
                                <th>Maker</th>
                                <th>Catalog No</th>
                                <th>Request Qty</th>
                                <th>Purpose</th>
                                <th>Requester</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($histories as $history)
                                <tr>
                                    <td>{{ $history->output_date ? date('d-m-Y', strtotime($history->output_date)) : '-' }}
                                    </td>
                                    <td>{{ $history->reagent_name }}</td>
                                    <td>{{ $history->dept_owner }}</td>
                                    <td>{{ $history->maker }}</td>
                                    <td>{{ $history->catalog_no }}</td>
                                    <td>{{ $history->request_qty }} {{ $history->quantity_uom }}</td>
                                    <td>{{ $history->purpose }}</td>
                                    <td>{{ $history->user }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @push('scripts')
            <script>
                // Add Reagent Name and Dept Owner filter dropdowns to the left of the search box and hide labels
                function addFilters() {
                    // Remove existing filters if present
                    $('#reagentNameFilterWrapper').remove();
                    $('#deptOwnerFilterWrapper').remove();

                    // Get unique reagent names from table
                    let reagentNames = new Set();
                    // Get unique dept owners from table
                    let deptOwners = new Set();

                    $('.datatable tbody tr').each(function() {
                        let name = $(this).find('td').eq(1).text().trim();
                        let deptOwner = $(this).find('td').eq(2).text().trim(); // Dept. Owner is column 2
                        if (name && name !== 'No data available') {
                            reagentNames.add(name);
                        }
                        if (deptOwner && deptOwner !== 'No data available') {
                            deptOwners.add(deptOwner);
                        }
                    });

                    // Build Reagent Name dropdown (no label)
                    let reagentFilterHtml = '<div id="reagentNameFilterWrapper" style="display:inline-block; margin-right:10px;">' +
                        '<select id="reagentNameFilter" class="form-control form-control-sm" style="width:auto; display:inline-block;">' +
                        '<option value="">Reagent Name</option>';
                    reagentNames.forEach(function(name) {
                        reagentFilterHtml += `<option value="${name}">${name}</option>`;
                    });
                    reagentFilterHtml += '</select></div>';

                    // Build Dept Owner dropdown (no label)
                    let deptOwnerFilterHtml = '<div id="deptOwnerFilterWrapper" style="display:inline-block; margin-right:10px;">' +
                        '<select id="deptOwnerFilter" class="form-control form-control-sm" style="width:auto; display:inline-block;">' +
                        '<option value="">Dept. Owner</option>';
                    deptOwners.forEach(function(name) {
                        deptOwnerFilterHtml += `<option value="${name}">${name}</option>`;
                    });
                    deptOwnerFilterHtml += '</select></div>';

                    // Insert before the datatable search box: Reagent Name, then Dept Owner
                    $('.dataTables_filter').prepend(deptOwnerFilterHtml);
                    $('.dataTables_filter').prepend(reagentFilterHtml);

                    // On change, filter the datatable
                    $('#reagentNameFilter').on('change', function() {
                        let val = $(this).val();
                        $('.datatable').DataTable().column(1).search(val, false, false).draw();
                    });
                    $('#deptOwnerFilter').on('change', function() {
                        let val = $(this).val();
                        $('.datatable').DataTable().column(2).search(val, false, false).draw();
                    });
                }

                function initDataTable() {
                    if ($.fn.DataTable.isDataTable('.datatable')) {
                        $('.datatable').DataTable().destroy();
                    }
                    $('.datatable').DataTable({
                        "order": [
                            [1, "asc"]
                        ],
                        "language": {
                            "emptyTable": "No data available in table"
                        },
                        "initComplete": function() {
                            addFilters();
                        }
                    });
                }

                $(document).ready(function() {
                    initDataTable();
                });

                window.addEventListener('livewire:navigated', function() {
                    setTimeout(initDataTable, 300);
                }, {
                    once: true
                });

                window.addEventListener('livewire:initialized', function() {
                    setTimeout(initDataTable, 300);
                }, {
                    once: true
                });
            </script>
        @endpush
    </div>
