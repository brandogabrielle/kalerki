<?php
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

$appointment_id = $_GET['id'];

// Fetch appointment data from the database
$sql = "
    SELECT a.id AS appointment_id, 
           a.patient_id, 
           a.appointment_date, 
           a.appointment_start_time, 
           a.appointment_end_time, 
           a.services, 
           a.add_info, 
           a.partial_denture_service, 
           a.partial_denture_count, 
           a.full_denture_service, 
           a.full_denture_range, 
           a.patient_name
    FROM appointments a
    INNER JOIN patient_registry p ON a.patient_id = p.id
    WHERE a.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appointment_id); 
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();

// Split services for easier manipulation
$selected_services = explode(',', $appointment['services']);
$selected_services = array_map('trim', $selected_services);

$partial_denture_service = explode(',', $appointment['partial_denture_service']);
$partial_denture_service = array_map('trim', $partial_denture_service); // Ensure clean strings

$partial_denture_count = explode(',', $appointment['partial_denture_count']);

// Store these values for later use
$selected_pontic_count = array();
foreach ($partial_denture_service as $key => $service) {
    $selected_pontic_count[] = $partial_denture_count[$key];
}

// Split full denture data
$full_denture_service = explode(',', $appointment['full_denture_service']);
$full_denture_service = array_map('trim', $full_denture_service);

$full_denture_range = explode(',', $appointment['full_denture_range']);

$selected_range = array();
foreach ($full_denture_service as $key => $service) {
    $selected_range[] = $full_denture_range[$key];
}

$patient_id = $appointment['patient_id']; // Get the patient ID associated with the appointment

