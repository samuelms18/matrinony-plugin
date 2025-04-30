jQuery(document).ready(function ($) {
  // Common functionality for all admin pages

  // Confirm before deleting
  $(document).on("click", ".button-delete", function (e) {
    if (!confirm(matrimonyAdmin.i18n.confirmDelete)) {
      e.preventDefault();
    }
  });

  // Confirm before restoring
  $(document).on("click", ".button-restore", function (e) {
    if (!confirm(matrimonyAdmin.i18n.confirmRestore)) {
      e.preventDefault();
    }
  });

  // Confirm before permanent delete
  $(document).on("click", ".button-permanent-delete", function (e) {
    if (!confirm(matrimonyAdmin.i18n.confirmPermanentDelete)) {
      e.preventDefault();
    }
  });

  // Toggle all checkboxes
  $(document).on("click", ".matrimony-toggle-all", function () {
    var isChecked = $(this).prop("checked");
    $(this)
      .closest("table")
      .find(".matrimony-checkbox")
      .prop("checked", isChecked);
  });

  // Handle bulk actions
  $(document).on("click", ".matrimony-bulk-action", function () {
    var action = $(this).closest(".bulkactions").find("select").val();
    if (!action || action === "-1") return;

    var checked = $(this).closest("form").find(".matrimony-checkbox:checked");
    if (checked.length === 0) {
      alert("Please select at least one item");
      return;
    }

    // Handle different bulk actions
    if (action === "delete") {
      if (!confirm(matrimonyAdmin.i18n.confirmDelete)) return;
    } else if (action === "restore") {
      if (!confirm(matrimonyAdmin.i18n.confirmRestore)) return;
    } else if (action === "permanent_delete") {
      if (!confirm(matrimonyAdmin.i18n.confirmPermanentDelete)) return;
    }

    // Submit the form
    $(this).closest("form").submit();
  });
});
