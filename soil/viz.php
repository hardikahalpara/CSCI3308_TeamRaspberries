<!DOCTYPE html>
<html>
<head>
<title>mapboxgl-jupyter viz</title>
<meta charset='UTF-8' />
<meta name='viewport'
      content='initial-scale=1,maximum-scale=1,user-scalable=no' />
<script type='text/javascript'
        src='https://api.tiles.mapbox.com/mapbox-gl-js/v0.42.2/mapbox-gl.js'></script>
<link type='text/css'
      href='https://api.tiles.mapbox.com/mapbox-gl-js/v0.42.2/mapbox-gl.css' rel='stylesheet' />
<!-- Bootstrap core CSS -->
<link href="/javascript/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<style type='text/css'>
    body { margin:0; padding:0; }
    .map { position:absolute; top:0; bottom:0; width:100%; }
    .legend {
        background-color: white;
        color: black;
        border-radius: 3px;
        bottom: 50px;
        width: 100px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.10);
        font: 12px/20px 'Helvetica Neue', Arial, Helvetica, sans-serif;
        padding: 12px;
        position: absolute;
        right: 10px;
        z-index: 1;
    }
    .legend h4 { margin: 0 0 10px; }
    .legend-title {
        margin: 6px;
        padding: 6px;
        font-weight: bold !important;
        font-size: 14px;
        font: 12px/20px 'Helvetica Neue', Arial, Helvetica, sans-serif;
    }
    .legend div span {
        border-radius: 50%;
        display: inline-block;
        height: 10px;
        margin-right: 5px;
        width: 10px;
    }
</style>

</head>
<body>

<div id='map' class='map'></div>
<div id='legend' class='legend'></div>

<script type='text/javascript'>

function calcCircleColorLegend(myColorStops, title) {
    //Calculate a legend element on a Mapbox GL Style Spec property function stops array
    var mytitle = document.createElement('div');
    mytitle.textContent = title;
    mytitle.className = 'legend-title'
    var legend = document.getElementById('legend');
    legend.appendChild(mytitle);

    for (p = 0; p < myColorStops.length; p++) {
        if (!!document.getElementById('legend-points-value-' + p)) {
            //update the legend if it already exists
            document.getElementById('legend-points-value-' + p).textContent = myColorStops[p][0];
            document.getElementById('legend-points-id-' + p).style.backgroundColor = myColorStops[p][1];
        } else {
            //create the legend if it doesn't yet exist
            var item = document.createElement('div');
            var key = document.createElement('span');
            key.className = 'legend-key';
            var value = document.createElement('span');

            key.id = 'legend-points-id-' + p;
            key.style.backgroundColor = myColorStops[p][1];
            value.id = 'legend-points-value-' + p;

            item.appendChild(key);
            item.appendChild(value);
            legend.appendChild(item);
            
            data = document.getElementById('legend-points-value-' + p)
            data.textContent = myColorStops[p][0];
        }
    }
}

function generateInterpolateExpression(propertyValue, stops) {
    var expression;
    if (propertyValue == 'zoom') {
        expression = ['interpolate', ['exponential', 1.2], ['zoom']]
    }
    else if (propertyValue == 'heatmap-density') {
        expression = ['interpolate', ['linear'], ['heatmap-density']]
    }
    else {
        expression = ['interpolate', ['linear'], ['get', propertyValue]]
    }

    for (var i=0; i<stops.length; i++) {
        expression.push(stops[i][0], stops[i][1])
    }
    return expression
}


function generateMatchExpression(propertyValue, stops, defaultValue) {
    var expression;
    expression = ['match', ['get', propertyValue]]
    for (var i=0; i<stops.length; i++) {
        expression.push(stops[i][0], stops[i][1])
    }
    expression.push(defaultValue)
    
    return expression
}


function generatePropertyExpression(expressionType, propertyValue, stops, defaultValue) {
    var expression;
    if (expressionType == 'match') {
        expression = generateMatchExpression(propertyValue, stops, defaultValue)
    }
    else {
        expression = generateInterpolateExpression(propertyValue, stops)
    }

    return expression
}


