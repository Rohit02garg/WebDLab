// Campus Event Registration System - Client-Side Logic (jQuery & AJAX)

var currentEvents = [];

$(document).ready(function() {
  loadEvents();

  // Navigation: My Registrations
  $("#navMyRegistrations").on("click", function(e) {
    e.preventDefault();
    var adm = (window.sessionStudent && window.sessionStudent.admissionNo) ? window.sessionStudent.admissionNo : "";
    $("#lookupAdmissionNo").val(adm);
    openModal("#myRegistrationsModal");
    if (adm) {
      fetchMyRegistrations(adm);
    }
  });

  // Lookup button inside My Registrations modal
  $("#btnLookupReg").on("click", function() {
    var adm = $("#lookupAdmissionNo").val().trim();
    if (adm) {
      fetchMyRegistrations(adm);
    }
  });

  // Event Registration Submission
  $("#registrationForm").on("submit", function(e) {
    e.preventDefault();
    var $btn = $("#btnSubmitReg");
    $btn.prop("disabled", true).text("Registering...");
    $("#regAlert").hide();

    $.ajax({
      url: "api/register.php",
      method: "POST",
      data: $(this).serialize(),
      dataType: "json",
      success: function(res) {
        if (res.success) {
          closeAllModals();
          alert(res.message || "Registration successful!");
          loadEvents();
        } else {
          $("#regAlert").text(res.message).show();
        }
      },
      error: function(xhr) {
        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Registration failed.";
        $("#regAlert").text(msg).show();
      },
      complete: function() {
        $btn.prop("disabled", false).text("Confirm Registration");
      }
    });
  });

  // Quick Cancel Seat Submission
  $("#quickCancelForm").on("submit", function(e) {
    e.preventDefault();
    var eventId = $("#quickCancelEventId").val();
    var admNo = $("#quickCancelAdmissionNo").val().trim();

    if (!admNo) {
      $("#quickCancelAlert").text("Please enter your admission number.").show();
      return;
    }

    var $btn = $("#btnSubmitQuickCancel");
    $btn.prop("disabled", true).text("Cancelling...");

    $.ajax({
      url: "api/cancel.php",
      method: "POST",
      data: { event_id: eventId, admission_no: admNo },
      dataType: "json",
      success: function(res) {
        if (res.success) {
          closeAllModals();
          alert(res.message || "Seat cancelled successfully.");
          loadEvents();
        } else {
          $("#quickCancelAlert").text(res.message).show();
        }
      },
      error: function(xhr) {
        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Cancellation failed.";
        $("#quickCancelAlert").text(msg).show();
      },
      complete: function() {
        $btn.prop("disabled", false).text("Confirm Cancellation");
      }
    });
  });

  // Modal Close Handlers
  $(".modal-close-btn, .btn-modal-close").on("click", function() {
    closeAllModals();
  });

  $(".modal-backdrop").on("click", function(e) {
    if ($(e.target).hasClass("modal-backdrop")) {
      closeAllModals();
    }
  });

  $(document).on("keydown", function(e) {
    if (e.key === "Escape") {
      closeAllModals();
    }
  });
});

// Load events from server via GET AJAX
function loadEvents() {
  $.get("api/get_events.php", function(res) {
    if (res.success) {
      currentEvents = res.events;
      renderEvents(res.events);
    }
  }, "json");
}

// Render event cards list
function renderEvents(events) {
  var $grid = $("#eventsGrid");
  $grid.empty();
  $("#eventsCountPill").text(events.length + " Available");

  if (!events || events.length === 0) {
    $grid.html('<p style="color: #666;">No campus events available.</p>');
    return;
  }

  events.forEach(function(ev) {
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
  var ev = currentEvents.find(function(e) { return e.id === eventId; });
  if (!ev) return;

  $("#regEventId").val(ev.id);
  $("#modalEventTitle").text(ev.title);
  $("#modalEventMeta").text(ev.date_formatted + " • " + ev.time_formatted + " | " + ev.venue);
  $("#modalEventRemaining").text(ev.remaining_seats + " seats remaining of " + ev.capacity);
  $("#regAlert").hide();

  openModal("#registrationModal");
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

// Fetch student's registered events
function fetchMyRegistrations(adm) {
  var $list = $("#myRegistrationsList");
  $list.html('<p style="text-align: center; color: #666;">Loading registrations...</p>');

  $.get("api/my_registrations.php", { admission_no: adm }, function(res) {
    $list.empty();
    if (!res.success || !res.registrations || res.registrations.length === 0) {
      $list.html('<p style="text-align: center; color: #666;">No active registrations found for ' + escapeHtml(adm) + '.</p>');
      return;
    }

    res.registrations.forEach(function(r) {
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
  }, "json").fail(function() {
    $list.html('<p style="color: red; text-align: center;">Could not load registrations.</p>');
  });
}

// Cancel registration from My Registrations list
function cancelRegistration(eventId, admNo) {
  if (!confirm("Are you sure you want to cancel this registration?")) return;

  $.post("api/cancel.php", { event_id: eventId, admission_no: admNo }, function(res) {
    if (res.success) {
      alert("Registration cancelled.");
      fetchMyRegistrations(admNo);
      loadEvents();
    } else {
      alert(res.message || "Failed to cancel.");
    }
  }, "json");
}

function openModal(id) {
  $(id).addClass("open");
  $("body").css("overflow", "hidden");
}

function closeAllModals() {
  $(".modal-backdrop").removeClass("open");
  $("body").css("overflow", "auto");
}

function escapeHtml(str) {
  if (!str) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}
