/* user.js - Final Version */

document.addEventListener("DOMContentLoaded", function () {
  // 1. Initialize calendar
  if (document.getElementById("calendar")) {
    initializeCalendar();
  }

  // 2. Menu Tab Switching
  document.querySelectorAll(".menu-btn").forEach((button) => {
    button.addEventListener("click", function () {
      const sectionId = this.getAttribute("data-section");
      if (
        (sectionId === "registration" || sectionId === "stockOfMedicines") &&
        userStatus !== "approved"
      ) {
        alert(
          "Your account is pending approval. You cannot access this section yet."
        );
        showContent("defaultContent");
        checkApprovalDisplay(); // Ensure warning is visible
        return;
      }
      showContent(sectionId);
    });
  });

  setupEventListeners();
  setupDeleteModalLogic();

  showContent("defaultContent");
  checkApprovalDisplay(); // Check on load
});

// --- HELPER: CHECK GENDER ---
function isMale() {
  if (typeof window.userGender !== "undefined" && window.userGender) {
    return window.userGender.toString().trim().toLowerCase() === "male";
  }
  return false;
}

// --- NAVIGATION FUNCTIONS ---
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

// --- CALENDAR LOGIC ---
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
      // --- CLICKING A DATE ---
      dateClick: function (info) {
        // DEBUG: Check your browser console (F12) to see this message
        console.log("Date Clicked:", info.dateStr);

        const day = info.date.getDay();

        // 1. Prevent Males on Tuesdays
        if (day === 2 && isMale()) {
          alert(
            "Males cannot schedule appointments on Pregnancy service days (Tuesday)."
          );
          return;
        }

        // 2. SHOW THE LIST (This calls the function at the bottom of the file)
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
      dayCellContent: function (arg) {
        const day = arg.date.getDay();
        const dateNum = arg.date.getDate();

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
                  display.style.color = "#d4d400";
                }
              }
            })
            .catch((e) => console.log(""));
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
          titleFormat: { year: "numeric", month: "long" },
        },
      },
    });
    calendar.render();
  }
}

/* REPLACE THESE FUNCTIONS IN user.js */

async function showAppointmentsForDate(dateStr) {
  try {
    console.log("Fetching schedule for:", dateStr); // Debug

    const response = await fetch(`get_schedules.php?date=${dateStr}`);
    if (!response.ok) throw new Error("Network response was not ok");

    const data = await response.json();

    // Build the HTML for the list
    let listHtml = "";

    if (data.events && data.events.length > 0) {
      data.events.forEach((event) => {
        // 1. Status Logic: Show "(Completed)" or leave blank
        const isCompleted = event.is_completed == 1;
        const statusBadge = isCompleted
          ? '<span style="color: green; font-weight: bold; margin-left: 10px;">(Completed)</span>'
          : ""; // Leave blank if not completed

        // 2. Name Logic: Get current user name safely
        const myName =
          typeof window.currentUserName !== "undefined" &&
          window.currentUserName
            ? window.currentUserName.toString().trim().toLowerCase()
            : "";
        const listName = event.patient_name
          ? event.patient_name.toString().trim().toLowerCase()
          : "";

        // 3. Masking Logic: Default to asterisks
        let displayName = "*******";
        let rowClass = "appointment-item";

        // If it is ME, show my name and highlight the row
        if (myName !== "" && listName === myName) {
          displayName = event.patient_name; // Show real name
          rowClass += " my-appointment"; // Add highlight class
        }

        // 4. Construct Row HTML
        listHtml += `
            <div class="${rowClass}">
                <div style="display:flex; align-items:center;">
                    <span style="font-weight: bold; color: #4a6fa5; margin-right:10px;">#${event.appointment_number}</span>
                    <span style="font-weight: 500;">${displayName}</span>
                </div>
                ${statusBadge}
            </div>`;
      });
    } else {
      listHtml = `<div class="no-appointments" style="padding: 20px; text-align: center; color: #777;">NO APPOINTMENTS SCHEDULED</div>`;
    }

    // Build the Full Modal Content
    const fullContent = `
        <h3 style="margin-top:0; margin-bottom:15px; border-bottom:1px solid #ddd; padding-bottom:10px;">
            Appointments for <br>
            <span style="font-size:0.8em; color:#666;">${formatDate(
              dateStr
            )}</span>
        </h3>
        <div class="appointments-list">
            ${listHtml}
        </div>
    `;

    // Show it
    showAppointmentsModal(fullContent);
  } catch (error) {
    console.error("Error:", error);
    alert("Error loading list: " + error.message);
  }
}

