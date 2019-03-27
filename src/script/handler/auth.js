class AuthHandler {
	
	constructor(controller) {
		this.controller = controller;
	}
	
	get pattern() {
		return /\/(login|logout)\/?$/;
	}
	
	async process(request) {
		switch(request.params[1]) {
		
			// Login
			case 'login': {
				const data = {};
				
				// Process input
				if(request.POST.has('submit')) {
					let result = await this.controller.query({
						endpoint: 'authorize',
						payload: request.POST,
					});
					
					// Handle login result
					if(result.login) {
						await IDB.server.put(result.device, 'device');
						await IDB.server.put(result.username, 'username');
						await IDB.server.put(result.displayname, 'displayname');
						await this.controller.refresh();
						return Response.redirect('/launch');
					} else data.error = true;
				}
				
				// Render input
				let template = await this.controller.fetch('/template/login.html').then(response => response.text());
				const header = await this.controller.fetch('/template/_header.html').then(response => response.text());
				const footer = await this.controller.fetch('/template/_footer.html').then(response => response.text());
				template = template.replace('{{>header}}', header).replace('{{>footer}}', footer);
				return Elements.render(template, data);
			}
			
			// Logout
			case 'logout': {
				await this.controller.query({
					endpoint: 'api',
					action: 'logout',
				});
				
				// Clear tables
				for(const table in this.controller.tables) IDB[table].clear();
				
				// Relaunch
				return Response.redirect('/launch');
			}
		}
	}
}