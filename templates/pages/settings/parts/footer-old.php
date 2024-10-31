<script type="text/javascript">
  var doughnutData = [
  {
    value: <?php echo $output->sent_notifications; ?>,
    color: "#2FCC70",
    highlight: "#28b263",
    label: "Sent Notifications"
  },
  {
    value: <?php echo $output->remaining_notifications; ?>,
    color:"#8E8E8B",
    highlight: "#727270",
    label: "Remaining"
  }
  ];
  var doughnutOptions = {

    segmentShowStroke : false,
    percentageInnerCutout : 70, // This is 0 for Pie charts
    animationSteps : 100,
    animationEasing : "easeOutBounce",
    animateRotate : true,
    animateScale : true,
    responsive: true
  };

  var options = {
    scaleShowGridLines : true,
    scaleGridLineColor : "rgba(0,0,0,.05)",
    scaleGridLineWidth : 1,
    bezierCurve : true,
    bezierCurveTension : 0.4,
    pointDot : true,
    pointDotRadius : 5,
    pointDotStrokeWidth : 3,
    pointHitDetectionRadius : 20,
    datasetStroke : true,
    datasetStrokeWidth : 0,
    datasetFill : true,
    responsive: true,
    maintainAspectRatio: false
  };

  var data = {
    labels: ['<?php echo implode("','", $output->labels_dataset); ?>'],
    datasets: [
    {
      label: "Sent Notifications",
      fillColor: "#2FCC70",
      strokeColor: "#2FCC70",
      pointColor: "#2FCC70",
      pointStrokeColor: "#2FCC70",
      pointHighlightFill: "#8E8E8B",
      pointHighlightStroke: "#8E8E8B",
      data: [<?php echo implode(",", $output->sent_notifications_dataset); ?>]
    },
    {
      label: "Opened Notifications",
      fillColor: "#363636",
      strokeColor: "#363636",
      pointColor: "#363636",
      pointStrokeColor: "#363636",
      pointHighlightFill: "#8E8E8B",
      pointHighlightStroke: "#8E8E8B",
      data: [<?php echo implode(",", $output->opened_notifications_dataset); ?>]
    }
    ]
  };
  jQuery(document).ready(function($) {

    if ($('#chart').size()) {
      var ctx = $("#chart").get(0).getContext("2d");
      var myNewChart = new Chart(ctx).Line(data, options);
    }

    if ($('#doughnut-chart').size())  {
      var ctx2 = $("#doughnut-chart").get(0).getContext("2d");
      var doughnutChart = new Chart(ctx2).Doughnut(doughnutData, doughnutOptions);
    }
  });
</script>
<script type="text/javascript">

  var gdpData = <?php echo json_encode($output->geo_data); ?>;

  jQuery(document).ready(function($) {

    var max = 0,
        min = Number.MAX_VALUE,
        cc,
        startColor = [108, 213, 251],
        endColor = [1, 35, 86],
        colors = {},
        hex;

    //find maximum and minimum values
    for (cc in gdpData) {

      if (parseFloat(gdpData[cc]) > max) {

        max = parseFloat(gdpData[cc]);
      }
      if (parseFloat(gdpData[cc]) < min) {

        min = parseFloat(gdpData[cc]);
      }
    }

    //set colors according to values of GDP
    for (cc in gdpData) {

      if (gdpData[cc] > 0) {

        colors[cc] = '#';
        for (var i = 0; i<3; i++) {

          hex = Math.round(startColor[i]
              + (endColor[i]
              - startColor[i])
              * (gdpData[cc] / (max - min))).toString(16);

          if (hex.length == 1) {

            hex = '0'+hex;
          }
          colors[cc] += (hex.length == 1 ? '0' : '') + hex;
        }
      }
    }
    if ($('#vmap').size()) {

      $('#vmap').vectorMap({

        backgroundColor: '#E8E8E1',
        borderColor: '#bbb',
        color: '#fff',
        colors: colors,
        hoverOpacity: 0.7,
        hoverColor: false,
        map: 'world_en'
      });
    }
  });
</script>