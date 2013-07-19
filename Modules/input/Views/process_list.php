<?php global $path; ?>

<script type="text/javascript" src="<?php echo $path; ?>Modules/input/Views/input.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/feed/feed.js"></script>

<br><br>

<h3>Configure input <?php echo $inputid; ?></h3>

<table class="table">

  <tbody id="items"></tbody>

  <tr>
    <td></td>
    <td>
      <div class="input-prepend">
        <span class="add-on">New</span>
        <select id="process_select" style="width:180px">
        <option value=0>Log to feed</option>
        <option value=1>Histogram</option>
        <option value=2>x</option>
        <option value=3>x input</option>
        </select>
      </div>
    </td>
    <td>
      <!-- First process option -->
      <div class="opt opt-feed input-prepend input-append">
        <span class="add-on">Feed</span>
        <select style="width:100px" id="feed_select"></select>
        <input id="create_feed_input" type="text" style="width:100px" / >
      </div>

      <div class="opt opt-input input-prepend input-append" style="display:none">
        <span class="add-on">Input</span>
        <select style="width:100px" id="input_select"></select>
      </div>

      <div class="opt opt-value input-prepend input-append" style="display:none">
        <span class="add-on">Value</span>
        <input type="text" style="width:100px" / >
      </div>

    </td>
    <td>
      <!-- Second process option -->
      <div class="opt opt-feed-interval input-prepend input-append">
        <span class="add-on">Interval</span>
        <select style="width:60px">
        <option>5s</option><option>10s<option>15s</option>
        </select>
      </div>

      <div class="opt opt-histogram-pot input-prepend input-append" style="display:none">
        <span class="add-on">Resolution</span>
        <input type="text" style="width:60px" / >
      </div>
    </td>

    <td>
      <button id="add-process" class="btn btn-info">Add</button>
    </td>
  </tr>

</table>

<script>

var path = "<?php echo $path; ?>";

var selected_process = 1; 

var inputid = <?php echo $inputid; ?>;
var feedlist = <?php echo json_encode($feedlist); ?>;
var inputlist = <?php echo json_encode($inputlist); ?>;
var processlist = <?php echo json_encode($processlist); ?>
// 
var inputprocesslist = JSON.parse(input.processlist(inputid));

draw_inputprocesslist();

function draw_inputprocesslist()
{
  var items = "";
  for (i in inputprocesslist) 
  {
    items += "<tr><td prcid="+i+">";
    if (i>0) items+= "<i class='icon-arrow-up'></i>";
    if (i<inputprocesslist.length-1) items += "<i class='icon-arrow-down'></i>";
    items += "</td><td>"+inputprocesslist[i][0]+"</td><td>"+inputprocesslist[i][1]+"</td>";
    if (inputprocesslist[i][2]!=undefined) items += "<td>"+inputprocesslist[i][2]+"</td>"; else items+="<td></td>"; 
    items += "<td prcid="+i+"><i class='icon-trash'></i></td></tr>";
  }
  $("#items").html(items);
}

$(".table").on('click', '.icon-arrow-up', function() {
  var current_position = 1*$(this).parent().attr('prcid');

  var tmp = inputprocesslist[current_position-1];
  inputprocesslist[current_position-1] = inputprocesslist[current_position];
  inputprocesslist[current_position] = tmp;

  draw_inputprocesslist();

  console.log(input.move_process(inputid,current_position+1,-1));
});

$(".table").on('click', '.icon-arrow-down', function() {
  var current_position = 1*$(this).parent().attr('prcid');

  var tmp = inputprocesslist[current_position+1];
  inputprocesslist[current_position+1] = inputprocesslist[current_position];
  inputprocesslist[current_position] = tmp;

  draw_inputprocesslist();

  console.log(input.move_process(inputid,current_position+1,1));
});

$(".table").on('click', '.icon-trash', function() {
  var current_position = 1*$(this).parent().attr('prcid');
  inputprocesslist.splice(current_position,1);
  draw_inputprocesslist();
  console.log(input.delete_process(inputid,current_position+1));
});

console.log(feedlist);
console.log(inputlist);
console.log(processlist);

// Process list
var out = "";
for (i in processlist) out += '<option value="'+i+'">'+processlist[i][0]+'</option>';
$("#process_select").html(out);

