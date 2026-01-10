// Calendar variable
let calendar;
let releasedChartInstance = null; // Add this at top of file
let expiredChartInstance = null;

// Add this at the top of your realAdmin.js file
document.addEventListener("DOMContentLoaded", function () {
  // Initialize notification icon
  const notificationIcon = document.getElementById("medicineNotificationIcon");
  if (notificationIcon) {
    notificationIcon.addEventListener("click", toggleNotifications);
    loadMedicineNotifications();

    // Check for notifications every 5 minutes
    setInterval(loadMedicineNotifications, 300000);
  }

  // Medicine modal functionality
  const medicineModal = document.getElementById("medicineModal");
  const addMedicineBtn = document.getElementById("addMedicineButton");
  const closeBtn = document.querySelector("#medicineModal .close");

  if (addMedicineBtn) {
    addMedicineBtn.addEventListener("click", function () {
      medicineModal.style.display = "block";
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener("click", function () {
      medicineModal.style.display = "none";
    });
  }

  // Close modal when clicking outside
  window.addEventListener("click", function (event) {
    if (event.target === medicineModal) {
      medicineModal.style.display = "none";
    }
  });

  // Initialize calendar if on registration page
  if (document.getElementById("calendar")) {
    initializeCalendar();
  }

  // Add event listener for the search input
  document
    .getElementById("searchMedicines")
    .addEventListener("keyup", filterMedicines);

  showContent("defaultContent");
});

// Function to toggle notifications
function toggleNotifications() {
  const dropdown = document.getElementById("notificationDropdown");
  dropdown.style.display =
    dropdown.style.display === "block" ? "none" : "block";
  loadMedicineNotifications();
}

// Function to load medicine notifications
async function loadMedicineNotifications() {
  const notificationList = document.getElementById("notificationList");
  const notificationBadge = document.getElementById("notificationBadge");

  try {
    // Clear previous state
    notificationList.innerHTML =
      '<div class="loading-notification">Loading...</div>';
    notificationBadge.style.display = "none";

    const response = await fetch("get_medicine_notification.php", {
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
    });

    // Check if response is HTML (error)
    const contentType = response.headers.get("content-type");
    if (contentType && contentType.includes("text/html")) {
      const errorText = await response.text();
      throw new Error(
        `Server returned HTML: ${errorText.substring(0, 100)}...`
      );
    }

    // Parse as JSON
    const data = await response.json();

    if (!response.ok || !data.success) {
      throw new Error(data.error || "Invalid server response");
    }

    updateNotificationUI(data.data);
  } catch (error) {
    console.error("Notification load failed:", error);
    notificationList.innerHTML = `
      <div class="error-notification">
          <i class="fas fa-exclamation-triangle"></i>
          <div>${error.message}</div>
      </div>`;
    notificationBadge.textContent = "!";
    notificationBadge.style.display = "flex";
  }
}

// Function to update notification UI
function updateNotificationUI(notifications) {
  const container = document.getElementById("notificationList");
  const badge = document.getElementById("notificationBadge");

  if (!notifications || notifications.length === 0) {
    container.innerHTML = '<div class="no-notifications">No alerts found</div>';
    badge.style.display = "none";
    return;
  }

  let html = "";
  notifications.forEach((notif) => {
    html += `
      <div class="notification-item" data-id="${notif.id}">
          <div class="medicine-name">${notif.name}</div>
          <div class="medicine-type">${notif.type}</div>
          <div class="alerts">
              ${notif.alerts
                .map((alert) => `<div class="alert">${alert}</div>`)
                .join("")}
          </div>
      </div>`;
  });

  container.innerHTML = html;
  badge.textContent = notifications.length;
  badge.style.display = "flex";
}

// Add this near the medicine search function
function filterPatients() {
  const searchTerm = document
    .getElementById("searchPatients")
    .value.toLowerCase();
  const rows = document.querySelectorAll("#patientsTable tbody tr");

  rows.forEach((row) => {
    const patientId = row.cells[0].textContent.toLowerCase();
    const surname = row.cells[1].textContent.toLowerCase();
    const firstName = row.cells[2].textContent.toLowerCase();
    const middleName = row.cells[3].textContent.toLowerCase();
    const contact = row.cells[4].textContent.toLowerCase();
    const age = row.cells[5].textContent.toLowerCase();

    if (
      patientId.includes(searchTerm) ||
      surname.includes(searchTerm) ||
      firstName.includes(searchTerm) ||
      middleName.includes(searchTerm) ||
      contact.includes(searchTerm) ||
      age.includes(searchTerm)
    ) {
      row.style.display = "";
    } else {
      row.style.display = "none";
    }
  });
}

// Update the DOMContentLoaded event listener to include patient search
document.addEventListener("DOMContentLoaded", function () {
  // ... existing code ...

  // Add event listeners for both search inputs
  document
    .getElementById("searchMedicines")
    ?.addEventListener("keyup", filterMedicines);
  document
    .getElementById("searchPatients")
    ?.addEventListener("keyup", filterPatients);

  // ... rest of existing code ...
});

function filterMedicines() {
  const searchTerm = document
    .getElementById("searchMedicines")
    .value.toLowerCase();
  const rows = document.querySelectorAll("#medicineTable tbody tr"); // Ensure this matches your table ID

  rows.forEach((row) => {
    const medicineName = row.cells[0].textContent.toLowerCase();
    const medicineType = row.cells[1].textContent.toLowerCase();
    const medicineDesc = row.cells[2].textContent.toLowerCase();

    if (
      medicineName.includes(searchTerm) ||
      medicineType.includes(searchTerm) ||
      medicineDesc.includes(searchTerm)
    ) {
      row.style.display = "";
    } else {
      row.style.display = "none";
    }
  });
}

// Function to show error notification
function showErrorNotification(message) {
  const notificationList = document.getElementById("notificationList");
  notificationList.innerHTML = `
    <div class="notification-error">
      Error: ${message || "Failed to load notifications"}
    </div>`;

  const notificationBadge = document.getElementById("notificationBadge");
  notificationBadge.textContent = "!";
  notificationBadge.style.display = "flex";
}

// Close notifications when clicking outside
document.addEventListener("click", function (event) {
  const notificationContainer = document.querySelector(
    ".notification-container"
  );
  const dropdown = document.getElementById("notificationDropdown");

  if (
    dropdown.style.display === "block" &&
    !notificationContainer.contains(event.target)
  ) {
    dropdown.style.display = "none";
  }
});

// Initialize calendar
function initializeCalendar() {
  const calendarEl = document.getElementById("calendar");
  if (calendarEl) {
    calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: "dayGridMonth",
      headerToolbar: {
        left: "prev,next today",
        center: "title",
        right: "",
      },
      events: "get_schedules.php",
      eventDisplay: "block",
      // Show full titles for Admin
      eventContent: function (arg) {
        return {
          html: `<div class="fc-event-title">${arg.event.title}</div>`,
        };
      },
      eventDidMount: function (info) {
        info.el.setAttribute("title", info.event.title);
      },
      dateClick: function (info) {
        showAppointmentsForDate(info.dateStr);
      },
      dayCellClassNames: function (arg) {
        const day = arg.date.getDay();
        const classNames = [];
        if (day === 2) classNames.push("tuesday-day");
        else if (day === 3) classNames.push("wednesday-day");
        else if (day === 0 || day === 6) classNames.push("weekend-day");
        return classNames;
      },

      // 1. SET UP THE LOOK (Layout)
      dayCellContent: function (arg) {
        const day = arg.date.getDay();
        const dateNum = arg.date.getDate();
        let dayMessage = "";

        switch (day) {
          case 1:
            dayMessage = "Check up";
            break;
          case 2:
            dayMessage = "Pregnancy";
            break;
          case 3:
            dayMessage = "Immunisation";
            break;
          case 4:
          case 5:
            dayMessage = "Check up";
            break;
          case 6:
          case 0:
            dayMessage = "Area";
            break;
        }

        // matches the layout of your screenshot (Message left, Date right, Count bottom)
        return {
          html: `<div class="fc-day-grid-content" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                  <div class="fc-day-top-info" style="display: flex; justify-content: space-between; align-items: center; padding: 2px 5px;">
                     <span class="fc-day-message" style="font-size: 0.85em; color: #888;">${dayMessage}</span>
                     <span class="fc-day-number">${dateNum}</span>
                  </div>
                  <div class="fc-patient-count-bottom" style="text-align: center; margin-bottom: 5px; font-weight: bold; font-size: 0.9em;"></div>
                 </div>`,
        };
      },

      // 2. FETCH THE DATA (Logic)
      dayCellDidMount: function (arg) {
        // Calculate the date string (YYYY-MM-DD)
        const year = arg.date.getFullYear();
        const month = String(arg.date.getMonth() + 1).padStart(2, "0");
        const dayStr = String(arg.date.getDate()).padStart(2, "0");
        const dateLocalStr = `${year}-${month}-${dayStr}`;

        // Find the specific empty div we created in Step 1
        const countDiv = arg.el.querySelector(".fc-patient-count-bottom");

        if (countDiv) {
          // This fetch will now work because you copied the file to the admin folder
          fetch(`get_day_count.php?date=${dateLocalStr}`)
            .then((res) => res.json())
            .then((data) => {
              const count = data.count || 0;

              if (count > 0) {
                countDiv.innerText = `${count} Scheduled`;

                // Color logic
                if (count >= 30) {
                  countDiv.style.color = "#ff0000"; // Red
                } else if (count >= 20) {
                  countDiv.style.color = "#d4d400"; // Yellow
                } else {
                  countDiv.style.color = "#000000"; // Black
                }
              }
            })
            .catch((err) => {
              console.error("Error fetching count:", err);
            });
        }
      },
      views: {
        dayGridMonth: {
          titleFormat: { year: "numeric", month: "long" },
        },
      },
    });
    calendar.render();
  }
}

