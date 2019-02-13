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
    name: "mautocomplete",
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
        return (result.streetAddress + ' ' + result.postalCode + ' ' + result.addressLocality + ' ' + result.addressCountry).trim();  
      },
      onSelected (value) {
        this.valStreetAddress = value.selectedObject.streetAddress.trim();
        this.valPostalCode = value.selectedObject.postalCode.trim();
        this.valAddressLocality = value.selectedObject.addressLocality.trim();
        this.valAddressCountry = value.selectedObject.addressCountry.trim();
        this.valLongitude = value.selectedObject.longitude;
        this.valLatitude = value.selectedObject.latitude;
      }
    }
  }
</script>