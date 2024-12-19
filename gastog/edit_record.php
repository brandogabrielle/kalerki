<?php

$id = $_GET['id'] ?? 0;
// Get patient ID
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "oro_va_dental_records"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the patient record from the database
$sql = "SELECT * FROM patient_registry WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();

if (!$patient) {
    die("Patient not found.");
}

$appointment_sql = "SELECT * FROM appointments WHERE patient_id = ?";
$appointment_stmt = $conn->prepare($appointment_sql);
$appointment_stmt->bind_param("i", $id);
$appointment_stmt->execute();
$appointment_result = $appointment_stmt->get_result();
$appointment = $appointment_result->fetch_assoc();

$stmt->close();
$appointment_stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="patient_registry.css">
    <title>Edit Record</title>
</head>
<body>
    <div class="container">
        <div class="header">
        <h1>Edit Record</h1>
            <div class ="bruh-button">
                <a href="check_appointment.php">View Appointments</a>
            </div>
        </div>
        <form action="update_record.php" method="POST">
            <div class="form-group">
                <label for="registryDate">Date of Registry:</label>
                <input type="date" id="registryDate" name="registryDate" value="<?php echo $patient['registry_date']; ?>" required>
            </div>

            <div class="name-section">
                <div class="form-group">
                    <label for="lastName">Last Name:</label>
                    <input type="text" id="lastName" name="lastName" value="<?php echo $patient['last_name']; ?>"required>
                </div>

                <div class="form-group">
                    <label for="givenName">Given Name:</label>
                    <input type="text" id="givenName" name="givenName" value="<?php echo $patient['first_name']; ?>"required>
                </div>

                <div class="form-group">
                    <label for="middleName">Middle Name:</label>
                    <input type="text" id="middleName" name="middleName" value="<?php echo $patient['middle_name']; ?>"required>
                </div>
            </div>

            <div class="form-group">
                <label for="birthday">Date of Birth:</label>
                <input type="date" id="birthday" name="birthday" value="<?php echo $patient['birthday']; ?>"required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo $patient['email']; ?>">
            </div>

            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="<?php echo $patient['address']; ?>">
            </div>

            <div class="form-group">
                <label for="mobileNumber">Mobile Number:</label>
                <input type="tel" id="mobileNumber" name="mobileNumber" value="<?php echo $patient['mobile_number']; ?>" placeholder="09*********" pattern="^[0-9]{11}$" required>
            </div>

            <div class="form-group">
                <label for="addInfo">Additional Info:</label>
                <input type="text" name="addInfo" value="<?= $appointment['add_info']; ?>">
            </div>

            <div class="button-container">
                <button type="submit">Update</button>
            </div>
        </form>
        <script>

document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("form");
    const registryDateInput = document.querySelector("#registryDate");
    const birthdayInput = document.querySelector("#birthday");
    const appointmentDateInput = document.querySelector("#appointmentDate"); // Assuming your Appointment Date has the ID 'appointmentDate'
    const startTimeInput = document.querySelector("#startTime");
    const endTimeInput = document.querySelector("#endTime");

    // Automatically set today's date in the registryDate input field
    const today = new Date();
    const todayLocal = new Date(today.getTime() - today.getTimezoneOffset() * 60000)
    .toISOString()
    .split("T")[0];

    registryDateInput.value = todayLocal;


    
    form.addEventListener("submit", function (event) {
        // Normalize today's date for comparison (without time)
        today.setHours(0, 0, 0, 0);

        // Function to validate the year for date inputs
        function validateDateInput(dateInput) {
            const date = new Date(dateInput.value);
            const year = date.getFullYear();
            if (year < 1000 || year > 9999) {
                alert("Please enter a valid 4-digit year.");
                event.preventDefault(); // Prevent form submission
                return false;
            }
            return true;
        }

        // Validate all date inputs for 4-digit year
        const dateInputs = document.querySelectorAll("input[type='date']");
        for (const dateInput of dateInputs) {
            if (!validateDateInput(dateInput)) {
                return;
            }
        }

        // Get the selected registry date
        const registryDate = new Date(registryDateInput.value);
        registryDate.setHours(0, 0, 0, 0); // Normalize to midnight for comparison

        // Check if registry date is not today
        if (registryDate.getTime() !== today.getTime()) {
            const confirmation = confirm(
                "The registry date is not today. Are you sure you want to submit this record?"
            );
            if (!confirmation) {
                event.preventDefault(); // Prevent form submission if user clicks "Cancel"
                return;
            }
        }

        const birthday = new Date(birthdayInput.value);
        if (birthday > today) {
            alert("Date of Birth cannot be in the future.");
            event.preventDefault(); // Prevent form submission
            return;
        }

        // Validate Appointment Date - cannot be in the past
        const appointmentDate = new Date(appointmentDateInput.value);
        if (appointmentDate < today) {
            alert("Appointment Date cannot be in the past.");
            event.preventDefault(); // Prevent form submission
            return;
        }

        // Get the selected appointment time range
        const startTime = startTimeInput.value;
        const endTime = endTimeInput.value;

        // Validate appointment date and times
        const selectedDate = new Date(appointmentDateInput.value);
        selectedDate.setHours(0, 0, 0, 0); // Normalize the time part for comparison

        // Ensure the start time is before the end time
        const [startHour, startMinute] = startTime.split(":").map(Number);
        const [endHour, endMinute] = endTime.split(":").map(Number);
        const startTimeObj = new Date(selectedDate);
        const endTimeObj = new Date(selectedDate);
        startTimeObj.setHours(startHour, startMinute, 0, 0);
        endTimeObj.setHours(endHour, endMinute, 0, 0);

        if (startTimeObj >= endTimeObj) {
            alert("End time must be later than start time.");
            event.preventDefault();
            return;
        }

        // Check if the appointment time is within working hours (1 PM to 5:30 PM)
        const workStart = new Date(selectedDate.setHours(13, 0, 0, 0));
        const workEnd = new Date(selectedDate.setHours(17, 30, 0, 0));

        if (startTimeObj < workStart || endTimeObj > workEnd) {
            alert("Appointment must be within working hours (1:00 PM to 5:30 PM).");
            event.preventDefault();
            return;
        }
        
        // Check if selected time conflicts with existing ones
        const isConflicting = existingAppointments.some(appointment => {
            if (appointment.date === appointmentDateInput.value) {
                const existingStart = new Date(`${appointment.date} ${appointment.start}`);
                const existingEnd = new Date(`${appointment.date} ${appointment.end}`);
                return (startTimeObj < existingEnd && endTimeObj > existingStart);
            }
            return false;
        });

        if (isConflicting) {
            alert("The selected time slot conflicts with an existing appointment.");
            event.preventDefault(); // Prevent form submission
        }
    });
});

        </script>
    </div>
</body>
</html>