// Show appointments for a specific date
// In the showAppointmentsForDate function, update the modal content:
async function showAppointmentsForDate(dateStr) {
  try {
    const response = await fetch("get_schedules.php?date=" + dateStr);
    const data = await response.json();

    // Create modal content
    let modalContent = `<div class="appointments-modal">
                            <h3>Appointments for ${formatDate(dateStr)}</h3>
                            <div class="appointments-list">`;

    if (data.events && data.events.length > 0) {
      data.events.forEach((event) => {
        modalContent += `<div class="appointment-item" data-id="${event.id}">
                            <span class="appointment-number">#${
                              event.appointment_number
                            }</span>
                            <span class="patient-info">
                                <span class="patient-name">${
                                  event.patient_name
                                }</span>
                                <span class="user-id">(${event.user_id})</span>
                            </span>
                            <label class="completed-checkbox">
                                <input type="checkbox" ${
                                  event.is_completed ? "checked" : ""
                                } 
                                    onchange="toggleAppointmentCompletion(${
                                      event.id
                                    }, this.checked)">
                                Completed
                            </label>
                        </div>`;
      });
    } else {
      modalContent += `<div class="no-appointments">NO APPOINTMENTS TODAY</div>`;
    }

    modalContent += `</div></div>`;

    // Show the modal
    showAppointmentsModal(modalContent);
  } catch (error) {
    console.error("Error fetching appointments:", error);
    showAppointmentsModal(`<div class="appointments-modal">
                                <h3>Error</h3>
                                <div class="appointments-list">
                                <div class="error-message">Failed to load appointments</div>
                                </div>
                            </div>`);
  }
}

