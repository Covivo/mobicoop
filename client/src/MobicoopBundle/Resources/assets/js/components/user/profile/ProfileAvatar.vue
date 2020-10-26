<template>
  <div style="position:relative;">
    <v-img
      aspect-ratio="2"
      :src="avatar"
    />
    <v-btn
      v-if="experienced"
      color="yellow"
      class="white--text"
      dark
      fab
      small
      style="position:absolute;top:10px;right:10px"
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