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
      v-if="experienced"
      color="yellow"
      class="white--text"
      dark
      fab
      small
      style="position:absolute"
      :style="(!minimized) ? 'position:absolute;top:10%;right:15%' : 'position:absolute;top:10%;right:20%;height:15px;width:15px;'"
    >
      <v-icon :class="(!minimized) ? 'display-1' : 'caption'">
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
    },
    minimized:{
      type: Boolean,
      default:false
    }
  }
}
</script>