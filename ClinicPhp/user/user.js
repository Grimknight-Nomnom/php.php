document.addEventListener("DOMContentLoaded", function () {
  // Initialize calendar if on registration page
  if (document.getElementById("calendar")) {
    initializeCalendar();
  }

  // Set up menu button click handlers
  document.querySelectorAll(".menu-btn").forEach((button) => {
    button.addEventListener("click", function () {
      const sectionId = this.getAttribute("data-section");
      showContent(sectionId);
    });
  });

  // Set up other event listeners
  setupEventListeners();

  // Show default content on load
  showContent("defaultContent");
});

// Calendar functions
let calendar;

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
      events: {
        url: "get_schedules.php",
        method: "GET",
        failure: function (error) {
          console.error("Failed to fetch events:", error);
        },
      },
      eventContent: function (arg) {
        const numbers = arg.event.extendedProps.numbers || [];
        const completions = arg.event.extendedProps.completions || [];

        const numbersHtml = numbers
          .map((num, index) => {
            const isCompleted = completions[index] === "1";
            const completionText = isCompleted
              ? '<div class="fc-event-completed">Complete</div>'
              : "";
            return `<div class="fc-event-number">${num}${completionText}</div>`;
          })
          .join("");

        return { html: numbersHtml };
      },
      eventDidMount: function (info) {
        info.el.style.background = "none";
        info.el.style.border = "none";
        info.el.style.boxShadow = "none";
      },
      // Inside initializeCalendar function...
      dateClick: function (info) {
        const day = info.date.getDay();

        // Check if it's a Tuesday (Pregnancy day)
        if (day === 2 && userGender === "male") {
          alert(
            "Males cannot schedule appointments on Pregnancy service days."
          );
          return;
        }

        showAppointmentsForDate(info.dateStr);
      },
      dayCellClassNames: function (arg) {
        const day = arg.date.getDay();
        const classNames = [];

        // Add day-specific classes
        if (day === 2) {
          classNames.push("tuesday-day");
        } else if (day === 3) {
          classNames.push("wednesday-day");
        } else if (day === 0 || day === 6) {
          classNames.push("weekend-day");
        }

        return classNames;
      },
      dayCellContent: function (arg) {
        const day = arg.date.getDay();
        const dateNum = arg.date.getDate();

        // Fix for the 17th vs 18th issue: Use local date methods instead of toISOString()
        const year = arg.date.getFullYear();
        const month = String(arg.date.getMonth() + 1).padStart(2, "0");
        const dayStr = String(arg.date.getDate()).padStart(2, "0");
        const dateLocalStr = `${year}-${month}-${dayStr}`;

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

        const countId = `count-${dateLocalStr}`;

        // Fetch count using the local date string
        setTimeout(() => {
          fetch(`get_day_count.php?date=${dateLocalStr}`)
            .then((res) => res.json())
            .then((data) => {
              const count = data.count || 0;
              const display = document.getElementById(countId);
              if (display && count > 0) {
                display.innerText = `${count} Scheduled`;

                if (count >= 30) {
                  display.style.color = "#ff0000";
                  display.style.fontWeight = "bold";
                } else if (count >= 20) {
                  display.style.color = "#ffff00";
                } else {
                  display.style.color = "#000000";
                }
              }
            });
        }, 0);

        return {
          html: `<div class="fc-day-grid-content">
                 <div class="fc-day-top-info">
                   <span class="fc-day-message">${dayMessage}</span>
                   <span class="fc-day-number">${dateNum}</span>
                 </div>
                 <div id="${countId}" class="fc-patient-count-bottom"></div>
               </div>`,
        };
      },
      views: {
        dayGridMonth: {
          dayMaxEvents: 50,
          titleFormat: { year: "numeric", month: "long" },
        },
      },
    });
    calendar.render();
  }
}

