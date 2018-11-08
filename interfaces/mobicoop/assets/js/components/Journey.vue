<template>
  <div>
    {{ info }}
    {{ apiUri | raw }}
  </div>
</template>
<script>
import axios from 'axios';
/*
  * Ok the code under this is the main componant !!
  */
export default {
  name: "Journey",
  props: ['apiUri'],
  data () {
    return {
      info: null
    }
  },
  mounted () {
    axios
      .get('http://localhost:8080/external_journey_providers.json')
      .then((response) =>{
        console.log(response);
        this.info = response.data;
      });
  }
}
</script>