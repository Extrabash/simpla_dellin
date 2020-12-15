$(document).ready(function () {
  var delivery_name = "dellinii";
  var container = $("#delivery_" + delivery_name);
  let delivery_id = container.data("delivery_id");
  var query_data = {delivery_method: delivery_id};
  var form = container.parents('form[name="cart"]');

  form.submit(function (ev) {
    if (container.hasClass("updating")) ev.preventDefault();
  });

  /* Для обноления суммы

  $('[name="delivery_id"]').change(function () {
    update_total($(this).data("price"));
  });*/


  run_dellinii_ajax(query_data, delivery_name, container);
});

function run_dellinii_ajax(query_data, delivery_name, container) {
    container.find(".dellinii_step").prop('disabled', true);
    container.addClass('updating');

    //update_total();

    let submit_button_text = form.find('[type="submit"]').html();
    form.find('[type="submit"]').html("Дождитесь расчета доставки");

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
            //console.log(query_data);
            run_dellinii_ajax(query_data, delivery_name, container);
        });

        // предотвратим отправку, пока не загрузилось
        form.submit(function (ev) {
          if (container.hasClass("updating")) ev.preventDefault();
        });

        /*
        // Обновление цены
        if (data.price != null) {
            let delivery_id = container.data("delivery_id");
            $("#ID_DELIVERY_" + delivery_id).data("price", data.price);
            update_total(data.price);
        }
        */

        container.removeClass("updating");
        form.find('[type="submit"]').html(submit_button_text);
    },
    });
}

/*
// Обновление суммы
function update_total(price = "") {
  if (price != "")
    $(".js-total-update").html(
      XFormatPrice(
        parseFloat($(".js-total-update").data("total_unconverted")) +
          parseFloat(price)
      )
    );
  else $(".js-total-update").html($(".js-total-update").data("total"));
}*/
/*
// Форматирование цены
function XFormatPrice(_number) {
  var decimal = 2;
  var separator = " ";
  var decpoint = ".";
  //var format_string = '# руб.';

  var r = parseFloat(_number);

  var exp10 = Math.pow(10, decimal); // приводим к правильному множителю
  r = Math.round(r * exp10) / exp10; // округляем до необходимого числа знаков после запятой

  rr = Number(r).toFixed(decimal).toString().split(".");

  b = rr[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g, "$1" + separator);

  r = rr[1] ? b + decpoint + rr[1] : b;
  return r;
}
*/
