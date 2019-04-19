class Controller {
	
	// IDB table definition
	get tables() {
		return {
			exams: { autoIncrement: true },
			meals: { autoIncrement: true },
			lectures: { autoIncrement: true },
			courses: { autoIncrement: true },
			events: { autoIncrement: true },
			professors: { autoIncrement: true },
			messages: { autoIncrement: true },
			tips: { autoIncrement: true },
			printers: { autoIncrement: true },
			subjects: { autoIncrement: true },
			server: {},
		};
	}
	
	// Cache definition
	get cache() {
		return [
			'/font/awesome.woff2?v=4.7.0',
			'/font/awesome.woff?v=4.7.0',
			
			'/script/lu.min.js',
			'/script/insight.js',
			'/script/client.js',
			
			'/style/main.scss',
			'/style/page.scss',
			
			'/error/incompatible',
			'/error/invalid device',
			'/error/invalid credentials',
			'/error/503',
			'/error/gateway',
			'/error/aborted',
			'/error/cooldown',
			'/error/disabled',
			'/error/offline',
			'/error/broken',
			
			'/lang/de.json',
			
			'/template/_courses.html',
			'/template/_events.html',
			'/template/_exams.html',
			'/template/_messages.html',
			'/template/_lectures.html',
			'/template/_meals.html',
			'/template/_menu.html',
			'/template/_printers.html',
			'/template/_professors.html',
			'/template/_tips.html',
			'/template/_footer.html',
			'/template/_header.html',
			'/template/shell.html',
			'/template/login.html'
		];
	}
	
	// Constructor
	constructor(version) {
		this.version = version;
		
		// Setup handlers
		this.handlers = [
			new LaunchHandler(this),
			new AuthHandler(this),
			new CoreHandler(this)
		];
	
		// Connect to DB
		IDB.open(this.tables, {
			upgrade: () => this.next = '/upgrade',
		});
	}
	
	// Exception handler
	async exceptionHandler(exception) {
		return Response.redirect('/error/'+exception);
	}
	
	// Response filter
	async responseFilter(response) {
		
		// Delayed redirect (coupled with auto refresh)
		if(this.next) {
			const redirect = Response.redirect(this.next);
			delete this.next;
			return redirect;
		}
		
		// Wrap html in response
		if(response instanceof Response) return response;
		else return new Response(response, {
			status: 200,
			statusText: 'OK',
			headers: new Headers({
				'Content-Type': 'text/html;charset=UTF-8',
				'Content-Length': response.length,
			}),
		});
	}
	
	// Refresh data
	async refresh() {
		
		// Check device (no refresh before login)
		const device = await IDB.server.get('device');
		if(!device) return;
		
		// Set check timestamp
		await IDB.server.put(new Date(), 'checked');
		
		// Perform request
		const result = await this.query({
			endpoint: 'api',
			action: 'refresh',
		});
		
		// Clear all tables but server
		for(let name in this.tables) {
			if(name == 'server') continue;
			await IDB[name].clear();
			
			// Refill tables
			if(result[name]) for(let object of result[name]) {
				
				// Cast date objects
				for(let index in object) if((
					(name == 'lectures' && index == 'start') ||
					(name == 'lectures' && index == 'end') ||
					(name == 'events' && index == 'start') ||
					(name == 'events' && index == 'end') ||
					(name == 'messages' && index == 'sent')
				) && object[index]) object[index] = new Date(object[index]);
				
				// Insert data
				await IDB[name].put(object);
			}
		}
		
		// Set refresh timestamp
		await IDB.server.put(new Date(result.refreshed), 'refreshed');
		await IDB.server.put(result.notifications, 'notifications');
	}
	
	// Fetch a resource
	async fetch(request) {
		return await caches.match(request) || await fetch(request);
	}
	
	// Query API
	async query(call) {
		
		// Check connection
		if(!navigator.onLine) throw 'offline';
		
		// Empty payload
		if(!call.payload) call.payload = new URLSearchParams();
		
		// Add device to api calls
		if(call.endpoint == 'api') call.payload.set('device', await IDB.server.get('device'));
		
		// Construct target
		let target = 'endpoint/'+call.endpoint+'.php';
		if(call.action) target+= '?action='+call.action;
		
		// Perform request
		const response = await fetch(target, {method: 'POST', body: call.payload}).then(response => response.json());
		
		// Check response
		if(response.status && response.status == 'OK') return response;
		else throw response.error || 'unknown error';
	}
}