<?php 
header('Content-Type: application/javascript');
?>
'use strict';

var pushMonkeySWConfig = {
    version: 4,
    logging: false, // TODO: set to false when live
    accountKey: "<?php 
    $s = $_SERVER['REQUEST_URI'];
    $comps = explode("-", $s);
    $last = $comps[count($comps) - 1];
    $comps = explode(".", $last);
    $key = $comps[0];
    echo $key; 
    ?>",
    host: "https://snd.tc"
};

var url = pushMonkeySWConfig.host + "/push/v1/notifs/" + pushMonkeySWConfig.accountKey;
self.addEventListener('push', function(event) {

  if (Object.keys(event.data.json()).length == 0) {

    event.waitUntil(fetch(url).then(function(response) {

      return response.json().then(function(data) {

        var title = data.title;
        var body = data.body;
        var icon = data.icon;
        var tag = data.id;
        var payload = {
              body: body,
              icon: icon,
              tag: tag,
              requireInteraction: true,
              actions: [{action: 'ok', title: 'OK'}]        
        };
        if (data.image) { 

          payload["image"] = data.image;
        }
        if (data.actions) {
            payload["actions"] = data.actions;
        }
        return self.registration.showNotification(title, payload);
      });
    }));
  } else {

    var data = event.data.json();
    var title = data.title;
    var body = data.body;
    var icon = data.icon;
    var tag = data.id;
    var payload = {
      body: body,
      icon: icon,
      tag: tag,
      requireInteraction: true,
      actions: [{action: 'ok', title: 'OK'}]            
    };
    if (data.image) { 

      payload["image"] = data.image;
    }
    if (data.actions) {
        payload["actions"] = data.actions;
    } 
    event.waitUntil(self.registration.showNotification(title, payload));
  }
});

self.addEventListener('notificationclick', function(event) {

  if (pushMonkeySWConfig.logging) console.log('On notification click: ', event.notification.tag);
  // Android doesn’t close the notification when you click on it
  // See: http://crbug.com/463146
  event.notification.close();
  // This looks to see if the current is already open and
  // focuses if it is
  event.waitUntil(clients.matchAll({
    type: "window"
  }).then(function(clientList) {
    for (var i = 0; i < clientList.length; i++) {
      var client = clientList[i];
      if (client.url == '/' && 'focus' in client)
        return client.focus();
    }
    var action = '';
    if (clients.openWindow)
      try {
        action = event.action;
      } catch(err){
        action = ''
      }
      return clients.openWindow(pushMonkeySWConfig.host + '/stats/track_open/' + event.notification.tag + '?action=' + action);
  }));
});

// 
// Trick to make service worker updates easier.
//
self.addEventListener('install', function(event) {

  event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', function(event) {

  event.waitUntil(self.clients.claim());
});