<template>
  <section class="parrotSection">
    <div class="field">
      <div class="control">
        <input 
          v-model="message"
          class="input is-info" 
          type="text" 
          placeholder="Adresse départ" 
          @keyup.enter="showTownNotWorking"
        >
      </div>
    </div>
    <p />
    <p>Destination selectionnée : {{ message | Upper }}</p>
    <mapbox 
      access-token="pk.eyJ1IjoiY29udGFjdC1jb3Zpdm8iLCJhIjoiY2pqeWU3aTBjYWxtajN3cDEzbWFuYm40bCJ9.a2YJ0ZzW2AOWIeefE88OHg"
      :map-options="{
        style: 'mapbox://styles/mapbox/streets-v9',
        center: [6.1768515,48.6937863],
        zoom: 13
      }"
    />
  </section>
</template>
 
<script>
import Mapbox from 'mapbox-gl-vue';
/*
  * Ok the code under this is the main componant !!
  */
export default 
{
  name: "Parrot",
  components: {
    'mapbox': Mapbox
  },
  filters: {
    // Filter definitions
    Upper(value) {
      return value.toUpperCase();
    }
  },
  data () {
    return {
      message: '', // very important, binding variable should be initialized ☢️
    }
  },
  methods: {
    showTownNotWorking(){
      console.log(this)
      console.log(this.$dialog)
      this.$dialog.alert({
        title: 'Erreur',
        message: `Maheuresement ${this.message} n'est pas encore une ville que nous deservons`,
        type: 'is-danger',
        hasIcon: true,
        icon: 'times-circle',
        iconPack: 'fa'
      })
    }
  },
}
</script>
 
 <!-- & You can create style for components ONLY 😇 -->
<style scoped lang="scss">
  // Import Bulma and Buefy styles
  @import '~bulma';
  @import '~buefy/src/scss/buefy';
  $code-family: "Helvetica";
  #map{
    width: 100%;
    height: 350px;
  }
  .parrotSection{
    font-size: 25px;
    p{
      color: $white;
    }
  }
</style>