// Add this new function to handle completion toggling
function toggleAppointmentCompletion(appointmentId, isCompleted) {
  fetch("update_appointment.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      id: appointmentId,
      is_completed: isCompleted,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (!data.success) {
        alert("Error updating appointment status");
        // You might want to revert the checkbox here
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Error updating appointment status");
    });
}

// Helper function to format date
function formatDate(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleDateString("en-US", {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
  });
}

// Function to show appointments modal
function showAppointmentsModal(content) {
  const modal = document.createElement("div");
  modal.className = "appointments-modal-container";
  modal.innerHTML = `
        <div class="appointments-modal-backdrop"></div>
        <div class="appointments-modal-content">
            <span class="close-appointments-modal">&times;</span>
            ${content}
        </div>`;

  document.body.appendChild(modal);

  // Add close handler
  modal
    .querySelector(".close-appointments-modal")
    .addEventListener("click", () => {
      document.body.removeChild(modal);
    });

  // Close when clicking backdrop
  modal
    .querySelector(".appointments-modal-backdrop")
    .addEventListener("click", () => {
      document.body.removeChild(modal);
    });
}

function showContent(contentType) {
  // 1. Hide all sections
  const contentSections = document.querySelectorAll(".content-section");
  contentSections.forEach((section) => {
    section.style.display = "none";
  });

  // 2. Show the selected section
  const selectedSection = document.getElementById(contentType);
  if (selectedSection) {
    selectedSection.style.display = "block";

    // --- THE FIX IS HERE ---
    // When opening Monthly Report, wait 100ms then fetch data
    if (contentType === "monthlyReport") {
      setTimeout(fetchReportData, 100);
    }
    // -----------------------

    // Render Calendar if opening Registration
    if (contentType === "registration" && typeof calendar !== "undefined") {
      setTimeout(() => calendar.render(), 100);
    }
  }
}

// Medicine functions
function updateQuantity(id, change) {
  // Get the current quantity element
  const quantityElement = document.getElementById(`quantity_${id}`);
  const currentQuantity = parseInt(quantityElement.textContent);
  const newQuantity = currentQuantity + change;

  // Don't allow negative quantities
  if (newQuantity < 0) {
    alert("Quantity cannot be negative");
    return;
  }

  // Send the update to the server
  fetch("update_quantity.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      id: id,
      change: change,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Update the displayed quantity
        quantityElement.textContent = newQuantity;
      } else {
        alert("Error updating quantity: " + (data.message || "Unknown error"));
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Error updating quantity");
    });
}

function deleteMedicine(id) {
  if (confirm("Are you sure you want to delete this medicine?")) {
    fetch("delete_medicine.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        id: id,
      }),
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          // Find and remove the row from the table
          const row = document.querySelector(`tr[data-id="${id}"]`);
          if (row) {
            row.remove();
          }
          // Show success message
          alert(data.message || "Medicine deleted successfully");

          // Optionally: Refresh the notifications
          loadMedicineNotifications();
        } else {
          throw new Error(data.message || "Failed to delete medicine");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Error deleting medicine: " + error.message);
      });
  }
}

// Initialize month/year picker when modal opens
document
  .getElementById("addMedicineButton")
  .addEventListener("click", function () {
    // Get current date
    const currentDate = new Date();
    const currentYear = currentDate.getFullYear();
    const currentMonth = currentDate.getMonth() + 1; // Months are 0-indexed

    // Set month select to current month
    document.getElementById("medicineExpirationMonth").value = currentMonth
      .toString()
      .padStart(2, "0");

    // Populate year select with current year + 10 years
    const yearSelect = document.getElementById("medicineExpirationYear");
    yearSelect.innerHTML = '<option value="">Year</option>';

    for (let year = currentYear; year <= currentYear + 10; year++) {
      const option = document.createElement("option");
      option.value = year;
      option.textContent = year;
      yearSelect.appendChild(option);
    }

    // Set year to current year
    yearSelect.value = currentYear;
  });

function addMedicine(event) {
  event.preventDefault();

  // Get form values
  const name = document.getElementById("medicineName").value;
  const type = document.getElementById("medicineType").value;
  const description = document.getElementById("medicineDescription").value;
  const quantity = document.getElementById("medicineQuantity").value;
  const month = document.getElementById("expirationMonth").value;
  const year = document.getElementById("expirationYear").value;

  // Validate inputs
  if (!name || !type || !quantity || !month || !year) {
    alert("Please fill in all required fields");
    return;
  }

  // Create expiration date string (YYYY-MM-DD format)
  const expirationDate = `${year}-${month.padStart(2, "0")}-01`;

  // Create data object
  const data = {
    name: name,
    type: type,
    description: description,
    quantity: quantity,
    expiration_date: expirationDate,
  };

  // Send data to server
  // ... inside addMedicine(event) ...

  fetch("add_medicine.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams(data).toString(),
  })
    .then((response) => {
      // Check if response is okay
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      // Return text first to debug if it's not JSON
      return response.text().then((text) => {
        try {
          return JSON.parse(text); // Try to parse JSON
        } catch (e) {
          console.error("Server returned non-JSON:", text);
          throw new Error("Server Error: " + text); // Show the HTML error in console
        }
      });
    })
    .then((data) => {
      if (data.success) {
        alert("Medicine added successfully");
        document.getElementById("medicineModal").style.display = "none";
        location.reload();
      } else {
        alert("Error: " + (data.message || "Failed to add medicine"));
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("An error occurred. Check console for details.");
    });
}

