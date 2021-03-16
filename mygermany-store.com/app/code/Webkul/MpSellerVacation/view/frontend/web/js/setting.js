define(
    [
          "jquery",
          "mage/mage",
          "mage/calendar"
     ],
    function ($) {
        $.widget(
            'webkul.setting',
            {
                _create: function () {
                    var currentDate = this.options.currentDate;
                    var dateFrom = this.options.dateFrom;
                    var dataForm = $('#form-mpsellervacation-validate');
                    var vacationMode = this.options.vacationMode;
                    var startoffset = this.options.startOffset;
                    var stopoffset = this.options.stopOffset;
                    var zone = Intl.DateTimeFormat().resolvedOptions().timeZone;
                    $("#seller_time_zone").val(zone);
                    if ($('#date_to').val()) {
                        var stopTime = converttoTz($('#date_to').val(), stopoffset);
                        var startTime = converttoTz($('#date_from').val(), startoffset);
                        $('#date_to').val(stopTime);
                        $('#date_from').val(startTime);
                    }
                    function converttoTz(time, offset)
                    {
                        var currentoffset = new Date().getTimezoneOffset() * -1 * 60000;
                        var date = new Date(new Date(time).getTime() + currentoffset + (parseInt(offset)* -1000));
                        var d,m,h,i,s;
                        d = date.getDate();
                        m = date.getMonth();
                        h = date.getHours();
                        i = date.getMinutes();
                        s = date.getSeconds();
                        if (parseInt(d) < 10) {
                                d = "0"+d;
                        }
                        if ((parseInt(m)+1) < 10) {
                                m = "0"+(parseInt(m)+1);
                        }
                        if (parseInt(h) < 10) {
                            h = "0"+h;
                        }
                        if (parseInt(i) < 10) {
                            i = "0"+i;
                        }
                        if (parseInt(s) < 10) {
                            s = "0"+s;
                        }
                        if (vacationMode == 'add_to_cart_disable') {
                          return date.getFullYear()+'-'+m+'-'+d+' '+h+':'+i+':'+s;
                        } else {
                          return date.getFullYear()+'-'+m+'-'+d;
                        }
                    }
                    dataForm.mage('validation', {});
                    if (vacationMode == 'add_to_cart_disable') {
                      $("#date_from").calendar(
                          {
                              showsTime:true,
                              dateFormat:"Y-mm-dd",
                              timeFormat:"HH:mm:ss",
                              minDate:currentDate,
                              onClose: function (selectedDate) {
                                  $("#date_to").datepicker('option','minDate',selectedDate);
                              }
                          }
                      );
                      var dateObj = null;
                      if (dateFrom) {
                          dateObj = {
                              showsTime:true,
                              dateFormat:"Y-mm-dd",
                              timeFormat:"HH:mm:ss",
                              minDate:dateFrom
                          };
                      } else {
                          dateObj = {
                              showsTime:true,
                              dateFormat:"Y-mm-dd",
                              timeFormat:"HH:mm:ss",
                              minDate:currentDate
                          };
                      }
                      $("#date_to").calendar(dateObj);
                    } else {
                          $("#date_from").calendar({'dateFormat':'Y-M-d','minDate':currentDate});
                          $("#date_to").calendar({'dateFormat':'Y-M-d','minDate':currentDate});
                          if (dateFrom) {
                              $("#date_to").datepicker('option','minDate',dateFrom);
                          }
                          $("#date_from").on('change',function () {
                              $("#date_to").datepicker('option','minDate',$(this).val());
                          });
                    }
                }
            }
        );
        return $.webkul.setting;
     }
);