// --- VISIBILITY FIX HELPER ---
function showAppointmentsModal(content) {
  // 1. Cleanup old modals
  const existingModal = document.querySelector(".appointments-modal-container");
  if (existingModal) {
    document.body.removeChild(existingModal);
  }

  // 2. Create Modal Structure
  const modal = document.createElement("div");
  modal.className = "appointments-modal-container";

  // Note: We don't need a separate backdrop div because the container handles the background color
  modal.innerHTML = `
    <div class="appointments-modal-content">
      <span class="close-appointments-modal">&times;</span>
      ${content}
    </div>
  `;

  // 3. Add to Document
  document.body.appendChild(modal);

  // 4. FORCE VISIBILITY (The most important part)
  requestAnimationFrame(() => {
    modal.style.display = "flex";
  });

  // 5. Close Logic
  const closeBtn = modal.querySelector(".close-appointments-modal");

  // Close on X button
  closeBtn.addEventListener("click", () => {
    modal.style.display = "none";
    document.body.removeChild(modal);
  });

  // Close on Outside Click
  modal.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.style.display = "none";
      document.body.removeChild(modal);
    }
  });
}
// --- DELETE MODAL LOGIC ---
function setupDeleteModalLogic() {
  const deleteBtn = document.getElementById("deleteScheduleBtn");
  const confirmModal = document.getElementById("deleteConfirmModal");
  const successModal = document.getElementById("deleteSuccessModal");
  const errorModal = document.getElementById("deleteErrorModal");

  const confirmActionBtn = document.getElementById("confirmDeleteActionBtn");
  const cancelActionBtn = document.getElementById("cancelDeleteActionBtn");
  const successOkBtn = document.getElementById("successOkBtn");
  const errorOkBtn = document.getElementById("errorOkBtn");

  if (deleteBtn) {
    const newDeleteBtn = deleteBtn.cloneNode(true);
    deleteBtn.parentNode.replaceChild(newDeleteBtn, deleteBtn);

    newDeleteBtn.addEventListener("click", function (e) {
      e.preventDefault();
      if (confirmModal) confirmModal.style.display = "flex";
    });
  }

  if (cancelActionBtn) {
    cancelActionBtn.addEventListener("click", function (e) {
      e.preventDefault();
      if (confirmModal) confirmModal.style.display = "none";
    });
  }

  if (confirmActionBtn) {
    confirmActionBtn.addEventListener("click", function (e) {
      e.preventDefault();
      if (confirmModal) confirmModal.style.display = "none";
      performDeleteRequest(successModal, errorModal);
    });
  }

  if (successOkBtn) {
    successOkBtn.addEventListener("click", function (e) {
      e.preventDefault();
      if (successModal) successModal.style.display = "none";
      window.location.reload();
    });
  }

  if (errorOkBtn) {
    errorOkBtn.addEventListener("click", function (e) {
      e.preventDefault();
      if (errorModal) errorModal.style.display = "none";
    });
  }

  window.addEventListener("click", function (e) {
    if (e.target.classList.contains("modal-backdrop")) {
      if (confirmModal) confirmModal.style.display = "none";
      if (errorModal) errorModal.style.display = "none";
    }
  });
}

async function performDeleteRequest(successModal, errorModal) {
  try {
    const response = await fetch("delete_schedule.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ userId: userId }),
    });

    const data = await response.json();

    if (data.success) {
      if (successModal) successModal.style.display = "flex";
    } else {
      if (errorModal) errorModal.style.display = "flex";
      else alert(data.message);
    }
  } catch (error) {
    console.error("Error:", error);
    alert("An error occurred while trying to delete.");
  }
}