</script>

<!-- main map creation code, extended by mapboxgl/templates/circle.html -->
<script type="text/javascript">


    mapboxgl.accessToken = 'pk.eyJ1IjoiY29vcGVydDkyNSIsImEiOiJjamdmd3l6ZjIwOHFkMzNwNHc5MjRtbTd6In0.GdxOVpiAfQ2WgZgfF_FUbg';

    var map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/light-v9?optimize=true',
        center: [-105, 40],
        zoom: 9,
        pitch: 0,
        bearing: 0,
        transformRequest: (url, resourceType) => {
                    if ( url.slice(0,22) == 'https://api.mapbox.com' || 
                        url.slice(0,26) == 'https://a.tiles.mapbox.com' || 
                        url.slice(0,26) == 'https://b.tiles.mapbox.com' ||
                        url.slice(0,26) == 'https://c.tiles.mapbox.com') {
                        //Add PowerBI Plugin identifier for Mapbox API traffic
                        return {
                           url: [url.slice(0, url.indexOf("?")+1), "pluginName=PowerBI&", url.slice(url.indexOf("?")+1)].join('')
                         }
                     }
                     else {
                         //Do not transform URL for non Mapbox GET requests
                         return {url: url}
                     }
                }
    });

    

        map.addControl(new mapboxgl.NavigationControl());

    

    

    
        calcCircleColorLegend([[4.2, 'red']] , "userID");
    
    


    

    map.on('style.load', function() {
        
        map.addSource("data", {
            "type": "geojson",
            "data": "lat_lng_plot.geojson",
            "buffer": 1,
            "maxzoom": 14
        });

        map.addLayer({
            "id": "label",
            "source": "data",
            "type": "symbol",
            "maxzoom": 24,
            "minzoom": 0,
            "layout": {
                
                "text-size" : generateInterpolateExpression('zoom', [[0, 8],[22, 3* 8]] ),
                "text-offset": [0,-1]
            },
            "paint": {
                "text-halo-color": "white",
                "text-halo-width": generatePropertyExpression('interpolate', 'zoom', [[0,1], [18,5* 1]]),
                "text-color": "#131516"
            }
        }, "waterway-label" )
        
        map.addLayer({
            "id": "circle",
            "source": "data",
            "type": "circle",
            "maxzoom": 24,
            "minzoom": 0,
            "paint": {
                
                    "circle-color": generatePropertyExpression("interpolate", "userID", [[4.2, 'red']], "grey" ),
                    
                "circle-radius" : generatePropertyExpression('interpolate', 'zoom', [[0,5], [22,10 * 5]]),
                "circle-stroke-color": "grey",
                "circle-stroke-width": generatePropertyExpression('interpolate', 'zoom', [[0,0.0], [18,5* 0.0]]),
                "circle-opacity" : 1,
                "circle-stroke-opacity" : 1
            }
        }, "label");
        
        // Create a popup
        var popup = new mapboxgl.Popup({
            closeButton: false,
            closeOnClick: false
        });
        
        // Show the popup on mouseover
        map.on('mousemove', 'circle', function(e) {
            map.getCanvas().style.cursor = 'pointer';
            
            let f = e.features[0];
            let popup_html = '<div><li><b>Location</b>: ' + f.geometry.coordinates[0].toPrecision(6) + 
                ', ' + f.geometry.coordinates[1].toPrecision(6) + '</li>';

            for (key in f.properties) {
                popup_html += '<li><b> ' + key + '</b>: ' + f.properties[key] + ' </li>'
            }

            popup_html += '</div>'
            popup.setLngLat(e.features[0].geometry.coordinates)
                .setHTML(popup_html)
                .addTo(map);
        });

        map.on('mouseleave', 'circle', function() {
            map.getCanvas().style.cursor = '';
            popup.remove();
        });
        
        // Fly to on click
        map.on('click', 'circle', function(e) {
            map.easeTo({
                center: e.features[0].geometry.coordinates,
                zoom: map.getZoom() + 1
            });
        });
    });




</script>

</body>
</html>