// Inputlist
var out = "";
for (i in inputlist) out += '<option value="'+inputlist[i].id+'">'+inputlist[i].nodeid+":"+inputlist[i].name+'</option>';
$("#input_select").html(out);

// Feedlist
$("#feed_select").html(compile_feed_options('0'));

$('#process_select').change(function() {
  selected_process = $(this).val();
  console.log(selected_process);
  var process_type = processlist[selected_process][1];

  // Start by hiding all option elements
  // we then show the option elements we want
  $(".opt").hide();
 
  // Value type processor (i.e: multiply by value)
  if (process_type == 0) $(".opt-value").show();
  // Input type processor (i.e: divide by input)
  if (process_type == 1) $(".opt-input").show();
  // Feed type processor (i.e: log to feed)
  if (process_type==2)
  {
    // Check for feed type 0: timestore
    if (processlist[selected_process][3] == 0) {
      $(".opt-feed, .opt-feed-interval, #create_feed_input").show();
      $("#feed_select").html(compile_feed_options('0'));
    }

    // 1: histogram
    if (processlist[selected_process][3] == 1) {
      $(".opt-feed, .opt-histogram-pot, #create_feed_input").show(); 
      $("#feed_select").html(compile_feed_options('1'));
    }
  }
});

// Used with either Log to feed or histogram to select or create feed
$('#feed_select').change(function() {
  var val = $(this).val();
  // VAL = -1 is create new feed
  if (val!=-1) {
    // Dont create a new feed: hide options
    $(".opt-histogram-pot, .opt-feed-interval, #create_feed_input").hide();
  } else {
    // Create a new feed show either timestore interval option or histogram resolution
    $("#create_feed_input").show();
    if (processlist[selected_process][3]==0) { $(".opt-feed-interval").show(); $(".opt-histogram-pot").hide(); }
    if (processlist[selected_process][3]==1) { $(".opt-histogram-pot").show(); $(".opt-feed-interval").hide(); }
  }
});

// Compiles HTML for feed selector
// Displays only feeds of given datatype (either TIMESTORE or HISTOGRAM)
function compile_feed_options(datatype) {
  var out = "<option value=-1>CREATE:</option>";
  for (i in feedlist)
    if (feedlist[i].datatype==datatype) 
      out += '<option value="'+feedlist[i].id+'">'+feedlist[i].name+'</option>';
  return out;
}

$("#add-process").click(function(){

  console.log(processlist[selected_process]);
  console.log(selected_process);

  if (selected_process==1) {

    var feedid = $('#feed_select').val();
    
    if (feedid == -1) {
      var feedname = $("#create_feed_input").val();
      var interval = $('.opt-feed-interval select').val();
      var result = feed.create(feedname,0,interval);
      console.log(result.feedid);
      feedid = result.feedid;
    }
    input.add_process(inputid,1,feedid); 
    inputprocesslist.push(['Log to feed',feedname]);
    draw_inputprocesslist();
  }

  if (selected_process==2) {
    var resolution = $('.opt-histogram-pot input').val();

    var feedid = $('#feed_select').val();
    if (feedid == -1) {
      var feedname = $("#create_feed_input").val();
      var resolution = $('.opt-histogram-pot input').val();
      var result = feed.create(feedname,1,resolution);
      console.log(result.feedid);
      feedid = result.feedid;
    }
    input.add_process(inputid,2,feedid);
    inputprocesslist.push(['Histogram',feedname]);
    draw_inputprocesslist();
  }

  if (selected_process==3) {
    var value = $('.opt-value input').val();
    input.add_process(inputid,3,value);
    inputprocesslist.push(['x',value]);
    draw_inputprocesslist();
  }

  if (selected_process==4) {
    var value = $('.opt-value input').val();
    input.add_process(inputid,4,value);
    inputprocesslist.push(['+',value]);
    draw_inputprocesslist();
  }

  if (selected_process==5) {
    input.add_process(inputid,5,'');
    inputprocesslist.push(['Allow positive','']);
    draw_inputprocesslist();
  }

  if (selected_process==6) {
    input.add_process(inputid,6,'');
    inputprocesslist.push(['Allow negative','']);
    draw_inputprocesslist();
  }

});

</script>