// Patient Edit Modal Functions
function showPatientEditModal(patientId) {
  // Add modal-open class to body
  document.body.classList.add("modal-open");

  // Fetch patient data from server
  fetch(`get_patient.php?id=${patientId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const patient = data.patient;

        // Set form values
        document.getElementById("editPatientId").value = patient.id;
        document.getElementById("editSurname").value = patient.surname || "";
        document.getElementById("editFirstName").value =
          patient.firstName || "";
        document.getElementById("editMiddleName").value =
          patient.middleInitial || "";
        document.getElementById("editBirthday").value = patient.birthday || "";
        document.getElementById("editAge").value = patient.age || "";
        document.getElementById("editGender").value = patient.gender || "";
        document.getElementById("editAddress").value = patient.address || "";
        document.getElementById("editEmail").value = patient.email || "";

        // Format contact numbers (remove +63 if present)
        formatPhoneNumber("editContactNumber", patient.contactNumber);
        formatPhoneNumber("editGuardianContact", patient.guardianContact);
        formatPhoneNumber(
          "editEmergencyContactNumber",
          patient.emergency_contact_number
        );

        document.getElementById("editCivilStatus").value =
          patient.civilStatus || "";
        document.getElementById("editOccupation").value =
          patient.occupation || "";
        document.getElementById("editExistingMedical").value =
          patient.existingMedical || "";
        document.getElementById("editCurrentMedication").value =
          patient.currentMedication || "";
        document.getElementById("editAllergies").value =
          patient.allergies || "";
        document.getElementById("editFamilyMedical").value =
          patient.familyMedical || "";
        document.getElementById("editBloodPressure").value =
          patient.bloodPressure || "";
        document.getElementById("editHeartRate").value =
          patient.heartRate || "";
        document.getElementById("editTemperature").value =
          patient.temperature || "";
        document.getElementById("editHeight").value = patient.height || "";
        document.getElementById("editWeight").value = patient.weight || "";
        document.getElementById("editBMI").value =
          calculateBMIValue(patient.height, patient.weight) || "";
        document.getElementById("editDescription").value =
          patient.description || "";
        document.getElementById("editCheckup").value = patient.checkup || "";
        document.getElementById("editGuardian").value = patient.guardian || "";
        document.getElementById("editRelationship").value =
          patient.relationship || "";

        // Set checkbox values
        document.getElementById("editPhilhealth").checked =
          patient.philhealth == 1;
        document.getElementById("editSeniorCitizen").checked =
          patient.seniorCitizen == 1;

        // Show the modal
        document.getElementById("patientEditModal").style.display = "block";
      } else {
        alert(
          "Error loading patient data: " + (data.message || "Unknown error")
        );
        document.body.classList.remove("modal-open");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Error loading patient data");
      document.body.classList.remove("modal-open");
    });
}

function closePatientEditModal() {
  document.getElementById("patientEditModal").style.display = "none";
  document.body.classList.remove("modal-open");
}

function formatPhoneNumber(fieldId, phoneNumber) {
  let number = phoneNumber || "";
  if (number.startsWith("+63")) {
    number = number.substring(3);
  } else if (number.startsWith("63")) {
    number = number.substring(2);
  } else if (number.startsWith("0")) {
    number = number.substring(1);
  }
  document.getElementById(fieldId).value = number;
}

function calculateBMIValue(height, weight) {
  if (height && weight) {
    const heightInMeters = height / 100;
    return (weight / (heightInMeters * heightInMeters)).toFixed(1);
  }
  return "";
}

function calculateBMI() {
  const height = parseFloat(document.getElementById("editHeight").value);
  const weight = parseFloat(document.getElementById("editWeight").value);
  document.getElementById("editBMI").value = calculateBMIValue(height, weight);
}

function updatePatient(event) {
  event.preventDefault();

  const patientId = document.getElementById("editPatientId").value;

  // Prepare the data
  const formData = new FormData();
  formData.append("id", patientId);
  formData.append("surname", document.getElementById("editSurname").value);
  formData.append("firstName", document.getElementById("editFirstName").value);
  formData.append(
    "middleInitial",
    document.getElementById("editMiddleName").value
  );
  formData.append("birthday", document.getElementById("editBirthday").value);
  formData.append("age", document.getElementById("editAge").value);
  formData.append("gender", document.getElementById("editGender").value);
  formData.append("address", document.getElementById("editAddress").value);
  formData.append("email", document.getElementById("editEmail").value);
  formData.append(
    "contactNumber",
    "+63" + document.getElementById("editContactNumber").value
  );
  formData.append(
    "civilStatus",
    document.getElementById("editCivilStatus").value
  );
  formData.append(
    "occupation",
    document.getElementById("editOccupation").value
  );
  formData.append(
    "existingMedical",
    document.getElementById("editExistingMedical").value
  );
  formData.append(
    "currentMedication",
    document.getElementById("editCurrentMedication").value
  );
  formData.append("allergies", document.getElementById("editAllergies").value);
  formData.append(
    "familyMedical",
    document.getElementById("editFamilyMedical").value
  );
  formData.append(
    "bloodPressure",
    document.getElementById("editBloodPressure").value
  );
  formData.append("heartRate", document.getElementById("editHeartRate").value);
  formData.append(
    "temperature",
    document.getElementById("editTemperature").value
  );
  formData.append("height", document.getElementById("editHeight").value);
  formData.append("weight", document.getElementById("editWeight").value);
  formData.append(
    "description",
    document.getElementById("editDescription").value
  );
  formData.append("checkup", document.getElementById("editCheckup").value);
  formData.append("guardian", document.getElementById("editGuardian").value);
  formData.append(
    "relationship",
    document.getElementById("editRelationship").value
  );
  formData.append(
    "guardianContact",
    "+63" + document.getElementById("editGuardianContact").value
  );
  formData.append(
    "emergency_contact_number",
    "+63" + document.getElementById("editEmergencyContactNumber").value
  );
  formData.append(
    "philhealth",
    document.getElementById("editPhilhealth").checked ? 1 : 0
  );
  formData.append(
    "seniorCitizen",
    document.getElementById("editSeniorCitizen").checked ? 1 : 0
  );

  // Send the data to the server
  fetch("update_patient.php", {
    method: "POST",
    body: formData,
    credentials: "same-origin", // This ensures cookies/session are sent
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("Patient updated successfully");
        closePatientEditModal();
        // Optional: refresh the patient list
        location.reload();
      } else {
        alert("Error updating patient: " + (data.message || "Unknown error"));
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Error updating patient");
    });
}

// Add event listeners for height and weight to auto-calculate BMI
document.addEventListener("DOMContentLoaded", function () {
  document
    .getElementById("editHeight")
    ?.addEventListener("input", calculateBMI);
  document
    .getElementById("editWeight")
    ?.addEventListener("input", calculateBMI);
});

// --- ADD THIS TO realAdmin.js ---

// 1. Function to OPEN the Edit Modal and fill data
// 1. OPEN MODAL: Pre-select the correct Month and Year
function openEditMedicineModal(button) {
  // Get data from the button's data-attributes
  const id = button.getAttribute("data-id");
  const name = button.getAttribute("data-name");
  const type = button.getAttribute("data-type");
  const desc = button.getAttribute("data-desc");
  const qty = button.getAttribute("data-qty");

  // Get the split date (Month and Year)
  const month = button.getAttribute("data-month"); // e.g., "01"
  const year = button.getAttribute("data-year"); // e.g., "2026"

  // Set values in the input fields
  document.getElementById("editMedId").value = id;
  document.getElementById("editMedName").value = name;
  document.getElementById("editMedType").value = type;
  document.getElementById("editMedDesc").value = desc;
  document.getElementById("editMedQty").value = qty;

  // PRE-SELECT THE DROPDOWNS
  if (month) {
    document.getElementById("editMedMonth").value = month;
  } else {
    document.getElementById("editMedMonth").value = ""; // Default to "Select Month"
  }

  if (year) {
    document.getElementById("editMedYear").value = year;
  } else {
    document.getElementById("editMedYear").value = ""; // Default to "Select Year"
  }

  // Show the modal
  document.getElementById("editMedicineModal").style.display = "block";
}

// 2. Function to SUBMIT the Edit Form
// 2. SUBMIT FORM: Send both Month and Year to PHP
function submitEditMedicine(event) {
  event.preventDefault();

  // Get values
  const id = document.getElementById("editMedId").value;
  const name = document.getElementById("editMedName").value;
  const type = document.getElementById("editMedType").value;
  const desc = document.getElementById("editMedDesc").value;
  const qty = document.getElementById("editMedQty").value;
  const month = document.getElementById("editMedMonth").value;
  const year = document.getElementById("editMedYear").value;

  // Check if both are selected
  if (month === "" || year === "") {
    alert("Please select both an Expiration Month and Year.");
    return;
  }

  const formData = new FormData();
  formData.append("id", id);
  formData.append("name", name);
  formData.append("type", type);
  formData.append("description", desc);
  formData.append("quantity", qty);
  formData.append("month", month);
  formData.append("year", year);

  fetch("update_medicine.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("Successfully updated!");
        // CRITICAL: Reload the page to show the new date in the table
        location.reload();
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("An error occurred.");
    });
}

// Close Edit Modal when clicking outside
window.addEventListener("click", function (event) {
  const editModal = document.getElementById("editMedicineModal");
  if (event.target === editModal) {
    editModal.style.display = "none";
  }
});

/* --- RELEASE MEDICINE FUNCTIONS --- */

function openReleaseMedicineModal(button) {
  const modal = document.getElementById("releaseMedicineModal");

  // Get data from button
  const id = button.getAttribute("data-id");
  const name = button.getAttribute("data-name");
  const maxQty = parseInt(button.getAttribute("data-qty"));

  // 1. Set ID
  document.getElementById("releaseMedId").value = id;

  // 2. Set Medicine Name (Create a single option)
  const nameSelect = document.getElementById("releaseMedName");
  nameSelect.innerHTML = `<option value="${id}" selected>${name}</option>`;

  // 3. Populate Quantity Dropdown (1 to Max Stock)
  const qtySelect = document.getElementById("releaseMedQty");
  qtySelect.innerHTML = '<option value="">Select Quantity</option>';

  if (maxQty > 0) {
    for (let i = 1; i <= maxQty; i++) {
      // Limit dropdown to reasonable number (e.g., 50) to prevent browser lag if stock is 1000
      if (i > 50) break;
      qtySelect.innerHTML += `<option value="${i}">${i}</option>`;
    }
  } else {
    qtySelect.innerHTML = '<option value="">Out of Stock</option>';
  }

  // 4. Clear Patient Name
  document.getElementById("releasePatientName").value = "";

  // Show Modal
  modal.style.display = "block";
}

function submitReleaseMedicine(event) {
  event.preventDefault();

  const id = document.getElementById("releaseMedId").value;
  const qty = document.getElementById("releaseMedQty").value;
  const patientName = document.getElementById("releasePatientName").value;

  if (!qty) {
    alert("Please select a quantity.");
    return;
  }

  // --- NEW VALIDATION FOR CUSTOM DROPDOWN ---
  // Check if the typed name exists in our hidden list options
  const listItems = document.querySelectorAll(
    "#patientDropdownList .item-name"
  );
  let matchFound = false;

  for (let i = 0; i < listItems.length; i++) {
    if (listItems[i].textContent === patientName) {
      matchFound = true;
      break;
    }
  }

  if (!matchFound) {
    alert("Please select a valid patient from the list.");
    return;
  }
  // ------------------------------------------

  // ... rest of your fetch code remains the same ...
  const formData = new URLSearchParams();
  formData.append("id", id);
  formData.append("quantity_released", qty);
  formData.append("patient_name", patientName);

  fetch("release_medicine.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: formData.toString(),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("Medicine released successfully!");
        document.getElementById("releaseMedicineModal").style.display = "none";
        location.reload();
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("An error occurred.");
    });
}

// Close Release Modal on outside click
window.addEventListener("click", function (event) {
  const modal = document.getElementById("releaseMedicineModal");
  if (event.target === modal) {
    modal.style.display = "none";
  }
});

// --- ADD TO realAdmin.js ---

function openHistoryModal() {
  const modal = document.getElementById("historyModal");
  const tbody = document.getElementById("historyTableBody");

  // Show loading
  tbody.innerHTML =
    '<tr><td colspan="6" style="text-align:center; padding:20px;">Loading history...</td></tr>';
  modal.style.display = "block";

  fetch("get_medicine_history.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        renderHistoryTable(data.data);
      } else {
        tbody.innerHTML =
          '<tr><td colspan="6" style="text-align:center;">Failed to load history</td></tr>';
      }
    })
    .catch((err) => {
      console.error(err);
      tbody.innerHTML =
        '<tr><td colspan="6" style="text-align:center;">Error loading data</td></tr>';
    });
}

function renderHistoryTable(logs) {
  const tbody = document.getElementById("historyTableBody");

  if (logs.length === 0) {
    tbody.innerHTML =
      '<tr><td colspan="6" style="text-align:center; padding:20px;">No history found.</td></tr>';
    return;
  }

  let html = "";
  logs.forEach((log) => {
    // Determine Color based on Action
    let color = "#333";
    let details = "-";

    if (log.action_type === "Released") {
      color = "#e74c3c"; // Red
      details = `Patient: <b>${log.patient_name}</b>`;
    } else if (log.action_type === "Added") {
      color = "#27ae60"; // Green
      details = `Exp: ${log.expiration_date}`;
    } else if (log.action_type === "Updated") {
      color = "#f39c12"; // Orange
      details = `Exp: ${log.expiration_date}`;
    }

    html += `
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px; font-weight:bold; color: ${color}">${log.action_type}</td>
                <td style="padding: 10px;">${log.medicine_name}</td>
                <td style="padding: 10px; font-weight:bold;">${log.quantity}</td>
                <td style="padding: 10px;">${details}</td>
                <td style="padding: 10px;">${log.formatted_date}</td>
                <td style="padding: 10px; color: #666;">${log.formatted_time}</td>
            </tr>
        `;
  });

  tbody.innerHTML = html;
}

// Close History Modal on outside click
window.addEventListener("click", function (event) {
  const modal = document.getElementById("historyModal");
  if (event.target === modal) {
    modal.style.display = "none";
  }
});

// Function to open Patient Medical Logs
function openMedicalLogs(patientId, surname) {
  const modal = document.getElementById("patientLogsModal");
  const tbody = document.getElementById("patientLogsTableBody");
  const title = document.getElementById("logsPatientName");

  // Set Title
  title.textContent = `Medical History: ${surname}`;

  // Show Loading
  tbody.innerHTML =
    '<tr><td colspan="5" style="text-align:center; padding:20px;">Loading logs...</td></tr>';
  modal.style.display = "block";

  fetch(`get_patient_logs.php?id=${patientId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.data.length > 0) {
        let html = "";
        data.data.forEach((log) => {
          html += `
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px; white-space: nowrap;">${
                  log.formatted_date
                }</td>
                <td style="padding: 10px; font-weight: bold;">${
                  log.checkup_doctor || "N/A"
                }</td>
                <td style="padding: 10px;">
                    <div>BP: <b>${log.blood_pressure || "-"}</b></div>
                    <div>HR: ${log.heart_rate || "-"}</div>
                    <div>Temp: ${log.temperature || "-"}°C</div>
                </td>
                <td style="padding: 10px;">
                    <div>H: ${log.height || "-"} cm</div>
                    <div>W: ${log.weight || "-"} kg</div>
                    <div>BMI: <b>${log.bmi || "-"}</b></div>
                </td>
                <td style="padding: 10px; max-width: 300px; word-wrap: break-word;">
                    ${log.description || "No description"}
                </td>
            </tr>
          `;
        });
        tbody.innerHTML = html;
      } else {
        tbody.innerHTML =
          '<tr><td colspan="5" style="text-align:center; padding:20px;">No medical logs found for this patient.</td></tr>';
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      tbody.innerHTML =
        '<tr><td colspan="5" style="text-align:center; color:red;">Error loading logs.</td></tr>';
    });
}

