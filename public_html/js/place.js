'use strict';
//u can link a busroute to a location that u could make a bustop(i dnt thunk the fact that bustops at a certain location makes it compulsory for u to call it a bustop, e.g busses could stop in from of shoprite, does that make shoprite a bustop, the fact is that buses wouldn't stop inside shop rite o, maybe just infront or adjacent to shoprite, so u could make shoprite a diff place, and the bustop infront or by d side of the place, so two places)
function Place(loc, type, info) {
    //type would determine the type of marker to be used and other info sha
    switch (type) {
        case 'BUSTOP':
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
    var infowindow = new google.maps.InfoWindow();
    var marker = new google.maps.Marker({
        map: map
    });
}

Place.prototype.remove = function () {

};