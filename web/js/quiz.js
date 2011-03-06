window.applicationCache.addEventListener('error', function(error) {
	var message = '';
	for (var name in error) {
		message += name + ': ' + error[name] + '; '
	}

	console.log(message, error);
}, false);
