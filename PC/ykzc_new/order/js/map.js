/**
 * Created by Administrator on 2017/4/21.
 */
  var mp = [];
  var base;
  //根据ip获取默认城市
  //地图初始化
  function initialize(base){
      var city = $('.takeArea').val() || $('.returnArea').val() || base;
      mp[0] = new BMap.Map('map');
      mp[0].centerAndZoom(city, 12);
      mp[0].addControl(new BMap.NavigationControl({anchor: BMAP_ANCHOR_TOP_LEFT, type: BMAP_NAVIGATION_CONTROL_SMALL}));
      mp[0].enableScrollWheelZoom();

      mp[1] = new BMap.Map('map-search');
      mp[1].centerAndZoom(city, 12);
      mp[1].addControl(new BMap.NavigationControl({anchor: BMAP_ANCHOR_TOP_LEFT, type: BMAP_NAVIGATION_CONTROL_SMALL}));
      mp[1].enableScrollWheelZoom();

      //标注
      marker(mp[0]);
      marker(mp[1]);
  }

  $.when(wait()).done(function(base){
      initialize(base);
  });

// 百度地图API功能


//


