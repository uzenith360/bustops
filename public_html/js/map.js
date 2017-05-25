'use strict';

//exports
var map = {};

$(function () {
    if (!navigator.geolocation) {
        alert('Bustops is not supported by your browser, please upgrade your browser to the latest version, or use chrome');
        return;
    }

    var config = {
        minAccuracy: 150,
        zoom: 16//15
    };
    var vars = {
        map: null,
        myMarker: null,
        googleMaps: null,
        myLoc: {}, //{lat: -34.397, lng: 150.644}
        myPos: {} //full position information returned by geolocation api
    };

    //init
    //get google.maps
    $(function _() {
        setTimeout(function () {
            if (typeof google !== 'object' || !google.maps) {
                return _();
            }

            vars.googleMaps = google.maps;
            init();
        }, 100);
    });

    function init() {
        watchMyLocation();
    }


    function watchMyLocation() {
        navigator.geolocation.watchPosition(myLocSuccess, myLocError, {enableHighAccuracy: true, maximumAge: 30000, timeout: 27000});
    }
    function myLocSuccess(pos) {
        if (pos.coords.latitude === vars.myLoc.lat && pos.coords.longitude === vars.myLoc.lng) {
            return;
        }

        //if the accuracy is too low, info the person that he's gps accuracy is low and he should select he's current position
        if (pos.coords.accuracy > config.minAccuracy) {
            alert('Your gps accuracy is too low, please select your current location from the map');
        }

        onMyLocationChange(pos);
    }
    function myLocError(err) {
        //maybe on error, if google maps has nt initialised , check server or local storage and get the last location d user was and display it in the map, or if u hv nt used d app bfr, then it'll use ur ip address to determine ur location and display that location, then also tell the user to turn on location or select hes location on d map

        console.error('Get location err: ' + JSON.stringify(err));

        switch (err.code) {
            case err.TIMEOUT:
                //"Timeout!";
                break;
        }
        //dnt refresh map, inform users that thrs a problem getting he's current location, either he should switch on location e.t.c
        alert('Problem getting your current location, please check if your location is switched on');
    }

    function onMyLocationChange(pos) {
        vars.myPos = pos;
        //maybe if u are moving fast(Max/min speed), i can adjust(reduce) the maximum age of the watchPosition and if u slow down, i'll adjust(increase) the maximum age again
        //i dnt kw wht to do with altitude infomation, u dey fly ni, go use another app :-D
        vars.myLoc = {lat: pos.coords.latitude, lng: pos.coords.longitude};

        updateMyMarker();
    }

    function updateMyMarker() {
        !vars.map && initMap(vars.myLoc);

        //lol did we really do this?
        //to prevent the location marker from having d blinking effect, first save the reference to the old marker, put the new marker, then delete the old marker
        !vars.myMarker && (vars.myMarker = new vars.googleMaps.Marker({
            position: vars.myLoc,
            map: vars.map,
            title: 'me'
        }));

        vars.myMarker.setPosition(vars.myLoc);
    }

    function updateLocationsMarkers(locs) {
        //lol did we really do this?
        //to prevent the location marker from having d blinking effect, first save the reference to the old marker, put the new marker, then delete the old marker
    }

    function initMap(latlng) {
        vars.map = new vars.googleMaps.Map(document.getElementById('map'), {
            center: latlng,
            zoom: config.zoom, //set other map options, i.e wen dnt want default controls to show on d map, and we want to set handlers for when d person clicks or scrolls the map
            disableDefaultUI: true,
            bounds_changed:onMapbounds_changed,
            center_changed:onMapcenter_changed,
            click:onMapclick,
            dblclick:onMapdblclick,
            drag:onMapdrag,
            dragend:onMapdragstart,
            heading_changed:onMapheading_changed,
            idle:onMapidle,
            maptypeid_changed:onMapmaptypeid_changed,
            mousemove:onMapmousemove,
            mouseout:onMapmouseout,
            mouseover:onMapmouseover,
            projection_changed:onMapprojection_changed,
            resize:onMapresize,
            rightclick:onMaprightclick,
            tilesloaded:onMaptilesloaded,
            tilt_changed:onMaptilt_changed,
            zoom_changed:onMapzoom_changed
        });

        //also request to get nearby locations from server and display
    }

    //Map Event handlers
    //U can trigger events: google.maps.event.trigger(map, 'resize')
    function onMapbounds_changed() {
        console.log('bounds_changed');
    }
    function onMapcenter_changed() {
        console.log('center_changed');
    }
    function onMapclick() {
        console.log('click');
    }
    function onMapdblclick() {
        console.log('dblclick');
    }
    function onMapdrag() {
        console.log('drag');
    }
    function onMapdragEnd() {
        console.log('dragEnd');
    }
    function onMapdragstart() {
        console.log('dragstart');
    }
    function onMapheading_changed() {
        console.log('heading_changed');
    }
    function onMapidle() {
        console.log('idle');
    }
    function onMapmaptypeid_changed() {
        console.log('maptypeid_changed');
    }
    function onMapmousemove() {
        console.log('mousemove');
    }
    function onMapmouseout() {
        console.log('mouseout');
    }
    function onMapmouseover() {
        console.log('mouseover');
    }
    function onMapprojection_changed() {
        console.log('projection_changed');
    }
    function onMapresize() {
        /*
         * Developers should trigger this event on the map when the div changes size: google.maps.event.trigger(map, 'resize') .
         */
        console.log('resize');
    }
    function onMaprightclick() {
        console.log('rightclick');
    }
    function onMaptilesloaded() {
        console.log('tilesloaded');
    }
    function onMaptilt_changed() {
        console.log('tilt_changed');
    }
    function onMapzoom_changed() {
        console.log('zoom_changed');
    }
});