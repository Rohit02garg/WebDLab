// Campus Event Registration System - IIT (ISM) Dhanbad
// Client-Side Logic (Lab 8 Asynchronous Data & jQuery style)

// Global state variables
let currentEvents = [];
let activeSearchQuery = "";

// Initialize application when DOM is ready
$(document).ready(function() {
  initEventHub();
  setupEventHandlers();

  // Periodic background sync every 30 seconds to update dynamic seats
  setInterval(function() {
    syncEventsSilently();
  }, 30000);
});

// Initialize Event Hub: load events and restore saved student info
function initEventHub() {
  loadEvents();

  // Prioritize active session credentials, fallback to localStorage
  let savedAdmissionNo = (window.sessionStudent && window.sessionStudent.admissionNo) ? window.sessionStudent.admissionNo : localStorage.getItem("iitism_admission_no");
  if (savedAdmissionNo) {
    $("#regAdmissionNo").val(savedAdmissionNo);
    $("#lookupAdmissionNo").val(savedAdmissionNo);
    $("#quickCancelAdmissionNo").val(savedAdmissionNo);
  }

  let savedName = (window.sessionStudent && window.sessionStudent.studentName) ? window.sessionStudent.studentName : localStorage.getItem("iitism_student_name");
  if (savedName) {
    $("#regStudentName").val(savedName);
  }

  let savedEmail = (window.sessionStudent && window.sessionStudent.email) ? window.sessionStudent.email : localStorage.getItem("iitism_email");
  if (savedEmail) {
    $("#regEmail").val(savedEmail);
  }

  let savedPhone = (window.sessionStudent && window.sessionStudent.phone) ? window.sessionStudent.phone : localStorage.getItem("iitism_phone");
  if (savedPhone) {
    $("#regPhone").val(savedPhone);
  }

  let savedDept = (window.sessionStudent && window.sessionStudent.department) ? window.sessionStudent.department : localStorage.getItem("iitism_department");
  if (savedDept) {
    $("#regDepartment").val(savedDept);
  }
}

// Setup event listeners for user interactions
function setupEventHandlers() {
  // Navigation button: All Events
  $("#navAllEvents").on("click", function() {
    $("#navMyRegistrations").removeClass("active");
    $(this).addClass("active");
    window.scrollTo({ top: 0, behavior: "smooth" });
  });

  // Navigation button: My Registrations - automatically fetch data for logged in student
  $("#navMyRegistrations").on("click", function(e) {
    e.preventDefault();
    let currAdm = $("#lookupAdmissionNo").val().trim();
    if (!currAdm && window.sessionStudent && window.sessionStudent.admissionNo) {
      currAdm = window.sessionStudent.admissionNo;
      $("#lookupAdmissionNo").val(currAdm);
    }
    openModal("#myRegistrationsModal");
    if (currAdm) {
      fetchMyRegistrations(currAdm);
    }
  });

  // Automatically fetch registrations as user types in the search box (live debounce)
  let lookupTimer = null;
  $("#lookupAdmissionNo").on("input", function() {
    clearTimeout(lookupTimer);
    let adm = $(this).val().trim();
    if (adm.length >= 6) {
      lookupTimer = setTimeout(function() {
        fetchMyRegistrations(adm);
      }, 300);
    }
  });

  // Lookup registrations button
  $("#btnLookupReg").on("click", function() {
    let admNo = $("#lookupAdmissionNo").val().trim();
    if (!admNo) {
      showToast("warning", "Admission No Required", "Please enter your Admission Number (e.g. 22JE0456).");
      return;
    }
    fetchMyRegistrations(admNo);
  });

  // Lookup on Enter key in admission number field
  $("#lookupAdmissionNo").on("keypress", function(e) {
    if (e.which === 13) {
      $("#btnLookupReg").trigger("click");
    }
  });

  // Registration form submission via AJAX
  $("#registrationForm").on("submit", function(e) {
    e.preventDefault();
    submitRegistration();
  });

  // Quick cancel form submission via AJAX
  $("#quickCancelForm").on("submit", function(e) {
    e.preventDefault();
    submitQuickCancel();
  });

  // Close modal when close button is clicked
  $(".modal-close-btn, .btn-modal-close").on("click", function() {
    closeAllModals();
  });

  // Close modal when backdrop is clicked
  $(".modal-backdrop").on("click", function(e) {
    if ($(e.target).hasClass("modal-backdrop")) {
      closeAllModals();
    }
  });

  // Close modal on Escape key press
  $(document).on("keydown", function(e) {
    if (e.key === "Escape") {
      closeAllModals();
    }
  });
}

