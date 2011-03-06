window.applicationCache.addEventListener('error', function(error) {
	var message = '';
	for (var name in error) {
		message += name + ': ' + error[name] + '; '
	}

	alert('Error: ' + message);
	console.log(message, error);
});