async function showAppointmentsForDate(dateStr) {
  try {
    const response = await fetch(`get_schedules.php?date=${dateStr}`);

    // First check if the response is OK
    if (!response.ok) {
      const errorText = await response.text();
      throw new Error(`Server returned ${response.status}: ${errorText}`);
    }

    // Try to parse as JSON
    const data = await response.json();

    // Check if the JSON has success: false
    if (!data.success) {
      throw new Error(data.error || "Failed to load appointments");
    }

    let modalContent = `<div class="appointments-modal">
                          <h3>Appointments for ${formatDate(dateStr)}</h3>
                          <div class="appointments-list">`;

    if (data.events && data.events.length > 0) {
      data.events.forEach((event) => {
        const completedBadge = event.is_completed
          ? '<span class="completed-badge">Complete</span>'
          : "";

        modalContent += `<div class="appointment-item">
                          <span class="appointment-number">#${event.appointment_number}</span>
                          <span class="patient-name">${event.patient_name}</span>
                          ${completedBadge}
                        </div>`;
      });
    } else {
      modalContent += `<div class="no-appointments">NO APPOINTMENTS SCHEDULED</div>`;
    }

    modalContent += `</div></div>`;
    showAppointmentsModal(modalContent);
  } catch (error) {
    console.error("Error fetching appointments:", error);
    showAppointmentsModal(`
      <div class="appointments-modal">
        <h3>Error</h3>
        <div class="error-message">
          Failed to load appointments. Please try again later.
          ${
            process.env.NODE_ENV === "development"
              ? `<br><small>${error.message}</small>`
              : ""
          }
        </div>
      </div>
    `);
  }
}

function formatDate(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleDateString("en-US", {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
  });
}

function showAppointmentsModal(content) {
  const modal = document.createElement("div");
  modal.className = "appointments-modal-container";
  modal.innerHTML = `
    <div class="appointments-modal-backdrop"></div>
    <div class="appointments-modal-content">
      <span class="close-appointments-modal">&times;</span>
      ${content}
    </div>
  `;

  document.body.appendChild(modal);

  modal
    .querySelector(".close-appointments-modal")
    .addEventListener("click", () => {
      document.body.removeChild(modal);
    });

  modal
    .querySelector(".appointments-modal-backdrop")
    .addEventListener("click", () => {
      document.body.removeChild(modal);
    });
}

function setupEventListeners() {
  document.addEventListener("click", function (e) {
    if (
      e.target &&
      (e.target.id === "scheduleBtn" || e.target.closest("#scheduleBtn"))
    ) {
      e.preventDefault();
      openScheduleModal();
    }
  });

  document.addEventListener("click", function (e) {
    if (
      e.target &&
      (e.target.classList.contains("close-btn") ||
        e.target.classList.contains("modal-backdrop"))
    ) {
      closeScheduleModal();
    }
  });

  const scheduleForm = document.getElementById("scheduleForm");
  if (scheduleForm) {
    scheduleForm.addEventListener("submit", function (e) {
      e.preventDefault();
      handleScheduleSubmit(e);
    });
  }

  document
    .getElementById("searchMedicine")
    ?.addEventListener("input", filterMedicines);
  document
    .getElementById("editRecordBtn")
    ?.addEventListener("click", enableEditMode);
  document
    .getElementById("saveRecordBtn")
    ?.addEventListener("click", saveRecordChanges);
  document
    .getElementById("cancelEditBtn")
    ?.addEventListener("click", cancelEditMode);
}

function showContent(sectionId) {
  document.querySelectorAll(".content-section").forEach((section) => {
    section.style.display = "none";
  });

  const section = document.getElementById(sectionId);
  if (section) {
    section.style.display = "block";
    if (sectionId === "registration" && typeof calendar !== "undefined") {
      setTimeout(() => {
        calendar.render();
      }, 100);
    }
  }
}

