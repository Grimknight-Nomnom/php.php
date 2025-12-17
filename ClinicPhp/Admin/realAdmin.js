// Calendar variable
let calendar;

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

        // Add day-specific classes
        if (day === 2) classNames.push("tuesday-day");
        else if (day === 3) classNames.push("wednesday-day");
        else if (day === 0 || day === 6) classNames.push("weekend-day");

        return classNames;
      },
      dayCellContent: function (arg) {
        const day = arg.date.getDay();
        const date = arg.date.getDate();
        let dayMessage = "";

        // Add day-specific messages
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
            dayMessage = "Check up";
            break;
          case 5:
            dayMessage = "Check up";
            break;
          case 6:
          case 0:
            dayMessage = "Area";
            break;
        }

        return {
          html: `<div class="fc-day-number">${date}</div>
                 <div class="fc-day-message">${dayMessage}</div>`,
        };
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

// Function to show/hide content sections
function showContent(contentType) {
  // Hide all content sections
  const contentSections = document.querySelectorAll(".content-section");
  contentSections.forEach((section) => {
    section.style.display = "none";
  });

  // Show the selected content section
  const selectedSection = document.getElementById(contentType);
  if (selectedSection) {
    selectedSection.style.display = "block";

    // Special handling for calendar
    if (contentType === "registration" && typeof calendar !== "undefined") {
      setTimeout(() => {
        calendar.render();
      }, 100);
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
  fetch("add_medicine.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams(data).toString(),
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        alert("Medicine added successfully");
        document.getElementById("medicineModal").style.display = "none";
        // Refresh the medicine list
        location.reload();
      } else {
        alert("Error: " + (data.message || "Failed to add medicine"));
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert(
        "An error occurred while adding the medicine. Check console for details."
      );
    });
}

// Initialize date picker for expiration date
document.addEventListener("DOMContentLoaded", function () {
  // Set minimum date to today for expiration date
  const today = new Date();
  const dd = String(today.getDate()).padStart(2, "0");
  const mm = String(today.getMonth() + 1).padStart(2, "0"); // January is 0!
  const yyyy = today.getFullYear();
  const minDate = `${yyyy}-${mm}-${dd}`;

  document.getElementById("expirationDate").min = minDate;
});

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
