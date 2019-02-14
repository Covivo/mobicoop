<template>
  <div>
    <autocomplete
      :source="url"
      :results-display="formattedDisplay"
      :placeholder="placeholder"
      :input-class="iclass"
      :required="required"
      @selected="onSelected"
        >
    </autocomplete>
    <input type="hidden" :name="streetaddress" :value="valStreetAddress">
    <input type="hidden" :name="postalcode" :value="valPostalCode">
    <input type="hidden" :name="addresslocality" :value="valAddressLocality">
    <input type="hidden" :name="addresscountry" :value="valAddressCountry">
    <input type="hidden" :name="longitude" :value="valLongitude">
    <input type="hidden" :name="latitude" :value="valLatitude">
  </div>
</template>

<script>
  import Autocomplete from 'vuejs-auto-complete';
  export default {
    components: { Autocomplete },
    name: "geocomplete",
    props: {
      url: {
        type: String,
        default: ''
      },
      required: {
        type: String,
        default: ''
      },
      placeholder: {
        type: String,
        default: ''
      },
      iclass: {
        type: String,
        default: ''
      },
      streetaddress: {
        type: String,
        default: ''
      },
      postalcode: {
        type: String,
        default: ''
      },
      addresslocality: {
        type: String,
        default: ''
      },
      addresscountry: {
        type: String,
        default: ''
      },
      longitude: {
        type: String,
        default: ''
      },
      latitude: {
        type: String,
        default: ''
      },
    },
    data () {
      return {
        valStreetAddress: '',
        valPostalCode: '',
        valAddressLocality: '',
        valAddressCountry: '',
        valLongitude: 0,
        valLatitude: 0
      }
    },
    methods: {
      formattedDisplay (result) {
        return (
            (result.streetAddress ? result.streetAddress + ' '  : '') + 
            (result.postalCode ? result.postalCode + ' ' : '') +
            (result.addressLocality ? result.addressLocality + ' ' : '') + 
            (result.addressCountry ? result.addressCountry : '')
        ).trim();  
      },
      onSelected (value) {
        if (value.selectedObject.streetAddress) this.valStreetAddress = value.selectedObject.streetAddress.trim();
        if (value.selectedObject.postalCode) this.valPostalCode = value.selectedObject.postalCode.trim();
        if (value.selectedObject.addressLocality) this.valAddressLocality = value.selectedObject.addressLocality.trim();
        if (value.selectedObject.addressCountry) this.valAddressCountry = value.selectedObject.addressCountry.trim();
        if (value.selectedObject.longitude) this.valLongitude = value.selectedObject.longitude;
        if (value.selectedObject.latitude) this.valLatitude = value.selectedObject.latitude;
      }
    }
  }
</script>