// Close Logs Modal on outside click
window.addEventListener("click", function (event) {
  const modal = document.getElementById("patientLogsModal");
  if (event.target === modal) {
    modal.style.display = "none";
  }
});

// Function to force selection from the list
function validatePatientName(input) {
  const list = document.getElementById("patientListSuggestions");
  const errorMsg = document.getElementById("patientError");
  let matchFound = false;

  // Loop through all options in the datalist
  for (let i = 0; i < list.options.length; i++) {
    if (input.value === list.options[i].value) {
      matchFound = true;
      break;
    }
  }

  // If the typed name is NOT in the list
  if (!matchFound && input.value !== "") {
    errorMsg.style.display = "block";
    input.setCustomValidity("Invalid"); // Prevents form submission
    input.value = ""; // Clear the invalid input
    alert("Please select a registered patient from the list.");
  } else {
    errorMsg.style.display = "none";
    input.setCustomValidity(""); // Allows submission
  }
}

/* --- CUSTOM PATIENT DROPDOWN FUNCTIONS --- */

// 1. Show the dropdown when clicked or focused
function showPatientDropdown() {
  const list = document.getElementById("patientDropdownList");
  list.style.display = "block";
}

// 2. Filter the list based on typing
function filterPatientDropdown() {
  const input = document.getElementById("releasePatientName");
  const filter = input.value.toUpperCase();
  const list = document.getElementById("patientDropdownList");
  const items = list.getElementsByClassName("dropdown-item");

  list.style.display = "block"; // Make sure it's visible when typing

  for (let i = 0; i < items.length; i++) {
    const nameSpan = items[i].getElementsByClassName("item-name")[0];
    const txtValue = nameSpan.textContent || nameSpan.innerText;

    // Check if name matches search
    if (txtValue.toUpperCase().indexOf(filter) > -1) {
      items[i].style.display = "";
    } else {
      items[i].style.display = "none";
    }
  }
}

