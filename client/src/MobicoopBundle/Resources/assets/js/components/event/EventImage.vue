<template>
  <v-img
    v-if="event.externalImageUrl"
    :src="event.externalImageUrl"
    width="225"
    height="200"
    :aspect-ratio="1"
    :alt="event.name"
    contain
  />
  <v-img
    v-else-if="event.images[0]"
    :src="event['images'][0]['versions']['square_250']"
    width="225"
    height="200"
    :aspect-ratio="1"
    :alt="event.name"
    contain
  />
  <v-img
    v-else-if="event.community"
    :src="event.community.image"
    width="225"
    height="200"
    :aspect-ratio="1"
    :alt="event.name"
    contain
  />
  <v-icon
    v-else
    class="mt-5"
    x-large
  >
    mdi-calendar-month
  </v-icon>
</template>
<script>
export default {
  props: {
    event: {
      type: Object,
      default: () => { }
    }
  }
}
</script>