// Fetch all events asynchronously from api/get_events.php
function loadEvents(callback) {
  let countPill = document.getElementById("eventsCountPill");
  if (countPill) {
    countPill.textContent = "Loading...";
  }

  $.ajax({
    url: "api/get_events.php",
    method: "GET",
    dataType: "json",
    success: function(res) {
      if (res.success) {
        currentEvents = res.events;
        filterAndRenderEvents();
        if (typeof callback === "function") {
          callback();
        }
      } else {
        showToast("error", "Error", res.message || "Failed to load events.");
      }
    },
    error: function(xhr, status, error) {
      console.error("API Error: " + error);
      showToast("error", "Connection Error", "Could not connect to event database.");
    }
  });
}

// Silently update seat counts in background without page reloads
function syncEventsSilently() {
  $.ajax({
    url: "api/get_events.php",
    method: "GET",
    dataType: "json",
    success: function(res) {
      if (res.success) {
        currentEvents = res.events;
        currentEvents.forEach(function(ev) {
          updateEventCardDOM(ev);
        });
      }
    }
  });
}

// Render events list
function filterAndRenderEvents() {
  renderEventsGrid(currentEvents);
}

// Render the grid of event cards into the DOM
function renderEventsGrid(events) {
  let $grid = $("#eventsGrid");
  $grid.empty();

  $("#eventsCountPill").text(events.length + " Available");

  if (events.length === 0) {
    $grid.html(
      '<div class="empty-state" style="grid-column: 1 / -1;">' +
        '<div class="empty-icon">🔍</div>' +
        '<h3>No Events Found</h3>' +
        '<p>No campus events match your search query.</p>' +
      '</div>'
    );
    return;
  }

  events.forEach(function(event) {
    let isFull = event.remaining_seats <= 0;
    let isFillingFast = !isFull && (event.remaining_seats <= Math.ceil(event.capacity * 0.25) || event.remaining_seats <= 3);

    let progressFillClass = "";
    let remainingValClass = "";

    if (isFull) {
      progressFillClass = "full";
      remainingValClass = "full";
    } else if (isFillingFast) {
      progressFillClass = "fast";
      remainingValClass = "fast";
    }

    let registerBtnHtml = isFull 
      ? '<button type="button" class="btn-register" id="btn-reg-' + event.id + '" disabled>🚫 Event Full</button>'
      : '<button type="button" class="btn-register" id="btn-reg-' + event.id + '" onclick="openRegistrationModal(' + event.id + ')">🎟️ Register Now</button>';

    let cardHtml = 
      '<div class="event-card ' + (isFull ? "is-full" : "") + '" id="event-card-' + event.id + '" data-id="' + event.id + '">' +
        '<div class="card-body">' +
          '<h3 class="event-title">' + escapeHtml(event.title) + '</h3>' +
          '<p class="event-desc">' + escapeHtml(event.description) + '</p>' +
          '<div class="event-meta-list">' +
            '<div class="meta-item">' +
              '<span class="meta-icon">📅</span>' +
              '<span><strong>Date:</strong> ' + event.date_formatted + ' • ' + event.time_formatted + '</span>' +
            '</div>' +
            '<div class="meta-item">' +
              '<span class="meta-icon">📍</span>' +
              '<span><strong>Venue:</strong> ' + escapeHtml(event.venue) + '</span>' +
            '</div>' +
          '</div>' +
          '<div class="seat-capacity-block">' +
            '<div class="seat-stats-row">' +
              '<div class="stat-seat-col">' +
                '<div class="val" id="cap-' + event.id + '">' + event.capacity + '</div>' +
                '<div class="lbl">Total Capacity</div>' +
              '</div>' +
              '<div class="stat-seat-col">' +
                '<div class="val" id="reg-' + event.id + '">' + event.registered_count + '</div>' +
                '<div class="lbl">Registered</div>' +
              '</div>' +
              '<div class="stat-seat-col highlight-remaining ' + remainingValClass + '" id="rem-col-' + event.id + '">' +
                '<div class="val" id="rem-' + event.id + '">' + event.remaining_seats + '</div>' +
                '<div class="lbl">Remaining Seats</div>' +
              '</div>' +
            '</div>' +
            '<div class="progress-container" title="' + event.percent_filled + '% seats filled">' +
              '<div class="progress-bar-fill ' + progressFillClass + '" id="progress-' + event.id + '" style="width: ' + event.percent_filled + '%;"></div>' +
            '</div>' +
          '</div>' +
        '</div>' +
        '<div class="card-footer">' +
          registerBtnHtml +
          '<button type="button" class="btn-cancel-seat" onclick="openQuickCancelModal(' + event.id + ', \'' + escapeHtml(event.title) + '\')">' +
            'Cancel Seat' +
          '</button>' +
        '</div>' +
      '</div>';

    $grid.append(cardHtml);
  });
}