// 3. Select an item from the list
function selectPatient(name) {
  const input = document.getElementById("releasePatientName");
  input.value = name; // Set input value

  // Hide list
  document.getElementById("patientDropdownList").style.display = "none";
}

// 4. Close dropdown if clicking outside
document.addEventListener("click", function (event) {
  const container = document.querySelector(".custom-dropdown-container");
  const list = document.getElementById("patientDropdownList");

  // If click is OUTSIDE the container, hide the list
  if (container && !container.contains(event.target)) {
    list.style.display = "none";
  }
});

/* --- APPROVAL LOGIC UPDATED --- */

let pendingApproveId = null; // Store ID for whichever modal is open

// 1. Initial Check Function
function checkApproval(id, isEdited) {
  pendingApproveId = id; // Save the ID

  if (isEdited == 0) {
    // CASE A: User has NOT edited profile -> Show WARNING Modal (Red)

    // Setup the confirm button for Warning Modal
    const warningBtn = document.getElementById("confirmApproveBtn");
    warningBtn.onclick = function () {
      performApprove(pendingApproveId);
      closeWarningModal();
    };

    document.getElementById("warningApproveModal").style.display = "block";
  } else {
    // CASE B: User HAS edited profile -> Show STANDARD Modal (Green)

    // Setup the confirm button for Standard Modal
    const standardBtn = document.getElementById("btnStandardConfirm");
    standardBtn.onclick = function () {
      performApprove(pendingApproveId);
      closeStandardApproveModal();
    };

    document.getElementById("standardApproveModal").style.display = "block";
  }
}

