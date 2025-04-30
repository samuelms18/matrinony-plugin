jQuery(document).ready(function ($) {
  // Debounce function to limit how often a function is called
  function debounce(func, wait, immediate) {
    var timeout;
    return function () {
      var context = this,
        args = arguments;
      var later = function () {
        timeout = null;
        if (!immediate) func.apply(context, args);
      };
      var callNow = immediate && !timeout;
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
      if (callNow) func.apply(context, args);
    };
  }

  // Live search with debounce
  $("#search_first_name, #search_last_name").on(
    "input",
    debounce(function () {
      performSearch(1);
    }, 500)
  );

  // Other search filters
  $(
    "#search_age_min, #search_age_max, #search_gender, #search_marital_status, #search_caste, #search_sub_caste, #search_per_page"
  ).change(function () {
    performSearch(1);
  });

  // Perform search via AJAX
  function performSearch(page) {
    var formData = {
      action: "matrimony_search_profiles",
      nonce: matrimonyAdmin.nonce,
      first_name: $("#search_first_name").val(),
      last_name: $("#search_last_name").val(),
      age_min: $("#search_age_min").val(),
      age_max: $("#search_age_max").val(),
      gender: $("#search_gender").val(),
      marital_status: $("#search_marital_status").val(),
      caste: $("#search_caste").val(),
      sub_caste: $("#search_sub_caste").val(),
      per_page: $("#search_per_page").val(),
      page: page,
    };

    $.ajax({
      url: matrimonyAdmin.ajaxurl,
      type: "POST",
      data: formData,
      beforeSend: function () {
        $("#search-results-table tbody").html(
          '<tr><td colspan="8" class="loading">Loading...</td></tr>'
        );
      },
      success: function (response) {
        if (response.success) {
          updateResultsTable(response.data);
          updatePagination(response.data.pagination, page);
        }
      },
    });
  }

  // Update results table with data
  function updateResultsTable(data) {
    var tbody = $("#search-results-table tbody");
    tbody.empty();

    if (data.profiles.length === 0) {
      tbody.append(
        '<tr><td colspan="8" class="no-results">No profiles found</td></tr>'
      );
      return;
    }

    $.each(data.profiles, function (i, profile) {
      var row = $("<tr>");

      // Checkbox
      row.append(
        $('<td class="check-column">').html(
          '<input type="checkbox" class="profile-checkbox" value="' +
            profile.profile_id +
            '">'
        )
      );

      // Profile ID
      row.append($("<td>").text(profile.profile_id));

      // Name
      row.append($("<td>").text(profile.first_name + " " + profile.last_name));

      // Age
      row.append($("<td>").text(profile.age));

      // Gender
      row.append($("<td>").text(profile.gender));

      // Caste
      row.append($("<td>").text(profile.caste));

      // Sub Caste
      row.append($("<td>").text(profile.sub_caste || "—"));

      // Photo
      var photoCell = $("<td>");
      if (profile.photo_url) {
        photoCell.html('<img src="' + profile.photo_url + '" width="50">');
      } else {
        photoCell.html(
          '<span class="dashicons dashicons-format-image"></span>'
        );
      }
      row.append(photoCell);

      tbody.append(row);
    });
  }

  // Update pagination controls
  function updatePagination(pagination, currentPage) {
    $(".displaying-num").text(pagination.total + " items");
    $(".current-page").val(currentPage);
    $(".total-pages").text(pagination.total_pages);

    // Toggle pagination buttons
    $(".first-page, .prev-page").toggleClass("disabled", currentPage === 1);
    $(".next-page, .last-page").toggleClass(
      "disabled",
      currentPage === pagination.total_pages
    );
  }

  // Pagination handlers
  $(".first-page").click(function (e) {
    e.preventDefault();
    performSearch(1);
  });

  $(".prev-page").click(function (e) {
    e.preventDefault();
    var current = parseInt($(".current-page").val());
    if (current > 1) performSearch(current - 1);
  });

  $(".next-page").click(function (e) {
    e.preventDefault();
    var current = parseInt($(".current-page").val());
    var total = parseInt($(".total-pages").text());
    if (current < total) performSearch(current + 1);
  });

  $(".last-page").click(function (e) {
    e.preventDefault();
    var total = parseInt($(".total-pages").text());
    performSearch(total);
  });

  $(".current-page").keypress(function (e) {
    if (e.which === 13) {
      // Enter key
      e.preventDefault();
      var page = parseInt($(this).val());
      var total = parseInt($(".total-pages").text());
      if (page >= 1 && page <= total) {
        performSearch(page);
      }
    }
  });

  // Bulk actions
  $("#select-all").click(function () {
    $(".profile-checkbox").prop("checked", $(this).prop("checked"));
  });

  $("#bulk-action-apply").click(function () {
    var action = $("#bulk-action").val();
    if (!action) return;

    var selected = [];
    $(".profile-checkbox:checked").each(function () {
      selected.push($(this).val());
    });

    if (selected.length === 0) {
      alert("Please select at least one profile");
      return;
    }

    if (action === "export") {
      exportProfiles(selected);
    } else if (action === "delete") {
      if (
        confirm(
          "Are you sure you want to move the selected profiles to recycle bin?"
        )
      ) {
        bulkAction("delete", selected);
      }
    }
  });

  // Export profiles
  function exportProfiles(profileIds) {
    $.ajax({
      url: matrimonyAdmin.ajaxurl,
      type: "POST",
      data: {
        action: "matrimony_export_profiles",
        nonce: matrimonyAdmin.nonce,
        profile_ids: profileIds,
      },
      xhrFields: {
        responseType: "blob",
      },
      success: function (response) {
        var blob = new Blob([response]);
        var link = document.createElement("a");
        link.href = window.URL.createObjectURL(blob);
        link.download =
          "matrimony-profiles-" +
          new Date().toISOString().slice(0, 10) +
          ".csv";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      },
    });
  }

  // Bulk action
  function bulkAction(action, profileIds) {
    $.ajax({
      url: matrimonyAdmin.ajaxurl,
      type: "POST",
      data: {
        action: "matrimony_bulk_action",
        nonce: matrimonyAdmin.nonce,
        bulk_action: action,
        profile_ids: profileIds,
      },
      success: function (response) {
        if (response.success) {
          alert(response.data.message);
          performSearch(parseInt($(".current-page").val()));
        }
      },
    });
  }

  // Handle caste/sub-caste relationship
  $("#search_caste").change(function () {
    var caste = $(this).val();
    var subCasteSelect = $("#search_sub_caste");

    subCasteSelect.empty().append('<option value="">All</option>');

    if (caste === "Iyer") {
      subCasteSelect.prop("disabled", false);
      subCasteSelect.append('<option value="Vadamal">Vadamal</option>');
      subCasteSelect.append(
        '<option value="Brahacharanam">Brahacharanam</option>'
      );
    } else if (caste === "Iyengar") {
      subCasteSelect.prop("disabled", false);
      subCasteSelect.append('<option value="Thenkalai">Thenkalai</option>');
      subCasteSelect.append('<option value="Vadakalai">Vadakalai</option>');
    } else {
      subCasteSelect.prop("disabled", true);
    }
  });
});
