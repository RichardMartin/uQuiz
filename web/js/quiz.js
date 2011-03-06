window.applicationCache.addEventListener('error', function(error) {
	type = error.type;
	message = '';
	message += 'event: ' + type;
	message += ', item: ' + error;

	alert('Error: ' + message);
	console.log(error);
});
