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

  /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   * Q19 EXAM TASK: Sort event cards (by seats / date / capacity)
   * HOW TO USE: Add this handler inside $(document).ready() + add <select> in index.php
   * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   * $("#sortEventsSelect").on("change", function () {
   *   var sortMode = $(this).val();
   *   var sorted = currentEvents.slice();
   *   if (sortMode === "seats_asc") {
   *     sorted.sort(function (a, b) { return a.remaining_seats - b.remaining_seats; });
   *   } else if (sortMode === "cap_desc") {
   *     sorted.sort(function (a, b) { return b.capacity - a.capacity; });
   *   } else {
   *     sorted.sort(function (a, b) { return a.id - b.id; });
   *   }
   *   renderEvents(sorted);
   * });
   *
   * Q20 EXAM TASK: Live search/filter event cards by title or venue
   * HOW TO USE: Add this handler inside $(document).ready() + add <input id="eventSearchInput"> in index.php
   * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   * $("#eventSearchInput").on("keyup", function () {
   *   var searchVal = $(this).val().toLowerCase().trim();
   *   $(".event-card").each(function () {
   *     var cardText = $(this).text().toLowerCase();
   *     $(this).toggle(cardText.indexOf(searchVal) > -1);
   *   });
   * });
   *
   * Q27 EXAM TASK: Category filter pill buttons (All/Technical/Cultural/Sports)
   * HOW TO USE: Add this handler inside $(document).ready() + add filter buttons in index.php
   * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   * $(document).on("click", ".btn-filter", function () {
   *   $(".btn-filter").removeClass("active");
   *   $(this).addClass("active");
   *   var cat = $(this).data("cat");
   *   if (cat === "all") {
   *     renderEvents(currentEvents);
   *   } else {
   *     var filtered = currentEvents.filter(function (e) { return e.category === cat; });
   *     renderEvents(filtered);
   *   }
   * });
   */
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

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * CHEAT SHEET Q46: Fix XSS in event cards via jQuery event delegation
 * HOW TO USE: Replace inline onclick with data-id attributes:
 * <button type="button" class="btn-register btn-open-reg" data-id="' + ev.id + '">Register Now</button>
 * And handle in $(document).ready():
 * $(document).on("click", ".btn-open-reg", function() { openRegistrationModal($(this).data("id")); });
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 */
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
    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Q28 EXAM TASK: Change 'Register Now' button text
     * HOW TO USE: In the btnHtml line below, replace 'Register Now' with your text (e.g. 'Book My Seat')
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Q30 EXAM TASK: Show 'Admin View Only' for admin users
     * HOW TO USE: Replace the var btnHtml block with:
     * var isAdmin = window.sessionStudent && window.sessionStudent.isAdmin;
     * var btnHtml = isFull ? '<button type="button" class="btn-register" disabled>Event Full</button>'
     *   : isAdmin ? '<button type="button" class="btn-secondary" disabled>Admin View Only</button>'
     *   : '<button type="button" class="btn-register" onclick="openRegistrationModal(' + ev.id + ')">Register Now</button>';
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     */
    var btnHtml = isFull
      ? '<button type="button" class="btn-register" disabled>Event Full</button>'
      : '<button type="button" class="btn-register" onclick="openRegistrationModal(' + ev.id + ')">Register Now</button>';

    var cardHtml =
      '<div class="event-card ' + (isFull ? 'is-full' : '') + '">' +
      '<div class="card-body">' +
      /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       * Q21 EXAM TASK: Show 'FILLING FAST!' orange badge when seats <= 2
       * HOW TO USE: Replace <h3 class="event-title"> line below with:
       * '<h3 class="event-title">' + escapeHtml(ev.title) +
       * (ev.remaining_seats > 0 && ev.remaining_seats <= 2 ? '<span style="background:orange;color:white;padding:2px 6px;font-size:11px;font-weight:bold;border-radius:3px;margin-left:8px;">FILLING FAST! Only ' + ev.remaining_seats + ' Left!</span>' : '') +
       * '</h3>' +
       * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       */
      '<h3 class="event-title">' + escapeHtml(ev.title) + '</h3>' +
      '<p class="event-desc">' + escapeHtml(ev.description) + '</p>' +
      '<div class="event-meta-list">' +
      '<div class="meta-item"><span><strong>Date:</strong> ' + ev.date_formatted + ' • ' + ev.time_formatted + '</span></div>' +
      '<div class="meta-item"><span><strong>Venue:</strong> ' + escapeHtml(ev.venue) + '</span></div>' +
      '</div>' +
      /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       * Q22 EXAM TASK: Add countdown timer element inside meta list
       * HOW TO USE: Add this line inside event-meta-list (also add setInterval at bottom of file):
       * '<div class="meta-item"><span class="countdown-timer" data-target="' + ev.date_formatted + '"><strong>Countdown:</strong> Calculating...</span></div>' +
       * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       */
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
  /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   * Q23 EXAM TASK: Add confirmation popup before booking
   * HOW TO USE: Add these lines right here before $btn line below:
   * var evName = $("#modalEventTitle").text();
   * if (!confirm("Reserve a seat for '" + evName + "'? Your seat will be allocated immediately.")) { return; }
   * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   */
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

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Q22 EXAM TASK: Countdown timer live ticking loop (ADD at bottom of file)
 * HOW TO USE: Uncomment this setInterval loop:
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * setInterval(function () {
 *   $(".countdown-timer").each(function () {
 *     var target = new Date($(this).data("target")).getTime();
 *     var now = new Date().getTime();
 *     var diff = target - now;
 *     if (diff <= 0) { $(this).html("<strong>Event Started!</strong>"); return; }
 *     var d = Math.floor(diff / (1000 * 60 * 60 * 24));
 *     var h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
 *     var m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
 *     $(this).html("<strong>Starts in:</strong> " + d + "d " + h + "h " + m + "m");
 *   });
 * }, 1000);
 */
