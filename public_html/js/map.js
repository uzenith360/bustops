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
        zoom: 15
    };
    var vars = {
        googleMaps: null,
        myLoc: null, //{lat: -34.397, lng: 150.644}
        myPos: null,//full position information returned by geolocation api
        mapElem :document.getElementById('map')
    };

    //init
    //get googleMaps
    $(function _() {
        setTimeout(function () {
            if (!(google && google.maps)) {
                return _();
            }

            vars.googleMaps = google.maps;
            init();
        }, 100);
    });

    function init() {
        console.log('Init');
        getMyLoc();
    }
    function getMyLoc() {
        navigator.geolocation.watchPosition(getMyLocSuccess, getMyLocError, {enableHighAccuracy: true, maximumAge: 30000, timeout: 27000});
    }
    function getMyLocSuccess(pos) {
        //if the accuracy is too low, info the person that he's gps accuracy is low and he should select he's current position
        if (pos.coords.accuracy <= vars.minAccuracy) {
            alert('You gps accuracy is too low, please select your current location from the map');
        }

        vars.myPos = pos;
        //maybe if u are moving fast(Max/min speed), i can adjust(reduce) the maximum age of the watchPosition and if u slow down, i'll adjust(increase) the maximum age again
        //i dnt kw wht to do with altitude infomation, u dey fly ni, go use another app :-D
        refreshMap(vars.myLoc = {lat: pos.coords.latitude, lng: pos.coords.longitude});
    }
    function getMyLocError(err) {
        console.error('Get location err: ' + JSON.stringify(err));

        switch (err.code) {
            case err.TIMEOUT:
                //"Timeout!";
                break;
        }
        //dnt refresh map, inform users that thrs a problem getting he's current location, either he should switch on location e.t.c
        alert('Problem getting your current location, please check if your location is switched on');
    }

    function refreshMap(locs) {
        var map = new vars.googleMaps.Map(vars.mapElem, {
            center: locs,
            zoom: config.zoom
        }), marker = new vars.googleMaps.Marker({
            position: locs,
            map: map
        });
    }


});