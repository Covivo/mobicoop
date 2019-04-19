<template>
  <div />
</template>

<script>
// Requirement libs
import axios from 'axios';

export default {
  props: {
    originLatitude: {
      type: String,
      default: ""
    },
    originLongitude: {
      type: String,
      default: ""
    },
    destinationLatitude: {
      type: String,
      default: ""
    },
    destinationLongitude: {
      type: String,
      default: ""
    },
  },
  
  mounted () {
    axios
      .get(this.constructProviderUrl())
      .then(res => {
        let providersList = res.data
        console.log (providersList);
      })
  },
  methods: {
    constructProviderUrl() {
      let providerUrl
      return providerUrl = window.location.origin+'/journey/rdex'
    },
    constructJourneyUrl() {
      let journeyUrl
      return journeyUrl = `${this.baseUrl}/external_jouneys?provider=mobicoop&driver=1&passenger=1&from_latitude=${this.destinationLatitude}&from_longitude=${this.destinationLongitude}&to_latitude=${this.originLatitude}&to_longitude=${this.originLongitude}`
    }
  },
};
</script>