$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="patient_registry.css">
    <title>Edit Appointment</title>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Edit Appointment</h1>
            <div class="bruh-button">
                <a href="check_appointment.php">Go back to Appointments</a>
            </div>
        </div>
        <form method="post" action="update_appointment.php">
        <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id']; ?>">
            <div class="form-group">      
                <label for="fullName">Full Name:</label>
                <input type="text" id="fullName" name="fullName" value="<?= $appointment['patient_name']; ?>" readonly>
            </div>

            <div class="apt-section">
                <div class="form-group">
                    <label for="appointmentDate">Appointment Date:</label>
                    <input type="date" name="appointment_date" value="<?= $appointment['appointment_date']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="appointmentStartTime">Start Time:</label>
                    <input type="time" name="appointmentStartTime" value="<?= $appointment['appointment_start_time']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="appointmentEndTime">End Time:</label>
                    <input type="time" name="appointmentEndTime" value="<?= $appointment['appointment_end_time']; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="addInfo">Additional Info:</label>
                <input type="text" name="addInfo" value="<?= $appointment['add_info']; ?>"> <!-- Fixed name attribute -->
            </div>

            <h2>Services:</h2>
                <table border="1" cellpadding="10">
                    <thead>
                        <tr>
                            <th>Diagnosis</th>
                            <th>Periodontics (Oral Prophylaxis)</th>
                            <th>Oral Surgery</th>
                            <th>Restorative</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <label><input type="checkbox" name="services[]" value="Diagnosis: Consultation" <?php echo (in_array("Diagnosis: Consultation", $selected_services)) ? 'checked' : ''; ?>> Consultation</label><br>
                                <label><input type="checkbox" name="services[]" value="+ Medical Certificate" <?php echo (in_array("+ Medical Certificate", $selected_services)) ? 'checked' : ''; ?>> w/ Medical Certificate</label>
                            </td>
                            <td>
                                <label><input type="checkbox" name="services[]" value="Periodontics (Oral Prohylaxis): Light-Moderate" <?php echo (in_array("Periodontics (Oral Prohylaxis): Light-Moderate", $selected_services)) ? 'checked' : ''; ?>> Light-Moderate</label><br>
                                <label><input type="checkbox" name="services[]" value="Periodontics (Oral Prohylaxis): Heavy" <?php echo (in_array("Periodontics (Oral Prohylaxis): Heavy", $selected_services)) ? 'checked' : ''; ?>> Heavy</label><br>
                                <label><input type="checkbox" name="services[]" value="+ Fluoride Treatment" <?php echo (in_array("+ Fluoride Treatment", $selected_services)) ? 'checked' : ''; ?>> w/ Fluoride Treatment</label>
                            </td>
                            <td>
                                <label><input type="checkbox" name="services[]" value="Oral Surgery: Simple Extraction" <?php echo (in_array("Oral Surgery: Simple Extraction", $selected_services)) ? 'checked' : ''; ?>> Simple Extraction</label><br>
                                <label><input type="checkbox" name="services[]" value="Oral Surgery: Complicated Extraction" <?php echo (in_array("Oral Surgery: Complicated Extraction", $selected_services)) ? 'checked' : ''; ?>> Complicated Extraction</label><br>
                                <label><input type="checkbox" name="services[]" value="Oral Surgery: Odontectomy" <?php echo (in_array("Oral Surgery: Odontectomy", $selected_services)) ? 'checked' : ''; ?>> Odontectomy</label>
                            </td>
                            <td>
                                <label><input type="checkbox" name="services[]" value="Restorative: Temporary" <?php echo (in_array("Temporary", $selected_services)) ? 'checked' : ''; ?>> Temporary</label><br>
                                <label><input type="checkbox" name="services[]" value="Restorative: Composite" <?php echo (in_array("Composite", $selected_services)) ? 'checked' : ''; ?>> Composite</label><br>
                                <label><input type="checkbox" name="services[]" value="Restorative: Additional Surface" <?php echo (in_array("Additional Surface", $selected_services)) ? 'checked' : ''; ?>> Additional Surface</label><br>
                                <label><input type="checkbox" name="services[]" value="Restorative: Pit & Fissure Sealant" <?php echo (in_array("Pit & Fissure Sealant", $selected_services)) ? 'checked' : ''; ?>> Pit & Fissure Sealant</label>
                            </td>
                        </tr>
                    </tbody>
                    <thead>
                        <tr>
                            <th>Repair</th>
                            <th>Prosthodontics</th>
                            <th>Orthodontics</th>
                            <th>Others</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <label><input type="checkbox" name="services[]" value="Repair: Crack" <?php echo (in_array("Repair: Crack", $selected_services)) ? 'checked' : ''; ?>> Crack</label><br>
                                <label><input type="checkbox" name="services[]" value="Repair: Broken with Impression" <?php echo (in_array("Repair: Broken with Impression", $selected_services)) ? 'checked' : ''; ?>> Broken with Impression</label><br>
                                <hr><div>Missing Pontic:</div><hr>
                                <label><input type="checkbox" name="services[]" value="Repair (Missing Pontic): Plastic" <?php echo (in_array("Repair (Missing Pontic): Plastic", $selected_services)) ? 'checked' : ''; ?>> Plastic</label><br>
                                <label><input type="checkbox" name="services[]" value="Repair (Missing Pontic): Porcelain" <?php echo (in_array("Repair (Missing Pontic): Porcelain", $selected_services)) ? 'checked' : ''; ?>> Porcelain</label>
                            </td>
                            <td>
                                <hr>Jacket Crown Per Unit<hr>
                                <label><input type="checkbox" name="services[]" value="Prosthodontics (Jacket Crown per Unit): Plastic" <?php echo (in_array("Prosthodontics (Jacket Crown per Unit): Plastic", $selected_services)) ? 'checked' : ''; ?>> Plastic</label><br>
                                <label><input type="checkbox" name="services[]" value="Prosthodontics (Jacket Crown per Unit): Porcelain Simple Metal" <?php echo (in_array("Prosthodontics (Jacket Crown per Unit): Porcelain Simple Metal", $selected_services)) ? 'checked' : ''; ?>> Porcelain Simple Metal</label><br>
                                <label><input type="checkbox" name="services[]" value="Prosthodontics (Jacket Crown per Unit): Porcelain Tilite" <?php echo (in_array("Prosthodontics (Jacket Crown per Unit): Porcelain Tilite", $selected_services)) ? 'checked' : ''; ?>> Porcelain Tilite</label><br>
                                <label><input type="checkbox" name="services[]" value="Prosthodontics (Jacket Crown per Unit): E-max" <?php echo (in_array("Prosthodontics (Jacket Crown per Unit): E-max", $selected_services)) ? 'checked' : ''; ?>> E-max</label><br>
                                <label><input type="checkbox" name="services[]" value="Prosthodontics (Jacket Crown per Unit): Zirconia" <?php echo (in_array("Prosthodontics (Jacket Crown per Unit): Zirconia", $selected_services)) ? 'checked' : ''; ?>> Zirconia</label><br><hr>
                                <label><input type="checkbox" name="services[]" value="Prosthodontics: Re-cementation" <?php echo (in_array("Prosthodontics: Re-cementation", $selected_services)) ? 'checked' : ''; ?>> Re-cementation</label>
                            </td>
                            <td>
                                <label><input type="checkbox" name="services[]" value="Orthodontics: Conventional Metal Brackets" <?php echo (in_array("Orthodontics: Conventional Metal Brackets", $selected_services)) ? 'checked' : ''; ?>> Conventional Metal Brackets</label><br>
                                <label><input type="checkbox" name="services[]" value="Orthodontics: Ceramic Brackets" <?php echo (in_array("Orthodontics: Ceramic Brackets", $selected_services)) ? 'checked' : ''; ?>> Ceramic Brackets</label><br>
                                <label><input type="checkbox" name="services[]" value="Orthodontics: Self-Ligating Metal Brackets" <?php echo (in_array("Orthodontics: Self-Ligating Metal Brackets", $selected_services)) ? 'checked' : ''; ?>> Self-Ligating Metal Brackets</label><br>
                                <label><input type="checkbox" name="services[]" value="Orthodontics: Functional Retainer" <?php echo (in_array("Orthodontics: Functional Retainer", $selected_services)) ? 'checked' : ''; ?>> Functional Retainer</label><br>
                                <label><input type="checkbox" name="services[]" value="Orthodontics: Retainer with Design" <?php echo (in_array("Orthodontics: Retainer with Design", $selected_services)) ? 'checked' : ''; ?>> Retainer with Design</label><br>
                                <label><input type="checkbox" name="services[]" value="Orthodontics: Ortho Kit" <?php echo (in_array("Orthodontics: Ortho Kit", $selected_services)) ? 'checked' : ''; ?>> Ortho Kit</label><br>
                                <label><input type="checkbox" name="services[]" value="Orthodontics: Ortho Wax" <?php echo (in_array("Orthodontics: Ortho Wax", $selected_services)) ? 'checked' : ''; ?>> Ortho Wax</label><br>
                            </td>
                            <td>
                                <label><input type="checkbox" name="services[]" value="Others: Teeth Whitening" <?php echo (in_array("Others: Teeth Whitening", $selected_services)) ? 'checked' : ''; ?>> Teeth Whitening</label><br>
                                <label><input type="checkbox" name="services[]" value="Others: Reline" <?php echo (in_array("Others: Reline", $selected_services)) ? 'checked' : ''; ?>> Reline</label><br>
                                <label><input type="checkbox" name="services[]" value="Others: Rebase" <?php echo (in_array("Others: Rebase", $selected_services)) ? 'checked' : ''; ?>> Rebase</label>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <h3>Partial Denture per Arch (Upper or Lower)</h3>
                <table border="1" cellpadding="10">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Pontic Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $partialDentureTypes = ['Stayplate Plastic', 'Stayplate Porcelain', 'One-piece Plastic', 'One-piece Porcelain', 'Flexite'];

                        // Loop through all denture types
                        foreach ($partialDentureTypes as $type) {
                            $selectedCount = '';

                            // Check if the type exists in the service array and retrieve the corresponding count
                            if (!empty($partial_denture_service) && in_array($type, $partial_denture_service)) {
                                $index = array_search($type, $partial_denture_service);
                                $selectedCount = $partial_denture_count[$index] ?? '';
                            }
                        ?>
                        <tr>
                            <td>
                                <label>
                                    <input type="checkbox" name="partial_denture[<?php echo $type; ?>]" value="<?php echo $type; ?>" 
                                        <?php echo (in_array($type, $partial_denture_service)) ? 'checked' : ''; ?>>
                                    <?php echo $type; ?>
                                </label>
                            </td>
                            <td>
                                <select name="partial_denture[<?php echo $type; ?>_pontic_count]">
                                    <?php for ($i = 1; $i <= 8; $i++) { ?>
                                        <option value="<?php echo $i; ?>" <?php echo ($selectedCount == $i) ? 'selected' : ''; ?>>
                                            <?php echo $i; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <h3>Full Denture per Arch (Upper AND/OR Lower)</h3>
                <table border="1" cellpadding="10">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Range</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $fullDentureTypes = ['Stayplate Plastic', 'Stayplate Porcelain', 'Ivocap', 'Thermosen'];

                        // Loop through all denture types
                        foreach ($fullDentureTypes as $type) {
                            // Default empty range
                            $selectedRange = '';

                            // Ensure the type exists in the service array
                            if (!empty($full_denture_service) && in_array($type, $full_denture_service)) {
                                $index = array_search($type, $full_denture_service); // Find the corresponding index
                                $selectedRange = trim($full_denture_range[$index] ?? '');  // Get the associated range and trim spaces
                            }
                        ?>
                        <tr>
                            <td>
                                <label>
                                    <input type="checkbox" name="full_denture[<?php echo $type; ?>]" value="<?php echo $type; ?>" 
                                        <?php echo (in_array($type, $full_denture_service)) ? 'checked' : ''; ?>>
                                    <?php echo $type; ?>
                                </label>
                            </td>
                            <td>
                                <select name="full_denture[<?php echo $type; ?>_range]">
                                    <?php 
                                    $ranges = ["Upper", "Lower", "Upper AND Lower"];
                                    foreach ($ranges as $range) { ?>
                                        <option value="<?php echo $range; ?>" <?php echo (strcasecmp($selectedRange, $range) === 0) ? 'selected' : ''; ?>>
                                            <?php echo $range; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>


            <div class="button-container">
                <button type="submit">Update</button>
                <button type="button" onclick="window.location.href='check_appointment.php'">Cancel</button>
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