// In-place dynamic DOM update for a specific card when seats change
function updateEventCardDOM(event) {
  let $card = $("#event-card-" + event.id);
  if ($card.length === 0) {
    return;
  }

  let isFull = event.remaining_seats <= 0;
  let isFillingFast = !isFull && (event.remaining_seats <= Math.ceil(event.capacity * 0.25) || event.remaining_seats <= 3);

  // Update numbers
  $("#cap-" + event.id).text(event.capacity);
  $("#reg-" + event.id).text(event.registered_count);
  $("#rem-" + event.id).text(event.remaining_seats);

  // Update progress bar
  let $progress = $("#progress-" + event.id);
  $progress.css("width", event.percent_filled + "%");
  $progress.removeClass("fast full");
  if (isFull) {
    $progress.addClass("full");
  } else if (isFillingFast) {
    $progress.addClass("fast");
  }

  // Update remaining column style
  let $remCol = $("#rem-col-" + event.id);
  $remCol.removeClass("fast full");
  if (isFull) {
    $remCol.addClass("full");
  } else if (isFillingFast) {
    $remCol.addClass("fast");
  }

  // Update register button
  let $btn = $("#btn-reg-" + event.id);
  if (isFull) {
    $card.addClass("is-full");
    $btn.prop("disabled", true).html("🚫 Event Full");
  } else {
    $card.removeClass("is-full");
    $btn.prop("disabled", false).html("🎟️ Register Now");
  }
}

// Open registration form modal for a chosen event
function openRegistrationModal(eventId) {
  let event = currentEvents.find(function(e) {
    return e.id === eventId;
  });

  if (!event) {
    return;
  }

  if (event.remaining_seats <= 0) {
    showToast("error", "Registration Closed", "This event has reached full capacity.");
    return;
  }

  // Automatically pre-fill fields and confirmation display with active logged-in student credentials
  if (window.sessionStudent) {
    if (window.sessionStudent.studentName) {
      $("#regStudentName").val(window.sessionStudent.studentName);
      $("#confirmStudentName").text(window.sessionStudent.studentName);
    }
    if (window.sessionStudent.admissionNo) {
      $("#regAdmissionNo").val(window.sessionStudent.admissionNo);
      $("#confirmAdmissionNo").text(window.sessionStudent.admissionNo);
    }
    if (window.sessionStudent.email) {
      $("#regEmail").val(window.sessionStudent.email);
      $("#confirmEmail").text(window.sessionStudent.email);
    }
    if (window.sessionStudent.phone) {
      $("#regPhone").val(window.sessionStudent.phone);
    }
    if (window.sessionStudent.department) {
      $("#regDepartment").val(window.sessionStudent.department);
      $("#confirmDepartment").text(window.sessionStudent.department);
    }
  }

  // Populate modal fields
  $("#regEventId").val(event.id);
  $("#modalEventTitle").text(event.title);
  $("#modalEventMeta").html("📅 " + event.date_formatted + " • " + event.time_formatted + " &nbsp;|&nbsp; 📍 " + escapeHtml(event.venue));
  $("#modalEventRemaining").html("<strong>" + event.remaining_seats + "</strong> seats remaining of " + event.capacity);

  // Clear previous alerts
  $("#regAlert").removeClass("error success").text("").hide();

  openModal("#registrationModal");
}

