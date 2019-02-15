<template>
  <div>
    <autocomplete
      :source="url"
      :results-display="formattedDisplay"
      :name="name"
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
      name: {
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
      }
    },
    data () {
      return this.initialData();
    },
    methods: {
      initialData(){
        return {
          valStreetAddress: '',
          valPostalCode: '',
          valAddressLocality: '',
          valAddressCountry: '',
          valLongitude: '0',
          valLatitude: '0'
        }
      },
      formattedDisplay (result) {
        return (
            (result.streetAddress ? result.streetAddress + ' '  : '') + 
            (result.postalCode ? result.postalCode + ' ' : '') +
            (result.addressLocality ? result.addressLocality + ' ' : '') + 
            (result.addressCountry ? result.addressCountry : '')
        ).trim();
      },
      onSelected (value) {
        for(let property in value.selectedObject){
          this[property] = value.selectedObject[property].trim(); 
        }
      }
    }
  }
</script>