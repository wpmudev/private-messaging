jQuery.fn.serializeAssoc = function() {
    var data = {};
    jQuery.each( this.serializeArray(), function( key, obj ) {
        var a = obj.name.match(/(.*?)\[(.*?)\]/);
        if(a !== null)
        {
            var subName = a[1];
            var subKey = a[2];
            if( !data[subName] ) data[subName] = [ ];
            if( data[subName][subKey] ) {
                if( jQuery.isArray( data[subName][subKey] ) ) {
                    data[subName][subKey].push( obj.value );
                } else {
                    data[subName][subKey] = [ ];
                    data[subName][subKey].push( obj.value );
                }
            } else {
                data[subName][subKey] = obj.value;
            }
        } else {
            if( data[obj.name] ) {
                if( jQuery.isArray( data[obj.name] ) ) {
                    data[obj.name].push( obj.value );
                } else {
                    data[obj.name] = [ ];
                    data[obj.name].push( obj.value );
                }
            } else {
                data[obj.name] = obj.value;
            }
        }
    });
    return data;
};