// Submit registration form asynchronously via AJAX
function submitRegistration() {
  let $btn = $("#btnSubmitReg");
  let originalText = $btn.html();

  $("#regAlert").removeClass("error success").hide();

  let s = window.sessionStudent || {};
  let formData = {
    event_id: parseInt($("#regEventId").val(), 10),
    student_name: ($("#regStudentName").val() || s.studentName || "").trim(),
    admission_no: ($("#regAdmissionNo").val() || s.admissionNo || "").trim().toUpperCase(),
    email: ($("#regEmail").val() || s.email || "").trim().toLowerCase(),
    phone: ($("#regPhone").val() || s.phone || "").trim(),
    department: ($("#regDepartment").val() || s.department || "")
  };

  // Quick client validation check
  if (!formData.student_name || !formData.admission_no || !formData.email || !formData.phone || !formData.department) {
    $("#regAlert").addClass("error").text("Please fill in all required campus registration fields.").show();
    return;
  }

  // Disable button and show loading text (Lab 8 pattern)
  $btn.prop("disabled", true).html("⏳ Registering...");

  $.ajax({
    url: "api/register.php",
    method: "POST",
    data: formData,
    dataType: "json",
    success: function(res) {
      if (res.success) {
        // Save student inputs to localStorage
        localStorage.setItem("iitism_student_name", formData.student_name);
        localStorage.setItem("iitism_admission_no", formData.admission_no);
        localStorage.setItem("iitism_email", formData.email);
        localStorage.setItem("iitism_phone", formData.phone);
        localStorage.setItem("iitism_department", formData.department);

        // Update in-memory session object
        if (window.sessionStudent) {
          window.sessionStudent.email = formData.email;
          window.sessionStudent.phone = formData.phone;
          window.sessionStudent.department = formData.department;
        }

        // Update local event seat counts dynamically
        let updatedData = res.data;
        let targetEvent = currentEvents.find(function(e) {
          return e.id === updatedData.event_id;
        });

        if (targetEvent) {
          targetEvent.registered_count = updatedData.registered_count;
          targetEvent.remaining_seats = updatedData.remaining_seats;
          targetEvent.percent_filled = updatedData.percent_filled;
          targetEvent.is_full = updatedData.is_full;

          // Update DOM dynamically without reload
          updateEventCardDOM(targetEvent);
        }

        closeAllModals();
        showToast("success", "Registration Confirmed! 🎉", res.message);
      } else {
        $("#regAlert").addClass("error").text(res.message).show();
        showToast("error", "Registration Failed", res.message);
      }
    },
    error: function(xhr) {
      let errorMsg = "An unexpected server error occurred. Please try again.";
      if (xhr.responseJSON && xhr.responseJSON.message) {
        errorMsg = xhr.responseJSON.message;
      }
      $("#regAlert").addClass("error").text(errorMsg).show();
      showToast("error", "Registration Blocked", errorMsg);
    },
    complete: function() {
      // Re-enable button when request completes (Lab 8 pattern)
      $btn.prop("disabled", false).html(originalText);
    }
  });
}

