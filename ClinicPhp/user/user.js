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

// --- CALENDAR LOGIC (Mobile Click Fix) ---
let calendar;

// Global wrapper for click events
window.showAppointmentsForDate = function (dateStr) {
  if (typeof fetchAndShowAppointments === "function") {
    fetchAndShowAppointments(dateStr);
  } else {
    console.error("Fetch function missing");
  }
};

function initializeCalendar() {
  const calendarEl = document.getElementById("calendar");
  if (!calendarEl) return;

  const isMobile = window.innerWidth < 768;
  const initialView = isMobile ? "dayGridDay" : "dayGridMonth";

  calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: initialView,
    headerToolbar: {
      left: isMobile ? "prev,next" : "prev,next today",
      center: "title",
      right: isMobile ? "" : "dayGridMonth",
    },
    height: "auto",

    events: {
      url: "get_schedules.php",
      method: "GET",
      failure: function (error) {
        console.error("Failed to fetch events:", error);
      },
    },

    // 1. Strict Resize Logic
    windowResize: function (view) {
      if (window.innerWidth < 768) {
        calendar.changeView("dayGridDay");
        calendar.setOption("headerToolbar", {
          left: "prev,next",
          center: "title",
          right: "",
        });
      } else {
        calendar.changeView("dayGridMonth");
        calendar.setOption("headerToolbar", {
          left: "prev,next today",
          center: "title",
          right: "",
        });
      }
    },

    // 2. Date Click (Background)
    dateClick: function (info) {
      handleDateInteraction(info.dateStr, info.date);
    },

    dayCellClassNames: function (arg) {
      const day = arg.date.getDay();
      const classNames = [];
      if (day === 2) classNames.push("tuesday-day");
      else if (day === 3) classNames.push("wednesday-day");
      else if (day === 0 || day === 6) classNames.push("weekend-day");
      return classNames;
    },

    // 3. Custom Content (WITH CLICK OVERLAY FIX)
    dayCellContent: function (arg) {
      const day = arg.date.getDay();
      const dateNum = arg.date.getDate();

      // Calculate Date String Correctly
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

      // Async fetch
      setTimeout(() => {
        fetch(`get_day_count.php?date=${dateLocalStr}`)
          .then((res) => res.json())
          .then((data) => {
            const display = document.getElementById(countId);
            if (display) {
              const count = data.count || 0;
              display.innerText =
                count > 0 ? `${count} Scheduled` : "No Appointments";

              // Colors
              if (count >= 30) display.style.color = "#c0392b";
              else if (count >= 20) display.style.color = "#d4ac0d";
              else if (count > 0) display.style.color = "#27ae60";
              else display.style.color = "#7f8c8d";
            }
          })
          .catch(console.error);
      }, 0);

      // --- THE HTML STRUCTURE FIX ---
      // We add an absolute overlay div that handles the click.
      // This solves issues where clicks on child elements (like text) get blocked.
      return {
        html: `<div class="fc-day-grid-content" style="position: relative; height: 100%; width: 100%;">
                
                <div class="mobile-click-overlay" 
                     onclick="window.showAppointmentsForDate('${dateLocalStr}')"
                     style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 10; cursor: pointer;">
                </div>

                <div class="fc-day-top-info" style="pointer-events: none;">
                  <span class="fc-day-message">${dayMessage}</span>
                  <span class="fc-day-number">${dateNum}</span>
                </div>
                <div id="${countId}" class="fc-patient-count-bottom" style="pointer-events: none;">Loading...</div>
              </div>`,
      };
    },
  });

  calendar.render();

  // 4. Swipe Support
  let touchStartX = 0;
  let touchEndX = 0;
  calendarEl.addEventListener(
    "touchstart",
    (e) => (touchStartX = e.changedTouches[0].screenX),
    { passive: true }
  );
  calendarEl.addEventListener(
    "touchend",
    (e) => {
      touchEndX = e.changedTouches[0].screenX;
      if (touchEndX < touchStartX - 50) calendar.next();
      if (touchEndX > touchStartX + 50) calendar.prev();
    },
    { passive: true }
  );
}

// --- HELPER ---
function handleDateInteraction(dateStr, dateObj) {
  const day = dateObj.getDay();
  if (day === 2 && isMale()) {
    alert(
      "Males cannot schedule appointments on Pregnancy service days (Tuesday)."
    );
    return;
  }
  window.showAppointmentsForDate(dateStr);
}

/* REPLACE THESE FUNCTIONS IN user.js */

