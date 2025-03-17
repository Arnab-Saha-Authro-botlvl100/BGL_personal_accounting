<x-app-layout>
    @include('layouts.links')
    <div class="container" id="main-content" style="transition: 0.3s;">
        <div class="mt-4 mx-auto px-2" >
            <div class="container" id="initial-div">
                <style>
                    @media (max-width: 768px) {
                        .form-container {
                            width: 100% !important;
                        }
                    }
                </style>
                
                <form id="cashbookForm" class="form-container mx-auto" style="width: 80%;">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        
                        <div class="col-md-5">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                
                        <div class="col-md-2 text-md-end text-center" style="margin-top: 33px;">
                            <button type="submit" class="btn btn-primary w-100">Submit</button>
                        </div>
                    </div>
                </form>
                
            </div>
           
        </div>
        <div id="reportdiv">

        </div>
    </div>

<!-- jQuery (Ensure jQuery is included) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function () {
        $("#cashbookForm").on("submit", function (e) {
        e.preventDefault(); // Prevent default form submission
        $.ajax({
            url: "{{ route('report.cashbook.report') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function (response) {
                $("#reportdiv").html(response.html);
            },
            error: function (xhr) {
                alert("Something went wrong. Please try again.");
            }
            });
        });

    });
</script>
</x-app-layout>