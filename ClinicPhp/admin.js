const loginModal = document.getElementById("loginModal");
const registerModal = document.getElementById("registerModal");
const congratsModal = document.getElementById("congratsModal");
const errorModal = document.getElementById("errorModal");
const loginBtn = document.getElementById("loginBtn");
const registerBtn = document.getElementById("registerBtn");
const closeLoginModal = document.getElementById("closeLoginModal");
const closeRegisterModal = document.getElementById("closeRegisterModal");
const closeCongratsBtn = document.getElementById("closeCongratsModal");

// Initialize modals and event listeners when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  // Initialize modal functionality
  initModals();

  // Check for login errors from PHP
  checkForLoginError();

  // Close modals when clicking outside
  setupOutsideClickHandlers();

  populateBirthdayDropdowns();

  // Initialize contact number validation
  setupContactNumberValidation();
});

function setupContactNumberValidation() {
  const contactNumberInput = document.getElementById("contactNumber");

  if (!contactNumberInput) return;

  // Validate input (only numbers, max 10 digits)
  contactNumberInput.addEventListener("input", function (e) {
    // Remove any non-digit characters
    this.value = this.value.replace(/\D/g, "");

    // Ensure the length doesn't exceed 10 digits
    if (this.value.length > 10) {
      this.value = this.value.slice(0, 10);
    }
  });

  // Prepend +63 when form is submitted
  const registerForm = document.getElementById("registerForm");
  if (registerForm) {
    registerForm.addEventListener("submit", function (e) {
      const contactNumber = document.getElementById("contactNumber");
      if (contactNumber && contactNumber.value) {
        // Ensure the value doesn't already start with +63
        if (!contactNumber.value.startsWith("+63")) {
          contactNumber.value = "+63" + contactNumber.value;
        }
      }
    });
  }
}

function populateBirthdayDropdowns() {
  console.log("Initializing birthday dropdowns for all ages...");

  const birthMonth = document.getElementById("birthMonth");
  const birthDay = document.getElementById("birthDay");
  const birthYear = document.getElementById("birthYear");

  if (!birthMonth || !birthDay || !birthYear) {
    console.error("Birthday dropdown elements not found!");
    return;
  }

  // Clear existing options (keep the first placeholder)
  birthMonth.innerHTML = '<option value="" disabled selected>Month</option>';
  birthDay.innerHTML = '<option value="" disabled selected>Day</option>';
  birthYear.innerHTML = '<option value="" disabled selected>Year</option>';

  // Populate Months (1-12)
  const months = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December",
  ];

  months.forEach((month, index) => {
    const option = document.createElement("option");
    option.value = index + 1;
    option.textContent = month;
    birthMonth.appendChild(option);
  });

  // Populate Years (from current year to 120 years ago)
  const currentYear = new Date().getFullYear();
  const minYear = currentYear - 120; // 120 years ago
  const maxYear = currentYear; // Up to current year

  for (let year = maxYear; year >= minYear; year--) {
    const option = document.createElement("option");
    option.value = year;
    option.textContent = year;
    birthYear.appendChild(option);
  }

  // Enable days when month/year are selected
  birthMonth.addEventListener("change", updateDays);
  birthYear.addEventListener("change", updateDays);

  function updateDays() {
    birthDay.innerHTML = '<option value="" disabled selected>Day</option>';

    const month = birthMonth.value;
    const year = birthYear.value;

    if (!month || !year) {
      birthDay.disabled = true;
      return;
    }

    birthDay.disabled = false;

    // Calculate days in month (handles leap years for February)
    const daysInMonth = new Date(year, month, 0).getDate();

    for (let day = 1; day <= daysInMonth; day++) {
      const option = document.createElement("option");
      option.value = day;
      option.textContent = day;
      birthDay.appendChild(option);
    }
  }
}

// Initialize all modal functionality
function initModals() {
  // Login Modal
  if (loginBtn) {
    loginBtn.addEventListener("click", () => {
      loginModal.style.display = "block";
    });
  }

  if (closeLoginModal) {
    closeLoginModal.addEventListener("click", () => {
      loginModal.style.display = "none";
    });
  }

  // Register Modal
  if (registerBtn) {
    registerBtn.addEventListener("click", () => {
      registerModal.style.display = "block";
    });
  }

  if (closeRegisterModal) {
    closeRegisterModal.addEventListener("click", () => {
      registerModal.style.display = "none";
    });
  }

  // Congrats Modal
  if (closeCongratsBtn) {
    closeCongratsBtn.addEventListener("click", () => {
      congratsModal.style.display = "none";
    });
  }
}

// Check for login errors passed from PHP
function checkForLoginError() {
  // Check for window.loginError set by PHP
  if (window.loginError) {
    showErrorModal(window.loginError);
  }

  // Check for error message in red text (legacy support)
  const loginError = document.querySelector('p[style="color:red;"]');
  if (loginError && loginError.textContent) {
    showErrorModal(loginError.textContent);
  }
}

