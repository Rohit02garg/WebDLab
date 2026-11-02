// NCSM301 Web Development Laboratory
// Lab 8: Asynchronous Data Exchange and jQuery
// Student & Event Registration Client Logic

var currentEvents = [];

// Initialize event handlers once DOM is ready (Lab 8 Section 6.1)
$(document).ready(function () {
  loadEvents();

  // Navigation: My Registrations handler (Lab 8 Section 7.3)
  $("#navMyRegistrations").on("click", function (e) {
    e.preventDefault();
    var adm = (window.sessionStudent && window.sessionStudent.admissionNo) ? window.sessionStudent.admissionNo : "";
    $("#lookupAdmissionNo").val(adm);
    openModal("#myRegistrationsModal");
    if (adm) {
      fetchMyRegistrations(adm);
    }
  });

  // Lookup button inside My Registrations modal
  $("#btnLookupReg").on("click", function () {
    var adm = $("#lookupAdmissionNo").val().trim();
    if (adm) {
      fetchMyRegistrations(adm);
    }
  });

  // Form submission: Event Registration with fetch POST (Lab 8 Section 5)
  $("#registrationForm").on("submit", function (e) {
    e.preventDefault();
    submitRegistration();
  });

  // Form submission: Cancel Seat with fetch POST (Lab 8 Section 5)
  $("#quickCancelForm").on("submit", function (e) {
    e.preventDefault();
    submitQuickCancel();
  });

  // Modal close handlers (Lab 8 Section 7.3 & 7.4)
  $(".modal-close-btn, .btn-modal-close").on("click", function () {
    closeAllModals();
  });

  $(".modal-backdrop").on("click", function (e) {
    if ($(e.target).hasClass("modal-backdrop")) {
      closeAllModals();
    }
  });

  $(document).on("keydown", function (e) {
    if (e.key === "Escape") {
      closeAllModals();
    }
  });
});

// Lab 8 Section 3: Receiving Data with fetch (GET)
function loadEvents() {
  fetch("api/get_events.php")
    .then(function (response) {
      if (!response.ok) {
        throw new Error("HTTP " + response.status);
      }
      return response.json();
    })
    .then(function (data) {
      if (data.success) {
        currentEvents = data.events;
        renderEvents(data.events);
      }
    })
    .catch(function (error) {
      console.log("Could not load events: " + error.message);
    });
}

// Render event list using jQuery DOM methods
function renderEvents(events) {
  var $grid = $("#eventsGrid");
  $grid.empty();
  $("#eventsCountPill").text(events.length + " Available");

  if (!events || events.length === 0) {
    $grid.html('<p style="color: #666;">No campus events available.</p>');
    return;
  }

  events.forEach(function (ev) {
    var isFull = ev.remaining_seats <= 0;
    var btnHtml = isFull
      ? '<button type="button" class="btn-register" disabled>Event Full</button>'
      : '<button type="button" class="btn-register" onclick="openRegistrationModal(' + ev.id + ')">Register Now</button>';

    var cardHtml =
      '<div class="event-card ' + (isFull ? 'is-full' : '') + '">' +
      '<div class="card-body">' +
      '<h3 class="event-title">' + escapeHtml(ev.title) + '</h3>' +
      '<p class="event-desc">' + escapeHtml(ev.description) + '</p>' +
      '<div class="event-meta-list">' +
      '<div class="meta-item"><span><strong>Date:</strong> ' + ev.date_formatted + ' • ' + ev.time_formatted + '</span></div>' +
      '<div class="meta-item"><span><strong>Venue:</strong> ' + escapeHtml(ev.venue) + '</span></div>' +
      '</div>' +
      '<div class="seat-capacity-block">' +
      '<div class="seat-stats-row">' +
      '<div class="stat-seat-col"><div class="val">' + ev.capacity + '</div><div class="lbl">Total Capacity</div></div>' +
      '<div class="stat-seat-col"><div class="val">' + ev.registered_count + '</div><div class="lbl">Registered</div></div>' +
      '<div class="stat-seat-col highlight-remaining ' + (isFull ? 'full' : '') + '"><div class="val">' + ev.remaining_seats + '</div><div class="lbl">Remaining Seats</div></div>' +
      '</div>' +
      '<div class="progress-container">' +
      '<div class="progress-bar-fill ' + (isFull ? 'full' : '') + '" style="width: ' + ev.percent_filled + '%;"></div>' +
      '</div>' +
      '</div>' +
      '</div>' +
      '<div class="card-footer">' +
      btnHtml +
      '<button type="button" class="btn-cancel-seat" onclick="openQuickCancelModal(' + ev.id + ', \'' + escapeHtml(ev.title) + '\')">Cancel Seat</button>' +
      '</div>' +
      '</div>';

    $grid.append(cardHtml);
  });
}

// Open Registration Modal
function openRegistrationModal(eventId) {
  var ev = currentEvents.find(function (e) { return e.id === eventId; });
  if (!ev) return;

  $("#regEventId").val(ev.id);
  $("#modalEventTitle").text(ev.title);
  $("#modalEventMeta").text(ev.date_formatted + " • " + ev.time_formatted + " | " + ev.venue);
  $("#modalEventRemaining").text(ev.remaining_seats + " seats remaining of " + ev.capacity);
  $("#regAlert").hide();

  openModal("#registrationModal");
}

