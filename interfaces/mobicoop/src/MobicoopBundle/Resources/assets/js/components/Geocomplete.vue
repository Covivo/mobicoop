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
  const defaultString = {
    type: String,
    default: ''
  }
  export default {
    components: { Autocomplete },
    name: "geocomplete",
    props: {
      url: defaultString,
      name: defaultString,
      required: defaultString,
      placeholder: defaultString,
      iclass: defaultString,
      streetaddress: defaultString,
      postalcode: defaultString,
      addresslocality: defaultString,
      addresscountry: defaultString,
      longitude: defaultString,
      latitude: defaultString
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
          valLongitude: 0,
          valLatitude: 0
        }
      },
      formattedDisplay (result) {
        let resultToShow = `${result.streetAddress} ${result.postalCode} ${result.addressLocality} ${result.addressCountry}`;
        return resultToShow.trim();
      },
      onSelected (value) {
        for(let property in value.selectedObject){
          this[property] = value.selectedObject[property].trim(); 
        }
      }
    }
  }
</script>