// Setup click handlers for closing modals when clicking outside
function setupOutsideClickHandlers() {
  // Login Modal
  if (loginModal) {
    window.addEventListener("click", (event) => {
      if (event.target === loginModal) {
        loginModal.style.display = "none";
      }
    });
  }

  // Register Modal
  if (registerModal) {
    window.addEventListener("click", (event) => {
      if (event.target === registerModal) {
        registerModal.style.display = "none";
      }
    });
  }

  // Congrats Modal
  if (congratsModal) {
    window.addEventListener("click", (event) => {
      if (event.target === congratsModal) {
        congratsModal.style.display = "none";
      }
    });
  }

  // Error Modal
  if (errorModal) {
    window.addEventListener("click", (event) => {
      if (event.target === errorModal) {
        closeErrorModal();
      }
    });
  }
}

// Show error modal with specific message
function showErrorModal(message) {
  const errorModal = document.getElementById("errorModal");
  const errorMessage = document.getElementById("errorMessage");

  if (errorModal && errorMessage) {
    errorMessage.textContent = message;
    errorModal.style.display = "block";
  }
}

// Close error modal
function closeErrorModal() {
  const errorModal = document.getElementById("errorModal");
  if (errorModal) {
    errorModal.style.display = "none";
  }
}

// Check for login errors on page load
document.addEventListener("DOMContentLoaded", function () {
  // Check if there's a login error from PHP
  if (window.loginError) {
    showErrorModal(window.loginError);
  }
});

// Validate password match in registration form
function validatePassword() {
  const password = document.getElementById("regPassword").value;
  const confirmPassword = document.getElementById("confirmPassword").value;

  if (password !== confirmPassword) {
    showErrorModal("Make sure that the password is the same!");
    return false; // Prevent form submission
  }
  return true; // Allow form submission
}

// Global variable to track password strength
let passwordStrength = "weak"; // Global variable to track strength

function checkPasswordStrength() {
  const password = document.getElementById("regPassword").value;
  const strengthMeter = document.querySelector(".strength-meter");
  const strengthText = document.getElementById("strengthText");
  let strength = 0;

  // Check criteria
  if (password.length >= 8) strength++;
  if (password.length >= 12) strength++;
  if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
  if (/\d/.test(password)) strength++;
  if (/[^a-zA-Z0-9]/.test(password)) strength++;

  // Update UI and set global strength
  const width = (strength / 5) * 100;
  strengthMeter.style.width = width + "%";

  if (strength <= 1) {
    strengthMeter.style.backgroundColor = "#e74c3c";
    strengthText.textContent = "Weak";
    passwordStrength = "weak";
  } else if (strength <= 3) {
    strengthMeter.style.backgroundColor = "#f39c12";
    strengthText.textContent = "Medium";
    passwordStrength = "medium";
  } else {
    strengthMeter.style.backgroundColor = "#16a085";
    strengthText.textContent = "Strong";
    passwordStrength = "strong";
  }
}

function validateRegistration() {
  // Check password strength
  if (passwordStrength === "weak") {
    showErrorModal(
      "MAKE YOUR PASSWORD STRONGER! (Use 8+ characters with mix of letters, numbers, and symbols)"
    );
    return false; // Prevent form submission
  }

  // Check password match
  const password = document.getElementById("regPassword").value;
  const confirmPassword = document.getElementById("confirmPassword").value;

  if (password !== confirmPassword) {
    showErrorModal("Passwords do not match!");
    return false;
  }

  return true; // Allow submission
}

// Update form submission
document
  .getElementById("registerForm")
  .addEventListener("submit", function (event) {
    if (!validateRegistration()) {
      event.preventDefault(); // Stop form submission
    }
  });

// Modify validatePassword() to check strength
function validatePassword() {
  const password = document.getElementById("regPassword").value;
  const confirmPassword = document.getElementById("confirmPassword").value;

  // Check if passwords match
  if (password !== confirmPassword) {
    showErrorModal("Passwords do not match!");
    return false;
  }

  // Check if password is strong enough
  if (!isPasswordStrong) {
    showErrorModal("Password is too weak! Please use a stronger password.");
    return false;
  }

  return true; // Allow form submission
}

// Update form submission to validate
document
  .getElementById("registerForm")
  .addEventListener("submit", function (event) {
    if (!validatePassword()) {
      event.preventDefault(); // Block form submission
    }
  });

function checkPasswordMatch() {
  const password = document.getElementById("regPassword").value;
  const confirmPassword = document.getElementById("confirmPassword").value;
  const matchIndicator = document.getElementById("passwordMatch");

  if (password && confirmPassword) {
    if (password === confirmPassword) {
      matchIndicator.classList.add("valid");
      matchIndicator.querySelector(".match-text").textContent =
        "Passwords match";
    } else {
      matchIndicator.classList.remove("valid");
      matchIndicator.querySelector(".match-text").textContent =
        "Passwords don't match";
    }
  }
}

// Set max date for birthday (18 years ago)
document.addEventListener("DOMContentLoaded", function () {
  const today = new Date();
  const maxDate = new Date();
  maxDate.setFullYear(today.getFullYear() - 18);

  // Format as YYYY-MM-DD
  const maxDateStr = maxDate.toISOString().split("T")[0];
  document.getElementById("birthday").max = maxDateStr;

  // Set min date (120 years ago)
  const minDate = new Date();
  minDate.setFullYear(today.getFullYear() - 120);
  const minDateStr = minDate.toISOString().split("T")[0];
  document.getElementById("birthday").min = minDateStr;
});
