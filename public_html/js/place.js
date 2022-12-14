'use strict';
//u can link a busroute to a location that u could make a bustop(i dnt thunk the fact that bustops at a certain location makes it compulsory for u to call it a bustop, e.g busses could stop in from of shoprite, does that make shoprite a bustop, the fact is that buses wouldn't stop inside shop rite o, maybe just infront or adjacent to shoprite, so u could make shoprite a diff place, and the bustop infront or by d side of the place, so two places)
function Place(info, options, onGetData, eventHandlers) {
    this._infowindow;
    this._marker;
    this._map = options.map;

    var icon, anchorPointY = -29, anchorPointX = 0;

    //type would determine the type of marker to be used and other info sha
    //dnt use lean names, like t instead of type, because i plan to use to the data generated by this app for other uses
    switch (info.type) {
        case 'BUSTOP':
            icon = 'http://maps.google.com/mapfiles/kml/shapes/placemark_circle_highlight.png';
            anchorPointY = -13;
            anchorPointX = -2;
            break;
        case 'STEP':
            break;
        case 'BAR':
            break;
        case 'CAFE':
            break;
        case 'HOTEL':
            break;
        case 'BUSINESS':
            break;
        case 'MARKET':
            break;
        case 'FOOD&DRINKS':
            break;
        case 'SCHOOL':
            break;
        case 'LIBRARY':
            break;
        case 'GOVT':
            break;
        case 'POSTOFFICE':
            break;
        case 'POSTSHOP':
            break;
        case 'CARPARK':
            break;
        case 'CARRENTAL':
            break;
        case 'PARK':
            break;
        case 'TOURISM':
            break;
        case 'GARDEN':
            break;
        case 'ESTATE':
            break;
        case 'BEACH':
            break;
        case 'TOLLGATE':
            break;
        case 'TUNNEL':
            break;
        case 'COURT':
            break;
        case 'KINGDOMHALL':
            break;
        case 'CHURCH':
            break;
        case 'MOSQUE':
            break;
        case 'CLUB':
            break;
        case 'SHOP':
            break;
        case 'SUPERMARKET':
            break;
        case 'SHOES':
            break;
        case 'FILLINGSTATION':
            break;
        case 'POLICESTATION':
            break;
        case 'BARRACK':
            break;
        case 'CAMP':
            break;
        case 'FIRESTATION':
            break;
        case 'AIRPORT':
            break;
        case 'HELLIPORT':
            break;
        case 'JETTY':
            break;
        case 'PORT':
            break;
        case 'GALLERY':
            break;
        case 'CINEMA':
            break;
        case 'MOVIERENTAL':
            break;
        case 'HOSPITAL':
            break;
        case 'PHARMACY':
            break;
        case 'GYM':
            break;
        case 'SPA':
            break;
        case 'MUSEUM':
            break;
        case 'STADIUM':
            break;
        case 'BANK':
            break;
        case 'INSURANCE':
            break;
        case 'LEGAL':
            break;
        case 'TEXTILE':
            break;
        case 'TAILOR':
            break;
        case 'PRINTING':
            break;
        case 'FOOD&BEVERAGE':
            break;
        case 'TRANSPORTCOMPANY':
            break;
        case 'FARM':
            break;
        case 'ATM':
            break;
        case 'VAULT':
            break;
        case 'CEMETRY':
            break;
        case 'POWERSTATION':
            break;
        case 'COMMUNITYCENTRE':
            break;

    }
    /*var marker = new Marker({
     map: map,
     position: loc,
     icon: {
     path: SQUARE_PIN,
     fillColor: '#00CCBB',
     fillOpacity: 1,
     strokeColor: '',
     strokeWeight: 0
     },
     map_icon_label: '<span class="map-icon map-icon-bus-station"></span>'
     });*/

    var addresses;
    if (info.address_components) {
        addresses += [
            (info.address_components[0] && info.address_components[0].short_name || ''),
            (info.address_components[1] && info.address_components[1].short_name || ''),
            (info.address_components[2] && info.address_components[2].short_name || '')
        ].join('<br/>');
    } else if (info.addresses) {
        addresses = info.addresses.reduce(function (addreses, address) {
            return addreses + (addreses ? '<br/>' : '') + address;
        }, '');
    }

    var names;
    if (Object.prototype.toString.call(info.names) === '[object Array]') {
        names = info.names.reduce(function (names, name) {
            return names + (names ? '<br/>' : '') + name;
        }, '');
    } else {
        names = String(info.names);
    }

    var content = document.createElement('div'), getDataElem;
    content.setAttribute('style', 'display: inline;');
    content.innerHTML = (info.icon ? '<img src="' + info.icon + '" width="16" height="16">' : '') + (names ? '<strong>' + names + '</strong>' : '') + (addresses ? '<br/><span>' + addresses + '</span>' : '') + (info.description ? '<hr style="margin:8px 0 8px 0;"><span style="color: #999;">' + info.description + '</span>' : '');

    var googleMaps = google.maps, infowindow = new googleMaps.InfoWindow({
        content: content//'<div style="display: inline;">' + (info.icon ? '<img src="' + info.icon + '" width="16" height="16">' : '') + (names ? '<strong>' + names + '</strong>' : '') + (addresses ? '<br/><span>' + addresses + '</span>' : '') + (info.description ? '<hr style="margin:8px 0 8px 0;"><span style="color: #999;">' + info.description + '</span>' : '') + (onGetData?'<a style="cursor:pointer;" id="__place_infowindow'+info.id+'">Get data</a>':'') +'</div>'
    }), marker = new googleMaps.Marker({
        position: options.loc,
        map: options.map,
        title: options.title,
        label: options.label,
        anchorPoint: new googleMaps.Point(anchorPointX, anchorPointY),
        icon: icon,
        visible: true,
        draggable: options.draggable
    });
    googleMaps.event.addListener(marker, 'click', function () {
        infowindow.open(options.map, marker);
        eventHandlers && eventHandlers.click && eventHandlers.click();
    });
    for (var eventHandler in eventHandlers) {
        if (eventHandler === 'click') {
            continue;
        }

        googleMaps.event.addListener(marker, eventHandler, eventHandlers[eventHandler]);
    }

    if (onGetData) {
        getDataElem = document.createElement('a');
        getDataElem.setAttribute('style', 'cursor:pointer;display:block;text-align:right;margin-top:5px;');
        getDataElem.textContent = 'Get data';
        content.appendChild(getDataElem);

        getDataElem.addEventListener('click', function () {
            if (onGetData({names: info.address_components || info.names, id: info.id})) {
                infowindow.close();
            }
        });
    }


    this._marker = marker;
    this._infowindow = infowindow;
}
Place.prototype.showInfo = function () {
    this._infowindow.open(this._map, this._marker);
};
Place.prototype.hide = function () {
//the marker
    this._marker.setVisible(false);

//remove the info window
    this._infowindow.close();
};
Place.prototype.show = function () {
//the marker
    this._marker.setVisible(true);
};
Place.prototype.remove = function () {
//the marker
    this._marker.setMap(null);

//remove the info window
    this._infowindow.close();
};
Place.prototype.getMarker = function () {
    return this._marker;
};