// Lab 8 Section 5: Sending Data with fetch (POST JSON)
function submitRegistration() {
  var $btn = $("#btnSubmitReg");
  $btn.prop("disabled", true).text("Registering...");
  $("#regAlert").hide();

  var payload = {
    event_id: parseInt($("#regEventId").val(), 10),
    student_name: $("#regStudentName").val(),
    admission_no: $("#regAdmissionNo").val(),
    email: $("#regEmail").val(),
    phone: $("#regPhone").val(),
    department: $("#regDepartment").val()
  };

  fetch("api/register.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify(payload)
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      if (data.success) {
        closeAllModals();
        alert(data.message || "Registration accepted!");
        loadEvents();
      } else {
        $("#regAlert").text(data.message || "Registration could not be completed.").show();
      }
    })
    .catch(function (error) {
      $("#regAlert").text("Network or server error. Try again.").show();
    })
    .finally(function () {
      $btn.prop("disabled", false).text("Confirm Registration");
    });
}

// Open Quick Cancel Modal
function openQuickCancelModal(eventId, title) {
  $("#quickCancelEventId").val(eventId);
  $("#quickCancelEventTitle").text(title);
  $("#quickCancelAlert").hide();

  if (window.sessionStudent && window.sessionStudent.admissionNo) {
    $("#quickCancelAdmissionNo").val(window.sessionStudent.admissionNo);
  }

  openModal("#quickCancelModal");
}

// Submit Cancellation with fetch POST (Lab 8 Section 5)
function submitQuickCancel() {
  var eventId = parseInt($("#quickCancelEventId").val(), 10);
  var admNo = $("#quickCancelAdmissionNo").val().trim();

  if (!admNo) {
    $("#quickCancelAlert").text("Please enter your Admission Number.").show();
    return;
  }

  var $btn = $("#btnSubmitQuickCancel");
  $btn.prop("disabled", true).text("Cancelling...");

  var payload = {
    event_id: eventId,
    admission_no: admNo
  };

  fetch("api/cancel.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify(payload)
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      if (data.success) {
        closeAllModals();
        alert(data.message || "Registration cancelled.");
        loadEvents();
      } else {
        $("#quickCancelAlert").text(data.message || "Cancellation could not be completed.").show();
      }
    })
    .catch(function (error) {
      $("#quickCancelAlert").text("Network or server error. Try again.").show();
    })
    .finally(function () {
      $btn.prop("disabled", false).text("Confirm Cancellation");
    });
}

// Fetch student's registered events using fetch GET
function fetchMyRegistrations(adm) {
  var $list = $("#myRegistrationsList");
  $list.html('<p style="text-align: center; color: #666;">Loading registrations...</p>');

  fetch("api/my_registrations.php?admission_no=" + encodeURIComponent(adm))
    .then(function (response) {
      if (!response.ok) {
        throw new Error("HTTP " + response.status);
      }
      return response.json();
    })
    .then(function (data) {
      $list.empty();
      if (!data.success || !data.registrations || data.registrations.length === 0) {
        $list.html('<p style="text-align: center; color: #666;">No active registrations found for ' + escapeHtml(adm) + '.</p>');
        return;
      }

      data.registrations.forEach(function (r) {
        var itemHtml =
          '<div class="reg-card">' +
          '<div class="reg-info">' +
          '<h4>' + escapeHtml(r.event_title) + '</h4>' +
          '<div class="reg-details">' +
          '<span>' + r.event_date + ' • ' + r.event_time + '</span> | <span>' + escapeHtml(r.venue) + '</span>' +
          '</div>' +
          '</div>' +
          '<div>' +
          '<button type="button" class="btn-cancel-reg" onclick="cancelRegistration(' + r.event_id + ', \'' + escapeHtml(r.admission_no) + '\')">Cancel Registration</button>' +
          '</div>' +
          '</div>';
        $list.append(itemHtml);
      });
    })
    .catch(function (error) {
      $list.html('<p style="color: red; text-align: center;">Could not load registrations. Try again.</p>');
    });
}

// Cancel registration from My Registrations list
function cancelRegistration(eventId, admNo) {
  if (!confirm("Are you sure you want to cancel this registration?")) return;

  var payload = {
    event_id: eventId,
    admission_no: admNo
  };

  fetch("api/cancel.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify(payload)
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      if (data.success) {
        alert("Registration cancelled.");
        fetchMyRegistrations(admNo);
        loadEvents();
      } else {
        alert(data.message || "Failed to cancel.");
      }
    })
    .catch(function (error) {
      alert("Network error while cancelling.");
    });
}

// Showing / hiding modal elements (Lab 8 Section 7.4)
function openModal(id) {
  $(id).addClass("open");
  $("body").css("overflow", "hidden");
}

function closeAllModals() {
  $(".modal-backdrop").removeClass("open");
  $("body").css("overflow", "auto");
}

// Escape HTML utility for safe rendering
function escapeHtml(str) {
  if (!str) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}
