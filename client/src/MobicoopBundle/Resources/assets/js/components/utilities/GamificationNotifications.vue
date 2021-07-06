<template>
  <div />
</template>
<script>
export default {
  computed:{
    gamificationNotifications(){
      return this.$store.getters['gn/gamificationNotifications'];
    }
  },
  watch:{
    gamificationNotifications(newVersion, oldVersion){
      console.log("Changement gamificationNotifications !");
    }
  }
}
</script>