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
    <input type="hidden" :name="streetaddress" :value="valstreetAddress">
    <input type="hidden" :name="postalcode" :value="valpostalCode">
    <input type="hidden" :name="addresslocality" :value="valaddressLocality">
    <input type="hidden" :name="addresscountry" :value="valaddressCountry">
    <input type="hidden" :name="longitude" :value="vallongitude">
    <input type="hidden" :name="latitude" :value="vallatitude">
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
          valstreetAddress: '',
          valpostalCode: '',
          valaddressLocality: '',
          valaddressCountry: '',
          vallongitude: 0,
          vallatitude: 0
        }
      },
      formattedDisplay (result) {
        let resultToShow = `${result.streetAddress} ${result.postalCode} ${result.addressLocality} ${result.addressCountry}`;
        return resultToShow.trim();
      },
      onSelected (value) {
        for(let property in value.selectedObject){
          this['val'+property] = typeof value.selectedObject[property] === "string" ? value.selectedObject[property].trim() : value.selectedObject[property]; 
        }
      }
    }
  }
</script>