<template>
  <v-container>
    <v-avatar
      color="grey lighten-3"
      size="225"
    >
      <img
        src="https://vuetifyjs.com/apple-touch-icon-180x180.png"
        alt="avatar"
      >
    </v-avatar>
  </v-container>
</template>
<script>
export default {
  props:{
    paths: {
      type: Object,
      default: null
    }
  },
}
</script>