// 2. The Actual API Call (Remains the same)
function performApprove(id) {
  fetch("approve_user.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id: id }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("Account Approved Successfully!");
        // Remove row visually
        const row = document.getElementById(`pending-row-${id}`);
        if (row) row.remove();
      } else {
        alert("Error: " + (data.message || "Failed to approve"));
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("An error occurred.");
    });
}

// 3. Helper to Close WARNING Modal
function closeWarningModal() {
  document.getElementById("warningApproveModal").style.display = "none";
  pendingApproveId = null;
}

// 4. Helper to Close STANDARD Modal (NEW)
function closeStandardApproveModal() {
  document.getElementById("standardApproveModal").style.display = "none";
  pendingApproveId = null;
}

// 5. Close modals if clicked outside (Updated to handle both)
window.addEventListener("click", function (event) {
  const warningModal = document.getElementById("warningApproveModal");
  const standardModal = document.getElementById("standardApproveModal");

  // Close Warning Modal
  if (event.target === warningModal) {
    closeWarningModal();
  }

  // Close Standard Modal
  if (event.target === standardModal) {
    closeStandardApproveModal();
  }

  // (Keep your existing close logic for other modals here if needed)
  // ...
});

/* --- MONTHLY REPORT FUNCTIONS --- */
let reportInitialized = false; // Add this variable above the function

