(function() {
    
    // Log current resultset in firebug
    if(window.console) {
	jQuery.fn.log = function() {
	    console.log(this);
	};
    }

    // Pluck a property from each item in resultset
    jQuery.fn.pluck = function(attr) {
	return this.map( function(n,i) {
	    return $(i).attr(attr)
	});
    };

    jQuery.fn.to_object = function(options) {
	var rv = {};

	this.find(':input').each( function() {
	    rv[this.attr('name')] = this.val();
	});

	return rv;
    };

    jQuery.fn.ajaxify = function(options) {
	options = jQuery.extend({
	    'beforeSend' : function(xhr) {
		this.find(':input').attr('disabled', 'disabled');
	    },
	    'complete'   : function(xhr, status) {
		this.find(':input').attr('disabled', '');
	    },
	    'success'    : function(data, status) {}, 
	    'error'      : function(xhr, status, err) {}
	}, options);

	this.each( function() {
	    this.submit( function() {
		$.ajax({
		    'url'        : this.attr('action'),
		    'type'       : this.attr('method'),
		    'data'       : this.serialize(),
		    'beforeSend' : function(xhr) { options['beforeSend'].call(this, xhr) },
		    'complete'   : function(xhr, status) { options['complete'].call(this, xhr, status) },
		    'success'    : function(xhr, status) { options['success'].call(this, xhr, status) },
		    'error'      : function(xhr, status, err) { options['error'].call(this, xhr, status, err) }				   
		});
	    });
	});
    };

    jQuery.fn.stripe = function(even_class, odd_class) {
	this.each( function(i) {
	    i%2 ? $(this).removeClass(even_class).addClass(odd_class) :  $(this).removeClass(odd_class).addClass(even_class);
	});
    };

    jQuery.fn.back = function(url) {
	this.click( function() {
	    if(url)
		window.location = url;
	    else
		window.history.go(-1);

	    return false;
	});
    };

    jQuery.fn.paginate = function(callback) {
	return this.each( function() {
	    var _this = this;

	    $(_this).find('a.pagination').click( function() {
		$.get(this.href, null, 
		      function(data) {
			  var ele = $(data).paginate(callback);
			  $(_this).replaceWith(ele);
			  if(callback)
			      callback(ele);
		      }
		 );
		return false;
	    });
	});
    }
    
})();