// Open quick cancel modal for an event
function openQuickCancelModal(eventId, eventTitle) {
  $("#quickCancelEventId").val(eventId);
  $("#quickCancelEventTitle").text(eventTitle);
  $("#quickCancelAlert").removeClass("error success").hide();

  // Autofill admission number if active session or saved
  let savedAdm = (window.sessionStudent && window.sessionStudent.admissionNo) ? window.sessionStudent.admissionNo : localStorage.getItem("iitism_admission_no");
  if (savedAdm) {
    $("#quickCancelAdmissionNo").val(savedAdm);
  }

  openModal("#quickCancelModal");
}

// Submit cancellation form asynchronously via AJAX
function submitQuickCancel() {
  let $btn = $("#btnSubmitQuickCancel");
  let originalText = $btn.html();
  let eventId = parseInt($("#quickCancelEventId").val(), 10);
  let admissionNo = $("#quickCancelAdmissionNo").val().trim().toUpperCase();

  if (!admissionNo) {
    $("#quickCancelAlert").addClass("error").text("Please enter your Admission Number.").show();
    return;
  }

  // Disable button while processing
  $btn.prop("disabled", true).html("⏳ Processing...");
  $("#quickCancelAlert").hide();

  $.ajax({
    url: "api/cancel.php",
    method: "POST",
    data: {
      event_id: eventId,
      admission_no: admissionNo
    },
    dataType: "json",
    success: function(res) {
      if (res.success) {
        // Update local event seat count
        let updatedData = res.data;
        let targetEvent = currentEvents.find(function(e) {
          return e.id === updatedData.event_id;
        });

        if (targetEvent) {
          targetEvent.registered_count = updatedData.registered_count;
          targetEvent.remaining_seats = updatedData.remaining_seats;
          targetEvent.percent_filled = updatedData.percent_filled;
          targetEvent.is_full = updatedData.is_full;

          // Dynamically update card in DOM without page reload
          updateEventCardDOM(targetEvent);
        }

        closeAllModals();
        showToast("success", "Registration Cancelled", res.message);
      } else {
        $("#quickCancelAlert").addClass("error").text(res.message).show();
        showToast("error", "Cancellation Failed", res.message);
      }
    },
    error: function(xhr) {
      let errorMsg = "Failed to cancel registration.";
      if (xhr.responseJSON && xhr.responseJSON.message) {
        errorMsg = xhr.responseJSON.message;
      }
      $("#quickCancelAlert").addClass("error").text(errorMsg).show();
      showToast("error", "Cancellation Error", errorMsg);
    },
    complete: function() {
      // Re-enable button
      $btn.prop("disabled", false).html(originalText);
    }
  });
}

// Fetch active registrations for a student
function fetchMyRegistrations(admissionNo) {
  let $container = $("#myRegistrationsList");
  $container.html('<div style="text-align:center; padding: 2rem;">⏳ Fetching registrations...</div>');

  $.ajax({
    url: "api/my_registrations.php",
    method: "GET",
    data: { admission_no: admissionNo },
    dataType: "json",
    success: function(res) {
      if (res.success) {
        renderMyRegistrations(res.registrations, admissionNo);
      } else {
        $container.html(
          '<div class="empty-state">' +
            '<div class="empty-icon">⚠️</div>' +
            '<p>' + escapeHtml(res.message) + '</p>' +
          '</div>'
        );
      }
    },
    error: function(xhr) {
      let msg = "Could not load registrations.";
      if (xhr.responseJSON && xhr.responseJSON.message) {
        msg = xhr.responseJSON.message;
      }
      $container.html(
        '<div class="empty-state">' +
          '<div class="empty-icon">⚠️</div>' +
          '<p>' + escapeHtml(msg) + '</p>' +
        '</div>'
      );
    }
  });
}

