Symfony Bundle version of the Ymanager application.  

in app/config/config.yml, add the search.vids service as global, and add the client_id and client_secret you get from "google developper console" 

	twig:	
		...
		...
		globals:
			searchVidForBot:"@search.vids"
		
	parameters:
		client_id: YOUR_CLIENT_ID
		client_secret: YOUR_CLIENT_SECRET


in security.yml, add the firewall 	


    	firewalls:
		...
		...
		ym:
			pattern: ^/Ymanager
			anonymous: true

in routing.yml, add the bundle ressource			
			
	mara_ymanager:
		resource: "@MaraYmanagerBundle/Resources/config/routing.yml"
		prefix:   /

		
- Copy the content of "Resources Web" directory in web/bundles
