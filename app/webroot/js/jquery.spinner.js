jQuery.fn.spinner = function(options) {
    
    var options = jQuery.extend({
	'start'     : 0.0,
	'min'       : 0.0,
	'max'       : 1.0,
	'step'      : 0.0100,
	'precision' : 4,
	'change'    : null, 
	'up'        : null,
	'down'      : null
    }, options);

    return this.each( function() {

	var input = this;
	var last = Number(input.value);
	
	var input_change = function() {
	    var cur, val;

	    if(input.value !== '') {
		cur = Number(input.value);

		if(isNaN(cur)) {
		    var val = last;
		} else if(cur > options.max) {
		    var val = options.max;
		} else if(cur < options.min) {
		    var val = options.min;
		} else {
		    var val = cur;
		    last = cur;
		}
		
		input.value = val.toFixed(options.precision);

		if(options.change)
		    options.change.call(input);
	    }
	}

	var spin_up = function() {
	    if(!input.disabled) {
		input.value = (Number(input.value) + options.step);
		
		$(input).change();

		if(options.up)
		    options.up.call(input);
	    }
	};
    
	var spin_down = function() {
	    if(!input.disabled) {
		input.value = (Number(input.value) - options.step);

		$(input).change();
		
		if(options.down)
		    options.down.call(input);
	    }
	};

	var input_keypress = function(e) {
	    if(this.value !== '') {
		if(e.keyCode == 38 || e.keyCode == 107 || e.keyCode == 61 || e.keyCode == 187) { // Up pressed
		    spin_up();
		    return false;
		} else if(e.keyCode == 40 || e.keyCode == 109 || e.keyCode == 189) { // Down pressed
		    spin_down();
		    return false;
		}
	    }
	}

	$(this).change(input_change).keydown(input_keypress);
	
	if(this.value !== '')
	  this.value = Number(this.value).toFixed(options.precision);
	  
    });
};