function openScheduleModal() {
  const modal = document.getElementById("modalContainer");
  if (modal) {
    const dateInput = document.getElementById("appointmentDate");
    if (dateInput) {
      const today = new Date().toISOString().split("T")[0];
      dateInput.value = today;
      dateInput.min = today;

      // Add an event listener to the date input inside the modal for manual changes
      dateInput.addEventListener("change", function () {
        const selectedDate = new Date(this.value);
        if (selectedDate.getDay() === 2 && userGender === "male") {
          alert(
            "Males cannot schedule on Tuesdays (Pregnancy Service Day). Please choose another date."
          );
          this.value = today; // Reset to today
        }
      });
    }

    // Final check if the button was clicked while "today" is a Tuesday
    const initialDate = new Date(dateInput.value);
    if (initialDate.getDay() === 2 && userGender === "male") {
      // If today is Tuesday, default the input to tomorrow for males
      const tomorrow = new Date();
      tomorrow.setDate(tomorrow.getDate() + 1);
      dateInput.value = tomorrow.toISOString().split("T")[0];
    }

    modal.style.display = "flex";
    document.body.style.overflow = "hidden";
  }
}

function closeScheduleModal() {
  const modal = document.getElementById("modalContainer");
  if (modal) {
    modal.style.display = "none";
    document.body.style.overflow = "";
  }
}

async function handleScheduleSubmit(e) {
  e.preventDefault();

  const patientName = document.getElementById("patientName").value.trim();
  const appointmentDate = document.getElementById("appointmentDate").value;

  if (!patientName || !appointmentDate) {
    alert("Please fill all fields");
    return;
  }

  try {
    const response = await fetch("save_schedule.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        patientName,
        appointmentDate,
        userId,
      }),
    });

    const data = await response.json();

    if (data.success) {
      alert("Appointment scheduled! Number: " + (data.appointmentNumber || ""));
      closeScheduleModal();
      if (calendar) {
        calendar.refetchEvents();
      }
    } else {
      throw new Error(data.message || "Failed to schedule");
    }
  } catch (error) {
    alert("Error: " + error.message);
  }
}

function filterMedicines() {
  const input = document.getElementById("searchMedicine").value.toLowerCase();
  const rows = document.querySelectorAll("#medicineTable tbody tr");

  rows.forEach((row) => {
    const name = row.cells[0].textContent.toLowerCase();
    row.style.display = name.includes(input) ? "" : "none";
  });
}

function enableEditMode() {
  document.getElementById("editRecordBtn").style.display = "none";
  document.getElementById("saveRecordBtn").style.display = "inline-block";
  document.getElementById("cancelEditBtn").style.display = "inline-block";

  document
    .querySelectorAll(".view-mode")
    .forEach((el) => (el.style.display = "none"));
  document
    .querySelectorAll(".edit-mode")
    .forEach((el) => (el.style.display = "inline-block"));

  const firstInput = document.querySelector(".edit-mode");
  if (firstInput) {
    firstInput.focus();
  }
}

function cancelEditMode() {
  document.getElementById("editRecordBtn").style.display = "inline-block";
  document.getElementById("saveRecordBtn").style.display = "none";
  document.getElementById("cancelEditBtn").style.display = "none";

  document
    .querySelectorAll(".view-mode")
    .forEach((el) => (el.style.display = "inline-block"));
  document
    .querySelectorAll(".edit-mode")
    .forEach((el) => (el.style.display = "none"));

  document.getElementById("recordForm").reset();
}

// Helper function to safely get form values
function getFormValue(form, fieldName) {
  const el = form.querySelector(`[name="${fieldName}"]`);
  if (!el) {
    console.warn(`Form field not found: ${fieldName}`);
    return null;
  }
  return el.value;
}

