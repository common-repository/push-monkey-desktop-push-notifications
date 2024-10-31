jQuery(function ($) {

  var formElements = (function () {

    // Bootstrap colopicker
    var feColorpicker = function () {
      if ($('#colorpicker').length > 0) { 

        $('#colorpicker').colorpicker({
          container: $('#colorpicker'),
          horizontal: true
        })
      }
      if ($('#colorpicker2').length > 0) { 

        $('#colorpicker2').colorpicker({
          container: $('#colorpicker2'),
          horizontal: true
        }) 
      }
    } // END Bootstrap colorpicker
      
    // Masked Inputs
    var feMasked = function () {
      if ($("input[class^='mask_']").length > 0) {
        $('input.mask_tin').mask('99-9999999')
        $('input.mask_ssn').mask('999-99-9999')
        $('input.mask_date').mask('9999-99-99')
        $('input.mask_product').mask('a*-999-a999')
        $('input.mask_phone').mask('99 (999) 999-99-99')
        $('input.mask_phone_ext').mask('99 (999) 999-9999? x99999')
        $('input.mask_credit').mask('9999-9999-9999-9999')
        $('input.mask_percent').mask('99%')
      }
    } 
    // END Masked Inputs

    // Bootstrap tooltip
    var feTooltips = function () {
      $('body').tooltip({ selector: '[data-toggle="tooltip"]', container: 'body' })
    } 
    // END Bootstrap tooltip

    // Bootstrap Popover
    var fePopover = function () {
      $('[data-toggle=popover]').popover()
      $('.popover-dismiss').popover({ trigger: 'focus' })
    } 
    // END Bootstrap Popover

    // iCheckbox and iRadion - custom elements
    var feiCheckbox = function () {
      if ($('.icheckbox').length > 0) {
        $('.icheckbox,.iradio').iCheck({ checkboxClass: 'icheckbox_minimal-grey', radioClass: 'iradio_minimal-grey' })
      }
    }
    // END iCheckbox

    // Bootstrap file input
    var feBsFileInput = function () {

      if ($('input.fileinput').length > 0) {

        $('input.fileinput').bootstrapFileInput()
      }
    }
    // END Bootstrap file input

    return { // Init all form element features
      init: function () {
        feColorpicker()
        feMasked()
        feTooltips()
        fePopover()
        feiCheckbox()
        feBsFileInput()
      }
    }
  }())

  formElements.init()

    // New selector case insensivity
  $.expr[':'].containsi = function (a, i, m) {
    return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0
  }
})

Object.size = function (obj) {
  var size = 0,
    key
  for (key in obj) {
    if (obj.hasOwnProperty(key)) size++
  }
  return size
}