async function fetchAndShowAppointments(dateStr) {
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

  // --- 1. DEFINING REQUIRED FIELDS ---
  const requiredFields = [
    { name: "surname", label: "Surname" },
    { name: "firstName", label: "First Name" },
    { name: "middleInitial", label: "Middle Name" },
    { name: "birthday", label: "Date of Birth" },
    { name: "age", label: "Age" },
    { name: "gender", label: "Gender" },
    { name: "address", label: "Address" },
    { name: "contactNumber", label: "Contact Number" },
    { name: "email", label: "Email" },
    { name: "civilStatus", label: "Civil Status" },
    { name: "occupation", label: "Occupation" },
    { name: "guardian", label: "Guardian" },
    { name: "relationship", label: "Relationship" },
    { name: "guardianContact", label: "Guardian Contact" },
    { name: "emergency_contact_number", label: "Emergency Contact #2" },
  ];

  // --- 2. VALIDATION CHECK (Using Custom Modal) ---
  for (const field of requiredFields) {
    const value = getFormValue(form, field.name);

    // Check if empty
    if (!value || value.trim() === "") {
      showValidationModal(
        `Please fill in the "${field.label}" field if none type N/a.`
      );
      return; // STOP EXECUTION
    }

    // Check phone number length (must be 13 chars)
    if (field.name.includes("Contact") || field.name.includes("Number")) {
      if (value.length < 13) {
        showValidationModal(
          `The "${field.label}" is incomplete. It must be 13 characters (e.g., +639...)`
        );
        return; // STOP EXECUTION
      }
    }
  }

  // --- 3. PROCEED TO SAVE ---
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
      alert("Profile updated successfully!"); // You can replace this with a success modal too if you want!
      window.location.reload();
    } else {
      showValidationModal("Error: " + result.message);
    }
  } catch (e) {
    showValidationModal("An error occurred while saving the record.");
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

/* ========================================= */
/* MEDICINE ACCORDION LOGIC (Mobile Only)    */
/* ========================================= */

// Make the function globally available
window.toggleMedDescription = function (row) {
  // Only run this logic on mobile screens (width < 768px)
  if (window.innerWidth > 768) return;

  // 1. Find the next row (which is the hidden description)
  const nextRow = row.nextElementSibling;

  // 2. Find the arrow icon inside the clicked row
  const arrow = row.querySelector(".mobile-arrow");

  // 3. Safety check: ensure the next row exists and is actually a description row
  if (nextRow && nextRow.classList.contains("mobile-desc-row")) {
    // 4. Toggle Visibility
    if (nextRow.style.display === "none") {
      // SHOW IT
      nextRow.style.display = "table-row";
      row.style.backgroundColor = "#e8f4f8"; // Highlight active row
      if (arrow) arrow.style.transform = "rotate(180deg)"; // Flip arrow Up
    } else {
      // HIDE IT
      nextRow.style.display = "none";
      row.style.backgroundColor = ""; // Remove highlight
      if (arrow) arrow.style.transform = "rotate(0deg)"; // Flip arrow Down
    }
  }
};

/* ========================================= */
/* RECORD ACCORDION LOGIC (Mobile Only)      */
/* ========================================= */

window.toggleRecordSection = function (header) {
  // Only work on mobile
  if (window.innerWidth > 768) return;

  // Get the parent group div
  const group = header.parentElement;

  // Toggle the 'active' class
  // This triggers the CSS to show .record-content and rotate the arrow
  if (group.classList.contains("active")) {
    group.classList.remove("active");
  } else {
    // Optional: Close others when opening one?
    // Uncomment lines below if you want "Accordion" behavior (only 1 open at a time)
    /*
        document.querySelectorAll('.record-group').forEach(el => el.classList.remove('active'));
        */

    group.classList.add("active");
  }
};

/* ========================================= */
/* VALIDATION MODAL LOGIC                    */
/* ========================================= */

function showValidationModal(message) {
  const modal = document.getElementById("validationModal");
  const msgElement = document.getElementById("validationMessage");

  if (modal && msgElement) {
    msgElement.innerText = message;
    modal.style.display = "flex"; // Uses your existing flex centering class
  }
}

function closeValidationModal() {
  const modal = document.getElementById("validationModal");
  if (modal) {
    modal.style.display = "none";
  }
}

// Close modal if clicking outside the box
window.addEventListener("click", function (e) {
  const modal = document.getElementById("validationModal");
  if (e.target === modal) {
    modal.style.display = "none";
  }
});
