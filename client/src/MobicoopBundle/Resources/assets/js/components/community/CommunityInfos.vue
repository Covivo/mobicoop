<template>
  <v-container>
    <v-avatar
      color="grey lighten-3"
      size="225"
    >
      <img
        v-if="!coverImage"
        src="/images/avatar.svg"
        alt="avatar"
      >
      <img
        v-else
        src="coverImage.url"
        alt="avatar"
      >
    </v-avatar>
  </v-container>
</template>
<script>
export default {
  props:{
    coverImage: {
      type: Object,
      default: null
    }
  },
}
</script>