function fetchReportData() {
  const monthDropdown = document.getElementById("reportFilterMonth");
  const yearDropdown = document.getElementById("reportFilterYear");

  // Safety check: if dropdowns don't exist, stop
  if (!monthDropdown || !yearDropdown) return;

  // Auto-select current month on first load
  if (!reportInitialized) {
    const now = new Date();
    const currentMonth = String(now.getMonth() + 1).padStart(2, "0");
    const currentYear = now.getFullYear();
    monthDropdown.value = currentMonth;
    yearDropdown.value = currentYear;
    reportInitialized = true;
  }

  const selectedMonth = monthDropdown.value;
  const selectedYear = yearDropdown.value;

  // Update the Report Title
  const monthName = monthDropdown.options[monthDropdown.selectedIndex].text;
  document.getElementById(
    "reportTitle"
  ).innerText = `Monthly Report (${monthName} ${selectedYear})`;

  // Fetch the data from PHP
  fetch(`get_report_data.php?month=${selectedMonth}&year=${selectedYear}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        renderReleasedChart(data.released_graph);
        renderExpiredChart(data.expired_graph);
        renderLowStockTable(data.low_stock_list);
        renderExpiryTable(data.expiry_list);
      }
    })
    .catch((error) => console.error("Error fetching report:", error));
}

function renderReleasedChart(data) {
  const ctxCanvas = document.getElementById("releasedChart");
  if (!ctxCanvas) return; // Stop if element is missing

  const ctx = ctxCanvas.getContext("2d");

  // Default to empty array if no data
  const safeData = data || [];
  const labels = safeData.map((item) => item.medicine_name);
  const counts = safeData.map((item) => item.total_qty);

  // Destroy old chart to prevent "flickering"
  if (releasedChartInstance) releasedChartInstance.destroy();

  // Create Chart (Even if empty)
  releasedChartInstance = new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Quantity Released",
          data: counts,
          backgroundColor: "rgba(22, 160, 133, 0.6)",
          borderColor: "rgba(22, 160, 133, 1)",
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          suggestedMax: 10, // Ensures grid lines appear even if data is 0
        },
      },
      plugins: {
        // Show "No Data" title if array is empty
        title: {
          display: safeData.length === 0,
          text: "No medicines released this month",
          font: { size: 14, style: "italic" },
          color: "#888",
        },
        legend: {
          display: safeData.length > 0, // Hide legend if empty
        },
      },
    },
  });
}

function renderExpiredChart(data) {
  const ctxCanvas = document.getElementById("expiredChart");
  if (!ctxCanvas) return;

  const ctx = ctxCanvas.getContext("2d");

  const safeData = data || [];
  const labels = safeData.map((item) => item.name);
  const counts = safeData.map((item) => item.quantity);

  if (expiredChartInstance) expiredChartInstance.destroy();

  expiredChartInstance = new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Quantity Expired",
          data: counts,
          backgroundColor: "rgba(192, 57, 43, 0.6)",
          borderColor: "rgba(192, 57, 43, 1)",
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          suggestedMax: 10,
        },
      },
      plugins: {
        title: {
          display: safeData.length === 0,
          text: "No expirations this month",
          font: { size: 14, style: "italic" },
          color: "#888",
        },
        legend: {
          display: safeData.length > 0,
        },
      },
    },
  });
}

function renderLowStockTable(data) {
  const tbody = document.getElementById("lowStockBody");
  tbody.innerHTML = "";

  if (data.length === 0) {
    tbody.innerHTML =
      '<tr><td colspan="3" style="text-align:center;">Stocks are healthy!</td></tr>';
    return;
  }

  data.forEach((item) => {
    let color = item.quantity == 0 ? "red" : "orange";
    tbody.innerHTML += `
            <tr>
                <td style="font-weight:bold;">${item.name}</td>
                <td>${item.type}</td>
                <td style="color:${color}; font-weight:bold;">${item.quantity}</td>
            </tr>
        `;
  });
}

function renderExpiryTable(data) {
  const tbody = document.getElementById("expiryBody");
  tbody.innerHTML = "";

  // Debugging: Check if data is arriving correctly
  console.log("Expiry Data Received:", data);

  if (!data || data.length === 0) {
    tbody.innerHTML =
      '<tr><td colspan="3" style="text-align:center;">No upcoming expirations.</td></tr>';
    return;
  }

  data.forEach((item) => {
    let status = "";
    let dateDisplay = "N/A";

    // 1. Format the Date (e.g., "January 2026")
    if (item.expiration_date && item.expiration_date !== "N/A") {
      const dateObj = new Date(item.expiration_date);
      dateDisplay = dateObj.toLocaleDateString("en-US", {
        month: "long",
        year: "numeric",
      });
    }

    // 2. Logic for Status Badges
    // Common Style for ALL Red Badges
    const redStyle = `background-color: #ffcccc; color: #cc0000; padding: 3px 8px; border-radius: 10px; font-weight: bold; font-size: 0.8em;`;

    if (item.days_left == null) {
      // If PHP fails to send days_left, show this gray badge
      status = `<span style="background-color:#e0e0e0; color:#555; padding:3px 8px; border-radius:10px; font-size:0.8em; font-weight:bold;">No Date Set</span>`;
    } else if (item.days_left < 0) {
      // Case: Already Expired
      status = `<span style="${redStyle}">Expired</span>`;
    } else if (item.days_left <= 30) {
      // Case: Within 30 days
      status = `<span style="${redStyle}">1 Month</span>`;
    } else {
      // Case: Between 31 and 60 days
      status = `<span style="${redStyle}">2 Months</span>`;
    }

    tbody.innerHTML += `
            <tr>
                <td style="font-weight:bold;">${item.name}</td>
                <td>${dateDisplay}</td>
                <td>${status}</td>
            </tr>
        `;
  });
}
