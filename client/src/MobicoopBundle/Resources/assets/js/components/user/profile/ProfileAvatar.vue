<template>
  <div
    style="position:relative;"
    class="text-center"
  >
    <v-avatar
      color="grey lighten-3"
      width="70%"
      height="auto"
    >
      <v-img
        :src="avatar"
        class="align-self-center"
      />
    </v-avatar>
    <v-btn
      v-if="!experienced"
      color="yellow"
      class="white--text"
      dark
      fab
      small
      style="position:absolute;top:5%;right:5%px"
    >
      <v-icon class="display-1">
        mdi-star
      </v-icon>
    </v-btn>
  </div>
</template>
<script>
export default {
  props:{
    avatar:{
      type:String,
      default: null
    },
    experienced:{
      type:Boolean,
      default:false
    }
  }
}
</script>