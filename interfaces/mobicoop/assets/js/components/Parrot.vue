<template>
  <section class="parrotSection">
    <div class="field">
      <div class="control">
        <input 
          v-model="message" 
          class="input is-info" 
          type="text" 
          placeholder="Adresse départ"
        >
      </div>
    </div>
    <p>Le message est : {{ message | Upper }}</p>
  </section>
</template>
 
<script>
console.log('jojo')

/*
  * Ok the code under this is the main componant !!
  */
export default {
  name: "Parrot", //Component name for identifiation of course
  filters: {
    // Filter definitions
    Upper(value) {
      return value.toUpperCase();
    }
  },
  data () {
    return {
      message: '' // very important, binding variable should be initialized ☢️
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
  #tets{
    text-align: center !important;
  }
  .parrotSection{
    font-size: 25px;
    p{
      color: $white;
    }
  }
</style>