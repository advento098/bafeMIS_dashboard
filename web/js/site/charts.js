document.addEventListener("DOMContentLoaded", function () {
  // const dropOptions = window.data.dropOptions;

  // For dropdownbutton ---------------------------------------------------------------------------------
  $("#dropdownSearchInput").on("input", function () {
    const filter = $(this).val().toLowerCase();
    $("#dropdownSearchMenu .dropdown-item").each(function () {
      const text = $(this).text().toLowerCase();
      $(this).parent().toggle(text.includes(filter));
    });
  });

  // Allow input editing when dropdown opens
  $("#dropdownSearchButton").on("click", function () {
    $("#dropdownSearchInput").prop("readonly", false).focus().val("");
    $("#dropdownSearchMenu").val("");
    $(this).toggleClass("active");
  });

  // Update selected label and submit form on selection
  $("#dropdownSearchMenu .dropdown-item").on("click", function (e) {
    e.preventDefault();
    const label = $(this).data("label");
    const value = $(this).data("value");
    $("#dropdownSearchInput").val(label).prop("readonly", true);
    // Optional: submit the form
    const form = $("#office-form");
    $("<input>")
      .attr({
        type: "hidden",
        name: "office",
        value: value,
      })
      .appendTo(form);
    form.submit();
  });
  // Left Container ----------------------------------------------------------------------------------
  const barLabels = window.data.barLabels || [];
  const barData = window.data.barData || [];

  const barCtx = document.getElementById("barChart").getContext("2d");
  // Chart.register(ChartDataLabels);
  new Chart(barCtx, {
    type: "bar",
    data: {
      // labels: barLabels,
      labels: barLabels.map((label) => label.split(" ")),
      datasets: [
        {
          label: "",
          data: barData,
          backgroundColor: "#00712D",
          borderColor: "rgba(75, 11, 127, 0)",
          borderWidth: 1,
        },
      ],
    },
    options: {
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          bodyFont: {
            size: 12,
          },
          callbacks: {
            label: function (tooltipItem) {
              const value = barData[tooltipItem.dataIndex];
              return `${value}`;
            },
          },
        },
        datalabels: {
          anchor: "end",
          align: "top",
          color: "#000",
          font: {
            size: 9,
            weight: "bold",
          },
          formatter: function (value, context) {
            return value;
          },
        },
      },
      responsive: true,
      // maintainAspectRatio: false,
      scales: {
        x: {
          ticks: {
            font: {
              size: 8,
              weight: "bold",
            },
            maxRotation: 0, // Prevent diagonal labels
            minRotation: 0, // Prevent diagonal labels
            autoSkip: false,
          },
          title: {
            display: true,
            text: "Property Type",
          },
        },
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: "Number of Items",
          },
        },
      },
      hover: {
        mode: "nearest",
        intersect: true,
        cursor: "pointer",
      },
      onClick: function (evt, activeElements) {
        if (activeElements.length > 0) {
          const chart = this;
          const index = activeElements[0].index;
          const label = chart.data.labels[index];
          // const value = chart.data.datasets[0].data[index];
          // alert("Clicked on: " + label + " with value ₱" + value);
          const propertyType = Array.isArray(label) ? label.join(" ") : label;
          console.log("Clicked on: " + propertyType);
          console.log("is type: " + typeof propertyType);
          initializeBarModal(propertyType);
        }
      },
      onHover: function (event, activeElements) {
        const chart = this;
        chart.canvas.style.cursor = activeElements.length
          ? "pointer"
          : "default";
      },
    },
  });

  const lineLabels = window.data.lineLabels || [];
  const lineData = window.data.lineData || [];

  const lineCtx = document.getElementById("lineChart").getContext("2d");
  new Chart(lineCtx, {
    type: "line",
    data: {
      labels: lineLabels,
      datasets: [
        {
          // label: "Monthly Sales",
          data: lineData,
          borderColor: "#E77706",
          tension: 0.1,
          pointRadius: 5,
          pointBorderColor: "rgba(0, 0, 0, 0)",
          pointBackgroundColor: "rgba(0, 0, 0, 0)",
          pointHoverRadius: 15,
          fill: true,
          backgroundColor: "#F5C799",
        },
      ],
    },
    options: {
      scales: {
        y: {
          beginAtZero: true, // Start the y-axis at 0
          // min: 0, // Minimum value for the y-axis
          // max: 800, // Maximum value for the y-axis
          // ticks: {
          //   stepSize: 200,
          // },
          title: {
            display: true,
            text: "Number of Items",
          },
        },
        x: {
          beginAtZero: true, // Start the y-axis at 0
          // min: 2014,
          title: {
            display: true,
            text: "Year",
          },
        },
      },
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          callbacks: {
            // Customize the tooltip label
            label: function (tooltipItem) {
              const label = lineLabels[tooltipItem.dataIndex];
              const value = lineData[tooltipItem.dataIndex];
              return value;
            },
          },
        },
      },
      onClick: function (evt, activeElements) {
        if (activeElements.length > 0) {
          const chart = this;
          const index = activeElements[0].index;
          const label = chart.data.labels[index];
          // const value = chart.data.datasets[0].data[index];
          console.log("Clicked on: " + label);
          console.log("is type: " + typeof label);
          initializeAcquiredPerYearModal(label);
        }
      },
      onHover: function (event, activeElements) {
        const chart = this;
        chart.canvas.style.cursor = activeElements.length
          ? "pointer"
          : "default";
      },
    },
  });

  // Right Container ----------------------------------------------------------------------------------
  const ctx = document.getElementById("pieChart");

  let totalUnitValue = window.data.totalUnitValue || 0;
  const perOfficeLabels = window.data.perOfficeLabels || [];
  const perOfficeTotal = window.data.perOfficeTotal || [];
  totalUnitValue = parseFloat(totalUnitValue.replace(/[₱,]/g, ""));
  // console.log("Total unit value:", totalUnitValue);

  const percentages = new Map();

  perOfficeLabels.forEach((label, index) => {
    const value = parseFloat(perOfficeTotal[index]) || 0;

    if (isNaN(value) || totalUnitValue === 0) {
      console.error(
        "Invalid value for label:",
        label,
        "Value:",
        perOfficeTotal[index]
      );
      percentages.set(label, "0.00"); // Set percentage to 0 if value is not a number
      return; // Skip this iteration if value is not a number
    }

    // console.log("Label:", label, "Value:", value);
    const percentage = ((value / totalUnitValue) * 100).toFixed(2);
    // console.log("Percentage for", label, ":", percentage);
    percentages.set(label, percentage);
  });

  new Chart(ctx, {
    type: "doughnut",
    data: {
      labels: perOfficeLabels,
      datasets: [
        {
          label: " ",
          data: perOfficeTotal,
          backgroundColor: [
            "#00712D",
            "#0AB429",
            "#FF9008",
            "#9AFA00",
            "#FFE203",
            "#D73600",
          ],
          borderWidth: 1,
          cutout: "60%",
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false, // <-- KEY to let it fill container
      layout: {
        padding: 5,
      },
      plugins: {
        title: {
          display: true,
          text: "Total value of unit per Property Type",
          font: {
            size: 16,
            family: "Arial",
            weight: "bold",
          },
          color: "#333",
          padding: {
            bottom: 50,
          },
        },
        legend: {
          position: "right",
          title: {
            display: true,
            text: "Property Type",
          },
          labels: {
            font: {
              size: 10,
              family: "Arial",
              weight: "bold",
            },
            color: "#333",
            padding: 20,
            boxWidth: 20,
            boxHeight: 55,
            usePointStyle: true,
          },
        },
        tooltip: {
          callbacks: {
            label: function (tooltipItem) {
              const label = perOfficeLabels[tooltipItem.dataIndex];
              const value = new Intl.NumberFormat("en-PH", {
                style: "currency",
                currency: "PHP",
              }).format(perOfficeTotal[tooltipItem.dataIndex]);
              const percentage = percentages.get(label);
              return `${value} (${percentage}%)`;
            },
          },
        },
      },
    },

    // plugins: [ChartDataLabels, calloutPlugin],
    // plugins: [ChartDataLabels],
  });
  // Right Container Bar Chart --------------------------------------------------------------
  const rBar = document.getElementById("rBar").getContext("2d");
  const fBarLabels = window.data.fYearServiceableLabels || [];
  const fBarData = window.data.fYearServiceableData || [];
  new Chart(rBar, {
    type: "bar",
    data: {
      labels: fBarLabels,
      datasets: [
        {
          label: "Sales",
          data: fBarData,
          backgroundColor: "#E77706",
          borderWidth: 0,
        },
      ],
    },
    options: {
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          callbacks: {
            label: function (tooltipItem) {
              const value = fBarData[tooltipItem.dataIndex];
              return `${value}`;
            },
          },
        },
      },
      indexAxis: "y",
      scales: {
        x: {
          beginAtZero: true,
          title: {
            display: true,
            text: "Count of 5+ Year Serviceable Items",
          },
        },
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: "Property Type",
          },
        },
      },

      onClick: function (evt, activeElements) {
        if (activeElements.length > 0) {
          const chart = this;
          const index = activeElements[0].index;
          const label = chart.data.labels[index];
          initializeServiceablePerProperty(label);
          // const value = chart.data.datasets[0].data[index];
          // alert("Clicked on: " + label + " with value ₱" + value);
        }
      },
      onHover: function (event, activeElements) {
        const chart = this;
        chart.canvas.style.cursor = activeElements.length
          ? "pointer"
          : "default";
      },
    },
  });
  // Middle graphs ---------------------------------------------------------------------------------------------------------------------------------
  const propDispCountPerYear = document
    .getElementById("propDispCountPerYear")
    .getContext("2d");

  const propDispCntLabels = window.data.propDispCntLabels || [];
  const propDispCntData = window.data.propDispCntData || [];
  new Chart(propDispCountPerYear, {
    type: "bar",
    data: {
      labels: propDispCntLabels,
      datasets: [
        {
          label: "Sales",
          data: propDispCntData,
          backgroundColor: "#00712D",
          borderWidth: 0,
        },
      ],
    },
    options: {
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          callbacks: {
            label: function (tooltipItem) {
              const value = propDispCntData[tooltipItem.dataIndex];
              return `${value}`;
            },
          },
        },
      },
      scales: {
        x: {
          beginAtZero: true,
          // title: {
          //   display: true,
          //   text: "Count of Items for Disposal",
          // },
        },
        y: {
          beginAtZero: true,
          // title: {
          //   display: true,
          //   text: "Property Type",
          // },
        },
      },

      onClick: function (evt, activeElements) {
        if (activeElements.length > 0) {
          const chart = this;
          const index = activeElements[0].index;
          const label = chart.data.labels[index];
          initializePropAmntPerYearModal(label);
          // const value = chart.data.datasets[0].data[index];
          // alert("Clicked on: " + label + " with value ₱" + value);
        }
      },
      onHover: function (event, activeElements) {
        const chart = this;
        chart.canvas.style.cursor = activeElements.length
          ? "pointer"
          : "default";
      },
    },
  });

  const propDispAmntPerYear = document
    .getElementById("propDispAmountPerYear")
    .getContext("2d");
  const propDispLabels = window.data.propDispLabels || [];
  const propDispData = window.data.propDispData || [];
  // console.log("propDispData: ", window.data.test);
  new Chart(propDispAmntPerYear, {
    type: "bar",
    data: {
      labels: propDispLabels,
      datasets: [
        {
          label: "Sales",
          data: propDispData,
          backgroundColor: "#00712D",
          borderWidth: 0,
        },
      ],
    },
    options: {
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          callbacks: {
            label: function (tooltipItem) {
              const value = propDispData[tooltipItem.dataIndex];
              return `${convertToPhp(value)}`;
            },
          },
        },
      },
      scales: {
        x: {
          beginAtZero: true,
          // title: {
          //   display: true,
          //   text: "Count of Items for Disposal",
          // },
        },
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1000000,
            callback: function (value) {
              return convertToPhp(value);
            },
          },
          // title: {
          //   display: true,
          //   text: "Property Type",
          // },
        },
      },

      onClick: function (evt, activeElements) {
        if (activeElements.length > 0) {
          const chart = this;
          const index = activeElements[0].index;
          const label = chart.data.labels[index];
          initializePropAmntPerYearModal(label);
          // const value = chart.data.datasets[0].data[index];
          // alert("Clicked on: " + label + " with value ₱" + value);
        }
      },
      onHover: function (event, activeElements) {
        const chart = this;
        chart.canvas.style.cursor = activeElements.length
          ? "pointer"
          : "default";
      },
    },
  });

  const propAmountPerYear = document
    .getElementById("propAmountPerYear")
    .getContext("2d");
  const propAmountLabels = window.data.propAmountLabels || [];
  const propAmountData = window.data.propAmountData || [];
  new Chart(propAmountPerYear, {
    type: "line",
    data: {
      labels: propAmountLabels,
      datasets: [
        {
          // label: "Monthly Sales",
          data: propAmountData,
          borderColor: "#E77706",
          tension: 0.1,
          pointRadius: 5,
          pointBorderColor: "rgba(0, 0, 0, 0)",
          pointBackgroundColor: "rgba(0, 0, 0, 0)",
          pointHoverRadius: 15,
          fill: true,
          backgroundColor: "#F5C799",
        },
      ],
    },
    options: {
      scales: {
        y: {
          beginAtZero: true, // Start the y-axis at 0
          ticks: {
            stepSize: 10000000,
          },
          title: {
            display: true,
            text: "Number of Items",
          },
        },
        x: {
          beginAtZero: true, // Start the y-axis at 0
          title: {
            display: true,
            text: "Year",
          },
        },
      },
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          callbacks: {
            label: function (tooltipItem) {
              const value = propAmountData[tooltipItem.dataIndex];
              return `${convertToPhp(value)}`;
            },
          },
        },
      },
      onClick: function (evt, activeElements) {
        if (activeElements.length > 0) {
          const chart = this;
          const index = activeElements[0].index;
          const label = chart.data.labels[index];
          initializeAcquiredPerYearModal(label);
        }
      },
      onHover: function (event, activeElements) {
        const chart = this;
        chart.canvas.style.cursor = activeElements.length
          ? "pointer"
          : "default";
      },
    },
  });

  // Modal functions -------------------------------------------------------------------------------------------------------------------------------
  const selectedOffice = window.data.office || "";
  var csrfToken = $('meta[name="csrf-token"]').attr("content");

  // function initializePropCntPerYearModal(label) {
  //   $("#propDispPerYearModal").modal("show");
  //   document.querySelector(
  //     "#propDispPerYearModalLabel"
  //   ).textContent = `Items for ${label}`;

  //   const tableSelector = "#propDispPerYear_table";

  //   // Destroy existing DataTable if already initialized
  //   if ($.fn.DataTable.isDataTable(tableSelector)) {
  //     $(tableSelector).DataTable().destroy();
  //     $(tableSelector).find("tbody").empty();
  //   }

  //   $("#propDispPerYearTableContainer").hide();
  //   $("#propDispPerYearLoadingScreenCont").show();

  //   // Initialize with fresh data
  //   $(tableSelector).DataTable({
  //     processing: true,
  //     serverSide: true,
  //     ajax: {
  //       url: "/site/prop-disp-amnt-per-year-ajax",
  //       type: "POST",
  //       data: function (d) {
  //         d.office = selectedOffice; // optional filter
  //         d.year = label;
  //         d._csrf = csrfToken;
  //         return d;
  //       },
  //       dataSrc: function (json) {
  //         return json.data;
  //       },
  //     },
  //     autoWidth: false,
  //     columns: [
  //       { data: "particular", title: "Particular" },
  //       {
  //         data: "unit_value",
  //         title: "Unit Value",
  //         render: function (value) {
  //           return convertToPhp(value);
  //         },
  //       },
  //       { data: "Possessor", title: "Possessor" },
  //       { data: "current_holder", title: "Current Holder" },
  //     ],
  //     columnDefs: [
  //       { width: "500px", targets: [0] },
  //       { width: "100px", targets: [1] },
  //       { width: "100px", targets: [2] },
  //       { width: "100px", targets: [3] },
  //     ],
  //     dom: '<"top"fip>rt<"clear">',
  //     initComplete: function () {
  //       // Attach row click handler once table is ready
  //       this.api().columns.adjust().draw();
  //       $("#propDispPerYearTableContainer").show();
  //       $("#propDispPerYearLoadingScreenCont").hide();
  //     },
  //   });
  // }

  function initializePropAmntPerYearModal(label) {
    $("#propDispPerYearModal").modal("show");
    document.querySelector(
      "#propDispPerYearModalLabel"
    ).textContent = `Items for ${label}`;

    const tableSelector = "#propDispPerYear_table";

    // Destroy existing DataTable if already initialized
    if ($.fn.DataTable.isDataTable(tableSelector)) {
      $(tableSelector).DataTable().destroy();
      $(tableSelector).find("tbody").empty();
    }

    $("#propDispPerYearTableContainer").hide();
    $("#propDispPerYearLoadingScreenCont").show();

    // Initialize with fresh data
    $(tableSelector).DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "/site/prop-disp-amnt-per-year-ajax",
        type: "POST",
        data: function (d) {
          d.office = selectedOffice; // optional filter
          d.year = label;
          d._csrf = csrfToken;
          return d;
        },
        dataSrc: function (json) {
          return json.data;
        },
      },
      autoWidth: false,
      columns: [
        { data: "particular", title: "Particular" },
        {
          data: "unit_value",
          title: "Unit Value",
          render: function (value) {
            return convertToPhp(value);
          },
        },
        { data: "Possessor", title: "Possessor" },
        { data: "current_holder", title: "Current Holder" },
      ],
      columnDefs: [
        { width: "500px", targets: [0] },
        { width: "100px", targets: [1] },
        { width: "100px", targets: [2] },
        { width: "100px", targets: [3] },
      ],
      dom: '<"top"fip>rt<"clear">',
      initComplete: function () {
        // Attach row click handler once table is ready
        this.api().columns.adjust().draw();
        $("#propDispPerYearTableContainer").show();
        $("#propDispPerYearLoadingScreenCont").hide();
      },
    });
  }

  // fdModal
  function initializeForDisposalsTable() {
    const tableSelector = "#disposal_table";

    // Destroy only if already initialized
    if ($.fn.DataTable.isDataTable(tableSelector)) {
      $(tableSelector).DataTable().destroy();
      // Do NOT call .empty() to avoid losing the <thead>
      // Instead, just clear the rows if needed:
      $(tableSelector).find("tbody").empty();
    }

    // Show loading, hide table initially
    $("#tableContainer").hide();
    $("#loadingScreenCont").show();

    // Initialize DataTable
    $(tableSelector).DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "/site/for-disposals-ajax",
        data: function (d) {
          d.office = selectedOffice; // or your dropdown selector
          console.log("Selected office: ", d.office);
        },
        dataSrc: function (json) {
          $("#tableContainer").show();
          $("#loadingScreenCont").hide();
          return json.data;
        },
      },
      // ->select(['property_no', 'particular', 'date_acquired', 'unit_value', 'current_holder', 'mr_date', 'office'])

      columns: [
        { data: "property_no", title: "Property No." },
        { data: "particular", title: "Item" },
        { data: "date_acquired", title: "Date Acquired" },
        {
          data: "unit_value",
          title: "Unit Value",
          render(value) {
            return convertToPhp(value);
          },
        },
        { data: "current_holder", title: "Current Holder" },
        {
          data: "mr_date",
          title: "MR Date",
          render(value) {
            return value == null ? "N/A" : value;
          },
        },
        { data: "office", title: "Office" },
      ],
      dom: '<"top"fip>rt<"clear">',
    });
    ``;
  }

  function initializeServiceableTable() {
    if (!$.fn.DataTable.isDataTable("#serviceable_table")) {
      $("#servTableContainer").hide();
      $("#servLoadingScreenCont").show();
      $("#serviceable_table").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: "/site/serviceables-ajax",
          data: function (d) {
            d.office = selectedOffice;
          },
          dataSrc: function (json) {
            $("#servTableContainer").show();
            $("#servLoadingScreenCont").hide();
            return json.data;
          },
        }, // use absolute or relative URL
        columns: [
          { data: "property_no", title: "Property No." },
          { data: "particular", title: "Item" },
          { data: "date_acquired", title: "Date Acquired" },
          {
            data: "unit_value",
            title: "Unit Value",
            render: function (value) {
              return convertToPhp(value);
            },
          },
          { data: "current_holder", title: "Current Holder" },
          {
            data: "mr_date",
            title: "MR Date",
            render: function (value) {
              return value == null ? "N/A" : value;
            },
          },
          { data: "office", title: "Office" },
        ],
        dom: '<"top"fip>rt<"clear">',
      });
      ``;
    }
  }

  // Holders Table click event -----------------------------------------------------------
  // function initializeHoldersTable() {
  //   if (!$.fn.DataTable.isDataTable("#holders_table")) {
  //     $("#holdTableContainer").hide();
  //     $("#holdLoadingScreenCont").show();
  //     $("#holders_table").DataTable({
  //       processing: true,
  //       serverSide: true,
  //       ajax: {
  //         url: "/site/holders-ajax",
  //         data: function (d) {
  //           console.log("Selected office: ", selectedOffice);
  //           console.log("clicked for site/holders-ajax");
  //           d.office = selectedOffice;
  //         },
  //         dataSrc: function (json) {
  //           $("#holdTableContainer").show();
  //           $("#holdLoadingScreenCont").hide();
  //           return json.data;
  //         },
  //       }, // use absolute or relative URL
  //       bAutoWidth: true,
  //       columns: [
  //         // { data: "date_acquired" },
  //         // { data: "item" },
  //         // { data: "property_type" },
  //         { data: "current_holder" },
  //       ],
  //       dom: '<"top"fip>rt<"clear">',
  //       initComplete: function (settings, json) {
  //         const table = this.api();

  //         $(tableSelector + " tbody").off("click").on("click", "tr", function () {
  //           const rowData = table.row(this).data();
  //           const currentHolder = rowData["current_holder"];
  //           if (currentHolder) {
  //             loadDetailedTableForHolder(currentHolder); // Reinitialize another table here
  //           }
  //         });
  //       },
  //     });
  //   }
  // }
  function initializeHoldersTable() {
    const tableSelector = "#holders_table";

    // Destroy existing DataTable if already initialized
    if ($.fn.DataTable.isDataTable(tableSelector)) {
      $(tableSelector).DataTable().destroy();
      $(tableSelector).find("tbody").empty();
    }

    $("#holdTableContainer").hide();
    $("#holdLoadingScreenCont").show();

    // Initialize with fresh data
    const table = $(tableSelector).DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "/site/holders-ajax",
        data: function (d) {
          d.office = selectedOffice; // optional filter
        },
        dataSrc: function (json) {
          $("#holdTableContainer").show();
          $("#holdLoadingScreenCont").hide();
          return json.data;
        },
      },
      bAutoWidth: false,
      columns: [{ data: "current_holder", title: "Current Holder" }],
      columnDefs: [{ width: "200px;", targets: [0] }],
      dom: '<"top"fip>rt<"clear">',
      initComplete: function () {
        // Attach row click handler once table is ready
        $(tableSelector + " tbody")
          .off("click")
          .on("click", "tr", function () {
            const rowData = table.row(this).data();
            const currentHolder = rowData["current_holder"];
            if (currentHolder) {
              loadDetailedTableForHolder("#holdModal", currentHolder); // Reinitialize another table here
            }
          });
      },
    });
  }

  // Function for loading new modal data based on current holder
  function loadDetailedTableForHolder(previousModal, currentHolder) {
    // turn of the modal
    $(previousModal).modal("hide");
    // Open the new modal
    $(detailedHoldModal).modal("show");

    const detailedTableSelector = "#detailed_table"; // Add this in your modal

    $("#detailedHoldTableContainer").hide();
    $("#detailedHoldLoadingScreenCont").show();

    // Destroy old table if exists
    if ($.fn.DataTable.isDataTable(detailedTableSelector)) {
      $(detailedTableSelector).DataTable().destroy();
      $(detailedTableSelector).find("tbody").empty();
    }

    // Initialize new one based on currentHolder
    $(detailedTableSelector).DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "/site/holder-details-ajax",
        data: function (d) {
          d.current_holder = currentHolder; // Pass this to PHP
        },
        dataSrc: function (json) {
          $("#detailedHoldTableContainer").show();
          $("#detailedHoldLoadingScreenCont").hide();
          return json.data;
        },
      },
      columns: [
        { data: "property_no", title: "Property No." },
        { data: "particular", title: "Item" },
        { data: "date_acquired", title: "Date Acquired" },
        { data: "unit_value", title: "Unit Value" },
        { data: "current_holder", title: "Current Holder" },
      ],
      bAutoWidth: false,
      columnDefs: [
        { width: "150px", targets: [0] },
        { width: "300px", targets: [1] },
        { width: "50px", targets: [2] },
        { width: "50px", targets: [3] },
        { width: "50px", targets: [4] },
      ],
      dom: '<"top"fip>rt<"clear">',
    });
  }

  $("#toHoldersBtn").on("click", function () {
    // Close the current modal
    $("#detailed_table").DataTable().destroy();
    $("#detailedHoldModal").modal("hide");

    // Open the holders modal
    $("#holdModal").modal("show");

    // Initialize the holders table
    initializeHoldersTable();
  });

  function initializeBarModal(propertyType) {
    console.log("Initializing bar modal for: " + propertyType);

    // Set the modal title dynamically
    document.querySelector(
      "#barModalLabel"
    ).textContent = `Items for ${propertyType}`;

    // Show the modal (if using Bootstrap 5+)
    const barModal = new bootstrap.Modal(document.getElementById("barModal"));
    barModal.show();

    const table = $("#barN_table");

    // Destroy previous instance if it exists
    if ($.fn.DataTable.isDataTable(table)) {
      table.DataTable().destroy();
      table.empty(); // Clear old headers if any
    }

    $("#barTableContainer").hide();
    $("#barLoadingScreenCont").show();

    table.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "/site/bar-total-number-ajax",
        type: "POST",
        data: function (d) {
          d.property_type = propertyType;
          d.office = selectedOffice;
          d._csrf = csrfToken;
          return d;
        },
        dataSrc: function (json) {
          console.log("Data received: ", json.data);
          return json.data;
        },
      },
      columns: [
        { data: "particular", title: "Item" },
        { data: "date_acquired", title: "Date Acquired" },
        {
          data: "unit_value",
          title: "Unit Value",
          render(value) {
            return convertToPhp(value);
          },
        },
        { data: "current_holder", title: "Current Holder" },
        {
          data: "mr_date",
          title: "MR Date",
          render(value) {
            return value == null ? "N/A" : value;
          },
        },
      ],
      bAutoWidth: false,
      columnDefs: [
        { width: "300px", targets: [0] },
        { width: "50px", targets: [1] },
        { width: "50px", targets: [2] },
        { width: "50px", targets: [3] },
      ],
      dom: '<"top"fip>rt<"clear">',
      // Move success handler here:
      initComplete: function (settings, json) {
        table.DataTable().columns.adjust().draw();
        $("#barTableContainer").show();
        $("#barLoadingScreenCont").hide();
      },
    });
  }

  function initializeAcquiredPerYearModal(year) {
    document.querySelector(
      "#acquiredPerYearModalLabel"
    ).textContent = `Items for ${year}`;

    modal = $("#acquiredPerYearModal");
    table = $("#acquiredPerYear_table");

    if ($.fn.DataTable.isDataTable(table)) {
      table.DataTable().destroy();
      table.empty(); // Clear old headers if any
    }

    modal.modal("show");

    $("#acquiredPerYearTableContainer").hide();
    $("#acquiredPerYearLoadingScreenCont").show();
    table.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "/site/items-per-year-ajax",
        type: "POST",
        data: function (d) {
          console.log("wtf is this: ", d);
          d.date = year;
          d._csrf = csrfToken;
          d.office = selectedOffice;
          return d;
        },
        // This is the response from the server
        dataSrc: function (json) {
          return json.data;
        },
        // Error handling
        error: function (xhr, error, code) {
          alert("Error: " + error + " Code: " + code);
        },
      },
      columns: [
        { data: "particular", title: "Item" },
        {
          data: "unit_value",
          title: "Unit Value",
          render: function (value) {
            return convertToPhp(value);
          },
        },
        { data: "current_holder", title: "Current Holder" },
        {
          data: "mr_date",
          title: "MR Date",
          render: function (value) {
            return value == null ? "N/A" : value;
          },
        },
      ],
      bAutoWidth: false,
      columnDefs: [
        { width: "50px", targets: [0] },
        { width: "50px", targets: [1] },
        { width: "50px", targets: [2] },
        { width: "50px", targets: [3] },
      ],
      dom: '<"top"fip>rt<"clear">',
      // Move success handler here:
      initComplete: function (settings, json) {
        table.DataTable().columns.adjust().draw();
        $("#acquiredPerYearTableContainer").show();
        $("#acquiredPerYearLoadingScreenCont").hide();
      },
    });
  }

  function initializeServiceablePerProperty(propertyType) {
    console.log("Initializing serviceables for: " + propertyType);

    // Set the modal title dynamically
    document.querySelector(
      "#propertyServiceablesModalLabel"
    ).textContent = `Serviceable Items Over 5 years for ${propertyType}`;

    // Show the modal (if using Bootstrap 5+)
    const barModal = new bootstrap.Modal(
      document.getElementById("propertyServiceablesModal")
    );
    barModal.show();

    const table = $("#propertyServiceables_table");

    // Destroy previous instance if it exists
    if ($.fn.DataTable.isDataTable(table)) {
      table.DataTable().destroy();
      table.empty(); // Clear old headers if any
    }

    $("#propertyServiceablesTableContainer").hide();
    $("#propertyServiceablesLoadingScreenCont").show();

    table.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "/site/property-serviceables-ajax",
        type: "POST",
        data: function (d) {
          // console.log("lemme see this: ", d);
          d.property_type = propertyType;
          d.office = selectedOffice;
          d._csrf = csrfToken;
          return d;
        },
        dataSrc: function (json) {
          return json.data;
        },
      },
      columns: [
        { data: "particular", title: "Item" },
        {
          data: "unit_value",
          title: "Unit Value",
          render(value) {
            return convertToPhp(value);
          },
        },
        { data: "current_holder", title: "Current Holder" },
        {
          data: "mr_date",
          title: "Mr Date",
          render(value) {
            return value == null ? "N/A" : value;
          },
        },
        { data: "date_acquired", title: "Date Acquired" },
      ],
      bAutoWidth: false,
      columnDefs: [
        { width: "300px", targets: [0] },
        { width: "50px", targets: [1] },
        { width: "50px", targets: [2] },
        { width: "50px", targets: [3] },
      ],
      dom: '<"top"fip>rt<"clear">',
      // Move success handler here:
      initComplete: function (settings, json) {
        table.DataTable().columns.adjust().draw();
        $("#propertyServiceablesTableContainer").show();
        $("#propertyServiceablesLoadingScreenCont").hide();
      },
    });
  }

  function destroyForDisposalsTable() {
    if ($.fn.DataTable.isDataTable("#disposal_table")) {
      $("#disposal_table").DataTable().destroy();
    }
  }
  function destroyServiceableTable() {
    if ($.fn.DataTable.isDataTable("#serviceable_table")) {
      $("#serviceable_table").DataTable().destroy();
    }
  }
  function destroyHoldersTable() {
    if ($.fn.DataTable.isDataTable("#holders_table")) {
      $("#holders_table").DataTable().destroy();
    }
  }
  function destroyDetailedHoldersTable() {
    if ($.fn.DataTable.isDataTable("#detailed_table")) {
      $("#detailed_table").DataTable().destroy();
    }
  }
  function destroyBarModal() {
    if ($.fn.DataTable.isDataTable("#barN_table")) {
      $("#barN_table").DataTable().destroy();
    }
  }
  function destroyAcquiredPerYearModal() {
    if ($.fn.DataTable.isDataTable("#acquiredPerYear_table")) {
      $("#acquiredPerYear_table").DataTable().destroy();
    }
  }
  function destroyPropertyServiceablesModal() {
    if ($.fn.DataTable.isDataTable("#propertyServiceables_table")) {
      $("#propertyServiceables_table").DataTable().destroy();
    }
  }
  // Modal event bindings
  $(document).ready(function () {
    $("#dispModal").on("shown.bs.modal", function () {
      initializeForDisposalsTable();
    });

    $("#servModal").on("shown.bs.modal", function () {
      initializeServiceableTable();
    });

    $("#holdModal").on("shown.bs.modal", function () {
      initializeHoldersTable();
    });

    // When the modal is closed, destroy the DataTable instance
    $("#dispModal").on("hidden.bs.modal", function () {
      destroyForDisposalsTable();
    });

    $("#servModal").on("hidden.bs.modal", function () {
      destroyServiceableTable();
    });

    $("#holdModal").on("hidden.bs.modal", function () {
      destroyHoldersTable();
    });

    $("#detailedHoldModal").on("hidden.bs.modal", function () {
      destroyDetailedHoldersTable();
    });

    $("#barModal").on("hidden.bs.modal", function () {
      // Destroy the DataTable instance
      destroyBarModal();
    });

    $("#acquiredPerYearModal").on("hidden.bs.modal", function () {
      // Destroy the DataTable instance
      destroyAcquiredPerYearModal();
    });

    $("#propertyServiceablesModal").on("hidden.bs.modal", function () {
      // Destroy the DataTable instance
      destroyPropertyServiceablesModal();
    });
  });
});

//#endregion

function convertToPhp(amount) {
  return new Intl.NumberFormat("en-PH", {
    style: "currency",
    currency: "PHP",
  }).format(amount);
}
