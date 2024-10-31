"use strict";

var peya_geo = {
  mapb: null,
  maps: null,
  markerb: null,
  markers: null,
  include_shipping: true,
  billing: {
    name:"billing",
    autocomplete: null,
    input: "#billing_address_1",
    country: "#billing_country",
    city: "#billing_city",
    state:"#billing_state",
    postcode:"#billing_postcode",
    lat:"#billing_wc_lat",
    lng:"#billing_wc_lng",
    countryRestrictions: [],
  },
  shipping: {
    name:"shipping",
    autocomplete: null,
    input: "#shipping_address_1",
    country: "#shipping_country",
    city: "#shipping_city",
    state:"#shipping_state",
    postcode:"#shipping_postcode",
    lat:"#shipping_wc_lat",
    lng:"#shipping_wc_lng",
    countryRestrictions: [],
  },
  getCurrentObj:function(){
    if(jQuery('#ship-to-different-address-checkbox:checked').length > 0){
      return this.shipping;
    }else{
      return this.billing;
    }
  },
  init: function(){
    var self = this;
    var string1 = "true";
    if (string1.localeCompare(peya_geo_settings.billing_only)== 0){
      self.include_shipping = false;
    }
    jQuery(document).ready(function(){
      self.initAutoCompleteSection(self.billing);
      if (self.include_shipping){
        self.initAutoCompleteSection(self.shipping);
      }
      self.initMap();
    });
  },

  initMap: function(){
    var self = this;

    jQuery("<p id='wc-peya-map-wrapper'><div id='wc-peya-map-b' style='height: 400px;' ></div></p>").insertAfter("#billing_address_2_field");
    if (self.include_shipping){
      jQuery("<p id='wc-peya-map-wrapper'><div id='wc-peya-map-s' style='height: 400px;' ></div></p>").insertAfter("#shipping_address_2_field");
    }

    const mapb = new google.maps.Map(document.getElementById("wc-peya-map-b"), {
      zoom: 13,
    });
    self.mapb = mapb;

    var maps;
    if (self.include_shipping){
      maps = new google.maps.Map(document.getElementById("wc-peya-map-s"), {
        zoom: 13,
      });
      self.maps = maps;
    }

    var geocoder = new google.maps.Geocoder();
    var geocoder2 = new google.maps.Geocoder();
    geocoder.geocode( {'address' : jQuery(self.billing.input).val() }, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
          self.mapb.setCenter(results[0].geometry.location);
          var marker = new google.maps.Marker({
      			position: results[0].geometry.location,
      			map: mapb});
            self.markerb = marker;           
            jQuery(self.billing.lat).val(results[0].geometry.location.lat());
            jQuery(self.billing.lng).val(results[0].geometry.location.lng());
      } else {
        geocoder2 = new google.maps.Geocoder();
        geocoder2.geocode( {'address' : peya_geo_settings.init_billing_address }, function(results, status) {
          if (status == google.maps.GeocoderStatus.OK) {
              self.mapb.setCenter(results[0].geometry.location);
              var marker = new google.maps.Marker({
          			position: results[0].geometry.location,
          			map: mapb});
                self.markerb = marker;
                jQuery(self.billing.lat).val(results[0].geometry.location.lat());
                jQuery(self.billing.lng).val(results[0].geometry.location.lng());              
          }
        });
      }
    });

    if (self.include_shipping){
      geocoder.geocode( {'address' : jQuery(self.shipping.input).val() }, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            self.maps.setCenter(results[0].geometry.location);
        var marker = new google.maps.Marker({
          position: results[0].geometry.location,
          map: maps});
          self.markers = marker;
          jQuery(self.shipping.lat).val(results[0].geometry.location.lat());
          jQuery(self.shipping.lng).val(results[0].geometry.location.lng());     
        }  else {
          geocoder2 = new google.maps.Geocoder();
          geocoder2.geocode( {'address' : peya_geo_settings.init_billing_address }, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                self.mapb.setCenter(results[0].geometry.location);
                var marker = new google.maps.Marker({
            			position: results[0].geometry.location,
            			map: mapb});
                 self.markerb = marker;
                 jQuery(self.shipping.lat).val(results[0].geometry.location.lat());
                 jQuery(self.shipping.lng).val(results[0].geometry.location.lng());  
            }
          });
        }

      });
    }

    google.maps.event.addListener(self.mapb, 'click', function(event) {
      var obj = self.billing;

      jQuery(obj.lat).val(event.latLng.lat());
      jQuery(obj.lng).val(event.latLng.lng());

      self.markerb.setPosition(event.latLng);
      var geocoder = geocoder = new google.maps.Geocoder();
      geocoder.geocode({ 'latLng': event.latLng }, function (results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
          if (results[1]) {
            self.fillInAddress(obj, results[1]);
            jQuery('body').trigger('update_checkout', { update_shipping_method: true });
          }
        }
      });

    });

    if (self.include_shipping){
      google.maps.event.addListener(self.maps, 'click', function(event) {
        var obj = self.shipping;

        jQuery(obj.lat).val(event.latLng.lat());
        jQuery(obj.lng).val(event.latLng.lng());

        self.markers.setPosition(event.latLng);
        var geocoder = geocoder = new google.maps.Geocoder();
        geocoder.geocode({ 'latLng': event.latLng }, function (results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            if (results[1]) {
              self.fillInAddress(obj, results[1]);
              jQuery('body').trigger('update_checkout', { update_shipping_method: true });
            }
          }
        });

      });
    }

  },

  initAutoCompleteSection: function(obj){
    var self = this;
    jQuery(document).ready(function(){
      jQuery(obj.lat + "_field").hide();
      jQuery(obj.lng + "_field").hide();
    });

    obj.autocomplete =  new google.maps.places.Autocomplete(document.querySelector(obj.input), {
      fields: ["address_components", "geometry", "icon", "name"],
      types: ["address"],
    });

    jQuery(obj.input).focus();
    obj.autocomplete.addListener("place_changed", function(){ self.fillInAddress(obj, obj.autocomplete.getPlace())});

  },

  fillInAddress: function(obj, place){
    var self = this;
    jQuery(obj.city).val('');
    jQuery(obj.postcode).val('');
    jQuery(obj.lat).val('');
    jQuery(obj.lng).val('');

    if(place.formatted_address){
      jQuery(obj.input).val(place.formatted_address);
    }

    for (const component of place.address_components) {
      const componentType = component.types[0];
      switch (componentType) {
        case "postal_code":
          jQuery(obj.postcode).val(`${component.long_name}`);
          break;
        case "sublocality_level_1":
          jQuery(obj.city).val(`${component.long_name}`);
          break;
        case "locality":
          jQuery(obj.city).val(`${component.long_name}`);
          break;
        case "sublocality":
          jQuery(obj.city).val(`${component.long_name}`);
          break;
        case "administrative_area_level_2":
          jQuery(obj.city).val(`${component.long_name}`);
          break;
      }

      jQuery(obj.lat).val(place.geometry.location.lat());
      jQuery(obj.lng).val(place.geometry.location.lng());

      if(  obj.name === self.billing.name){
        self.markerb.setPosition(new google.maps.LatLng(place.geometry.location.lat(),place.geometry.location.lng()));
        self.mapb.setCenter(new google.maps.LatLng(place.geometry.location.lat(),place.geometry.location.lng()));
      } else {
        if (self.include_shipping){
          self.markers.setPosition(new google.maps.LatLng(place.geometry.location.lat(),place.geometry.location.lng()));
          self.maps.setCenter(new google.maps.LatLng(place.geometry.location.lat(),place.geometry.location.lng()));
        }
      }
    }
  }
}
