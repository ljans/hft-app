class LaunchHandler {

	constructor(controller) {
		this.controller = controller;
	}
	
	get pattern() {
		return /\/launch\/?$/;
	}
	
	async process(request) {
		
		// Store token and username
		if(request.GET.has('device')) await IDB.server.put(request.GET.get('device'), 'device');
		if(request.GET.has('username')) await IDB.server.put(request.GET.get('username'), 'username');
		
		// Redirect to last visited page or messages
		const page = await IDB.server.get('page') || 'messages';
		return Response.redirect('/'+page);
	}
}