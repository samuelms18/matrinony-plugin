jQuery(document).ready(function ($) {
  // Initialize charts on dashboard

  // Gender distribution chart
  var genderCtx = document.getElementById("genderChart").getContext("2d");
  var genderChart = new Chart(genderCtx, {
    type: "pie",
    data: {
      labels: ["Male", "Female"],
      datasets: [
        {
          data: [0, 0], // Will be updated via AJAX
          backgroundColor: ["#36a2eb", "#ff6384"],
          hoverOffset: 4,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: "right",
        },
        title: {
          display: true,
          text: "Gender Distribution",
        },
      },
    },
  });

  // Caste distribution chart
  var casteCtx = document.getElementById("casteChart").getContext("2d");
  var casteChart = new Chart(casteCtx, {
    type: "doughnut",
    data: {
      labels: [],
      datasets: [
        {
          data: [],
          backgroundColor: [],
          hoverOffset: 4,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: "right",
        },
        title: {
          display: true,
          text: "Caste Distribution",
        },
      },
    },
  });

  // Monthly growth chart
  var growthCtx = document.getElementById("growthChart").getContext("2d");
  var growthChart = new Chart(growthCtx, {
    type: "line",
    data: {
      labels: [],
      datasets: [
        {
          label: "Profiles Added",
          data: [],
          fill: false,
          borderColor: "rgb(75, 192, 192)",
          tension: 0.1,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        title: {
          display: true,
          text: "Monthly Profile Growth",
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            precision: 0,
          },
        },
      },
    },
  });

  // Load chart data via AJAX
  function loadChartData(chart, chartType) {
    $.ajax({
      url: matrimonyAdmin.ajaxurl,
      type: "POST",
      data: {
        action: "matrimony_get_chart_data",
        nonce: matrimonyAdmin.nonce,
        chart_type: chartType,
      },
      success: function (response) {
        if (response.success) {
          chart.data.labels = response.data.data.labels;
          chart.data.datasets = response.data.data.datasets;
          chart.update();
        }
      },
    });
  }

  // Load data for all charts
  loadChartData(genderChart, "gender_dist");
  loadChartData(casteChart, "caste_dist");
  loadChartData(growthChart, "monthly_growth");

  // Tooltip plugin for charts
  Chart.register({
    id: "customTooltip",
    beforeDraw: function (chart) {
      if (chart.tooltip._active && chart.tooltip._active.length) {
        var ctx = chart.ctx;
        var activePoint = chart.tooltip._active[0];
        var x = activePoint.element.x;
        var y = activePoint.element.y;
        var radius = activePoint.element.outerRadius + 5;

        ctx.save();
        ctx.beginPath();
        ctx.arc(x, y, radius, 0, 2 * Math.PI);
        ctx.fillStyle = "rgba(255, 255, 255, 0.8)";
        ctx.fill();
        ctx.restore();
      }
    },
  });
});
