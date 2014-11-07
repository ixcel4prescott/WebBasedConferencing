(function() {
   var formatters = {
     'phone'      : [/^\(?\s?(\d{3})\s?\)?\s?\-?\s?(\d{3})\s?\-?\s?(\d{4})$/,     '($1) $2-$3' ],
     'ssn'        : [/^(\d{3})\s?\-?\s?(\d{2})\s?\-?\s?(\d{3})$/,                 '$1-$2-$3'   ],
     'creditcard' : [/^(\d{4})\s?\-?\s?(\d{4})\s?\-?\s?(\d{4})\s?\-?\s?(\d{4})$/, '$1-$2-$3-$4']
   };

   jQuery.fn.formatWith = function(regex, replacement) {
     $(this).change(function() {
       this.value = this.value.replace(regex, replacement);
     }).change();
   };

   jQuery.fn.format = function(formatter) {
     if(formatter in formatters)
       jQuery.fn.formatWith.call(this, formatters[formatter][0], formatters[formatter][1]);
   };
})();