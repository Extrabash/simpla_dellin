$(document).ready(function () {
  var delivery_name = "dellinii";

  var container = $("#delivery_" + delivery_name);
  var delivery_id = container.data("delivery_id");

  var query_data = {delivery_method: delivery_id};

  run_dellinii_ajax(query_data, delivery_name, container);
});

function run_dellinii_ajax(query_data, delivery_name, container) {
    container.find(".dellinii_step").prop('disabled', true);
    container.addClass('updating');
    $.ajax({
    url: "delivery/" + delivery_name + "/run.php",
    data: query_data,
    dataType: "json",
    success: function (data) {
        //console.log(data);
        container.html(data.printed_tpl);
        container.find(".dellinii_step").change(function(){
            let var_name = $(this).attr('name');
            query_data[var_name] = $(this).val();
            console.log(query_data);
            run_dellinii_ajax(query_data, delivery_name, container);
        });
        container.removeClass("updating");
    },
    });
}