// Render student's active registrations in the modal drawer
function renderMyRegistrations(registrations, admissionNo) {
  let $container = $("#myRegistrationsList");
  $container.empty();

  if (registrations.length === 0) {
    $container.html(
      '<div class="empty-state">' +
        '<div class="empty-icon">📋</div>' +
        '<h4>No Registrations Found</h4>' +
        '<p>No active event registrations found for Admission No <strong>' + escapeHtml(admissionNo) + '</strong>.</p>' +
      '</div>'
    );
    return;
  }

  registrations.forEach(function(reg) {
    let cardHtml = 
      '<div class="reg-card" id="reg-item-' + reg.registration_id + '">' +
        '<div class="reg-info">' +
          '<h4>' + escapeHtml(reg.event_title) + '</h4>' +
          '<div class="reg-details">' +
            '<span>📅 ' + reg.event_date + ' • ' + reg.event_time + '</span>' +
            '<span>📍 ' + escapeHtml(reg.venue) + '</span>' +
          '</div>' +
        '</div>' +
        '<div>' +
          '<button type="button" class="btn-cancel-reg" onclick="cancelRegistrationFromDrawer(' + reg.event_id + ', \'' + escapeHtml(reg.admission_no) + '\', ' + reg.registration_id + ')">' +
            'Cancel Registration' +
          '</button>' +
        '</div>' +
      '</div>';

    $container.append(cardHtml);
  });
}

// Cancel registration directly from My Registrations drawer
function cancelRegistrationFromDrawer(eventId, admissionNo, regId) {
  if (!confirm("Are you sure you want to cancel this event registration? Your reserved seat will be immediately released.")) {
    return;
  }

  $.ajax({
    url: "api/cancel.php",
    method: "POST",
    data: {
      event_id: eventId,
      admission_no: admissionNo,
      registration_id: regId
    },
    dataType: "json",
    success: function(res) {
      if (res.success) {
        $("#reg-item-" + regId).slideUp(200, function() {
          $(this).remove();
          if ($("#myRegistrationsList .reg-card").length === 0) {
            renderMyRegistrations([], admissionNo);
          }
        });

        // Dynamically update main event card without reloading
        let updatedData = res.data;
        let targetEvent = currentEvents.find(function(e) {
          return e.id === updatedData.event_id;
        });

        if (targetEvent) {
          targetEvent.registered_count = updatedData.registered_count;
          targetEvent.remaining_seats = updatedData.remaining_seats;
          targetEvent.percent_filled = updatedData.percent_filled;
          targetEvent.is_full = updatedData.is_full;

          updateEventCardDOM(targetEvent);
        }

        showToast("success", "Registration Cancelled", res.message);
      } else {
        showToast("error", "Cancellation Failed", res.message);
      }
    },
    error: function(xhr) {
      let msg = "Failed to cancel registration.";
      if (xhr.responseJSON && xhr.responseJSON.message) {
        msg = xhr.responseJSON.message;
      }
      showToast("error", "Error", msg);
    }
  });
}

// Modal open helper
function openModal(modalSelector) {
  $(modalSelector).addClass("open");
  $("body").css("overflow", "hidden");
}

// Modal close helper
function closeAllModals() {
  $(".modal-backdrop").removeClass("open");
  $("body").css("overflow", "auto");
}

// Toast notification helper
function showToast(type, title, message) {
  let icons = {
    success: "✅",
    error: "❌",
    warning: "⚠️",
    info: "ℹ️"
  };

  let toastId = "toast-" + Date.now();
  let toastHtml = 
    '<div class="toast ' + type + '" id="' + toastId + '">' +
      '<div class="toast-icon">' + (icons[type] || "ℹ️") + '</div>' +
      '<div class="toast-content">' +
        '<div class="toast-title">' + escapeHtml(title) + '</div>' +
        '<div class="toast-message">' + escapeHtml(message) + '</div>' +
      '</div>' +
    '</div>';

  $("#toastContainer").append(toastHtml);

  // Auto-remove after 4.5 seconds
  setTimeout(function() {
    $("#" + toastId).fadeOut(300, function() {
      $(this).remove();
    });
  }, 4500);
}

// Utility function to escape HTML special characters for XSS prevention
function escapeHtml(str) {
  if (!str) {
    return "";
  }
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}