async function saveRecordChanges() {
  const form = document.getElementById("recordForm");
  if (!form) {
    alert("Error: Form not found");
    return;
  }

  // Check if userId is available
  if (!userId) {
    alert("Error: User ID is missing. Please log in again.");
    return;
  }

  const saveBtn = document.getElementById("saveRecordBtn");
  if (!saveBtn) {
    alert("Error: Save button not found");
    return;
  }

  // Validate email
  const emailInput = form.querySelector('[name="email"]');
  if (
    emailInput &&
    emailInput.value &&
    !emailInput.value.endsWith("@gmail.com")
  ) {
    alert("Email must be a Gmail address (@gmail.com)");
    return;
  }

  try {
    saveBtn.disabled = true;
    saveBtn.innerHTML =
      '<span class="spinner-border spinner-border-sm" role="status"></span> Saving...';

    // Get all form values safely
    const formData = {
      surname: getFormValue(form, "surname"),
      firstName: getFormValue(form, "firstName"),
      middleInitial: getFormValue(form, "middleInitial"),
      birthday: getFormValue(form, "birthday"),
      age: getFormValue(form, "age"),
      gender: getFormValue(form, "gender"),
      address: getFormValue(form, "address"),
      email: getFormValue(form, "email"),
      contactNumber: getFormValue(form, "contactNumber"),
      civilStatus: getFormValue(form, "civilStatus"),
      occupation: getFormValue(form, "occupation"),
      existingMedical: getFormValue(form, "existingMedical"),
      currentMedication: getFormValue(form, "currentMedication"),
      allergies: getFormValue(form, "allergies"),
      familyMedical: getFormValue(form, "familyMedical"),
      bloodPressure: getFormValue(form, "bloodPressure"),
      heartRate: getFormValue(form, "heartRate"),
      temperature: getFormValue(form, "temperature"),
      height: getFormValue(form, "height"),
      weight: getFormValue(form, "weight"),
      description: getFormValue(form, "description"),
      checkup: getFormValue(form, "checkup"),
      guardian: getFormValue(form, "guardian"),
      relationship: getFormValue(form, "relationship"),
      guardianContact: getFormValue(form, "guardianContact"),
      emergency_contact_number: getFormValue(form, "emergency_contact_number"),
      philhealth: form.querySelector('[name="philhealth"]')?.checked ? 1 : 0,
      seniorCitizen: form.querySelector('[name="seniorCitizen"]')?.checked
        ? 1
        : 0,
      userId: userId,
    };

    console.log("Form data being sent:", formData);

    const response = await fetch("update_user.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(formData),
    });
    const responseText = await response.text();
    console.log("Raw server response:", responseText);

    let result;
    try {
      result = JSON.parse(responseText);
    } catch (parseError) {
      console.error("Failed to parse JSON:", parseError);
      throw new Error(
        "Server returned invalid response. Please check console for details."
      );
    }

    if (!response.ok) {
      throw new Error(
        result.message || `Server returned status ${response.status}`
      );
    }

    if (!result.success) {
      throw new Error(result.message || "Update failed without specific error");
    }

    alert("Profile updated successfully!");
    window.location.reload();
  } catch (error) {
    console.error("Full error details:", error);
    alert(`Error: ${error.message}\nCheck console for more details`);
  } finally {
    saveBtn.disabled = false;
    saveBtn.textContent = "Save Changes";
  }
}
document.addEventListener("keydown", function (e) {
  if (
    e.key === "Enter" &&
    document.getElementById("saveRecordBtn")?.style.display === "inline-block"
  ) {
    e.preventDefault();
    saveRecordChanges();
  }

  if (
    e.key === "Escape" &&
    document.getElementById("cancelEditBtn")?.style.display === "inline-block"
  ) {
    cancelEditMode();
  }
});

// Phone number formatting
document.querySelectorAll('input[type="tel"]').forEach((input) => {
  input.addEventListener("input", function (e) {
    let value = this.value.replace(/\D/g, "");
    if (value.startsWith("63")) {
      value = "+" + value;
    } else if (!value.startsWith("+63")) {
      value = "+63" + value;
    }
    this.value = value.substring(0, 13);
  });
});

// Add this inside your setupEventListeners() function or at the bottom of the file
document
  .getElementById("deleteScheduleBtn")
  ?.addEventListener("click", deleteMySchedule);

async function deleteMySchedule() {
  const confirmation = confirm(
    "Once you click OK, your schedule will be deleted permanently. Are you sure you want to remove your schedule?"
  );

  if (!confirmation) {
    return;
  }

  try {
    const response = await fetch("delete_schedule.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        userId: userId,
      }),
    });

    const data = await response.json();

    if (data.success) {
      alert("Your schedule has been successfully removed.");
      if (calendar) {
        calendar.refetchEvents(); // Refresh the calendar
      }
    } else {
      alert(data.message || "No active schedule found to delete.");
    }
  } catch (error) {
    console.error("Error deleting schedule:", error);
    alert("An error occurred while trying to delete your schedule.");
  }
}
