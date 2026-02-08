<?php
if (!isset($_GET['course'])) {
    exit("Course ID missing");
}

$course = (int)$_GET['course']; // cast to int for safety
$facdetails = exec('python py/gendata.py');
?>

<!-- genslots.php?course=1964 -->
<!DOCTYPE html>
<html>

<head>
  <title>Faculty Availability</title>
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      margin: 0;
      padding: 20px;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .container {
      background: white;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
      max-width: 600px;
      width: 100%;
    }

    h2 {
      color: #333;
      text-align: center;
      margin-bottom: 30px;
      font-size: 28px;
      font-weight: 600;
    }

    .form-group {
      margin-bottom: 25px;
    }

    label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #555;
      font-size: 14px;
    }

    input,
    select {
      width: 100%;
      padding: 12px 16px;
      border: 2px solid #e1e5e9;
      border-radius: 8px;
      font-size: 16px;
      transition: all 0.3s ease;
      background-color: #f8f9fa;
    }

    input:focus,
    select:focus {
      outline: none;
      border-color: #667eea;
      background-color: white;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    button {
      width: 100%;
      padding: 15px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 10px;
    }

    button:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }

    button:active:not(:disabled) {
      transform: translateY(0);
    }

    button:disabled {
      background: #ccc;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    .button-group {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
      margin-top: 20px;
    }

    @media (max-width: 600px) {
      .container {
        padding: 20px;
        margin: 10px;
      }

      .row {
        grid-template-columns: 1fr;
        gap: 15px;
      }

      h2 {
        font-size: 24px;
      }
    }
  </style>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
  <div class="container">
    <h2>Faculty Availability Input</h2>
    <form id="slotForm">

      <div class="form-group">
        <label for="facultyid">Faculty ID</label>
        <select id="facultyid" name="facultyid" required>
          <option value="">Select Faculty</option>
          <?php echo $facdetails;?> 
        </select>
      </div>
      <div class="form-group">
        <label for="facultyid">Interview Id</label>
        <select id="interviewid" name="interviewid" required>
          <option value="">Select InterviewId</option>
          <option value="0">Mock 1 Interview</option>
          <option value="1">Mock 2 Interview</option>
        </select>
      </div>
      <div class="form-group">
        <div class="row">
          <div>
            <label for="start_date">Start Date</label>
            <input type="date" id="start_date" name="start_date" required>
          </div>
        </div>
      </div>

      <div class="form-group">
        <div class="row">
          <div>
            <label for="start_time">Daily Start Time</label>
            <input type="time" id="start_time" name="start_time" required>
          </div>
          <div>
            <label for="end_time">Daily End Time</label>
            <input type="time" id="end_time" name="end_time" required>
          </div>
        </div>
      </div>
      <div id="result" style="margin-top:20px;"></div>
      
      <div class="button-group">
        <button type="button" id="checkAvailability">Check Availability</button>
        <button type="button" id="generateSlots" disabled>Generate Slots</button>
      </div>
    </form>
  </div>

<script>
  const COURSE_ID = <?php echo $course; ?>;
</script>
  <script>
    $(document).ready(function () {
      $('#checkAvailability').on('click', function () {
        const formData = {
          course: COURSE_ID,
          facultyid: $('#facultyid').val(),
          start_date: $('#start_date').val(),
          end_date: $('#start_date').val(),
          start_time: $('#start_time').val(),
          end_time: $('#end_time').val()
        };

        $.ajax({
          url: 'cgi/check_availability.cgi',
          type: 'POST',
          data: formData,
          success: function (response) {
            if (response.includes('Available')) {
              $('#checkAvailability').prop('disabled', true);
              $('#generateSlots').prop('disabled', false);
              alert('Faculty is available! You can now generate slots.');
            } else if (response.includes('Conflict')) {
              alert('Faculty already has slots in this time range.');
            } else {
              alert('Unexpected response from server.');
            }
          },
          error: function () {
            alert('Error checking availability');
          }
        });
      });

      $('#generateSlots').on('click', function () {
        const formData = {
          course: COURSE_ID,
          facultyid: $('#facultyid').val(),
          interviewid: $('#interviewid').val(),
          start_date: $('#start_date').val(),
          end_date: $('#start_date').val(),
          start_time: $('#start_time').val(),
          end_time: $('#end_time').val()
        };

        $.ajax({
          url: 'cgi/generate_slots.cgi',
          type: 'POST',
          data: formData,
          success: function (response) {
            $('#result').html(response);
            alert('Slots generated successfully!');
            const dateid = $('#start_date').val();
            const interviewid = $('#interviewid').val(); 
            window.location.href = 'http://dev.oliveboard.in/thakshith/interviewslotsob/slotsgen/showslots.php?dateid=' + `${dateid}` + '&courseid=' + `${COURSE_ID}` + '&interviewid=' + `${interviewid}`;
          },
          error: function () {
            alert('Error generating slots');
          }
        });
      });
    });
  </script>
</body>

</html>


