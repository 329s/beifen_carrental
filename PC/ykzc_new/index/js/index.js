$(function(){
    $('.zoom').click(function(){
        $('#mapModal').modal('toggle');
        setTimeout(function(){
            theLocation('金华',[1]);
        },200)
    })

    $.when(wait()).done(function(base){
        var geo;
        base = base;
        initialize(geo,base,[{id:'map',zoom:13},{id:'map2',zoom:12},{id:'map-search',zoom:12}]);
    });
})
//'use strict';
//
//var d = require('../');
//
//module.exports = function (t, a) {
//	var o = Object.defineProperties({}, t({
//		bar: d(function () { return this === o; }),
//		bar2: d(function () { return this; })
//	}));
//
//	a.deep([(o.bar)(), (o.bar2)()], [true, o]);
//};
//                                   
//                                                                                 