<?php
if (!isset($_GET['dateid'])) {
        exit("Date missing");
}

$dateid = $_GET['dateid'];

if (isset($_GET['courseid'])){
    $courseid = str_replace('"', '', $_GET['courseid']);
}
else{
    exit("Courseid missing");
}

if (isset($_GET['interviewid'])){
    $interviewid = str_replace('"', '', $_GET['interviewid']);
}
else{
    exit("Interviewid missing");
}


$slotdata = shell_exec("/usr/bin/python py/genslots.py \"$dateid\" \"$courseid\" \"$interviewid\" ");
$usersel = shell_exec("/usr/bin/python py/genuserdata.py \"$dateid\" \"$courseid\" \"$interviewid\"");
$gencourse = shell_exec("/usr/bin/python py/gencourse.py")

?>

<!-- showslots.php?dateid=2026-02-03&courseid=1964&interviewid=0 -->
<!DOCTYPE html>
<html>

<head>
  <title>Slots Viewer</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 20px;
      background-color: #f8f9fa;
    }

    h2 {
      color: #2c3e50;
      margin: 20px 0 15px 0;
      font-weight: 600;
    }

    .section {
      margin-bottom: 25px;
    }

    .slots {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .slot-btn {
      padding: 12px 20px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      color: #fff;
    }

    .available {
      background-color: #4CAF50;
    }

    /* Green */
    .booked {
      background-color: #e53935;
      /*cursor: not-allowed;*/
    }

    /* Red */
    .past {
      background-color: #9e9e9e;
      cursor: not-allowed;
    }

    /* Grey */
    .legend span {
      display: inline-block;
      padding: 8px 16px;
      border-radius: 20px;
      margin-right: 15px;
      color: #fff;
      font-size: 13px;
      font-weight: 500;
    }

    .legend {
      margin-bottom: 25px;
      padding: 15px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    form {
      margin-bottom: 25px;
      padding: 20px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      border: 1px solid #e9ecef;
    }

    form label {
      font-size: 16px;
      color: #495057;
      margin-right: 15px;
    }

    form input[type="date"], select {
      padding: 10px;
      border: 1px solid #ced4da;
      border-radius: 6px;
      margin-right: 15px;
      font-size: 14px;
    }

    form input[type="submit"] {
      padding: 10px 20px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
    }

    form input[type="submit"]:hover {
      background-color: #0056b3;
    }

    .selected {
      background-color: #ff9800 !important;
      transform: scale(1.05);
    }
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
    }

    .modal-content {
      background-color: #fff;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      padding: 20px;
      border-radius: 8px;
      width: 400px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .modal-content h3 {
      margin-top: 0;
      color: #2c3e50;
    }

    .modal-content input, .modal-content select {
      width: 100%;
      padding: 8px;
      margin: 5px 0 15px 0;
      border: 1px solid #ddd;
      border-radius: 4px;
    }

    .modal-buttons {
      text-align: right;
      margin-top: 20px;
    }

    .modal-buttons button {
      padding: 8px 16px;
      margin-left: 10px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    .save-btn {
      background-color: #28a745;
      color: white;
    }

    .cancel-btn {
      background-color: #6c757d;
      color: white;
    }
</style>
<!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>
  <!-- Date Picker -->
  <form method="get">
    <label><b>Pick a date:</b></label>
    <input type="date" name="date" required>
    <label><b>Pick a course:</b></label>
    <select name="courseid" id="courseid">
        <option value=''>Select a Course</option>
        <?php echo $gencourse;?>     
    </select>
    <label><b>Pick Interview:</b></label>
    <select name="interviewid" id="interviewid" required>
      <option value="">Select Interview</option>
      <option value="0">Mock 1 Interview</option>
      <option value="1">Mock 2 Interview</option>
    </select>

    <input type="submit" value="Show slots">
  </form>
 
<label>Select Faculty:</label>
<select id="facultySelect">
  <option value="">All Faculties</option>
</select> 
  <h2></h2>

  <!-- Legend -->
<!--  <div class="legend">
    <span style="background:#4CAF50;">Available</span>
    <span style="background:#e53935;">Booked</span>
    <span style="background:#9e9e9e;">Past</span>
    <span style="background-color: #ff9800;">Selected</span>
  </div>-->

        <?php echo $slotdata; ?>

<!--Add Modal -->
<div id="slotModal" class="modal">
  <div class="modal-content">
    <h3>Book Slot</h3>
    <label>Booking ID:</label>
    <input type="text" id="modalBookingId" readonly>
    
    <label>Course ID:</label>
    <input type="text" id="modalCourseId" readonly>
    
    <label>User Email:</label>
    <select name="" id="modalUser" >
      <option value="">Select User Email</option>
      <?php echo $usersel; ?>
      
    </select>
    
    <label>Interview ID:</label>
    <input type="text" id="modalInterviewId" readonly>
    
    <label>Faculty:</label>
    <input type="text" id="modalFaculty" readonly>
    
    <label>Slot Time:</label>
    <input type="text" id="modalSlotTime" readonly>
    
    <div class="modal-buttons">
      <button class="save-btn" id="saveBtn">Save</button>
      <button class="cancel-btn" id="cancelBtn">Cancel</button>
    </div>
  </div>
</div>

<!-- Booked Details Modal -->
<div id="bookedDetailsModal" class="modal">
  <div class="modal-content">
    <h3>Booking Details</h3>

    <label>Booking ID:</label>
    <input type="text" id="detailBookingId" readonly>

    <label>User Email:</label>
    <input type="text" id="detailUserEmail" readonly>
    
    <label>Faculty Name:</label>
    <input type="text" id="detailFacultyName" readonly>

    <label>Course ID:</label>
    <input type="text" id="detailCourseId" readonly>

    <label>Slot Time:</label>
    <input type="text" id="detailSlotTime" readonly>
    
    <label>Interview Id:</label>
    <input type="text" id="detailinterviewId" readonly>


    <div class="modal-buttons">
       <button class="delete-btn" id="deleteBtn" style="background-color: #dc3545; color: white;">Delete Slot</button> 
      <button class="save-btn" id="updateBtn">UnBook</button>
      <button class="cancel-btn" id="closeDetailsBtn">Close</button>
    </div>
  </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
const courseid = <?php echo $courseid ?>;
</script>
<script>
//Filter on submit the date course interviewid
$(document).ready(function () {
// Initialize Select2 for user dropdown
      $('#modalUser').select2({
        placeholder: 'Search and select user...',
        allowClear: true,
        width: '100%'
      });
// Handle available slot clicks
  $('.available').click(function () {
    $('.slot-btn').removeClass('selected');
    $(this).addClass('selected');
    alert('Slot ' + $(this).text() + ' selected!');
  });

  // Handle form submission
  $('form').submit(function (e) {
    e.preventDefault();
    const selectedDate = $('input[name="date"]').val();
    const selectedCourse = $('#courseid').val();
    const selectedInterview = $('#interviewid').val();
    
    if (!selectedDate || !selectedCourse || !selectedInterview){
        alert("Please select Date, Course and Interview");
        return;
    }
    if (selectedDate && selectedCourse && selectedInterview) {
        window.location.href = window.location.pathname + '?dateid=' + selectedDate + '&courseid=' + selectedCourse + '&interviewid=' + selectedInterview;
    }
  });
    const params = new URLSearchParams(window.location.search);
      const date = params.get('dateid');

      if (date) {
              $('h2').text('Slots for ' + date);
                }

});

//Filter dropdown based on Present Faculty
$(document).ready(function () {

  let facultySet = {};

  // STEP 1: build faculty dropdown from slots
  $('.slot-btn').each(function () {
    let fid = $(this).data('facultyid');
    let fname = $(this).data('facultyname');

    if (!facultySet[fid]) {
      facultySet[fid] = true;
      $('#facultySelect').append(
        `<option value="${fid}">${fname}</option>`
      );
    }
  });

  // STEP 2: filter slots on faculty change
  $('#facultySelect').on('change', function () {
    let selectedFaculty = $(this).val();

    if (selectedFaculty === '') {
      $('.slot-btn').show();
    } else {
      $('.slot-btn').each(function () {
        $(this).toggle(
          $(this).data('facultyid') == selectedFaculty
        );
      });
    }
    
    // Show/hide "No PM slots available" message
    let pmSlotsVisible = $('.section:last .slot-btn:visible').length > 0;
    $('#pmNoSlots').toggle(!pmSlotsVisible);
  
// Show/hide "No AM slots available" message
    let amSlotsVisible = $('.section:first .slot-btn:visible').length > 0;
    $('#amNoSlots').toggle(!amSlotsVisible);
  });
});

//Modal add user for green button
  $(document).ready(function () {
      // Handle available slot clicks
      $('.available').click(function () {
        $('.slot-btn').removeClass('selected');
        $(this).addClass('selected');
        
        // Populate modal with slot data
        $('#modalBookingId').val($(this).data('bookingid'));
        $('#modalFaculty').val($(this).data('facultyname'));
        $('#modalSlotTime').val($(this).text());
        $('#modalUser').val('');
        //$('#modalInterviewId').val($(this).data('interviewid'));
        
        var inid = $(this).data('interviewid');
        //Map 0/1
        var interviewLabel = (inid == 0) ? 'Mock 1 Interview' : 'Mock 2 Interview';
        //show the label
        $('#modalInterviewId').val(interviewLabel);

        $('#modalCourseId').val($(this).data('courseid'));
        
        // Show modal
        $('#slotModal').show();
      });

      // Modal save button
      $('#saveBtn').click(function() {
          const userId = $("#modalUser").val();
          let interviewLabel = $("#modalInterviewId").val();
          const bookingId = $("#modalBookingId").val();
          //Reverse mapping: label → numeric ID
          let interviewId;
          if (interviewLabel === "Mock 1 Interview"){
              interviewId =0;
          }else if (interviewLabel === "Mock 2 Interview"){
              interviewId = 1;
          }
          if (!userId){
              alert("Need to select both User Id and Interview Id");
              return;
          }
          console.log(interviewId);
          //send data to backend
          $.ajax({
              url: 'cgi/save_booking.cgi',
              method: 'POST',
              data: {
                  user_id: userId,
                  interview_id: interviewId,
                  booking_id: bookingId
              },
              success: function(response){
                response = response.trim();
                if(response === "success"){
                    alert("Slots generated Successfully!");
                    $('#slotModal').hide();
                    location.reload();
                } else if(response === "duplicate"){
                    alert("This user already has a slot for this interview/course.");
                } else if(response === "notfound"){
                    alert("Booking ID not found.");
                } else {
                    alert("Error saving booking, Please try again.");
                }
            }




          });
        $('#slotModal').hide();
      });

      // Modal cancel button
      $('#cancelBtn').click(function() {
        $('#slotModal').hide();
      });
});

//clear modal data
function clearBookedModal() {
    $('#detailBookingId').val('');
    $('#detailUserEmail').val('');
    $('#detailFacultyName').val('');
    $('#detailCourseId').val('');
    $('#detailSlotTime').val('');
}


// Red button data
$(document).on('click', '.slot-btn.booked', function () {

    // 1. Clear old data
    $('#detailBookingId, #detailUserEmail, #detailFacultyName, #detailCourseId, #detailSlotTime').val('');

    // 2. Get booking id
    const bookingId = $(this).data('bookingid');
    const courseId  = $(this).data('courseid');

    // 3. Call backend
    $.ajax({
        url: 'cgi/fetch.cgi',
        type: 'GET',
        dataType: 'json',
        data: { bookingid: bookingId,
            courseid: courseId
        },

        success: function (res) {
            if (res.status !== 'success') {
                alert('Failed to fetch booking details');
                return;
            }

            $('#detailBookingId').val(res.data.bookingid);
            $('#detailUserEmail').val(res.data.useremail);
            $('#detailFacultyName').val(res.data.facultyname);
            $('#detailCourseId').val(res.data.courseid);
            $('#detailSlotTime').val(res.data.slottime);
            $('#detailinterviewId').val(res.data.interviewid);

            $('#bookedDetailsModal').show();
        },

        error: function () {
            alert('Server error');
        }
    });
    $('#closeDetailsBtn').click(function() {
        $('#bookedDetailsModal').hide();
      });

});


//Modal update btn for the booked buttons

$(document).ready(function(){
    $("#updateBtn").click(function(){
        const bookingId = $('#detailBookingId').val().trim();
        if (!confirm("Are you sure you want to unbook this slot?")) {
            return; //clicked cancel
        }
        //Send data to backend
        $.ajax({
            url: 'cgi/unbook_slot.cgi',
            method: 'POST',
            data:{booking_id: bookingId},
            success: function(response){
                response = response.trim();
                if (response === 'success'){
                    alert("Unbooked successfully");
                    location.reload();
                }else{
                    alert("Error Unbooking the slots");
                }
            }
        });
    });
    $("#deleteBtn").click(function(){
        const bookingId = $('#detailBookingId').val().trim();
        if (!confirm("Are you sure you want to permanently delete this slot? This action cannot be undone.")) {
            return;
        }
        $.ajax({
            url: 'cgi/delete_slot.cgi',
            method: 'POST',
            data:{booking_id: bookingId},
            success: function(response){
                response = response.trim();
                if (response === 'success'){
                    alert("Slot deleted successfully");
                    location.reload();
                }else{
                    alert("Error deleting the slot");
                }
            }
        });
    });
});

$(document).ready(function () {

    // AM slots
    if ($('.section:first .slot-btn').length === 0) {
        $('#amNoSlots').show();
    }

    // PM slots
    if ($('.section:last .slot-btn').length === 0) {
        $('#pmNoSlots').show();
    }
});



</script>
</body>

</html>
