/* global Morris, jvm, jQuery */
(function ($, window, document) {
  'use strict'

  window.Monkey = window.Monkey || {

    init: function () {

      this.$window = $(window)
      this.$body = $('body')

      this.globalData = global_data

      this.graphNotificationsLine()
      this.graphOpenedLine()
      this.graphsMap()
    },

    graphNotificationsLine: function () {

      if ($('#push-monkey-dashboard-line-1').length) {

        var that = this
        var setData = []
        var i, date
        for (i = 0; i <= that.globalData.stats.labels_dataset.length; i = i + 1) {

          if (that.globalData.stats.labels_dataset[i] !== undefined) {

            setData[i] = {
              y: that.globalData.stats.labels_dataset[i],
              a: that.globalData.stats.sent_notifications_dataset[i]
            }
          }
        }

        Morris.Line({
          element: 'push-monkey-dashboard-line-1',
          data: setData,
          xkey: 'y',
          ykeys: ['a'],
          labels: ['Notifications'],
          resize: true,
          hideHover: true,
          xLabels: 'day',
          gridTextSize: '10px',
          lineColors: ['#3FBAE4'],
          gridLineColor: '#E5E5E5',
          parseTime: false
        })
      }
    },

    graphOpenedLine: function () {

      if ($('#push-monkey-dashboard-line-2').length) {

        var that = this
        var setData = []
        var i, date

        for (i = 0; i <= that.globalData.stats.labels_dataset.length; i = i + 1) {

          if (that.globalData.stats.labels_dataset[i] !== undefined) {

            date = new Date()
            setData[i] = {
              y: that.globalData.stats.labels_dataset[i],
              a: that.globalData.stats.opened_notifications_dataset[i]
            }
          }
        }

        Morris.Line({
          element: 'push-monkey-dashboard-line-2',
          data: setData,
          xkey: 'y',
          ykeys: ['a'],
          labels: ['Opened'],
          resize: true,
          hideHover: true,
          xLabels: 'day',
          gridTextSize: '10px',
          lineColors: ['#33414E'],
          gridLineColor: '#E5E5E5',
          parseTime: false
        })
      }
    },

    graphsMap: function () {

      if (!$('#dashboard-map-seles').length) {

        return;
      }
      var that = this
      var convertToUppercase = that.convertToUppercase([that.globalData.stats.geo_data])

      var jvm_wm = new jvm.WorldMap(
        {
          container: $('#dashboard-map-seles'),
          map: 'world_mill_en',
          backgroundColor: '#FFFFFF',
          regionsSelectable: true,
          regionStyle: {
            selected: {
              fill: '#B64645'
            },
            initial: {
              fill: '#33414E'
            }
          },
          series: {
            regions: [{
              values: convertToUppercase[0],
              scale: ['#C8EEFF', '#0071A4'],
              normalizeFunction: 'polynomial'
            }]
          },
          onRegionTipShow: function (e, el, code) {
            el.html(el.html() + ' (GDP - ' + that.globalData.stats.geo_data[code] + ')')
          }
        })
    },

    convertToUppercase: function (obj) {
      return obj.map(function (item) {
        for (var key in item) {
          var upper = key.toUpperCase()
          // check if it already wasn't uppercase

          if (upper !== key) {
            item[ upper ] = item[key]
            delete item[key]
          }
        }
        return item
      })
    }
  }

  $( document ).ready( function() {
    window.Monkey.init()
  } );

  // Trigger `have an account` tab.
  $( document ).on( 'click', '#have_an_account', function( event ) {
    jQuery( '.login-container ul.nav li:last-child a' ).click();
    event.preventDefault();
  } );
}(jQuery, window, document))
