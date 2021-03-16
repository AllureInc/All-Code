/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

self.addEventListener('install', function (event) {
  self.skipWaiting();
 
});

self.addEventListener('activate', function (event) {
 
});

var url ='';
var subscriptionId="";
var hostName = '';
var protocol = '';
var baseUrl;
self.addEventListener('push', function (event) {
    var loc = location;
    var pathName = loc.pathname.substring(0, loc.pathname.indexOf('pub'));
    baseUrl =  loc.href.substring(0, loc.href.length - ((loc.pathname + loc.search + loc.hash).length - pathName.length));
    event.waitUntil(
        self.registration.pushManager.getSubscription().then(function (sub) {
        hostName = location.hostname;
        protocol = location.protocol;
        subscriptionId = sub.endpoint.split("/").slice(-1)[0];
        url = baseUrl+'mppushnotification/users/templatedata?token='+subscriptionId+"&param="+Math.random();
        }).then(function (obj) {
              
            fetch(url).then(function (response) {
              return response.text();
            }).then(function (responseContent) {

                data = JSON.parse(responseContent);

                var title = data.title;
                var message = data.message;
                var icon = data.logo;
                var tags = data.tags;

                return self.registration.showNotification(title, {
                    body: message,
                    icon: icon,
                    tag: tags,
                    data: {
                        url: data.url
                    }
                });
            });
        })
    );
});

// The user has clicked on the notification ...
self.addEventListener('notificationclick', function (event) {
 
  event.notification.close();
 
event.waitUntil(
    clients.matchAll({
      type: "window"
      })
      .then(function (clientList) {
      for (var i = 0; i < clientList.length; i++) {
        var client = clientList[i];
        if (client.url == '/' && 'focus' in client) {
          return client.focus();
        }
      }
      if (clients.openWindow) {
        return clients.openWindow(event.notification.data.url);
      }
      })
);
});