// Import dependencies
self.importScripts(
    'script/idb.min.js',
    'script/service.min.js',
    'script/elements.min.js',
    'script/controller.js',
    'script/table.js',
    'script/handler/auth.js',
    'script/handler/launch.js',
    'script/handler/core.js',
);

// Setup controller and service
const controller = new Controller('3.1.0');
const service = new Service(controller);

// Setup auto-refresh
setInterval(async () => {
    if (!navigator.onLine) return;

    // Check refresh interval
    const checked = await IDB.server.get('checked');
    if (!checked || new Date() - checked > 15 * 60 * 1000) {

        // Perform refresh
        try {
            await controller.refresh();
        } catch (e) {
            console.error(e);
            controller.next = '/error/' + encodeURI(e);
        }
    }
}, 3000);

self.addEventListener('push', function (event) {
    const data = event.data.json();
    event.waitUntil(self.registration.showNotification(data.title, {
        body: data.text,
        icon: "image/icon.php",
        data: {
            href: data.href
        }
    }));
});

self.addEventListener('notificationclick', function (event) {
    const notification = event.notification;
    notification.close();

    event.waitUntil(
        clients.matchAll({
            type: "window"
        }).then(client => {
            if (client.length === 0) {
                return clients.openWindow(notification.data.href)
            }
            for (const client of allClients) {
                if ('navigate' in client) {
                    return client.navigate(notification.data.href);
                }
            }
        })
    );
});