// --- GENERAL EVENT LISTENERS ---
function setupEventListeners() {
  document.addEventListener("click", function (e) {
    if (
      e.target &&
      (e.target.id === "scheduleBtn" || e.target.closest("#scheduleBtn"))
    ) {
      e.preventDefault();
      if (userStatus !== "approved") {
        alert("Your account must be approved by an admin before scheduling.");
        return;
      }
      openScheduleModal();
    }
  });

  document.addEventListener("click", function (e) {
    if (e.target && e.target.classList.contains("close-btn")) {
      const modal = document.getElementById("modalContainer");
      if (modal) modal.style.display = "none";
    }
    if (
      e.target &&
      e.target.classList.contains("modal-backdrop") &&
      e.target.closest("#modalContainer")
    ) {
      document.getElementById("modalContainer").style.display = "none";
    }
  });

  const scheduleForm = document.getElementById("scheduleForm");
  if (scheduleForm) {
    scheduleForm.addEventListener("submit", handleScheduleSubmit);
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

// --- HELPER FUNCTIONS ---

function openScheduleModal(dateStr = null) {
  const modal = document.getElementById("modalContainer");
  if (modal) {
    const dateInput = document.getElementById("appointmentDate");
    const today = new Date().toISOString().split("T")[0];

    if (dateStr) {
      dateInput.value = dateStr;
    } else {
      dateInput.value = today;
    }
    dateInput.min = today;

    dateInput.addEventListener("change", function () {
      const d = new Date(this.value);
      if (d.getDay() === 2 && isMale()) {
        alert("Males cannot schedule on Tuesdays (Pregnancy Service Day).");
        this.value = "";
      }
    });

    modal.style.display = "flex";
  }
}

async function handleScheduleSubmit(e) {
  e.preventDefault();
  if (userStatus !== "approved") {
    alert("Account not approved.");
    return;
  }
  const patientName = document.getElementById("patientName").value;
  const appointmentDate = document.getElementById("appointmentDate").value;

  if (!patientName || !appointmentDate) {
    alert("Please fill all fields");
    return;
  }

  const d = new Date(appointmentDate);
  if (d.getDay() === 2 && isMale()) {
    alert("Males cannot schedule on Tuesdays.");
    return;
  }

  try {
    const response = await fetch("save_schedule.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ patientName, appointmentDate, userId }),
    });
    const data = await response.json();
    if (data.success) {
      alert(
        "Appointment scheduled! Queue #: " + (data.appointmentNumber || "")
      );
      document.getElementById("modalContainer").style.display = "none";
      if (calendar) calendar.refetchEvents();
      window.location.reload();
    } else {
      alert(data.message || "Failed to schedule");
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
}

function getFormValue(form, name) {
  const el = form.querySelector(`[name="${name}"]`);
  return el ? el.value : null;
}

async function saveRecordChanges() {
  const form = document.getElementById("recordForm");
  const saveBtn = document.getElementById("saveRecordBtn");

  const formData = {
    userId: userId,
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
  };

  saveBtn.innerText = "Saving...";

  try {
    const response = await fetch("update_user.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(formData),
    });
    const result = await response.json();

    if (result.success) {
      alert("Profile updated successfully!");
      window.location.reload();
    } else {
      alert("Error: " + result.message);
    }
  } catch (e) {
    alert("Error saving record.");
    console.error(e);
  } finally {
    saveBtn.innerText = "Save Changes";
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

// --- REPLACE THIS FUNCTION AT THE BOTTOM OF USER.JS ---

function showAppointmentsModal(content) {
  console.log("Opening Appointment List Modal..."); // Debug check

  // 1. Remove any existing modals first
  const existingModal = document.querySelector(".appointments-modal-container");
  if (existingModal) {
    document.body.removeChild(existingModal);
  }

  // 2. Create the new modal
  const modal = document.createElement("div");
  modal.className = "appointments-modal-container";

  modal.innerHTML = `
    <div class="appointments-modal-backdrop"></div>
    <div class="appointments-modal-content">
      <span class="close-appointments-modal" style="cursor:pointer; font-size:24px; float:right;">&times;</span>
      ${content}
    </div>
  `;

  // 3. Append to body
  document.body.appendChild(modal);

  // 4. FORCE DISPLAY (This fixes the "Invisible" issue)
  modal.style.display = "flex";

  // 5. Add Close Event Listeners
  const closeBtn = modal.querySelector(".close-appointments-modal");
  const backdrop = modal.querySelector(".appointments-modal-backdrop");

  if (closeBtn) {
    closeBtn.addEventListener("click", () => {
      document.body.removeChild(modal);
    });
  }

  if (backdrop) {
    backdrop.addEventListener("click", () => {
      document.body.removeChild(modal);
    });
  }
}

// --- NEW: Handle click on "Pending" sidebar box ---
function goToDefaultContent() {
  showContent("defaultContent");
  const warning = document.getElementById("approvalWarning");
  if (warning) {
    warning.scrollIntoView({ behavior: "smooth" });
    warning.style.border = "2px solid red";
    setTimeout(() => {
      warning.style.border = "1px solid #ffeeba";
    }, 2000);
  }
}

// --- NEW: Check if we should show the warning box ---
function checkApprovalDisplay() {
  const warningBox = document.getElementById("approvalWarning");
  if (
    typeof userStatus !== "undefined" &&
    (userStatus === "pending" || userStatus === "")
  ) {
    if (warningBox) warningBox.style.display = "block";
  } else {
    if (warningBox) warningBox.style.display = "none";
  }
}
