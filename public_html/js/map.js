'use strict';

//exports
var map = {};

window.onload = function () {
    if (!navigator.geolocation) {//new Dialog('', '<input type="text">', '<button z-dialog-send>send</button>', {send:['click', function(){alert('Send');return true;}]})
        new Dialog('Upgrade your browser', 'Bustops is not supported by your browser, please upgrade your browser to the latest version, or use chrome');
        return;
    }

    var config = {
        minAccuracy: 150,
        zoom: 16, //15
        loadingTimeout: 10000
    };
    var vars = {
        loadStart: Date.now(),
        map: null,
        myMarker: null,
        googleMaps: null,
        myLoc: {}, //{lat: -34.397, lng: 150.644}
        myPos: {} //full position information returned by geolocation api
    };

    //init
    //get google.maps
    (function _() {
        if ((Date.now() - vars.loadStart) > config.loadingTimeout) {
            alert('The page is taking too long to load. Check your internet connection, then click ok to refresh');
            location.reload();
            return;
        }

        setTimeout(function () {
            //make sure all the required libraries are loaded
            if (typeof google !== 'object' || !google.maps || typeof $ !== 'function' || typeof Parsley !== 'object') {
                return _();
            }

            Parsley.addMessages('en', {
                dimensions: 'The display picture dimensions should be a minimum of 100px by 100px'
            });

            vars.googleMaps = google.maps;
            init();
        }, 100);
    })();

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
            new Dialog('Low GPS accuracy', 'Your gps accuracy is too low, please select your current location from the map, or switch to a device with a higher GPS accuracy');
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
        new Dialog('Turn on location', 'Problem getting your current location, please check if your location is switched on');
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
            title: 'me',
            icon: 'http://maps.google.com/mapfiles/kml/paddle/blu-circle_maps.png'
        }));

        vars.myMarker.setPosition(vars.myLoc);
    }

    function updateLocationsMarkers(locs) {
        //lol did we really do this?
        //to prevent the location marker from having d blinking effect, first save the reference to the old marker, put the new marker, then delete the old marker
    }

    function initMap(latlng) {
        if (vars.map) {
            return;
        }

        //eventually wen our map is very complete, we would hide all google's map styles so that users dnt confuse it with ours
        //on the users end that's whr i need to hide all google map locations, i'll leave d locations on d admin part so that d admins can use them as landmarks to easily identify places
        var styles/* = [
         {
         featureType: "poi",
         elementType: "labels",
         stylers: [
         {visibility: "off"}
         ]
         },{
         featureType: "transit",
         elementType: "labels",
         stylers: [
         {visibility: "off"}
         ]
         },{
         featureType: "transit",
         elementType: "labels",
         stylers: [
         {visibility: "off"}
         ]
         }
         ]*/;

        vars.map = new vars.googleMaps.Map(document.getElementById('map'), {
            center: latlng,
            zoom: config.zoom, //set other map options, i.e wen dnt want default controls to show on d map, and we want to set handlers for when d person clicks or scrolls the map
            mapTypeControl: false,
            streetViewControl: false,
            styles: styles
                    //disableDefaultUI: true
        });

        vars.googleMaps.event.addListener(vars.map, 'bounds_changed', onMapbounds_changed);
        vars.googleMaps.event.addListener(vars.map, 'center_changed', onMapcenter_changed);
        vars.googleMaps.event.addListener(vars.map, 'drag', onMapdrag);
        vars.googleMaps.event.addListener(vars.map, 'dragend', onMapdragend);
        vars.googleMaps.event.addListener(vars.map, 'dragstart', onMapdragstart);
        vars.googleMaps.event.addListener(vars.map, 'heading_changed', onMapheading_changed);
        vars.googleMaps.event.addListener(vars.map, 'idle', onMapidle);
        vars.googleMaps.event.addListener(vars.map, 'maptypeid_changed', onMapmaptypeid_changed);
        vars.googleMaps.event.addListener(vars.map, 'mousemove', onMapmousemove);
        vars.googleMaps.event.addListener(vars.map, 'mouseout', onMapmouseout);
        vars.googleMaps.event.addListener(vars.map, 'mouseover', onMapmouseover);
        vars.googleMaps.event.addListener(vars.map, 'mousedown', onMapmousedown);
        vars.googleMaps.event.addListener(vars.map, 'mouseup', onMapmouseup);
        vars.googleMaps.event.addListener(vars.map, 'click', onMapclick);
        vars.googleMaps.event.addListener(vars.map, 'dblclick', onMapdblclick);
        vars.googleMaps.event.addListener(vars.map, 'projection_changed', onMapprojection_changed);
        vars.googleMaps.event.addListener(vars.map, 'resize', onMapresize);
        vars.googleMaps.event.addListener(vars.map, 'rightclick', onMaprightclick);
        vars.googleMaps.event.addListener(vars.map, 'tilesloaded', onMaptilesloaded);
        vars.googleMaps.event.addListener(vars.map, 'tilt_changed', onMaptilt_changed);
        vars.googleMaps.event.addListener(vars.map, 'zoom_changed', onMapzoom_changed);

        var input = document.createElement("input"), icoSpan = document.createElement("span"), ico = document.createElement("img");
        input.setAttribute('type', 'text');
        input.setAttribute('placeholder', 'Enter a location');
        input.setAttribute('class', 'controls');
        input.setAttribute('style', 'margin-left:2px;');
        input.setAttribute('autocomplete', 'off');
        icoSpan.setAttribute('style', 'width:29px;height:29px;padding:.3% 4px');
        icoSpan.classList.add('controls');
        ico.setAttribute('height', '20px');
        ico.setAttribute('src', 'img/map-search.png');
        ico.setAttribute('alt', 'search map');
        icoSpan.appendChild(ico);

        vars.map.controls[vars.googleMaps.ControlPosition.TOP_LEFT].push(icoSpan);
        vars.map.controls[vars.googleMaps.ControlPosition.TOP_LEFT].push(input);

        var autocomplete = new vars.googleMaps.places.Autocomplete(input);
        autocomplete.bindTo('bounds', vars.map);

        var infowindow = new vars.googleMaps.InfoWindow();
        var marker = new vars.googleMaps.Marker({
            map: vars.map
        });
        vars.googleMaps.event.addListener(marker, 'click', function () {
            infowindow.open(vars.map, marker);
        });

        vars.googleMaps.event.addListener(autocomplete, 'place_changed', function () {
            infowindow.close();
            var place = autocomplete.getPlace();
            if (!place.geometry) {
                return;
            }

            if (place.geometry.viewport) {
                vars.map.fitBounds(place.geometry.viewport);
            } else {
                vars.map.setCenter(place.geometry.location);
                //vars.map.setZoom(17);
            }

            // Set the position of the marker using the place ID and location.
            marker.setPlace(/** @type {!google.maps.Place} */ ({
                placeId: place.place_id,
                location: place.geometry.location
            }));
            marker.setVisible(true);

            infowindow.setContent('<div><strong>' + place.name + '</strong><br>' +
                    // 'Place ID: ' + place.place_id + '<br>' +
                    place.formatted_address +
                    '</div>');
            infowindow.open(vars.map, marker);
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
    function onMapclick(e) {//console.log(e);
        /*
         * stop()	
         Return Value:  None
         Prevents this event from propagating further.
         */
        var lat = e.latLng.lat(), lng = e.latLng.lng();
        console.log({t: e.latLng.lat(), n: e.latLng.lng()});
        //wen thinking of fields to send to server for a location look at d json google returns for a location, we'll nt only save d coordinates of a place, we'll also save other details to make searching and bounds searching easily, either strict bounds or biased bounds searching
        new Dialog('<div class="dh">Save location</div>', '<form z-dialog-save_location_form><div class="form-group"><div class="input-group"><span class="input-group-addon"><i class="glyphicon glyphicon-map-marker"></i></span><select data-parsley-required class="form-control" name="type"><option disabled selected>Location type</option><option value="BUSTOP">Bustop</option><option value="MARKET"><img alt="icon">Market</option><option value="SHOP">Shop</option><option value="BANK">Bank</option><option value="ATM">ATM</option><option value="GOVT">Government</option></select></div></div><div class="form-group"><div class="input-group"><span class="input-group-addon"><i class="glyphicon glyphicon-home"></i></span><input data-parsley-required name="name" type="text" class="form-control" placeholder="Location name"></div></div><div class="form-group"><input data-parsley-required class="form-control" multiple name="pictures[]" type="file" accept="image/jpeg,image/jpg,image/png" data-parsley-filemaxmegabytes="2" data-parsley-trigger="change" data-parsley-dimensions="true" data-parsley-dimensions-options="{\'min_width\': \'100\',\'min_height\': \'100\'}" data-parsley-filemimetypes="image/jpeg,image/jpg,image/png"></div><div class="form-group"><textarea data-parsley-required class="form-control" rows="5" name="description" placeholder="Description"></textarea></div><div class="form-group"><textarea class="form-control" rows="5" name="extra_info" placeholder="Extra information"></textarea></div></form>', '<button type="button" z-dialog-cancel class="btn btn-default">Cancel</button><button type="button" z-dialog-send class="btn btn-primary">Save</button>', {send: ['click', function (e) {
                    //trigger form submission to invoke parsley validation
                    $('#' + e['z-dialog'].id + 'z-dialog-save_location_form').trigger('submit');
                }], cancel: ['click', function () {
                    //close the dialog by returning true
                    return true;
                }]}, true, function (e) {
            var zDialog = e;
            //On Dialog Create
            $('#' + e.id + 'z-dialog-save_location_form').parsley().on('form:submit', function (e) {
                //after sending ajax request and req is success create the pin and close the dialog
                var elemPrefix = zDialog.id + 'z-dialog-', form = document.getElementById(elemPrefix + 'save_location_form'), formElements = form.elements,
                        type = formElements['type'].value, name = formElements['name'].value, description = formElements['description'].value, extra_info = formElements['extra_info'].value,
                        data = {name: name, type: type, description: description, extra_info: extra_info}, formData = new FormData(form);

                //Test value for admin id, admin_id is supposed to be saved to seesion on login
                formData.append('admin_id', '2');
                formData.append('lat', lat);
                formData.append('lng', lng);

                //Make the button change color and display saving
                
                //u can save everything with php, from php to mongo. i dnt think that needs nodejs
                $.ajax({
                    type: "POST",
                    url: "save_location.php",
                    data: formData,
                    dataType: 'JSON',
                    /*** Options to tell JQuery not to process data or worry about content-type ****/
                    cache: false,
                    contentType: false,
                    processData: false,
                    /****************************************/
                    success: function (response) {
                        if (!response.err) {
                            //Display submitted

                            //on success
                            Place(data, {map: vars.map, loc: {lat: lat, lng: lng}, title: 'test'});

                            zDialog.close();
                        } else {
                            var msg;

                            switch (response.err.error) {
                                case 'VALIDATION':
                                    msg = response.err.msg.message;
                                    formElements[response.err.msg.field] && formElements[response.err.msg.field].classList.add('parsley-error');
                                    break;
                                default:
                                    msg = response.err.msg.message;
                                    break;
                            }

                            //display msg
                        }
                    }, error: function () {
//say problem saving pls check ur network and retry
//check d text on d save button to retry and change the class to btn-danger or sth, remember to change it back wen d dialog is closed!!!

                    }, complete: function () {

                    }
                });

                return false;
            });
        });

        console.log('click');
    }
    function onMapdblclick() {
        console.log('dblclick');
    }
    function onMapmousemove() {
        console.log('mousemove');
    }
    function onMapmouseout() {
        console.log('mouseout');
    }
    function onMapmousedown() {
        console.log('mousedown');
    }
    function onMapmouseup() {
        console.log('mouseup');
    }
    function onMapmouseover() {
        console.log('mouseover');
    }
    function onMapdrag() {
        console.log('drag');
    }
    function onMapdragend() {
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
        new Place(vars.myLoc, vars.map, 'BUSTOP', 'info');
        console.log('tilesloaded');
    }
    function onMaptilt_changed() {
        console.log('tilt_changed');
    }
    function onMapzoom_changed() {
        console.log('zoom_changed');
    }
};