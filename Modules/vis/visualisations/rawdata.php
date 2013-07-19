<?php

  // All Emoncms code is released under the GNU Affero General Public License.
  // See COPYRIGHT.txt and LICENSE.txt.
  // ---------------------------------------------------------------------
  // Emoncms - open source energy visualisation
  // Part of the OpenEnergyMonitor project:
  // http://openenergymonitor.org

  global $path, $embed;

  
?>

<script type="text/javascript" src="<?php echo $path; ?>Modules/feed/feed.js"></script>

<!--[if IE]><script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/flot/excanvas.min.js"></script><![endif]-->
<script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/flot/jquery.flot.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/flot/jquery.flot.time.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path; ?>Lib/flot/jquery.flot.selection.min.js"></script>

<script language="javascript" type="text/javascript" src="<?php echo $path;?>Modules/vis/visualisations/vis.helper.js"></script>

<style>
#vis-title {
  font-size:24px;
  font-weight:bold;
}
</style>

<br>

<div id="vis-title"></div>

<div id="placeholder_bound" style="width:100%; height:400px; position:relative; ">
  <div id="placeholder"></div>
  <div style="position:absolute; top:20px; left:45px;">

    <div class='btn-group'>
      <button class='btn time' type='button' time='1'>D</button>
      <button class='btn time' type='button' time='7'>W</button>
      <button class='btn time' type='button' time='30'>M</button>
      <button class='btn time' type='button' time='365'>Y</button>
    </div>

    <div class='btn-group'>
      <button id='zoomin' class='btn' >+</button>
      <button id='zoomout' class='btn' >-</button>
      <button id='left' class='btn' ><</button>
      <button id='right' class='btn' >></button>
    </div>

    <div class='btn-group'>
      <button id='csv' class='btn' >csv</button>
      <button id='info' class='btn' >i</button>
    </div>

  </div>

    <h3 style="position:absolute; top:0px; right:0px;"><span id="stats"></span></h3>
</div>

<script id="source" language="javascript" type="text/javascript">

var path = "<?php echo $path; ?>";
var apikey = "";

var feedid = urlParams['feedid'];
var embed = urlParams['embed'] || false;

var placeholder_bound = $('#placeholder_bound');
var placeholder = $('#placeholder').width(placeholder_bound.width()).height($('#placeholder_bound').height());
if (embed) placeholder.height($(window).height());

$(window).resize(function(){
  placeholder.width(placeholder_bound.width());
  if (embed) placeholder.height($(window).height());
  //plot();
});

var timeWindow = (3600000*24.0*7);
view.start = +new Date - timeWindow;
view.end = +new Date;

var data = [];

$(function() {

  if (embed==false) $("#vis-title").html("Raw data");
  draw();

  $("#zoomout").click(function () {view.zoomout(); draw();});
  $("#zoomin").click(function () {view.zoomin(); draw();});
  $('#right').click(function () {view.panright(); draw();});
  $('#left').click(function () {view.panleft(); draw();});
  $('.time').click(function () {view.timewindow($(this).attr("time")); draw();});

  placeholder.bind("plotselected", function (event, ranges) { view.start = ranges.xaxis.from; view.end = ranges.xaxis.to; draw(); });

  function draw()
  {
    data = [];
    data = feed.get_data(feedid,view.start,view.end,800);

    stats.calc(data);
    console.log(stats.mean);

    var options = {
	    xaxis: { mode: "time", min: view.start, max: view.end },
      grid: {hoverable: true, clickable: true},
      selection: { mode: "x" }
	  }

    $.plot(placeholder, [data], options);
  }
});





</script>

