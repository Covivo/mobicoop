<template>
  <section class="">
    <input v-model="message" placeholder="modifiez-moi">
    <p>Le message est : {{ message | Upper }}</p>
  </section>
</template>
 
<script>
  /*
  * Ok the code under this is the main componant !!
  */
  export default {
    data () {
      return {
        message: '' // very important, binding variable should be initialized ☢️
      }
    },
    name: "parrot", //Component name for identifiation of course
    filters: {
      // Filter definitions
      Upper(value) {
        return value.toUpperCase();
      }
    }
  }
</script>
 
 <!-- & You can create style for components ONLY 😇 -->
<style scoped lang="scss">
  
</style>