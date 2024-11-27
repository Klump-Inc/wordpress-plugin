function syncProductsWithExternalServer() {
  if (confirm('Are you sure you want to sync products with the external server?')) {
	jQuery.ajax({
	  url: ajaxurl,
	  type: 'POST',
	  data: {
		action: 'klp_sync_products'
	  },
	  success: function(response) {
		alert('Products synced initiated successfully');
	  },
	  error: function(error) {
		alert('Error syncing products: ' + error.responseText);
	  }
	});
  }
}
