'use strict';

//exports
var map = {};

window.onload = function () {
    if (!navigator.geolocation) {//new Dialog('', '<input type="text">', '<button z-dialog-send>send</button>', {send:['click', function(){alert('Send');return true;}]})
        new Dialog('Location not supported by your browser', 'Bustops needs location to work properly, please upgrade your browser to the latest version, or use chrome');
        return;
    }

    var config = {
        //minAccuracy: 150,
        zoom: 16, //15
        loadingTimeout: 10000,
        defaultLocation: {lat: 6.5179, lng: 3.3712}, //yabatech coordinates
        locMaxAgeTime: 30000,
        maxLocPrecision: 150, //reduce this value too if map still jumps, if map doesnt jump again, but delays for long, then jumps to a long distance, then increase it. Keep adjusting until we get a fine tuned value
        accuracyCutoffPoint: 600
    };
    var vars = {
        loadStart: Date.now(),
        map: null,
        myMarker: null,
        googleMaps: null,
        myLoc: {}, //{lat: -34.397, lng: 150.644}
        myPos: {}, //full position information returned by geolocation api
        myHeading: {},
        acquiredCurrentLoc: false,
        accuracy: null,
        accuracyInfowindowElem: null,
        tripMode: false,
        lastLocTimestamp: null,
        watchingMyLoc: false,
        locationWatch: null
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
        if (vars.map) {
            return;
        }

        //first init map with last location stored in localStorage, also cheack server and update the vars.loc/vars.pos if the location from server is diff, means wen location changes, i shoud tell d localStorage/server
        //i think wen d script gets the users current location, it should jst put a marker thr, save current location to server and wait till the person requests a route or clicks go to my current location
        //u should setCenter of d map, because user might be current looking at sth on d map, then u just change am for d person?!
        var lastLocationFrmStorage = getLastLocation(function (pos) {
            if (!vars.acquiredCurrentLoc && locationIsDiff(pos) && vars.myLoc.lat === config.defaultLocation.lat && vars.myLoc.lng === config.defaultLocation.lng) {
                vars.myPos = pos;
                vars.map.panTo(vars.myLoc = {lat: pos.coords.latitude, lng: pos.coords.longitude});
            }
        });

        if (!vars.myLoc.lat && locationIsDiff(lastLocationFrmStorage)) {
            vars.myPos = lastLocationFrmStorage;
            vars.myLoc = {lat: lastLocationFrmStorage.coords.latitude, lng: lastLocationFrmStorage.coords.longitude};
        }

        initMap();
    }


    function watchMyLocation() {
        vars.locationWatch = navigator.geolocation.watchPosition(myLocSuccess, myLocError, {enableHighAccuracy: true/*, maximumAge: 30000, timeout: 27000*/});
    }
    function myLocSuccess(pos) {
        if (pos.coords.accuracy < config.accuracyCutoffPoint || !vars.acquiredCurrentLoc) {
            var newLoc = {lat: pos.coords.latitude, lng: pos.coords.longitude};

            //check d location change if its within reasonable limits
            if ((Date.now() - vars.lastLocTimestamp) >= config.locMaxAgeTime || getDistanceBtwPoints(vars.myLoc, newLoc) < vars.maxLocPrecision) {
                if (locationIsDiff(pos)) {
                    //if the accuracy is too low, info the person that he's location accuracy is low and he should select he's current position
                    /*if (pos.coords.accuracy > config.minAccuracy) {
                     new Dialog('Low location accuracy', 'Your location accuracy is too low, please select or search your current location from the map, or switch to a device with a better location accuracy');
                     }
                     */
                    vars.tripMode && vars.myLoc && updateHeading(vars.myLoc, newLoc);

                    vars.myLoc = newLoc;
                    onMyLocationChange(pos);
                }

                vars.lastLocTimestamp = Date.now();
            }

            //Heading doesnt return anything useful, so i dnt even use it for anything
            /*if (vars.myHeading !== pos.heading) {
             onheadingChanged(vars.myHeading = pos.heading);
             }*/
        }

        if (vars.accuracy !== pos.coords.accuracy) {
            onMyLocationAccuracyChange(vars.accuracy = pos.coords.accuracy);
        }

        //if the accuracy is very bad dnt call anymore event handlers, we cant trust d coordinates

    }
    function myLocError(err) {
        //maybe on error, if google maps has nt initialised , check server or local storage and get the last location d user was and display it in the map, or if u hv nt used d app bfr, then it'll use ur ip address to determine ur location and display that location, then also tell the user to turn on location or select hes location on d map

        console.error('Get location err: ' + JSON.stringify(err));

        var heading, body;

        switch (err.code) {
            case err.PERMISSION_DENIED:
                heading = 'Allow location';
                body = 'Permission denied, please allow this site to use your location';
                break;
            case err.POSITION_UNAVAILABLE:
                heading = 'No Location';
                body = 'Location unavailable, please turn on location or use a device with location support';
                break;
            case err.TIMEOUT:
            case err.UNKNOWN_ERROR:
                heading = 'Turn on location';
                body = 'Problem getting your current location, please check if your location is switched on';
                break;
        }
        //dnt refresh map, inform users that thrs a problem getting he's current location, either he should switch on location e.t.c
        new Dialog(heading, body);

        if (vars.acquiredCurrentLoc) {
            var iconMe = document.getElementById('iM');
            iconMe.classList.remove('my-location-blue');
            iconMe.classList.add('my-location-normal');
            iconMe.setAttribute('title', 'Go to my last reported location');
            vars.acquiredCurrentLoc = false;
        }
    }

    function onMyLocationAccuracyChange() {
        updateLocationAccuracy();
    }

    function onMyLocationChange(pos) {
        vars.myPos = pos;
        //maybe if u are moving fast(Max/min speed), i can adjust(reduce) the maximum age of the watchPosition and if u slow down, i'll adjust(increase) the maximum age again
        //i dnt kw wht to do with altitude infomation, u dey fly ni, go use another app :-D

        vars.tripMode && vars.map.panTo(vars.myLoc);

        updateMyMarker();

        //save info to server
        saveCurrentLocation();

        if (!vars.acquiredCurrentLoc) {
            vars.acquiredCurrentLoc = true;
            (function _() {
                if (!document.getElementById('iM')) {
                    return setTimeout(_, 5);
                }

                vars.myMarker.setMap(vars.map);

                var iconMe = document.getElementById('iM');
                iconMe.classList.remove('my-location-normal');
                iconMe.classList.add('my-location-blue');
                iconMe.setAttribute('title', 'Go to my current location');
            })();
        }
    }

    function updateHeading(frmLoc, toLoc) {
        var L = toLoc.lng - frmLoc.lng;
        var X = Math.cos(toLoc.lat) * Math.sin(L);
        var Y = Math.cos(frmLoc.lat) * Math.sin(toLoc.lat) - Math.sin(frmLoc.lat) * Math.cos(toLoc.lat) * Math.cos(L);
        var β = Math.atan2(X, Y);
        var rotate = (β > 0 ? β : (2 * Math.PI + β)) * 360 / (2 * Math.PI);

        document.getElementById('h').setAttribute('style', '-o-transform: rotate(' + rotate + 'deg);-moz-transform: rotate(' + rotate + 'deg);-ms-transform: rotate(' + rotate + 'deg);-webkit-transform: rotate(' + rotate + 'deg);transform: rotate(' + rotate + 'deg);');
    }

    function updateLocationAccuracy() {//accuracySpec
        var accuracy;
        if (vars.accuracy < 50) {
            accuracy = ['blu', '#0275d8', 'Excellent'];
        } else if (vars.accuracy < 100) {
            accuracy = ['grn', '#5cb85c', 'Good'];
        } else if (vars.accuracy < 200) {
            accuracy = ['orange', '#f0ad4e', 'Fair'];
        } else if (vars.accuracy < 350) {
            accuracy = ['ylw', '#FFFF00', 'Poor'];
        } else if (vars.accuracy < config.accuracyCutoffPoint) {
            accuracy = ['red', '#d9534f', 'Bad'];
        } else {
            accuracy = ['pink', '#C71585', 'Out of range'];
        }

        vars.myMarker.setIcon('http://maps.google.com/mapfiles/kml/paddle/' + accuracy[0] + '-circle_maps.png');
        !isNaN(vars.accuracy) && (vars.accuracyInfowindowElem.innerHTML = '<span>Location accuracy: ' + (vars.accuracy < 150 ? vars.accuracy.toLocaleString() + 'm' : '<span style="color:#d9534f;">' + vars.accuracy.toLocaleString() + 'm</span>') + '</span><br><span style="color:' + accuracy[1] + ';">' + accuracy[2] + '</span>' + (vars.accuracy < 150 ? '' : '<hr style="margin-top:6.5px;margin-bottom:6.5px;"><span>Switch to a device with better accuracy</span>'));
    }

    function updateMyMarker() {
        //lol did we really do this?
        //to prevent the location marker from having d blinking effect, first save the reference to the old marker, put the new marker, then delete the old marker
        if (!vars.myMarker) {
            vars.myMarker = new vars.googleMaps.Marker({
                position: vars.myLoc,
                //map: vars.map,
                title: 'me',
                icon: 'http://maps.google.com/mapfiles/kml/paddle/blu-circle_maps.png'/*,
                 anchorPoint: vars.googleMaps.Point(0, -29)*/
            });
            var contentContainer = document.createElement('div'), contentHeading = document.createElement('strong'), contentBody = document.createElement('div');

            vars.accuracyInfowindowElem = contentBody;

            contentHeading.innerHTML = 'You are here';
            contentContainer.appendChild(contentHeading);
            contentContainer.appendChild(contentBody);


            var infowindow = new vars.googleMaps.InfoWindow({
                content: contentContainer
            });

            vars.googleMaps.event.addListener(vars.myMarker, 'click', function () {
                infowindow.open(vars.map, vars.myMarker);
            });

            return;
        }

        vars.myMarker.setPosition(vars.myLoc);
    }

    function updateLocationsMarkers(locs) {
        //lol did we really do this?
        //to prevent the location marker from having d blinking effect, first save the reference to the old marker, put the new marker, then delete the old marker
    }

    function initMap() {
        //jst in case
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
            center: vars.myLoc.lat ? vars.myLoc : config.defaultLocation,
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

        var input = document.createElement("input"), icoSpan = document.createElement("span"), ico = document.createElement("img"), meCntrl = document.createElement("div"), icoMe = document.createElement("div"), icoMeBtn = document.createElement("button"), tripCntl = document.createElement("div"), tripCntlIcon = document.createElement("span"), dirCntl = document.createElement("div"), dir = document.createElement("img");
        input.setAttribute('type', 'text');
        input.setAttribute('placeholder', 'Enter a location');
        input.setAttribute('class', 'controls');
        input.setAttribute('style', 'margin-left:2px;');
        input.setAttribute('autocomplete', 'off');
        icoSpan.setAttribute('style', 'width:29px;height:29px;padding:.3% 4px');
        icoSpan.classList.add('controls');
        icoSpan.setAttribute('title', 'Search for a location');
        ico.setAttribute('height', '20px');
        ico.setAttribute('src', 'img/map-search.png');
        ico.setAttribute('alt', 'search map');
        icoSpan.appendChild(ico);
        meCntrl.setAttribute('style', 'margin-right:10px;');
        icoMeBtn.classList.add('btn');
        icoMeBtn.classList.add('my-location');
        icoMe.setAttribute('id', 'iM');
        icoMe.classList.add('my-location-icon-common');
        icoMe.classList.add('my-location-normal');
        icoMe.classList.add('my-location-cookie');
        icoMe.setAttribute('title', 'Go to my location');
        icoMeBtn.appendChild(icoMe);
        meCntrl.appendChild(icoMeBtn);
        tripCntl.setAttribute('id', 'tC');
        tripCntl.setAttribute('title', 'Trip mode');
        tripCntl.setAttribute('style', 'cursor:pointer;margin-right:10px;margin-bottom:10px;width: 28px; height: 27px;padding:6px 6px;background-color: #fff;border-radius: 2px;border: 1px solid transparent;box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);box-sizing: border-box;font-family: Roboto;font-size: 100%;font-weight: 300;');
        tripCntlIcon.setAttribute('id', 'tCI');
        tripCntlIcon.classList.add('glyphicon');
        tripCntlIcon.classList.add('glyphicon-record');
        tripCntl.appendChild(tripCntlIcon);
        dir.setAttribute('alt', 'heading');
        dir.setAttribute('src', 'img/heading.png');
        dir.setAttribute('id', 'h');
        dirCntl.setAttribute('title', 'Your heading');
        dirCntl.setAttribute('style', 'display:none;margin-right:10px;margin-bottom:10px;width: 28px; height: 27px;padding:4px 4px;background-color: #fff;border-radius: 2px;border: 1px solid transparent;box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);box-sizing: border-box;');
        dirCntl.appendChild(dir);

        meCntrl.addEventListener('click', function () {
            if (!vars.watchingMyLoc) {
                vars.watchingMyLoc = true;

                watchMyLocation();
                return;
            }

            if (vars.acquiredCurrentLoc) {
                vars.map.panTo(vars.myLoc);
                vars.map.setZoom(17);
            }
        });
        $(meCntrl).on('dblclick', function (e) {
            if (!vars.watchingMyLoc) {
                return;
            }

            //for some reason setting the watchid to null makes it not possible to watch location again
            vars.lastLocTimestamp = /*vars.locationWatch = */vars.accuracy = null;
            vars.watchingMyLoc = vars.acquiredCurrentLoc = false;
            vars.myLoc = vars.myPos = vars.myHeading = {};

            vars.tripMode && tripCntl.click();

            vars.myMarker.setMap(null);

            var iconMe = document.getElementById('iM');
            iconMe.classList.remove('my-location-blue');
            iconMe.classList.add('my-location-normal');
            iconMe.setAttribute('title', 'Go to my last reported location');

            navigator.geolocation.clearWatch(vars.locationWatch);
        });

        meCntrl.addEventListener('mousedown', function () {
            vars._eventsStuff_startMouseDownTime = Date.now();
        });
        meCntrl.addEventListener('mouseup', function () {
            (Date.now() - vars._eventsStuff_startMouseDownTime) >= 500 && $(meCntrl).dblclick();
        });

        tripCntl.addEventListener('click', function () {
            if (!vars.tripMode) {
                if (vars.acquiredCurrentLoc) {
                    //Move map to my location
                    vars.map.panTo(vars.myLoc);
                    setTimeout(function () {
                        //running this immediately after the previous panTo, causes andriod browser to freeze
                        vars.map.setZoom(17);
                    }, 0);

                    tripCntlIcon.setAttribute('style', 'color:#68A1E3;');
                    dirCntl.style.display = 'block';

                    vars.tripMode = true;
                } else {
                    meCntrl.click();
                }
            } else {
                tripCntlIcon.style.color = '';
                dirCntl.style.display = 'none';
                dir.setAttribute('style', '-o-transform: rotate(0deg);-moz-transform: rotate(0deg);-ms-transform: rotate(0deg);-webkit-transform: rotate(0deg);transform: rotate(0deg);');
                vars.tripMode = false;
            }
        });

        vars.map.controls[vars.googleMaps.ControlPosition.TOP_LEFT].push(icoSpan);
        vars.map.controls[vars.googleMaps.ControlPosition.TOP_LEFT].push(input);
        vars.map.controls[vars.googleMaps.ControlPosition.RIGHT_BOTTOM].push(meCntrl);
        vars.map.controls[vars.googleMaps.ControlPosition.RIGHT_BOTTOM].push(tripCntl);
        vars.map.controls[vars.googleMaps.ControlPosition.RIGHT_BOTTOM].push(dirCntl);

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
                vars.map.panTo(place.geometry.location);
                vars.map.setZoom(17);
            }

            //I dnt like the view in google maps link, so i use setPosition instead
            // Set the position of the marker using the place ID and location.
            /*marker.setPlace({
             placeId: place.place_id,
             location: place.geometry.location
             });*/
            marker.setPosition(place.geometry.location);
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
        //wen thinking of fields to send to server for a location look at d json google returns for a location, we'll nt only save d coordinates of a place, we'll also save other details to make searching and bounds searching easily, either strict bounds or biased bounds searching
        new Dialog('<div z-dialog-heading class="dh">Save location</div>', '<form z-dialog-save_location_form><div class="form-group"><div class="input-group"><span class="input-group-addon"><i class="glyphicon glyphicon-map-marker"></i></span><select data-parsley-required class="form-control" name="type"><option disabled selected>Location type</option><option value="BUSTOP">Bustop</option><option value="MARKET"><img alt="icon">Market</option><option value="SHOP">Shop</option><option value="BANK">Bank</option><option value="ATM">ATM</option><option value="GOVT">Government</option><option value="HOTEL">Hotel</option></select></div></div><div class="form-group"><div class="input-group"><span class="input-group-addon"><i class="glyphicon glyphicon-home"></i></span><input data-parsley-required name="names[]" type="text" class="form-control" placeholder="Location name"></div><div z-dialog-locations></div><p z-dialog-add_location class="addLocationInfo"><span class="glyphicon glyphicon-plus"></span> Add location name</p></div><div class="form-group"><input data-parsley-required class="form-control" multiple name="pictures[]" type="file" accept="image/jpeg,image/jpg,image/png" data-parsley-filemaxmegabytes="2" data-parsley-trigger="change" data-parsley-dimensions="true" data-parsley-dimensions-options="{\'min_width\': \'100\',\'min_height\': \'100\'}" data-parsley-filemimetypes="image/jpeg,image/jpg,image/png"></div><div class="form-group"><textarea data-parsley-required class="form-control" rows="2" name="addresses[]" placeholder="Address"></textarea><div z-dialog-addresses></div><p z-dialog-add_address class="addLocationInfo"><span class="glyphicon glyphicon-plus"></span> Add address</p></div><div class="form-group"><textarea class="form-control" rows="5" name="description" placeholder="Description"></textarea></div></form>', '<button type="button" z-dialog-cancel class="btn btn-default">Cancel</button><button type="button" z-dialog-send class="btn btn-primary">Save</button>', {send: ['click', function (e) {
                    //trigger form submission to invoke parsley validation
                    $('#' + e['z-dialog'].id + 'z-dialog-save_location_form').trigger('submit');
                }], cancel: ['click', function () {
                    //close the dialog by returning true
                    return true;
                }], add_location: ['click', function (e) {
                    $('#' + e['z-dialog'].id + 'z-dialog-locations').append('<div style="margin-top:5px;" class="input-group"><span class="input-group-addon"><i class="glyphicon glyphicon-home"></i></span><input data-parsley-required name="names[]" type="text" class="form-control" placeholder="Location name"></div>');
                }], add_address: ['click', function (e) {
                    $('#' + e['z-dialog'].id + 'z-dialog-addresses').append('<textarea style="margin-top:5px;" data-parsley-required class="form-control" rows="2" name="addresses[]" placeholder="Address"></textarea>');
                }]}, true, function (zDialog) {
            //On Dialog Create
            $('#' + zDialog.id + 'z-dialog-save_location_form').parsley().on('form:submit', function (e) {
                console.log(formElements);
                //after sending ajax request and req is success create the pin and close the dialog
                var elemPrefix = zDialog.id + 'z-dialog-', form = document.getElementById(elemPrefix + 'save_location_form'), formElements = form.elements,
                        type = formElements['type'].value, names = [], addresses = [], description = formElements['description'].value,
                        data, formData = new FormData(form), sendBtn = document.getElementById(elemPrefix + 'send'), heading = document.getElementById(elemPrefix + 'heading');

                for (var i = 0, list = formElements['names[]'], listLength = list.length; i < listLength; ++i) {
                    names.push(list[i].value);
                }
                for (var i = 0, list = formElements['addresses[]'], listLength = list.length; i < listLength; ++i) {
                    addresses.push(list[i].value);
                }

                data = {names: names, type: type, addresses: addresses, description: description};

                //Test value for admin id, admin_id is supposed to be saved to seesion on login
                formData.append('admin_id', '2');
                formData.append('lat', lat);
                formData.append('lng', lng);

                //Make the button change color and display saving
                sendBtn.classList.remove('btn-primary');
                sendBtn.classList.add('btn-warning');
                sendBtn.innerHTML = 'Saving';
                heading.innerHTML = 'Saving...';

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

                            /*
                             * No need to change the button class back to primary since were closing the dialog anyway
                             */
                            sendBtn.classList.remove('btn-warning');
                            sendBtn.classList.add('btn-success');
                            sendBtn.innerHTML = 'Success';
                            heading.innerHTML = 'Saved';

                            //on success
                            Place(data, {map: vars.map, loc: {lat: lat, lng: lng}, title: 'test'});

                            zDialog.close();
                        } else {
                            var field = formElements[response.err.msg.field];

                            switch (response.err.error) {
                                case 'VALIDATION':
                                    heading.innerHTML = 'Review some field(s)';
                                    break;
                                default:
                                    heading.innerHTML = 'Problem Saving, please try again';
                                    break;
                            }

                            sendBtn.classList.remove('btn-warning');
                            sendBtn.classList.add('btn-danger');
                            sendBtn.innerHTML = 'Try again';
                            field && $(field).parsley().addError('error', {message: response.err.msg.message});
                        }
                    }, error: function () {
//say problem saving pls check ur network and retry
//check d text on d save button to retry and change the class to btn-danger or sth, remember to change it back wen d dialog is closed!!!
                        sendBtn.classList.remove('btn-warning');
                        sendBtn.classList.add('btn-danger');
                        sendBtn.innerHTML = 'Try again';
                        heading.innerHTML = 'Try saving again';
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

    function saveCurrentLocation() {
        var pos = {
            coords: {
                accuracy: vars.myPos.coords.accuracy,
                altitude: vars.myPos.coords.altitude,
                altitudeAccuracy: vars.myPos.coords.altitudeAccuracy,
                heading: vars.myPos.coords.heading,
                latitude: vars.myPos.coords.latitude,
                longitude: vars.myPos.coords.longitude,
                speed: vars.myPos.coords.speed
            },
            timestamp: vars.myPos.coords.timestamp
        };

        saveCurrentLocationToServer(pos);
        saveCurrentLocationToLocalStorage(pos);
    }
    function saveCurrentLocationToServer(pos) {
        //it saves all the location info, nt jst latlng

    }
    function saveCurrentLocationToLocalStorage(pos) {
        //it saves all the location info, nt jst latlng
        typeof (Storage) !== "undefined" && localStorage.setItem("_lp", JSON.pruned(pos));
    }
    function getLastLocationFromLocalStorage() {
        try {
            return typeof (Storage) !== "undefined" ? JSON.parse(localStorage.getItem("_lp")) || {} : {};
        } catch (e) {
            return {};
        }
    }
    function getLastLocationFromLocalServer(cb) {

    }
    function getLastLocation(cb) {
        //Also make an ajax request and return the last location that server has and call cb with the lastlocation or {} on error
        getLastLocationFromLocalServer(cb);

        return getLastLocationFromLocalStorage();
    }
    function locationIsDiff(newPos) {
        if (!newPos.coords || isNaN(newPos.coords.latitude) || isNaN(newPos.coords.longitude)) {
            return null;
        }

        return !vars.myLoc.lat || vars.myPos.latitude !== newPos.coords.latitude || vars.myPos.longitude !== newPos.coords.longitude ? true : false;
    }
    //Heading doesnt return anything useful, so i dnt even use it for anything
    function onheadingChanged() {
        //alert('Heading changed: ' + vars.myHeading);
        console.log('Heading changed!');
    }

    function rad(x) {
        return x * Math.PI / 180;
    }
    function getDistanceBtwPoints(p1, p2) {
        //Haversine formula
        var R = 6378137; // Earth’s mean radius in meter
        var dLat = rad(p2.lat - p1.lat);
        var dLong = rad(p2.lng - p1.lng);
        var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(rad(p1.lat)) * Math.cos(rad(p2.lat)) *
                Math.sin(dLong / 2) * Math.sin(dLong / 2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        var d = R * c;
        return d; // returns the distance in meter
    }
};